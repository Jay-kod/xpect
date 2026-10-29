<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

class Metrics
{
    public const EPSILON = 1e-15;

    /**
     * Compute accuracy (fraction of correct predictions).
     * @param array<array{predicted: string, actual: string}> $items
     */
    public function accuracy(array $items): float
    {
        if (empty($items)) {
            return 0.0;
        }

        $correct = 0;
        foreach ($items as $item) {
            if ($item['predicted'] === $item['actual']) {
                $correct++;
            }
        }

        return $correct / count($items);
    }

    /**
     * Compute multi-class Log Loss (Cross Entropy). Lower is better.
     * @param array<array{p_home: float, p_draw: float, p_away: float, actual: string}> $items
     */
    public function logLoss(array $items): float
    {
        if (empty($items)) {
            return 0.0;
        }

        $totalLoss = 0.0;
        foreach ($items as $item) {
            $p = match ($item['actual']) {
                'H' => $item['p_home'],
                'D' => $item['p_draw'],
                'A' => $item['p_away'],
                default => 0.0,
            };

            $clampedP = max(self::EPSILON, min(1.0 - self::EPSILON, (float) $p));
            $totalLoss += -log($clampedP);
        }

        return $totalLoss / count($items);
    }

    /**
     * Compute multi-class Brier score. Lower is better.
     * sum((p - y)^2) for H, D, A.
     * @param array<array{p_home: float, p_draw: float, p_away: float, actual: string}> $items
     */
    public function brier(array $items): float
    {
        if (empty($items)) {
            return 0.0;
        }

        $totalBrier = 0.0;
        foreach ($items as $item) {
            $yH = $item['actual'] === 'H' ? 1.0 : 0.0;
            $yD = $item['actual'] === 'D' ? 1.0 : 0.0;
            $yA = $item['actual'] === 'A' ? 1.0 : 0.0;

            $loss = pow($item['p_home'] - $yH, 2)
                  + pow($item['p_draw'] - $yD, 2)
                  + pow($item['p_away'] - $yA, 2);

            $totalBrier += $loss;
        }

        return $totalBrier / count($items);
    }

    /**
     * Single prediction Log Loss.
     */
    public function singleLogLoss(float $prob): float
    {
        $clamped = max(self::EPSILON, min(1.0 - self::EPSILON, $prob));
        return -log($clamped);
    }

    /**
     * Single prediction Brier score.
     */
    public function singleBrier(float $pH, float $pD, float $pA, string $actual): float
    {
        $yH = $actual === 'H' ? 1.0 : 0.0;
        $yD = $actual === 'D' ? 1.0 : 0.0;
        $yA = $actual === 'A' ? 1.0 : 0.0;

        return pow($pH - $yH, 2) + pow($pD - $yD, 2) + pow($pA - $yA, 2);
    }

    /**
     * Compute 10-bin calibration table: predicted probability vs observed empirical frequency.
     * @param array<array{prob: float, occurred: bool}> $events
     */
    public function calibration(array $events): array
    {
        $bins = [];
        for ($i = 0; $i < 10; $i++) {
            $bins[$i] = [
                'range' => sprintf('%d-%d%%', $i * 10, ($i + 1) * 10),
                'count' => 0,
                'sum_predicted' => 0.0,
                'occurred' => 0,
            ];
        }

        foreach ($events as $event) {
            $p = max(0.0, min(0.9999, $event['prob']));
            $binIndex = (int) floor($p * 10);
            $bins[$binIndex]['count']++;
            $bins[$binIndex]['sum_predicted'] += $p;
            if ($event['occurred']) {
                $bins[$binIndex]['occurred']++;
            }
        }

        $table = [];
        foreach ($bins as $idx => $bin) {
            $cnt = $bin['count'];
            $avgPred = $cnt > 0 ? $bin['sum_predicted'] / $cnt : ($idx * 0.1 + 0.05);
            $obsFreq = $cnt > 0 ? $bin['occurred'] / $cnt : 0.0;

            $table[] = [
                'bin' => $bin['range'],
                'samples' => $cnt,
                'avg_predicted' => round($avgPred * 100, 1),
                'observed_freq' => round($obsFreq * 100, 1),
            ];
        }

        return $table;
    }
}

