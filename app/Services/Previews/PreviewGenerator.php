<?php

declare(strict_types=1);

namespace App\Services\Previews;

use App\Http\Clients\LoggedHttpClient;
use App\Models\Prediction;
use App\Models\Preview;
use RuntimeException;

class PreviewGenerator
{
    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly PromptBuilder $promptBuilder,
        protected readonly NumberCheckGuard $guard
    ) {
    }

    /**
     * Generate an AI preview for a match prediction supporting Claude, ChatGPT, and Gemini.
     * All calls go through LoggedHttpClient; raw request & response are saved in api_calls.
     */
    public function generate(Prediction $prediction, ?string $modelOverride = null, ?string $providerOverride = null): Preview
    {
        $payload = $this->promptBuilder->buildPayload($prediction);
        $provider = $this->resolveProvider($providerOverride);
        $userPrompt = "Generate an objective match preview strictly using the following structured statistical model output:\n\n" . json_encode($payload['input_json'], JSON_PRETTY_PRINT);

        if ($provider === 'chatgpt') {
            $apiKey = (string) (config('services.openai.key') ?: env('OPENAI_API_KEY'));
            $model = $modelOverride ?: (string) (config('services.openai.model') ?: env('OPENAI_MODEL', 'gpt-4o'));

            if (empty($apiKey) && !app()->environment('testing')) {
                throw new RuntimeException('OPENAI_API_KEY is not configured in .env. Previews require a valid OpenAI API key.');
            }

            $requestBody = [
                'model' => $model,
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'system', 'content' => $payload['system']],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ];

            $headers = [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ];

            $response = $this->httpClient->request(
                source: 'openai',
                method: 'POST',
                url: 'https://api.openai.com/v1/chat/completions',
                params: [],
                body: $requestBody,
                headers: $headers,
                triggeredBy: 'preview:generate',
                countsTowardQuota: false,
                ttlSeconds: 0
            );

            if ($response->failed()) {
                throw new RuntimeException("OpenAI API call failed: {$response->error} (HTTP {$response->status})");
            }

            $decoded = json_decode($response->body, true);
            $text = $decoded['choices'][0]['message']['content'] ?? null;
        } elseif ($provider === 'gemini') {
            $apiKey = (string) (config('services.gemini.key') ?: env('GEMINI_API_KEY'));

            if (empty($apiKey) && !app()->environment('testing')) {
                throw new RuntimeException('GEMINI_API_KEY is not configured in .env. Previews require a valid Gemini API key.');
            }

            if (app()->environment('testing')) {
                $candidateModels = [$modelOverride ?: (string) (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3-flash-preview'))];
            } else {
                $configuredModel = (string) (config('services.gemini.model') ?: env('GEMINI_MODEL', ''));
                $candidateModels = [];
                if ($modelOverride) {
                    $candidateModels[] = $modelOverride;
                }
                if ($configuredModel && !in_array($configuredModel, ['gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-2.5-flash', 'gemini-2.0-flash'], true)) {
                    $candidateModels[] = $configuredModel;
                }
                $candidateModels[] = 'gemini-3-flash-preview';
                $candidateModels[] = 'gemini-3.5-flash';
                $candidateModels[] = 'gemini-3.8-flash';
                $candidateModels[] = 'gemini-flash-latest';
                $candidateModels = array_values(array_unique($candidateModels));
            }

            $requestBody = [
                'systemInstruction' => [
                    'parts' => [['text' => $payload['system']]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $userPrompt]],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 1024,
                    'temperature' => 0.2,
                ],
            ];

            $headers = [
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ];

            $response = null;
            $text = null;
            $model = null;

            foreach ($candidateModels as $candidateModel) {
                $response = $this->httpClient->request(
                    source: 'gemini',
                    method: 'POST',
                    url: "https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent",
                    params: [],
                    body: $requestBody,
                    headers: $headers,
                    triggeredBy: 'preview:generate',
                    countsTowardQuota: false,
                    ttlSeconds: 0
                );

                if ($response->status === 200) {
                    $decoded = json_decode($response->body, true);
                    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        $model = $candidateModel;
                        break;
                    }
                }

                // If fatal authentication error (400 API_KEY_INVALID or 401/403 Forbidden), do not loop models
                if (in_array($response->status, [400, 401, 403], true) && !str_contains($response->body, 'not found')) {
                    break;
                }
            }

            if ($response === null || $response->failed() || empty($text)) {
                $err = $response?->error ?: "HTTP " . ($response?->status ?? 500);
                throw new RuntimeException("Google Gemini API call failed: {$err} (HTTP {$response?->status})");
            }
        } else {
            // Default: Claude (Anthropic)
            $apiKey = (string) (config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY'));
            $model = $modelOverride ?: (string) (config('services.anthropic.model') ?: 'claude-3-5-sonnet-20241022');

            if (empty($apiKey) && !app()->environment('testing')) {
                throw new RuntimeException('ANTHROPIC_API_KEY is not configured in .env. Previews require a valid Anthropic API key.');
            }

            $requestBody = [
                'model' => $model,
                'max_tokens' => 1024,
                'system' => $payload['system'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ];

            $headers = [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ];

            $response = $this->httpClient->request(
                source: 'anthropic',
                method: 'POST',
                url: 'https://api.anthropic.com/v1/messages',
                params: [],
                body: $requestBody,
                headers: $headers,
                triggeredBy: 'preview:generate',
                countsTowardQuota: false,
                ttlSeconds: 0
            );

            if ($response->failed()) {
                throw new RuntimeException("Anthropic API call failed: {$response->error} (HTTP {$response->status})");
            }

            $decoded = json_decode($response->body, true);
            $text = $decoded['content'][0]['text'] ?? null;
        }

        if (empty($text)) {
            throw new RuntimeException(ucfirst($provider) . ' response contained no text content. Response: ' . substr($response->body, 0, 200));
        }

        // Verify every number in the text against input JSON
        $guardResult = $this->guard->check($text, $payload['input_json']);

        return Preview::create([
            'prediction_id' => $prediction->id,
            'api_call_id' => $response->apiCallId,
            'model' => $model,
            'prompt_version' => $payload['prompt_version'],
            'text' => $text,
            'flagged' => $guardResult->isFlagged(),
        ]);
    }

    /**
     * Resolve the active AI provider.
     */
    public function resolveProvider(?string $override = null): string
    {
        $candidate = strtolower(trim((string) ($override ?: config('services.ai.provider') ?: env('AI_PROVIDER', 'claude'))));

        return match ($candidate) {
            'openai', 'chatgpt' => 'chatgpt',
            'gemini', 'google' => 'gemini',
            default => 'claude',
        };
    }
}
