<?php

declare(strict_types=1);

namespace App\Http\Clients\RateLimiter;

use App\Models\ApiCall;
use Carbon\CarbonImmutable;
use RuntimeException;

class QuotaGuard
{
    /**
     * Check rate limits and daily quotas before sending a request.
     * Throws RuntimeException if daily quota is exhausted.
     * Sleeps briefly if approaching per-minute limits.
     */
    public function checkAndThrottle(string $source, bool $countsTowardQuota = true): void
    {
        $config = config("football.quotas.{$source}");
        if (!$config) {
            return;
        }

        $now = CarbonImmutable::now('UTC');

        // 1. Check daily quota (if configured)
        if ($countsTowardQuota && isset($config['per_day']) && $config['per_day'] !== null) {
            $reserve = (int) ($config['reserve'] ?? 0);
            $dailyLimit = (int) $config['per_day'];
            $maxAllowed = max(0, $dailyLimit - $reserve);

            // Count quota-consuming calls today UTC
            $usedToday = ApiCall::where('source', $source)
                ->where('counts_toward_quota', true)
                ->where('called_at', '>=', $now->startOfDay())
                ->count();

            if ($usedToday >= $maxAllowed) {
                throw new RuntimeException(
                    "Daily quota limit reached for [{$source}]. Used: {$usedToday}, Allowed (less reserve {$reserve}): {$maxAllowed}/{$dailyLimit}"
                );
            }
        }

        // 2. Check per-minute throttle
        if (isset($config['per_minute']) && $config['per_minute'] !== null) {
            $perMinuteLimit = (int) $config['per_minute'];
            $oneMinuteAgo = $now->subSeconds(60);

            $callsInLastMinute = ApiCall::where('source', $source)
                ->where('served_from_cache', false)
                ->where('called_at', '>=', $oneMinuteAgo)
                ->count();

            if ($callsInLastMinute >= $perMinuteLimit) {
                // Sleep until the earliest call in this 60s window rolls out
                $oldestInWindow = ApiCall::where('source', $source)
                    ->where('served_from_cache', false)
                    ->where('called_at', '>=', $oneMinuteAgo)
                    ->orderBy('called_at', 'asc')
                    ->value('called_at');

                if ($oldestInWindow) {
                    $secondsToWait = max(1, 61 - ($now->timestamp - $oldestInWindow->timestamp));
                    sleep((int) min($secondsToWait, 10)); // sleep up to 10s safely
                }
            }
        }
    }

    /**
     * Get quota summary for dev inspector and CLI status.
     */
    public function getQuotaStatus(string $source): array
    {
        $config = config("football.quotas.{$source}", []);
        $now = CarbonImmutable::now('UTC');

        $usedToday = ApiCall::where('source', $source)
            ->where('counts_toward_quota', true)
            ->where('called_at', '>=', $now->startOfDay())
            ->count();

        $callsInLastMinute = ApiCall::where('source', $source)
            ->where('served_from_cache', false)
            ->where('called_at', '>=', $now->subSeconds(60))
            ->count();

        return [
            'source' => $source,
            'used_today' => $usedToday,
            'daily_limit' => $config['per_day'] ?? null,
            'reserve' => $config['reserve'] ?? 0,
            'per_minute_used' => $callsInLastMinute,
            'per_minute_limit' => $config['per_minute'] ?? null,
        ];
    }
}

