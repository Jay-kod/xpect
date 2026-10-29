<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Previews\NumberCheckGuard;
use PHPUnit\Framework\TestCase;

class NumberCheckGuardTest extends TestCase
{
    protected NumberCheckGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = new NumberCheckGuard();
    }

    public function test_passes_when_all_numbers_exist_in_input(): void
    {
        $inputJson = [
            'match' => ['matchday' => 10, 'season' => 2024],
            'model_output' => [
                'probabilities' => [
                    'home_win_pct' => 55.2,
                    'draw_pct' => 24.3,
                    'away_win_pct' => 20.5,
                ],
                'expected_goals' => [
                    'home' => 1.85,
                    'away' => 0.95,
                    'total' => 2.80,
                ],
                'top_scorelines' => [
                    ['home' => 2, 'away' => 1, 'prob' => 0.12],
                ],
            ],
        ];

        $validText = "Arsenal enter matchday 10 as favourites with a 55.2% win probability compared to 20.5% for Chelsea, with a draw estimated at 24.3%. The Poisson model projects 1.85 expected goals for the hosts against 0.95 for the visitors, for a total expectancy of 2.80. The most likely scoreline is a 2-1 victory.";

        $result = $this->guard->check($validText, $inputJson);

        $this->assertFalse($result->isFlagged());
        $this->assertEmpty($result->disallowedNumbers);
    }

    public function test_flags_when_text_contains_hallucinated_number(): void
    {
        $inputJson = [
            'model_output' => [
                'probabilities' => [
                    'home_win_pct' => 55.2,
                    'draw_pct' => 24.3,
                    'away_win_pct' => 20.5,
                ],
            ],
        ];

        // "47 goals", "last 6 games", "won 9-0" are hallucinated outside stats
        $hallucinatedText = "Arsenal have scored 47 goals in their last 6 games and have a 55.2% chance to win, with Chelsea holding 20.5%.";

        $result = $this->guard->check($hallucinatedText, $inputJson);

        $this->assertTrue($result->isFlagged());
        $this->assertContains('47', $result->disallowedNumbers);
    }
}

