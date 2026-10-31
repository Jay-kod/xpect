<?php

declare(strict_types=1);

namespace App\Services\Api;

use App\Http\Clients\LoggedHttpClient;
use InvalidArgumentException;
use Throwable;

class ApiKeyTesterService
{
    public function __construct(
        protected readonly LoggedHttpClient $httpClient
    ) {
    }

    /**
     * Test whether an API key is valid and active for the specified provider.
     */
    public function testKey(string $provider, ?string $key = null): ApiKeyTestResult
    {
        $normalized = strtolower(trim($provider));

        return match ($normalized) {
            'claude', 'anthropic' => $this->testAnthropic($key),
            'chatgpt', 'openai' => $this->testOpenAi($key),
            'gemini', 'google' => $this->testGemini($key),
            'football_data', 'footballdata', 'football-data' => $this->testFootballData($key),
            'api_football', 'apifootball', 'api-football' => $this->testApiFootball($key),
            default => throw new InvalidArgumentException("Unsupported API provider: [{$provider}]"),
        };
    }

    /**
     * Test Anthropic Claude API Key.
     */
    protected function testAnthropic(?string $key): ApiKeyTestResult
    {
        $apiKey = $key !== null ? trim($key) : (string) env('ANTHROPIC_API_KEY', '');
        if ($apiKey === '') {
            return new ApiKeyTestResult(
                provider: 'claude',
                isValid: false,
                statusCode: 0,
                durationMs: 0,
                message: 'No Anthropic Claude API key provided or configured.'
            );
        }

        try {
            $url = 'https://api.anthropic.com/v1/messages';
            $payload = [
                'model' => (string) env('ANTHROPIC_MODEL', 'claude-3-5-haiku-latest'),
                'max_tokens' => 1,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping'],
                ],
            ];

            $response = $this->httpClient->request(
                source: 'anthropic',
                method: 'POST',
                url: $url,
                body: $payload,
                headers: [
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                triggeredBy: 'key-verification',
                countsTowardQuota: false,
                fresh: true
            );

            $data = json_decode($response->body, true) ?? [];

            if ($response->status === 200) {
                return new ApiKeyTestResult(
                    provider: 'claude',
                    isValid: true,
                    statusCode: 200,
                    durationMs: $response->durationMs,
                    message: 'Anthropic Claude API key is active and connected!',
                    details: 'Authenticated successfully with model: ' . ($data['model'] ?? 'Claude'),
                    rawResponse: $data
                );
            }

            $errMsg = $data['error']['message'] ?? "HTTP {$response->status}";
            return new ApiKeyTestResult(
                provider: 'claude',
                isValid: false,
                statusCode: $response->status,
                durationMs: $response->durationMs,
                message: "Anthropic Claude verification failed: {$errMsg}",
                details: $response->error,
                rawResponse: $data
            );
        } catch (Throwable $e) {
            return new ApiKeyTestResult(
                provider: 'claude',
                isValid: false,
                statusCode: 500,
                durationMs: 0,
                message: 'Connection exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test OpenAI ChatGPT API Key.
     */
    protected function testOpenAi(?string $key): ApiKeyTestResult
    {
        $apiKey = $key !== null ? trim($key) : (string) env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            return new ApiKeyTestResult(
                provider: 'chatgpt',
                isValid: false,
                statusCode: 0,
                durationMs: 0,
                message: 'No OpenAI ChatGPT API key provided or configured.'
            );
        }

        try {
            $url = 'https://api.openai.com/v1/chat/completions';
            $payload = [
                'model' => (string) env('OPENAI_MODEL', 'gpt-4o-mini'),
                'max_tokens' => 1,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping'],
                ],
            ];

            $response = $this->httpClient->request(
                source: 'openai',
                method: 'POST',
                url: $url,
                body: $payload,
                headers: [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'content-type' => 'application/json',
                ],
                triggeredBy: 'key-verification',
                countsTowardQuota: false,
                fresh: true
            );

            $data = json_decode($response->body, true) ?? [];

            if ($response->status === 200) {
                return new ApiKeyTestResult(
                    provider: 'chatgpt',
                    isValid: true,
                    statusCode: 200,
                    durationMs: $response->durationMs,
                    message: 'OpenAI ChatGPT API key is active and connected!',
                    details: 'Authenticated successfully with model: ' . ($data['model'] ?? 'GPT-4o'),
                    rawResponse: $data
                );
            }

            $errMsg = $data['error']['message'] ?? "HTTP {$response->status}";
            return new ApiKeyTestResult(
                provider: 'chatgpt',
                isValid: false,
                statusCode: $response->status,
                durationMs: $response->durationMs,
                message: "OpenAI ChatGPT verification failed: {$errMsg}",
                details: $response->error,
                rawResponse: $data
            );
        } catch (Throwable $e) {
            return new ApiKeyTestResult(
                provider: 'chatgpt',
                isValid: false,
                statusCode: 500,
                durationMs: 0,
                message: 'Connection exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test Google Gemini API Key.
     */
    protected function testGemini(?string $key): ApiKeyTestResult
    {
        $apiKey = $key !== null ? trim($key) : (string) env('GEMINI_API_KEY', '');
        if ($apiKey === '') {
            return new ApiKeyTestResult(
                provider: 'gemini',
                isValid: false,
                statusCode: 0,
                durationMs: 0,
                message: 'No Google Gemini API key provided or configured.'
            );
        }

        try {
            $candidateModels = array_values(array_filter(array_unique([
                (string) env('GEMINI_MODEL', ''),
                'gemini-3.8-flash',
                'gemini-3-flash-preview',
            ])));

            $lastResponse = null;
            $lastData = [];
            $lastModel = 'gemini-3.8-flash';

            foreach ($candidateModels as $model) {
                $lastModel = $model;
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
                $payload = [
                    'contents' => [
                        ['parts' => [['text' => 'ping']]],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 1,
                    ],
                ];

                $response = $this->httpClient->request(
                    source: 'gemini',
                    method: 'POST',
                    url: $url,
                    body: $payload,
                    headers: [
                        'x-goog-api-key' => $apiKey,
                        'content-type' => 'application/json',
                    ],
                    triggeredBy: 'key-verification',
                    countsTowardQuota: false,
                    fresh: true
                );

                $lastResponse = $response;
                $lastData = json_decode($response->body, true) ?? [];

                if ($response->status === 200) {
                    return new ApiKeyTestResult(
                        provider: 'gemini',
                        isValid: true,
                        statusCode: 200,
                        durationMs: $response->durationMs,
                        message: 'Google Gemini API key is active and connected!',
                        details: "Authenticated successfully with {$model}.",
                        rawResponse: $lastData
                    );
                }

                // If authentication error (400 API_KEY_INVALID or 401/403 Forbidden), key is definitively invalid
                if (in_array($response->status, [400, 401, 403], true) && !str_contains($response->body, 'is not found for API version')) {
                    break;
                }
            }

            $errMsg = $lastData['error']['message'] ?? "HTTP " . ($lastResponse ? $lastResponse->status : 500);
            return new ApiKeyTestResult(
                provider: 'gemini',
                isValid: false,
                statusCode: $lastResponse ? $lastResponse->status : 500,
                durationMs: $lastResponse ? $lastResponse->durationMs : 0,
                message: "Google Gemini verification failed: {$errMsg}",
                details: $lastResponse?->error,
                rawResponse: $lastData
            );
        } catch (Throwable $e) {
            return new ApiKeyTestResult(
                provider: 'gemini',
                isValid: false,
                statusCode: 500,
                durationMs: 0,
                message: 'Connection exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test football-data.org API Key.
     */
    protected function testFootballData(?string $key): ApiKeyTestResult
    {
        $apiKey = $key !== null ? trim($key) : (string) env('FOOTBALL_DATA_API_KEY', '');
        if ($apiKey === '') {
            return new ApiKeyTestResult(
                provider: 'football_data',
                isValid: false,
                statusCode: 0,
                durationMs: 0,
                message: 'No football-data.org API key provided or configured.'
            );
        }

        try {
            $url = 'https://api.football-data.org/v4/competitions/PL';

            $response = $this->httpClient->request(
                source: 'football_data',
                method: 'GET',
                url: $url,
                headers: [
                    'X-Auth-Token' => $apiKey,
                ],
                triggeredBy: 'key-verification',
                countsTowardQuota: true,
                fresh: true
            );

            $data = json_decode($response->body, true) ?? [];

            if ($response->status === 200) {
                $compName = $data['name'] ?? 'Premier League';
                $remaining = $this->getHeader($response->headers, 'X-Requests-Available-Minute');
                $details = "Connected to {$compName}" . ($remaining !== null ? " ({$remaining} requests left this minute)" : '');

                return new ApiKeyTestResult(
                    provider: 'football_data',
                    isValid: true,
                    statusCode: 200,
                    durationMs: $response->durationMs,
                    message: 'football-data.org API token is active and connected!',
                    details: $details,
                    rawResponse: $data
                );
            }

            $errMsg = $data['message'] ?? "HTTP {$response->status}";
            return new ApiKeyTestResult(
                provider: 'football_data',
                isValid: false,
                statusCode: $response->status,
                durationMs: $response->durationMs,
                message: "football-data.org verification failed: {$errMsg}",
                details: $response->error,
                rawResponse: $data
            );
        } catch (Throwable $e) {
            return new ApiKeyTestResult(
                provider: 'football_data',
                isValid: false,
                statusCode: 500,
                durationMs: 0,
                message: 'Connection exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test API-Football (api-sports) Key.
     */
    protected function testApiFootball(?string $key): ApiKeyTestResult
    {
        $apiKey = $key !== null ? trim($key) : (string) env('API_FOOTBALL_KEY', '');
        if ($apiKey === '') {
            return new ApiKeyTestResult(
                provider: 'api_football',
                isValid: false,
                statusCode: 0,
                durationMs: 0,
                message: 'No API-Football key provided or configured.'
            );
        }

        try {
            $url = 'https://v3.football.api-sports.io/status';

            $response = $this->httpClient->request(
                source: 'api_football',
                method: 'GET',
                url: $url,
                headers: [
                    'x-apisports-key' => $apiKey,
                ],
                triggeredBy: 'key-verification',
                countsTowardQuota: true,
                fresh: true
            );

            $data = json_decode($response->body, true) ?? [];

            // API-Football returns errors inside json even with HTTP 200
            if (!empty($data['errors'])) {
                $errStr = is_array($data['errors']) ? implode(', ', array_values($data['errors'])) : (string) $data['errors'];
                return new ApiKeyTestResult(
                    provider: 'api_football',
                    isValid: false,
                    statusCode: $response->status,
                    durationMs: $response->durationMs,
                    message: "API-Football verification failed: {$errStr}",
                    details: $errStr,
                    rawResponse: $data
                );
            }

            if ($response->status === 200 && isset($data['response'])) {
                $account = $data['response']['account'] ?? [];
                $subscription = $data['response']['subscription'] ?? [];
                $requests = $data['response']['requests'] ?? [];

                $plan = $subscription['plan'] ?? 'Free';
                $current = $requests['current'] ?? 0;
                $limitDay = $requests['limit_day'] ?? 100;
                $email = $account['email'] ?? 'Verified Account';

                return new ApiKeyTestResult(
                    provider: 'api_football',
                    isValid: true,
                    statusCode: 200,
                    durationMs: $response->durationMs,
                    message: "API-Football key is active! Plan: {$plan} ({$email})",
                    details: "Quota: {$current}/{$limitDay} requests used today",
                    rawResponse: $data
                );
            }

            return new ApiKeyTestResult(
                provider: 'api_football',
                isValid: false,
                statusCode: $response->status,
                durationMs: $response->durationMs,
                message: "API-Football verification failed with status {$response->status}",
                details: $response->error,
                rawResponse: $data
            );
        } catch (Throwable $e) {
            return new ApiKeyTestResult(
                provider: 'api_football',
                isValid: false,
                statusCode: 500,
                durationMs: 0,
                message: 'Connection exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Case-insensitively retrieve a header value from response headers.
     */
    protected function getHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $values) {
            if (strcasecmp((string) $key, $name) === 0) {
                return is_array($values) ? ($values[0] ?? null) : (string) $values;
            }
        }

        return null;
    }
}
