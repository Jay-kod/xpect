<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

class TeamFeatures
{
    public function __construct(
        public readonly int $teamId,
        public readonly string $teamName,
        public readonly CarbonImmutable $asOf,
        public readonly float $homeAttackStrength,
        public readonly float $homeDefenceStrength,
        public readonly float $awayAttackStrength,
        public readonly float $awayDefenceStrength,
        public readonly int $matchesSampleCount,
        public readonly bool $lowConfidence,
        public readonly int $formPointsLast5,
        public readonly ?int $restDays,
        public readonly float $leagueAvgHomeGoals,
        public readonly float $leagueAvgAwayGoals,
        public readonly float $homeXgAttackStrength = 1.0,
        public readonly float $homeXgDefenceStrength = 1.0,
        public readonly float $awayXgAttackStrength = 1.0,
        public readonly float $awayXgDefenceStrength = 1.0,
        public readonly int $xgSampleCount = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'team_id' => $this->teamId,
            'team_name' => $this->teamName,
            'as_of' => $this->asOf->toIso8601String(),
            'home_attack_strength' => round($this->homeAttackStrength, 4),
            'home_defence_strength' => round($this->homeDefenceStrength, 4),
            'away_attack_strength' => round($this->awayAttackStrength, 4),
            'away_defence_strength' => round($this->awayDefenceStrength, 4),
            'home_xg_attack_strength' => round($this->homeXgAttackStrength, 4),
            'home_xg_defence_strength' => round($this->homeXgDefenceStrength, 4),
            'away_xg_attack_strength' => round($this->awayXgAttackStrength, 4),
            'away_xg_defence_strength' => round($this->awayXgDefenceStrength, 4),
            'xg_sample_count' => $this->xgSampleCount,
            'matches_sample_count' => $this->matchesSampleCount,
            'low_confidence' => $this->lowConfidence,
            'form_points_last_5' => $this->formPointsLast5,
            'rest_days' => $this->restDays,
            'league_avg_home_goals' => round($this->leagueAvgHomeGoals, 4),
            'league_avg_away_goals' => round($this->leagueAvgAwayGoals, 4),
        ];
    }
}

