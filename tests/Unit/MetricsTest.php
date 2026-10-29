<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Evaluation\Metrics;
use PHPUnit\Framework\TestCase;

class MetricsTest extends TestCase
{
    protected Metrics $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = new Metrics();
    }

    public function test_accuracy_with_hand_checked_values(): void
    {
        $this->assertSame(0.0, $this->metrics->accuracy([]));

        $items = [
            ['predicted' => 'H', 'actual' => 'H'],
            ['predicted' => 'D', 'actual' => 'H'],
            ['predicted' => 'A', 'actual' => 'A'],
            ['predicted' => 'H', 'actual' => 'D'],
        ];

        // 2 correct out of 4 = 0.5 (50%)
        $this->assertEquals(0.5, $this->metrics->accuracy($items));
    }

    public function test_log_loss_with_hand_checked_values(): void
    {
        $this->assertSame(0.0, $this->metrics->logLoss([]));

        $items = [
            ['p_home' => 0.6, 'p_draw' => 0.25, 'p_away' => 0.15, 'actual' => 'H'],
            ['p_home' => 0.2, 'p_draw' => 0.30, 'p_away' => 0.50, 'actual' => 'A'],
        ];

        // Item 1: -log(0.6) = 0.51082562376
        // Item 2: -log(0.5) = 0.69314718056
        // Mean = (0.51082562376 + 0.69314718056) / 2 = 0.60198640216
        $expected = (-log(0.6) + -log(0.5)) / 2;
        $this->assertEqualsWithDelta($expected, $this->metrics->logLoss($items), 0.0001);
    }

    public function test_log_loss_clamps_probabilities_preventing_infinity(): void
    {
        $items = [
            ['p_home' => 0.0, 'p_draw' => 0.0, 'p_away' => 1.0, 'actual' => 'H'],
        ];

        $loss = $this->metrics->logLoss($items);
        $this->assertFalse(is_infinite($loss));
        $this->assertFalse(is_nan($loss));
        $this->assertGreaterThan(30.0, $loss); // -log(1e-15) is ~34.5
    }

    public function test_brier_score_with_hand_checked_values(): void
    {
        $this->assertSame(0.0, $this->metrics->brier([]));

        $items = [
            ['p_home' => 0.6, 'p_draw' => 0.25, 'p_away' => 0.15, 'actual' => 'H'],
        ];

        // (0.6 - 1)^2 + (0.25 - 0)^2 + (0.15 - 0)^2
        // = 0.16 + 0.0625 + 0.0225 = 0.2450
        $this->assertEqualsWithDelta(0.2450, $this->metrics->brier($items), 0.0001);

        // Single brier method test
        $single = $this->metrics->singleBrier(0.6, 0.25, 0.15, 'H');
        $this->assertEqualsWithDelta(0.2450, $single, 0.0001);
    }

    public function test_single_log_loss(): void
    {
        $loss = $this->metrics->singleLogLoss(0.5);
        $this->assertEqualsWithDelta(-log(0.5), $loss, 0.0001);
    }

    public function test_calibration_generates_10_bins(): void
    {
        $events = [
            ['prob' => 0.15, 'occurred' => true],
            ['prob' => 0.12, 'occurred' => false],
            ['prob' => 0.75, 'occurred' => true],
            ['prob' => 0.72, 'occurred' => true],
        ];

        $cal = $this->metrics->calibration($events);
        $this->assertCount(10, $cal);

        // Bin 1: 10-20%
        $this->assertSame('10-20%', $cal[1]['bin']);
        $this->assertSame(2, $cal[1]['samples']);
        $this->assertEqualsWithDelta(13.5, $cal[1]['avg_predicted'], 0.1);
        $this->assertEqualsWithDelta(50.0, $cal[1]['observed_freq'], 0.1);

        // Bin 7: 70-80%
        $this->assertSame('70-80%', $cal[7]['bin']);
        $this->assertSame(2, $cal[7]['samples']);
        $this->assertEqualsWithDelta(73.5, $cal[7]['avg_predicted'], 0.1);
        $this->assertEqualsWithDelta(100.0, $cal[7]['observed_freq'], 0.1);
    }
}

