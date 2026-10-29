<?php

declare(strict_types=1);

namespace App\Data;

class Probabilities
{
    public function __construct(
        public readonly float $pHome,
        public readonly float $pDraw,
        public readonly float $pAway,
        public readonly float $expHomeGoals,
        public readonly float $expAwayGoals,
        public readonly array $mostLikelyScore,
        public readonly array $topScorelines,
        public readonly array $scoreMatrix,
    ) {
    }

    public function toArray(): array
    {
        return [
            'p_home' => round($this->pHome, 4),
            'p_draw' => round($this->pDraw, 4),
            'p_away' => round($this->pAway, 4),
            'exp_home_goals' => round($this->expHomeGoals, 2),
            'exp_away_goals' => round($this->expAwayGoals, 2),
            'most_likely_score' => $this->mostLikelyScore,
            'top_scorelines' => $this->topScorelines,
        ];
    }
}

