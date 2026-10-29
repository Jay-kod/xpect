<?php

declare(strict_types=1);

namespace App\Services\Previews;

class NumberCheckResult
{
    /**
     * @param string[] $disallowedNumbers
     */
    public function __construct(
        public readonly bool $flagged,
        public readonly array $disallowedNumbers,
        public readonly int $allowedNumbersCount
    ) {
    }

    public function isFlagged(): bool
    {
        return $this->flagged;
    }

    public function disallowedSummary(): string
    {
        if (empty($this->disallowedNumbers)) {
            return 'None (all numbers verified in model input)';
        }

        return implode(', ', $this->disallowedNumbers);
    }
}

