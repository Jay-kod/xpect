<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dev;

use App\Http\Clients\LoggedHttpClient;
use App\Http\Clients\RateLimiter\QuotaGuard;
use App\Http\Controllers\Controller;
use App\Models\ApiCall;
use App\Models\FootballMatch;
use App\Models\Preview;
use App\Models\StandingsSnapshot;
use App\Models\TeamRating;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ApiInspectorController extends Controller
{
    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly QuotaGuard $quotaGuard
    ) {
    }

    public function index(): View
    {
        return view('dev.inspector');
    }

    public function calls(Request $request): JsonResponse
    {
        $query = ApiCall::query()->orderBy('called_at', 'desc');

        if ($source = $request->query('source')) {
            $query->where('source', $source);
        }

        if ($status = $request->query('status')) {
            if ($status === 'success') {
                $query->whereBetween('response_status', [200, 299])->whereNull('error');
            } elseif ($status === 'error') {
                $query->where(function ($q) {
                    $q->where('response_status', '>=', 300)
                      ->orWhereNull('response_status')
                      ->orWhereNotNull('error');
                });
            } elseif ($status === 'cache') {
                $query->where('served_from_cache', true);
            }
        }

        if ($command = $request->query('command')) {
            $query->where('triggered_by', $command);
        }

        if ($date = $request->query('date')) {
            $carbon = CarbonImmutable::parse($date);
            $query->whereBetween('called_at', [$carbon->startOfDay(), $carbon->endOfDay()]);
        }

        $calls = $query->paginate(30);

        return response()->json($calls);
    }

    public function call(int $id): JsonResponse
    {
        $call = ApiCall::with(['cachedFrom'])->findOrFail($id);

        // Calculate provenance rows
        $provenance = [
            'matches' => FootballMatch::where('source_api_call_id', $call->id)->count(),
            'standings_snapshots' => StandingsSnapshot::where('source_api_call_id', $call->id)->count(),
            'team_ratings' => TeamRating::where('source_api_call_id', $call->id)->count(),
            'previews' => Preview::where('api_call_id', $call->id)->count(),
        ];

        return response()->json([
            'call' => $call,
            'provenance' => $provenance,
        ]);
    }

    public function quota(): JsonResponse
    {
        $sources = ['football_data', 'api_football'];
        $status = [];

        foreach ($sources as $source) {
            $status[$source] = $this->quotaGuard->getQuotaStatus($source);
        }

        return response()->json($status);
    }

    public function catalog(): JsonResponse
    {
        $catalog = config('api_catalog.sources', []);
        $enriched = [];

        foreach ($catalog as $sourceKey => $sourceData) {
            $endpoints = [];
            foreach ($sourceData['endpoints'] ?? [] as $ep) {
                // Find latest call matching path pattern
                $latestCall = ApiCall::where('source', $sourceKey)
                    ->where('method', $ep['method'])
                    ->orderBy('called_at', 'desc')
                    ->first();

                $ep['last_called'] = $latestCall?->called_at?->toIso8601String();
                $ep['last_status'] = $latestCall?->response_status;
                $ep['has_saved_response'] = !empty($latestCall?->response_body);

                $endpoints[] = $ep;
            }

            $sourceData['endpoints'] = $endpoints;
            $enriched[$sourceKey] = $sourceData;
        }

        return response()->json($enriched);
    }

    public function tryIt(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|string',
            'url' => 'required|url',
            'params' => 'nullable|array',
        ]);

        $source = (string) $request->input('source');
        $url = (string) $request->input('url');
        $params = (array) $request->input('params', []);

        // Authenticate based on source
        $headers = [];
        if ($source === 'football_data' && env('FOOTBALL_DATA_API_KEY')) {
            $headers['X-Auth-Token'] = env('FOOTBALL_DATA_API_KEY');
        } elseif ($source === 'api_football' && env('API_FOOTBALL_KEY')) {
            $headers['x-apisports-key'] = env('API_FOOTBALL_KEY');
        }

        try {
            $response = $this->httpClient->request(
                source: $source,
                method: 'GET',
                url: $url,
                params: $params,
                headers: $headers,
                triggeredBy: 'dev-inspector-try-it',
                countsTowardQuota: true,
                fresh: true
            );

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status,
                'api_call_id' => $response->apiCallId,
                'duration_ms' => $response->durationMs,
                'error' => $response->error,
                'body' => $response->json() ?? $response->body,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveFixture(Request $request): JsonResponse
    {
        $request->validate([
            'api_call_id' => 'required|integer',
            'filename' => 'required|string',
        ]);

        $call = ApiCall::findOrFail($request->input('api_call_id'));
        $filename = basename((string) $request->input('filename'));

        $fixturesDir = base_path('tests/Fixtures');
        if (!is_dir($fixturesDir)) {
            mkdir($fixturesDir, 0755, true);
        }

        file_put_contents($fixturesDir . '/' . $filename, $call->response_body ?: '');

        return response()->json([
            'saved' => true,
            'path' => "tests/Fixtures/{$filename}",
        ]);
    }
}

