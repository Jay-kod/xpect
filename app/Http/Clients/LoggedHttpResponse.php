<?php

declare(strict_types=1);

namespace App\Http\Clients;

use JsonException;

class LoggedHttpResponse
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
        public readonly array $headers,
        public readonly int $durationMs,
        public readonly bool $servedFromCache,
        public readonly int $apiCallId,
        public readonly ?string $error = null,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
    ) {
    }

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300 && empty($this->error);
    }

    public function failed(): bool
    {
        return !$this->successful();
    }

    /**
     * @throws JsonException
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        if ($key === null) {
            return $data;
        }

        return data_get($data, $key, $default);
    }
}

