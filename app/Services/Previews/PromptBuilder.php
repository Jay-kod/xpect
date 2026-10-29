<?php

declare(strict_types=1);

namespace App\Services\Previews;

use App\Models\Prediction;

class PromptBuilder
{
    public const PROMPT_VERSION = 'v1.0.0';

    /**
     * Build the structured payload for the LLM.
     * Contains ONLY structured mathematical and statistical numbers.
     */
    public function buildPayload(Prediction $prediction): array
    {
        $match = $prediction->match;
        $homeTeam = $match?->homeTeam;
        $awayTeam = $match?->awayTeam;
        $competition = $match?->competition;

        $pHome = round((float) $prediction->p_home * 100, 1);
        $pDraw = round((float) $prediction->p_draw * 100, 1);
        $pAway = round((float) $prediction->p_away * 100, 1);

        $expHome = round((float) $prediction->exp_home_goals, 2);
        $expAway = round((float) $prediction->exp_away_goals, 2);

        $inputJson = [
            'match' => [
                'id' => $match?->id,
                'competition' => $competition?->name,
                'season' => $match?->season,
                'matchday' => $match?->matchday,
                'kickoff_utc' => $match?->kickoff_at?->toIso8601String(),
            ],
            'teams' => [
                'home' => [
                    'name' => $homeTeam?->name,
                    'short_name' => $homeTeam?->short_name,
                ],
                'away' => [
                    'name' => $awayTeam?->name,
                    'short_name' => $awayTeam?->short_name,
                ],
            ],
            'model_output' => [
                'model_version' => $prediction->model_version,
                'probabilities' => [
                    'home_win_pct' => $pHome,
                    'draw_pct' => $pDraw,
                    'away_win_pct' => $pAway,
                ],
                'expected_goals' => [
                    'home' => $expHome,
                    'away' => $expAway,
                    'total' => round($expHome + $expAway, 2),
                ],
                'top_scorelines' => $prediction->top_scorelines ?? [],
                'low_confidence' => (bool) $prediction->low_confidence,
            ],
            'features' => $prediction->features ?? [],
        ];

        return [
            'system' => $this->systemPrompt(),
            'prompt_version' => self::PROMPT_VERSION,
            'input_json' => $inputJson,
        ];
    }

    /**
     * System prompt with strict constraints:
     * - Model decides the numbers; LLM explains only.
     * - No outside facts or player names.
     * - Express uncertainty.
     * - Word limit: 150-220 words.
     */
    public function systemPrompt(): string
    {
        return <<<PROMPT
You are Xpect's statistical football match analyst. Your job is to explain the mathematical Poisson model output for an upcoming match to a single informed reader.

STRICT INSTRUCTIONS:
1. Ground every claim STRICTLY in the provided input JSON.
2. NEVER mention outside football facts, player names, manager quotes, weather, historical head-to-head records, or transfer gossip not provided in the input.
3. Every number, percentage, or scoreline you cite MUST come directly from the input JSON. Do not fabricate or estimate numbers.
4. Express uncertainty realistically: football is high-variance and probabilistic; probabilities indicate mathematical expectations, never certainty.
5. Do not contradict the model's win/draw/loss probabilities or expected scorelines.
6. Length: Keep the preview between 150 and 220 words, structured in two or three short, punchy paragraphs. Focus on expected goal balance, probability tilt, and the most probable scorelines.
PROMPT;
    }
}

