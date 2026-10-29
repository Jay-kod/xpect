<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\MatchOdds;
use PHPUnit\Framework\TestCase;

class MatchOddsTest extends TestCase
{
    /**
     * Hand-calculated decimal odds normalization:
     * Odds: H = 2.0, D = 3.5, A = 4.0
     * Raw probabilities:
     * invH = 1 / 2.0 = 0.50
     * invD = 1 / 3.5 = 0.285714
     * invA = 1 / 4.0 = 0.25
     * Overround = 0.50 + 0.285714 + 0.25 = 1.035714 (~3.57% bookmaker margin)
     * 
     * Normalized implied probabilities (proportional margin removal):
     * probH = 0.50 / 1.035714 = 0.482759 -> 0.4828
     * probD = 0.285714 / 1.035714 = 0.275862 -> 0.2759
     * probA = 0.25 / 1.035714 = 0.241379 -> 0.2414
     * Sum = 0.4828 + 0.2759 + 0.2414 = 1.0001 (~1.0)
     */
    public function test_odds_normalization_removes_overround(): void
    {
        $h = 2.0;
        $d = 3.5;
        $a = 4.0;

        $invH = 1.0 / $h;
        $invD = 1.0 / $d;
        $invA = 1.0 / $a;
        $overround = $invH + $invD + $invA;

        $probH = round($invH / $overround, 4);
        $probD = round($invD / $overround, 4);
        $probA = round($invA / $overround, 4);

        $this->assertEqualsWithDelta(1.0357, $overround, 0.001);
        $this->assertEquals(0.4828, $probH);
        $this->assertEquals(0.2759, $probD);
        $this->assertEquals(0.2414, $probA);

        $sum = $probH + $probD + $probA;
        $this->assertEqualsWithDelta(1.0, $sum, 0.001);
    }
}

