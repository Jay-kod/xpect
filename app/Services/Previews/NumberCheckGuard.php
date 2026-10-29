<?php

declare(strict_types=1);

namespace App\Services\Previews;

class NumberCheckGuard
{
    /**
     * Inspect preview text against input JSON.
     * Returns true if flagged (contains numbers not found in input JSON).
     */
    public function check(string $text, array $inputJson): NumberCheckResult
    {
        $allowedFloats = $this->extractAllowedNumbers($inputJson);

        // Find all number tokens in the text
        // Handles integers, decimals, percentages, and scorelines (e.g. 2-1 or 52.2%)
        preg_match_all('/\b\d+(?:\.\d+)?%?\b|\b\d+-\d+\b/', $text, $matches);

        $disallowed = [];
        $tokens = $matches[0] ?? [];

        foreach ($tokens as $token) {
            $tokenClean = trim($token);

            // Handle scoreline format e.g. "2-1"
            if (str_contains($tokenClean, '-')) {
                $parts = explode('-', $tokenClean);
                foreach ($parts as $p) {
                    $num = (float) trim($p);
                    if (!$this->isNumberAllowed($num, $allowedFloats)) {
                        $disallowed[] = $tokenClean;
                        break;
                    }
                }
                continue;
            }

            // Strip trailing %
            $numStr = rtrim($tokenClean, '%');
            if (!is_numeric($numStr)) {
                continue;
            }

            $val = (float) $numStr;
            if (!$this->isNumberAllowed($val, $allowedFloats)) {
                $disallowed[] = $tokenClean;
            }
        }

        $disallowed = array_values(array_unique($disallowed));
        $isFlagged = !empty($disallowed);

        return new NumberCheckResult(
            flagged: $isFlagged,
            disallowedNumbers: $disallowed,
            allowedNumbersCount: count($allowedFloats)
        );
    }

    /**
     * Check if a numeric value is within acceptable precision/rounding of any allowed number.
     * @param float[] $allowedFloats
     */
    protected function isNumberAllowed(float $val, array $allowedFloats): bool
    {
        foreach ($allowedFloats as $allowed) {
            // Exact or close float equality
            if (abs($val - $allowed) < 0.05) {
                return true;
            }

            // Rounded representation match (e.g. 52.2% cited as 52%)
            if (round($allowed) == $val) {
                return true;
            }

            // 1-decimal rounding (e.g. 1.85 cited as 1.9 or 1.8)
            if (round($allowed, 1) == $val) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively collect all numbers from the input payload.
     * @return float[]
     */
    public function extractAllowedNumbers(array $data): array
    {
        $numbers = [];

        array_walk_recursive($data, function ($value) use (&$numbers) {
            if (is_numeric($value)) {
                $f = (float) $value;
                $numbers[] = $f;

                // If float probability 0.xx, add percentage representation
                if ($f > 0.0 && $f < 1.0) {
                    $numbers[] = round($f * 100, 1);
                    $numbers[] = (float) round($f * 100);
                }

                // If percentage 1..100, add float decimal representation
                if ($f >= 1.0 && $f <= 100.0) {
                    $numbers[] = round($f / 100, 4);
                }
            }
        });

        // Add 0-8 for standard scoreline bounds since model predicts 0..8 goals
        for ($i = 0; $i <= 8; $i++) {
            $numbers[] = (float) $i;
        }

        return array_values(array_unique($numbers));
    }
}

