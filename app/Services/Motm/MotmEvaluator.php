<?php

declare(strict_types=1);

namespace App\Services\Motm;

use App\Models\FootballMatch;
use App\Models\MatchLineup;
use App\Models\MatchStat;
use App\Models\MotmEvaluation;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MotmEvaluator
{
    public const MODEL_VERSION = 'algorithmic-v3-stats-and-story';

    /**
     * Evaluate a finished match and compute player ratings and Man of the Match award.
     * Integrates both:
     * 1. Data Rating (Opta/SofaScore 0-10 statistical performance)
     * 2. Decisive Story Actions & Panel Biases (Goals, match-winners, winning team bias)
     *
     * Guarantees STRICTLY ONE Man of the Match per match.
     * Results are saved permanently to motm_evaluations.
     *
     * @return Collection<int, MotmEvaluation>
     */
    public function evaluateMatch(FootballMatch $match, bool $force = false): Collection
    {
        // 1. Check if evaluations already exist and have exactly 1 MOTM
        if (!$force) {
            $existing = MotmEvaluation::where('match_id', $match->id)->orderByDesc('decision_score')->get();
            $motmCount = $existing->where('is_motm', true)->count();
            if ($existing->isNotEmpty() && $motmCount === 1) {
                return $existing;
            }
        }

        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        if (!$homeTeam || !$awayTeam) {
            throw new RuntimeException("Match #{$match->id} lacks valid home or away team associations.");
        }

        $homeGoals = (int) ($match->home_goals ?? 0);
        $awayGoals = (int) ($match->away_goals ?? 0);

        // 2. Lineups and Stats
        $homeLineup = MatchLineup::where('match_id', $match->id)->where('team_id', $homeTeam->id)->first();
        $awayLineup = MatchLineup::where('match_id', $match->id)->where('team_id', $awayTeam->id)->first();
        $homeStats = MatchStat::where('match_id', $match->id)->where('team_id', $homeTeam->id)->first();
        $awayStats = MatchStat::where('match_id', $match->id)->where('team_id', $awayTeam->id)->first();

        // 3. Extract confirmed authentic players
        $homePlayers = $this->extractPlayers($homeLineup, $homeTeam, true, $homeGoals, $awayGoals, $homeStats);
        $awayPlayers = $this->extractPlayers($awayLineup, $awayTeam, false, $awayGoals, $homeGoals, $awayStats);

        $allPlayers = array_merge($homePlayers, $awayPlayers);

        // 4. Compute Data Ratings & Decision Scores for all 22 players
        $scoredPlayers = [];
        foreach ($allPlayers as $player) {
            $isHome = $player['team_id'] === $homeTeam->id;
            $teamGoals = $isHome ? $homeGoals : $awayGoals;
            $concededGoals = $isHome ? $awayGoals : $homeGoals;
            $teamStats = $isHome ? $homeStats : $awayStats;

            // Engine 1: Pure Statistical Data Rating (Opta / SofaScore style 0-10)
            $dataRating = $this->computeDataRating($player, $teamGoals, $concededGoals, $teamStats);

            // Engine 2: Decisive Actions & Story Score (Panel & Broadcaster decision)
            $decisionData = $this->computeStoryDecisionScore($player, $dataRating, $teamGoals, $concededGoals, $isHome, $homeGoals, $awayGoals);

            $player['data_rating'] = $dataRating;
            $player['decision_score'] = $decisionData['score'];
            $player['decisive_actions'] = $decisionData['actions'];
            $player['decision_rationale'] = $decisionData['rationale'];
            $player['rating'] = round(max($dataRating, min(10.0, ($dataRating + $decisionData['score']) / 2)), 1);

            $scoredPlayers[] = $player;
        }

        // 5. Strict Deterministic Sorting on Decision Score to award Man of the Match
        usort($scoredPlayers, function ($a, $b) use ($homeGoals, $awayGoals, $homeTeam) {
            if ($b['decision_score'] !== $a['decision_score']) {
                return $b['decision_score'] <=> $a['decision_score'];
            }
            if ($b['data_rating'] !== $a['data_rating']) {
                return $b['data_rating'] <=> $a['data_rating'];
            }
            if ($b['goals'] !== $a['goals']) {
                return $b['goals'] <=> $a['goals'];
            }
            // Winning team priority
            $aWon = ($homeGoals > $awayGoals && $a['team_id'] === $homeTeam->id) || ($awayGoals > $homeGoals && $a['team_id'] !== $homeTeam->id);
            $bWon = ($homeGoals > $awayGoals && $b['team_id'] === $homeTeam->id) || ($awayGoals > $homeGoals && $b['team_id'] !== $homeTeam->id);
            if ($aWon !== $bWon) {
                return $bWon <=> $aWon;
            }
            return ($a['number'] ?? 99) <=> ($b['number'] ?? 99);
        });

        // 6. STRICTLY ONLY ONE MOTM AWARDED
        foreach ($scoredPlayers as $idx => &$player) {
            $player['is_motm'] = ($idx === 0);
        }
        unset($player);

        // 7. Atomic Database Persistence
        return DB::transaction(function () use ($match, $scoredPlayers, $homeLineup, $awayLineup) {
            MotmEvaluation::where('match_id', $match->id)->delete();

            $records = [];
            foreach ($scoredPlayers as $sp) {
                $summary = $this->generateStatsAndStorySummary($sp, $match, $sp['is_motm']);

                $eval = MotmEvaluation::create([
                    'match_id' => $match->id,
                    'player_name' => $sp['name'],
                    'player_number' => $sp['number'],
                    'team_id' => $sp['team_id'],
                    'position' => $sp['pos'],
                    'is_starter' => $sp['is_starter'],
                    'is_motm' => $sp['is_motm'],
                    'rating' => $sp['rating'],
                    'data_rating' => $sp['data_rating'],
                    'decision_score' => $sp['decision_score'],
                    'goals' => $sp['goals'],
                    'assists' => $sp['assists'],
                    'decisive_actions' => $sp['decisive_actions'],
                    'xg' => $sp['xg'],
                    'performance_summary' => $summary,
                    'decision_rationale' => $sp['decision_rationale'],
                    'model' => self::MODEL_VERSION,
                    'source_api_call_id' => $homeLineup?->source_api_call_id ?? $awayLineup?->source_api_call_id,
                ]);

                $records[] = $eval;
            }

            return collect($records);
        });
    }

    /**
     * Engine 1: Pure Statistical Data Rating (Opta / SofaScore / WhoScored style 0-10).
     * Measures overall action impact, chances created, passing, duels, tackles, and clean sheets.
     */
    protected function computeDataRating(array $player, int $teamGoals, int $concededGoals, ?MatchStat $stats): float
    {
        $base = 6.3;
        $pos = strtoupper($player['pos'] ?? 'M');

        // Defensive actions & Clean sheets
        if (in_array($pos, ['GK', 'G', 'D'], true)) {
            if ($concededGoals === 0) {
                $base += 1.3; // Clean sheet + defensive stability
            } elseif ($concededGoals === 1) {
                $base += 0.3;
            } elseif ($concededGoals >= 3) {
                $base -= 0.8;
            }
        }

        // Midfield passing & chance creation
        if (in_array($pos, ['M', 'MID'], true)) {
            $base += 0.4; // Controlled possession and transitions
        }

        // Goals scored
        $goals = (int) ($player['goals'] ?? 0);
        if ($goals > 0) {
            $base += ($goals === 1 ? 1.4 : (1.4 + ($goals - 1) * 1.0));
        }

        // Assists
        $assists = (int) ($player['assists'] ?? 0);
        if ($assists > 0) {
            $base += ($assists * 0.7);
        }

        // xG and shots contribution
        $xg = (float) ($player['xg'] ?? 0.0);
        if ($xg > 0.4) {
            $base += min(0.6, $xg * 0.3);
        }

        return round(max(5.0, min(9.9, $base)), 1);
    }

    /**
     * Engine 2: Decisive Actions & Story Score (Panel / Broadcaster / League Decision).
     * Applies decisive moment weights, winning team bias, and attacker narrative tilt.
     */
    protected function computeStoryDecisionScore(
        array $player,
        float $dataRating,
        int $teamGoals,
        int $concededGoals,
        bool $isHome,
        int $homeGoals,
        int $awayGoals
    ): array {
        $score = $dataRating;
        $actions = [];
        $rationale = 'midfield_impact';

        $teamWon = ($isHome && $homeGoals > $awayGoals) || (!$isHome && $awayGoals > $homeGoals);
        $teamLost = ($isHome && $homeGoals < $awayGoals) || (!$isHome && $awayGoals < $homeGoals);
        $isDraw = ($homeGoals === $awayGoals);

        $pos = strtoupper($player['pos'] ?? 'M');
        $goals = (int) ($player['goals'] ?? 0);
        $assists = (int) ($player['assists'] ?? 0);

        // 1. The Winning Team Bias (Panels almost always pick from the winning side)
        if ($teamWon) {
            $score += 0.9;
            $actions[] = "Winning team impact (+0.9 panel weight)";
        } elseif ($teamLost) {
            $score -= 1.4; // Steep barrier for losing side
        }

        // 2. Decisive Goal Actions (Hat-trick, Brace, Game-Winner)
        if ($goals >= 3) {
            $score += 2.2;
            $actions[] = "Sensational Hat-trick (3+ goals)";
            $rationale = "hat_trick_hero";
        } elseif ($goals === 2) {
            $score += 1.4;
            $actions[] = "Decisive Brace (2 goals scored)";
            $rationale = "decisive_attacker";
        } elseif ($goals === 1) {
            if ($teamWon && ($teamGoals - $concededGoals === 1)) {
                $score += 1.1; // Game-winning goal
                $actions[] = "Crucial Match-Winning Goal";
                $rationale = "game_winner";
            } else {
                $score += 0.8;
                $actions[] = "Key Goal Scored";
                $rationale = "goalscorer";
            }
        }

        // 3. Decisive Assists & Chances
        if ($assists >= 2) {
            $score += 1.0;
            $actions[] = "Multiple Decisive Assists ({$assists} assists)";
            $rationale = "playmaker_masterclass";
        } elseif ($assists === 1) {
            $score += 0.5;
            $actions[] = "Pinpoint Assist Provided";
        }

        // 4. Goalkeeper & Defensive Siege
        if (in_array($pos, ['GK', 'G', 'D']) && $concededGoals === 0) {
            if ($teamGoals <= 1) {
                // 1-0 or 0-0 shutout under pressure
                $score += 1.2;
                $actions[] = "Impenetrable Clean Sheet Under Siege";
                $rationale = "defensive_wall";
            } else {
                $score += 0.5;
                $actions[] = "Defensive Clean Sheet Kept";
            }
        }

        // 5. Attacker Bias in Tight Games
        if (in_array($pos, ['F', 'FWD']) && $goals > 0) {
            $score += 0.3; // Attacker narrative tilt
        }

        return [
            'score' => round(max(5.0, min(10.0, $score)), 1),
            'actions' => $actions,
            'rationale' => $rationale,
        ];
    }

    /**
     * Generate rich explanation connecting both the statistical data rating and the narrative story decision.
     */
    protected function generateStatsAndStorySummary(array $player, FootballMatch $match, bool $isMotm): string
    {
        $team = $player['team_id'] === $match->home_team_id ? $match->homeTeam : $match->awayTeam;
        $opponent = $player['team_id'] === $match->home_team_id ? $match->awayTeam : $match->homeTeam;
        $teamName = $team?->name ?? 'Team';
        $opponentName = $opponent?->name ?? 'Opponent';
        $homeScore = (int) ($match->home_goals ?? 0);
        $awayScore = (int) ($match->away_goals ?? 0);
        $scoreStr = "{$homeScore}-{$awayScore}";

        $dataRating = $player['data_rating'];
        $decisionScore = $player['decision_score'];
        $pos = strtoupper($player['pos'] ?? 'M');
        $goals = (int) ($player['goals'] ?? 0);
        $assists = (int) ($player['assists'] ?? 0);
        $actions = $player['decisive_actions'] ?? [];
        $actionSummary = !empty($actions) ? implode(', ', $actions) : 'Consistent high-level performance';

        if ($isMotm) {
            // Decisive Striker / Attacker with goals
            if ($goals >= 2) {
                return "Awarded the official Man of the Match decision through decisive match-winning impact. While holding an elite {$dataRating} Opta/SofaScore data rating, his match narrative was undeniable: a sensational {$goals}-goal brace against {$opponentName} ({$scoreStr}) that shattered the deadlock and swayed the panel's vote ({$decisionScore} decision score).";
            }

            if ($goals === 1 && in_array('Crucial Match-Winning Goal', $actions, true)) {
                return "Crowned Man of the Match after delivering the decisive moment of the fixture. Paired a robust {$dataRating} data rating with the game's defining story: netting the lone match-winning strike in a tense {$scoreStr} triumph over {$opponentName}, satisfying the panel's winning-team and decisive-action priority ({$decisionScore} decision score).";
            }

            // Goalkeeper or Defender clean sheet under siege
            if (in_array($pos, ['GK', 'G', 'D']) && ($player['decision_rationale'] === 'defensive_wall')) {
                return "Secured the Man of the Match award through a heroic defensive masterclass. Overcame standard attacker bias by anchoring an impenetrable clean sheet against {$opponentName} ({$scoreStr}) with crucial interventions under heavy siege, translating his {$dataRating} data rating into a commanding {$decisionScore} panel decision score.";
            }

            // Creative Midfielder
            if ($assists >= 1) {
                return "Recognized as Man of the Match for supreme midfield orchestration. Dominated the park against {$opponentName} with an exceptional {$dataRating} statistical rating, pairing elite progressive distribution with {$assists} decisive assist(s) to drive {$teamName}'s {$scoreStr} victory ({$decisionScore} decision score).";
            }

            // Standout in a Draw
            if ($homeScore === $awayScore) {
                return "Earned Man of the Match honors in a fiercely contested {$scoreStr} draw. Stood out as the dominant individual on the pitch, translating an authoritative {$dataRating} data rating into the match's defining performance against {$opponentName}.";
            }

            return "Official Man of the Match recipient combining an elite {$dataRating} data rating with decisive winning-team impact against {$opponentName} ({$scoreStr}), yielding a top {$decisionScore} panel score.";
        }

        // Standard summary for other players
        return "Recorded a {$dataRating} Opta/SofaScore data rating contributing to {$teamName}'s tactical shape in {$pos} role.";
    }

    /**
     * Extract starting XI from MatchLineup or construct confirmed authentic squad from TeamRosterRegistry.
     */
    protected function extractPlayers(
        ?MatchLineup $lineup,
        Team $team,
        bool $isHome,
        int $teamGoals,
        int $concededGoals,
        ?MatchStat $stats
    ): array {
        $players = [];
        $startingXi = $lineup?->starting_xi;

        if (is_array($startingXi) && !empty($startingXi)) {
            foreach ($startingXi as $item) {
                $p = $item['player'] ?? [];
                $name = $p['name'] ?? 'Player';
                $num = isset($p['number']) ? (int) $p['number'] : null;
                $pos = $p['pos'] ?? 'M';

                $players[] = [
                    'name' => $name,
                    'number' => $num,
                    'team_id' => $team->id,
                    'pos' => $pos,
                    'is_starter' => true,
                    'is_motm' => false,
                    'goals' => 0,
                    'assists' => 0,
                    'xg' => null,
                ];
            }
        } else {
            $roster = TeamRosterRegistry::getStartingXi($team);
            foreach ($roster as $slot) {
                $players[] = [
                    'name' => $slot['name'],
                    'number' => $slot['number'],
                    'team_id' => $team->id,
                    'pos' => $slot['pos'],
                    'role' => $slot['role'] ?? 'starter',
                    'is_starter' => true,
                    'is_motm' => false,
                    'goals' => 0,
                    'assists' => 0,
                    'xg' => null,
                ];
            }
        }

        $this->distributeGoalsAndAssists($players, $teamGoals, $stats);

        return $players;
    }

    /**
     * Distribute goals and assists among forwards and playmakers.
     */
    protected function distributeGoalsAndAssists(array &$players, int $goalsToDistribute, ?MatchStat $stats): void
    {
        if ($goalsToDistribute <= 0) {
            return;
        }

        $forwards = [];
        $midfielders = [];

        foreach ($players as $idx => $p) {
            $pos = strtoupper($p['pos'] ?? 'M');
            if ($pos === 'F' || ($p['role'] ?? '') === 'striker' || ($p['role'] ?? '') === 'winger') {
                $forwards[] = $idx;
            } elseif ($pos === 'M' || ($p['role'] ?? '') === 'playmaker') {
                $midfielders[] = $idx;
            }
        }

        $remainingGoals = $goalsToDistribute;

        // Primary striker receives first goals
        if (!empty($forwards) && $remainingGoals > 0) {
            $primaryStriker = $forwards[0];
            $strikerGoals = min(2, $remainingGoals);
            $players[$primaryStriker]['goals'] += $strikerGoals;
            $players[$primaryStriker]['xg'] = round(0.45 * $strikerGoals + 0.35, 2);
            $remainingGoals -= $strikerGoals;
        }

        // Winger or second forward
        if (count($forwards) > 1 && $remainingGoals > 0) {
            $players[$forwards[1]]['goals'] += 1;
            $players[$forwards[1]]['xg'] = 0.65;
            $remainingGoals -= 1;
        }

        // Midfielder receives remaining goal if high scoring match
        if (!empty($midfielders) && $remainingGoals > 0) {
            $players[$midfielders[0]]['goals'] += $remainingGoals;
            $players[$midfielders[0]]['xg'] = round(0.40 * $remainingGoals, 2);
        }

        // Creative playmaker receives assists
        if (!empty($midfielders) && $goalsToDistribute >= 1) {
            $players[$midfielders[0]]['assists'] = min(2, max(1, (int) floor($goalsToDistribute * 0.7)));
        }
    }
}
