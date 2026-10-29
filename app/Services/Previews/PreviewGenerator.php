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
     * Generate an AI preview for a match prediction.
     * All calls go through LoggedHttpClient; raw request & response are saved in api_calls.
     */
    public function generate(Prediction $prediction, ?string $modelOverride = null): Preview
    {
        $apiKey = (string) (config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY'));
        $model = $modelOverride ?: (string) (config('services.anthropic.model') ?: 'claude-3-5-sonnet-20241022');

        if (empty($apiKey) && !app()->environment('testing')) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured in .env. Previews require a valid Anthropic API key.');
        }

        $payload = $this->promptBuilder->buildPayload($prediction);

        $requestBody = [
            'model' => $model,
            'max_tokens' => 1024,
            'system' => $payload['system'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Generate an objective match preview strictly using the following structured statistical model output:\n\n" . json_encode($payload['input_json'], JSON_PRETTY_PRINT),
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
            ttlSeconds: 0 // LLM calls are never cached
        );

        if ($response->failed()) {
            throw new RuntimeException("Anthropic API call failed: {$response->error} (HTTP {$response->status})");
        }

        $decoded = json_decode($response->body, true);
        $text = $decoded['content'][0]['text'] ?? null;

        if (empty($text)) {
            throw new RuntimeException('Anthropic response contained no text content. Response: ' . substr($response->body, 0, 200));
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
}

