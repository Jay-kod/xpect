<?php

declare(strict_types=1);

namespace App\Services\Api;

class ApiKeyTestResult
{
    public function __construct(
        public readonly string $provider,
        public readonly bool $isValid,
        public readonly int $statusCode,
        public readonly int $durationMs,
        public readonly string $message,
        public readonly ?string $details = null,
        public readonly ?array $rawResponse = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'is_valid' => $this->isValid,
            'status_code' => $this->statusCode,
            'duration_ms' => $this->durationMs,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
