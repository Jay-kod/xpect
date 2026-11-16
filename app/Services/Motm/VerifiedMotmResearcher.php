<?php

declare(strict_types=1);

namespace App\Services\Motm;

use App\Http\Clients\LoggedHttpClient;
use App\Models\FootballMatch;
use App\Models\MotmEvaluation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;

class VerifiedMotmResearcher
{
    public const MODEL_VERSION = 'gemini-grounded-motm-v1';

    public function __construct(
        protected readonly LoggedHttpClient $httpClient
    ) {
    }

    public function research(FootballMatch $match, bool $force = false): ?MotmEvaluation
    {
        $existing = MotmEvaluation::query()
            ->where('match_id', $match->id)
            ->where('verification_status', 'verified')
            ->first();

        if ($existing && !$force) {
            return $existing;
        }

        $model = (string) (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3-flash-preview'));
        $apiKey = (string) (config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is not configured. Verified MOTM research requires Gemini Search grounding.');
        }

        $home = $match->homeTeam?->name;
        $away = $match->awayTeam?->name;
        $competition = $match->competition?->name ?? 'football';
        if (!$home || !$away || !$match->kickoff_at) {
            throw new RuntimeException("Match #{$match->id} is missing teams or kickoff time.");
        }

        $prompt = <<<PROMPT
Research the official or reputable Man of the Match award for this completed football match:
Competition: {$competition}
Date: {$match->kickoff_at->format('Y-m-d')}
Fixture: {$home} vs {$away}
Final score: {$match->home_goals}-{$match->away_goals}

Use Google Search. Prefer an official competition or club match report, then a reputable
match report that explicitly names the award winner. Do not infer a winner from goals,
ratings, or your own judgement. If no source explicitly names the Man of the Match,
return verified=false.

Return ONLY valid JSON:
{
  "verified": true,
  "player_name": "...",
  "team_name": "...",
  "reason": "A concise explanation supported by the sources.",
  "sources": [{"title": "...", "url": "...", "publisher": "..."}],
  "search_queries": ["..."]
}
PROMPT;

        $response = $this->httpClient->request(
            source: 'gemini',
            method: 'POST',
            url: "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
            body: [
                'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                'tools' => [['google_search' => (object) []]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ],
            headers: [
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            triggeredBy: 'motm:research',
            countsTowardQuota: false,
            ttlSeconds: 0,
        );

        if ($response->failed()) {
            throw new RuntimeException("Gemini MOTM research failed: {$response->error} (HTTP {$response->status})");
        }

        $decoded = json_decode($response->body, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini MOTM research returned no structured result.');
        }

        $result = json_decode(trim($text), true);
        if (!is_array($result) || ($result['verified'] ?? false) !== true) {
            return null;
        }

        $playerName = trim((string) ($result['player_name'] ?? ''));
        $teamName = trim((string) ($result['team_name'] ?? ''));
        $reason = trim((string) ($result['reason'] ?? ''));
        $sources = is_array($result['sources'] ?? null) ? $result['sources'] : [];
        $urls = collect($sources)->pluck('url')->filter(fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL))->values()->all();
        $titles = collect($sources)->pluck('title')->filter(fn ($title) => is_string($title) && trim($title) !== '')->values()->all();
        $groundedSources = $this->extractGroundedSources($decoded);
        $groundedUrls = array_column($groundedSources, 'url');
        $urls = array_values(array_intersect($urls, $groundedUrls));

        if ($playerName === '' || $teamName === '' || $reason === '' || $urls === []) {
            throw new RuntimeException('Gemini MOTM research did not provide a named winner, reason, and grounding citation URL.');
        }

        $team = $this->resolveTeam($match, $teamName);
        if (!$team) {
            throw new RuntimeException("Gemini returned a team outside the fixture: {$teamName}.");
        }

        if ($force) {
            MotmEvaluation::where('match_id', $match->id)->where('verification_status', 'verified')->delete();
        }

        return MotmEvaluation::create([
            'match_id' => $match->id,
            'player_name' => $playerName,
            'team_id' => $team->id,
            'is_starter' => true,
            'is_motm' => true,
            'rating' => null,
            'performance_summary' => $reason,
            'decision_rationale' => $reason,
            'model' => self::MODEL_VERSION,
            'verification_status' => 'verified',
            'source_urls' => $urls,
            'source_titles' => $titles,
            'research_queries' => is_array($result['search_queries'] ?? null) ? $result['search_queries'] : [],
            'verified_at' => CarbonImmutable::now('UTC'),
            'source_api_call_id' => $response->apiCallId,
        ]);
    }

    /**
     * Extract URLs returned by Gemini's Search grounding, not URLs invented in
     * the model's structured text response.
     *
     * @return array<int, array{url: string, title: string}>
     */
    protected function extractGroundedSources(array $response): array
    {
        $sources = [];
        $metadata = $response['candidates'][0]['groundingMetadata'] ?? [];

        foreach (($metadata['groundingChunks'] ?? []) as $chunk) {
            $url = $chunk['web']['uri'] ?? null;
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $sources[] = [
                    'url' => $url,
                    'title' => (string) ($chunk['web']['title'] ?? ''),
                ];
            }
        }

        foreach (($response['candidates'][0]['content']['parts'] ?? []) as $part) {
            foreach (($part['annotations'] ?? []) as $annotation) {
                $url = $annotation['url_citation']['url'] ?? null;
                if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $sources[] = [
                        'url' => $url,
                        'title' => (string) ($annotation['url_citation']['title'] ?? ''),
                    ];
                }
            }
        }

        return collect($sources)->unique('url')->values()->all();
    }

    protected function resolveTeam(FootballMatch $match, string $teamName): ?object
    {
        $candidate = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $teamName) ?: '');
        foreach ([$match->homeTeam, $match->awayTeam] as $team) {
            if (!$team) {
                continue;
            }
            $names = [$team->name, $team->short_name];
            foreach ($names as $name) {
                $normalized = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', (string) $name) ?: '');
                if ($candidate === $normalized || ($candidate !== '' && str_contains($candidate, $normalized))) {
                    return $team;
                }
            }
        }

        return null;
    }
}
