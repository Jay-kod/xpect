<?php

declare(strict_types=1);

namespace App\Http\Clients;

use App\Http\Clients\RateLimiter\QuotaGuard;
use App\Models\ApiCall;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Throwable;

class LoggedHttpClient
{
    public function __construct(
        protected readonly QuotaGuard $quotaGuard
    ) {
    }

    /**
     * The ONLY outbound HTTP entry point in the application.
     */
    public function request(
        string $source,
        string $method,
        string $url,
        array $params = [],
        string|array|null $body = null,
        array $headers = [],
        string $triggeredBy = 'system',
        bool $countsTowardQuota = true,
        ?int $ttlSeconds = null,
        bool $fresh = false,
        ?int $syncRunId = null
    ): LoggedHttpResponse {
        $method = strtoupper($method);
        $calledAt = CarbonImmutable::now('UTC');
        $cacheKey = $this->generateCacheKey($source, $method, $url, $params);

        // 1. Check Cache
        if (!$fresh && $ttlSeconds !== 0) {
            $cachedCall = $this->findCachedCall($cacheKey, $ttlSeconds);
            if ($cachedCall) {
                // Insert cache-hit marker row per Architecture doc (no body, no quota consumed)
                $marker = ApiCall::create([
                    'source' => $source,
                    'method' => $method,
                    'url' => $this->redactUrl($url),
                    'request_params' => $this->redactArray($params),
                    'request_body' => null,
                    'request_headers' => $this->redactHeaders($headers),
                    'response_status' => $cachedCall->response_status,
                    'response_body' => null, // marker rows store no body
                    'response_headers' => null,
                    'duration_ms' => 0,
                    'error' => null,
                    'counts_toward_quota' => false,
                    'served_from_cache' => true,
                    'cached_from_call_id' => $cachedCall->id,
                    'cache_key' => $cacheKey,
                    'triggered_by' => $triggeredBy,
                    'sync_run_id' => $syncRunId,
                    'called_at' => $calledAt,
                ]);

                return new LoggedHttpResponse(
                    status: (int) $cachedCall->response_status,
                    body: (string) $cachedCall->response_body,
                    headers: (array) ($cachedCall->response_headers ?? []),
                    durationMs: 0,
                    servedFromCache: true,
                    apiCallId: $marker->id,
                    error: null,
                );
            }
        }

        // 2. Quota & Throttle Check
        $this->quotaGuard->checkAndThrottle($source, $countsTowardQuota);

        // 3. Prepare Request & Redact before sending
        $sanitizedHeaders = $this->redactHeaders($headers);
        $sanitizedParams = $this->redactArray($params);
        $sanitizedBody = is_array($body) ? json_encode($this->redactArray($body)) : $body;

        $startTime = hrtime(true);
        $responseStatus = null;
        $responseBody = null;
        $responseHeaders = [];
        $errorMessage = null;
        $inputTokens = null;
        $outputTokens = null;

        try {
            $httpClient = Http::withHeaders($headers)->timeout(30);

            $response = match ($method) {
                'GET' => $httpClient->get($url, $params),
                'POST' => is_array($body) ? $httpClient->post($url, $body) : $httpClient->withBody((string) $body, 'application/json')->post($url),
                default => $httpClient->send($method, $url, ['query' => $params, 'body' => $body]),
            };

            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);
            $responseStatus = $response->status();
            $responseBody = $response->body();
            $responseHeaders = $response->headers();

            // 4. Provider Error Detection
            $errorMessage = $this->detectProviderErrors($source, $responseStatus, $responseBody);

            // 5. LLM Token Counting
            if ($responseStatus === 200) {
                $decoded = json_decode($responseBody, true);
                if ($source === 'anthropic') {
                    $inputTokens = $decoded['usage']['input_tokens'] ?? null;
                    $outputTokens = $decoded['usage']['output_tokens'] ?? null;
                } elseif ($source === 'openai') {
                    $inputTokens = $decoded['usage']['prompt_tokens'] ?? null;
                    $outputTokens = $decoded['usage']['completion_tokens'] ?? null;
                } elseif ($source === 'gemini') {
                    $inputTokens = $decoded['usageMetadata']['promptTokenCount'] ?? null;
                    $outputTokens = $decoded['usageMetadata']['candidatesTokenCount'] ?? null;
                }
            }
        } catch (Throwable $e) {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);
            $errorMessage = $e->getMessage();
            $responseStatus = 500;
            $responseBody = '';
        }

        // 6. Mandatory Persistence to api_calls (Redacted)
        $apiCall = ApiCall::create([
            'source' => $source,
            'method' => $method,
            'url' => $this->redactUrl($url),
            'request_params' => $sanitizedParams,
            'request_body' => $sanitizedBody,
            'request_headers' => $sanitizedHeaders,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'response_headers' => $responseHeaders,
            'duration_ms' => $durationMs,
            'error' => $errorMessage,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'counts_toward_quota' => $countsTowardQuota,
            'served_from_cache' => false,
            'cached_from_call_id' => null,
            'cache_key' => $cacheKey,
            'triggered_by' => $triggeredBy,
            'sync_run_id' => $syncRunId,
            'called_at' => $calledAt,
        ]);

        return new LoggedHttpResponse(
            status: $responseStatus,
            body: $responseBody ?: '',
            headers: $responseHeaders,
            durationMs: $durationMs,
            servedFromCache: false,
            apiCallId: $apiCall->id,
            error: $errorMessage,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
        );
    }

    public function generateCacheKey(string $source, string $method, string $url, array $params = []): string
    {
        ksort($params);
        $normUrl = strtolower(explode('?', $url)[0]);
        $paramJson = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', "{$source}|{$method}|{$normUrl}|{$paramJson}");
    }

    protected function findCachedCall(string $cacheKey, ?int $ttlSeconds): ?ApiCall
    {
        $query = ApiCall::where('cache_key', $cacheKey)
            ->where('served_from_cache', false)
            ->whereBetween('response_status', [200, 299])
            ->whereNull('error')
            ->orderBy('called_at', 'desc');

        if ($ttlSeconds !== null) {
            $cutoff = CarbonImmutable::now('UTC')->subSeconds($ttlSeconds);
            $query->where('called_at', '>=', $cutoff);
        }

        return $query->first();
    }

    protected function detectProviderErrors(string $source, ?int $status, ?string $body): ?string
    {
        if ($status === null || $status < 200 || $status >= 300) {
            return "HTTP request failed with status {$status}";
        }

        if (empty($body)) {
            return null;
        }

        // Check provider-specific envelopes
        if ($source === 'api_football') {
            $data = json_decode($body, true);
            if (!empty($data['errors'])) {
                return is_array($data['errors']) ? json_encode($data['errors']) : (string) $data['errors'];
            }
        }

        if ($source === 'anthropic' || $source === 'openai' || $source === 'gemini') {
            $data = json_decode($body, true);
            if (!empty($data['error'])) {
                return is_array($data['error']) ? ($data['error']['message'] ?? json_encode($data['error'])) : (string) $data['error'];
            }
        }

        return null;
    }

    public function redactHeaders(array $headers): array
    {
        $secrets = config('football.secrets', []);
        $redacted = [];

        foreach ($headers as $key => $value) {
            if ($this->isSecretKey((string) $key, $secrets)) {
                $redacted[$key] = '[REDACTED]';
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    public function redactArray(array $data): array
    {
        $secrets = config('football.secrets', []);
        $redacted = [];

        foreach ($data as $key => $value) {
            if ($this->isSecretKey((string) $key, $secrets)) {
                $redacted[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $redacted[$key] = $this->redactArray($value);
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    protected function redactUrl(string $url): string
    {
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $queryParams);
        $redactedParams = $this->redactArray($queryParams);
        $newQuery = http_build_query($redactedParams);

        $result = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
        if (!empty($parts['port'])) {
            $result .= ':' . $parts['port'];
        }
        $result .= ($parts['path'] ?? '');
        if ($newQuery !== '') {
            $result .= '?' . $newQuery;
        }

        return $result;
    }

    protected function isSecretKey(string $key, array $secrets): bool
    {
        $lowerKey = strtolower($key);
        foreach ($secrets as $secret) {
            if ($lowerKey === strtolower($secret)) {
                return true;
            }
        }

        return false;
    }
}

