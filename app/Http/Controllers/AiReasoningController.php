<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApiCall;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Preview;
use App\Services\Previews\NumberCheckGuard;
use App\Services\Previews\PreviewGenerator;
use App\Services\Previews\PromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AiReasoningController extends Controller
{
    public function __construct(
        protected readonly PromptBuilder $promptBuilder,
        protected readonly PreviewGenerator $previewGenerator,
        protected readonly NumberCheckGuard $guard
    ) {
    }

    /**
     * Display the AI Reasoning Studio and Permutation Explainer.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->filled('date') ? trim((string) $request->query('date')) : null;
        $dateFilterInfo = null;

        // 1. Available matches with predictions for selector dropdown
        $availableMatchesQuery = FootballMatch::whereHas('predictions')
            ->with(['homeTeam', 'awayTeam', 'competition'])
            ->orderBy('kickoff_at', 'desc');

        if ($selectedDate) {
            try {
                $parsedDate = \Carbon\CarbonImmutable::parse($selectedDate, 'UTC');
                $selectedDate = $parsedDate->format('Y-m-d');
                $availableMatchesQuery->whereBetween('kickoff_at', [
                    $parsedDate->startOfDay(),
                    $parsedDate->endOfDay(),
                ]);
                $dateFilterInfo = [
                    'date_str' => $selectedDate,
                    'formatted' => $parsedDate->format('l, d M Y'),
                ];
            } catch (\Throwable) {
                $selectedDate = null;
                $dateFilterInfo = null;
            }
        }

        $availableMatches = $availableMatchesQuery->take(60)->get();

        // 2. Resolve selected match
        $selectedMatchId = $request->query('match_id') ? (int) $request->query('match_id') : null;

        if ($selectedMatchId) {
            $selectedMatch = FootballMatch::with([
                'homeTeam',
                'awayTeam',
                'competition',
                'predictions' => fn ($q) => $q->latest(),
                'predictions.previews' => fn ($q) => $q->latest(),
            ])->find($selectedMatchId);
        } else {
            // Default to first match in available matches (which respects date filter) or scheduled match
            if ($availableMatches->isNotEmpty()) {
                $selectedMatch = FootballMatch::with([
                    'homeTeam',
                    'awayTeam',
                    'competition',
                    'predictions' => fn ($q) => $q->latest(),
                    'predictions.previews' => fn ($q) => $q->latest(),
                ])->find($availableMatches->first()->id);
            } else {
                $selectedMatch = FootballMatch::whereHas('predictions')
                    ->where('status', 'scheduled')
                    ->with([
                        'homeTeam',
                        'awayTeam',
                        'competition',
                        'predictions' => fn ($q) => $q->latest(),
                        'predictions.previews' => fn ($q) => $q->latest(),
                    ])
                    ->orderBy('kickoff_at', 'asc')
                    ->first();
            }
        }

        $prediction = $selectedMatch?->predictions->first();
        $mathBreakdown = null;
        $scoreGrid = [];
        $maxGridProb = 0.0;
        $aiPayload = null;
        $guardResult = null;
        $existingPreview = null;

        if ($prediction) {
            // 3. Mathematical Permutation Matrix & Derivation
            $mathBreakdown = $this->computeMathematicalDerivation($prediction);
            $scoreGridData = $this->computeScorelineMatrix(
                (float) $prediction->exp_home_goals,
                (float) $prediction->exp_away_goals
            );
            $scoreGrid = $scoreGridData['grid'];
            $maxGridProb = $scoreGridData['max_prob'];

            // 4. AI Structured Payload Inspector
            $aiPayload = $this->promptBuilder->buildPayload($prediction);

            // 5. Existing preview & Anti-Hallucination Guard inspection
            $existingPreview = $prediction->previews->first();
            if ($existingPreview) {
                $guardResult = $this->guard->check($existingPreview->text, $aiPayload['input_json']);
            }
        }

        // 6. Complete History of AI Reasoning & Previews
        $recentPreviews = Preview::with([
            'prediction.match.homeTeam',
            'prediction.match.awayTeam',
            'prediction.match.competition',
            'apiCall',
        ])
            ->latest()
            ->take(25)
            ->get();

        $hasAnthropicKey = !empty(config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY'));

        return view('ai-reasoning.index', [
            'availableMatches' => $availableMatches,
            'selectedMatch' => $selectedMatch,
            'prediction' => $prediction,
            'mathBreakdown' => $mathBreakdown,
            'scoreGrid' => $scoreGrid,
            'maxGridProb' => $maxGridProb,
            'aiPayload' => $aiPayload,
            'existingPreview' => $existingPreview,
            'guardResult' => $guardResult,
            'recentPreviews' => $recentPreviews,
            'hasAnthropicKey' => $hasAnthropicKey,
            'selectedDate' => $selectedDate,
            'dateFilterInfo' => $dateFilterInfo,
        ]);
    }

    /**
     * Trigger reasoning preview generation or simulation.
     */
    public function simulate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prediction_id' => 'required|integer|exists:predictions,id',
            'model' => 'nullable|string|max:64',
            'mode' => 'nullable|string|in:auto,simulation,live',
        ]);

        $prediction = Prediction::with(['match.homeTeam', 'match.awayTeam', 'match.competition'])->findOrFail($validated['prediction_id']);
        $hasKey = !empty(config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY'));
        $mode = $validated['mode'] ?? 'auto';
        $model = !empty($validated['model']) ? $validated['model'] : 'claude-3-5-sonnet-20241022';

        if ($mode === 'live' || ($mode === 'auto' && $hasKey)) {
            try {
                $preview = $this->previewGenerator->generate($prediction, $model);
                return redirect()->route('ai-reasoning', ['match_id' => $prediction->match_id])
                    ->with('success', "Live Claude AI reasoning generated successfully (Preview #{$preview->id})! Saved to persistent history.");
            } catch (Throwable $e) {
                return redirect()->route('ai-reasoning', ['match_id' => $prediction->match_id])
                    ->with('error', "Live Anthropic call failed: {$e->getMessage()}. Switch to Simulation Mode to preview offline.");
            }
        }

        // Deterministic Simulation Mode (Zero-HTTP, safe offline guarantee)
        $payload = $this->promptBuilder->buildPayload($prediction);
        $input = $payload['input_json'];

        $homeName = $prediction->match?->homeTeam?->name ?? 'Home';
        $awayName = $prediction->match?->awayTeam?->name ?? 'Away';
        $pHome = round((float) $prediction->p_home * 100, 1);
        $pDraw = round((float) $prediction->p_draw * 100, 1);
        $pAway = round((float) $prediction->p_away * 100, 1);
        $expHome = round((float) $prediction->exp_home_goals, 2);
        $expAway = round((float) $prediction->exp_away_goals, 2);
        $topScore = $prediction->top_scorelines[0] ?? ['home' => 1, 'away' => 1];
        $topScoreStr = "{$topScore['home']}-{$topScore['away']}";

        $simulatedText = "{$homeName} host {$awayName} with the mathematical model projecting a {$pHome}% home win probability versus {$pAway}% for the away side and {$pDraw}% for the draw. Expected goals indicate {$expHome} for {$homeName} and {$expAway} for {$awayName}, yielding {$topScoreStr} as the highest probability scoreline under the Poisson distribution. Note that high match-to-match variance applies.";

        // Run anti-hallucination guard
        $guardResult = $this->guard->check($simulatedText, $input);

        // Record simulated API call in api_calls table for auditability
        $apiCall = ApiCall::create([
            'source' => 'anthropic',
            'method' => 'POST',
            'url' => 'https://api.anthropic.com/v1/messages',
            'request_params' => [],
            'request_body' => json_encode([
                'model' => $model . '-simulation',
                'messages' => [['role' => 'user', 'content' => 'Simulated prompt payload']],
            ]),
            'request_headers' => ['content-type' => 'application/json'],
            'response_status' => 200,
            'response_body' => json_encode([
                'id' => 'msg_sim_' . uniqid(),
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => $simulatedText]],
                'model' => $model,
                'usage' => ['input_tokens' => 280, 'output_tokens' => 75],
            ]),
            'response_headers' => ['content-type' => 'application/json'],
            'duration_ms' => 12,
            'error' => null,
            'input_tokens' => 280,
            'output_tokens' => 75,
            'counts_toward_quota' => false,
            'served_from_cache' => false,
            'cache_key' => sha1('anthropic-sim-' . $prediction->id . '-' . microtime(true)),
            'triggered_by' => 'ai-reasoning:simulate',
            'called_at' => now(),
        ]);

        $preview = Preview::create([
            'prediction_id' => $prediction->id,
            'api_call_id' => $apiCall->id,
            'model' => $model . ' (Simulated)',
            'prompt_version' => $payload['prompt_version'],
            'text' => $simulatedText,
            'flagged' => $guardResult->isFlagged(),
        ]);

        return redirect()->route('ai-reasoning', ['match_id' => $prediction->match_id])
            ->with('success', "Reasoning simulation created and verified by Anti-Hallucination Guard (Preview #{$preview->id})! Saved to persistent history.");
    }

    /**
     * Interactive Anti-Hallucination Sandbox API: test arbitrary text against model output numbers.
     */
    public function checkGuard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prediction_id' => 'required|integer|exists:predictions,id',
            'text' => 'required|string|max:5000',
        ]);

        $prediction = Prediction::findOrFail($validated['prediction_id']);
        $payload = $this->promptBuilder->buildPayload($prediction);
        $result = $this->guard->check($validated['text'], $payload['input_json']);

        return response()->json([
            'flagged' => $result->isFlagged(),
            'disallowed_numbers' => $result->disallowedNumbers,
            'allowed_numbers_count' => $result->allowedNumbersCount,
        ]);
    }

    /**
     * Compute step-by-step mathematical derivation from features.
     */
    protected function computeMathematicalDerivation(Prediction $prediction): array
    {
        $features = $prediction->features ?? [];
        $homeFeatures = $features['home'] ?? [];
        $awayFeatures = $features['away'] ?? [];

        $lambdaH = (float) $prediction->exp_home_goals;
        $lambdaA = (float) $prediction->exp_away_goals;

        $homeAttXg = (float) ($homeFeatures['home_xg_attack'] ?? $homeFeatures['attack'] ?? 1.15);
        $homeAttActual = (float) ($homeFeatures['home_attack'] ?? 1.10);
        $blendedHomeAtt = round(0.70 * $homeAttXg + 0.30 * $homeAttActual, 3);

        $awayDefXg = (float) ($awayFeatures['away_xg_defence'] ?? $awayFeatures['defence'] ?? 1.05);
        $awayDefActual = (float) ($awayFeatures['away_defence'] ?? 1.00);
        $blendedAwayDef = round(0.70 * $awayDefXg + 0.30 * $awayDefActual, 3);

        $awayAttXg = (float) ($awayFeatures['away_xg_attack'] ?? $awayFeatures['attack'] ?? 1.05);
        $awayAttActual = (float) ($awayFeatures['away_attack'] ?? 1.00);
        $blendedAwayAtt = round(0.70 * $awayAttXg + 0.30 * $awayAttActual, 3);

        $homeDefXg = (float) ($homeFeatures['home_xg_defence'] ?? $homeFeatures['defence'] ?? 1.00);
        $homeDefActual = (float) ($homeFeatures['home_defence'] ?? 0.95);
        $blendedHomeDef = round(0.70 * $homeDefXg + 0.30 * $homeDefActual, 3);

        $leagueAvgH = (float) ($homeFeatures['league_avg_home_goals'] ?? 1.55);
        $leagueAvgA = (float) ($homeFeatures['league_avg_away_goals'] ?? 1.25);

        return [
            'blended_home_attack' => $blendedHomeAtt,
            'blended_away_defence' => $blendedAwayDef,
            'blended_away_attack' => $blendedAwayAtt,
            'blended_home_defence' => $blendedHomeDef,
            'league_avg_home' => $leagueAvgH,
            'league_avg_away' => $leagueAvgA,
            'lambda_home' => $lambdaH,
            'lambda_away' => $lambdaA,
            'dixon_coles_rho' => -0.06,
            'h2h_home_modifier' => (float) ($features['h2h_modifier'] ?? 1.0),
            'home_rest_days' => $homeFeatures['rest_days'] ?? null,
            'away_rest_days' => $awayFeatures['rest_days'] ?? null,
            'p_home' => round((float) $prediction->p_home * 100, 1),
            'p_draw' => round((float) $prediction->p_draw * 100, 1),
            'p_away' => round((float) $prediction->p_away * 100, 1),
            'model_version' => $prediction->model_version,
        ];
    }

    /**
     * Compute 6x6 scoreline probability matrix using Dixon-Coles bivariate Poisson.
     * Returns 0..5 goals grid for both teams with probabilities.
     */
    protected function computeScorelineMatrix(float $lambdaH, float $lambdaA): array
    {
        $maxGoals = 5;
        $rho = -0.06;
        $homeProbs = [];
        $awayProbs = [];

        for ($i = 0; $i <= $maxGoals; $i++) {
            $homeProbs[$i] = $this->poissonProbability($lambdaH, $i);
            $awayProbs[$i] = $this->poissonProbability($lambdaA, $i);
        }

        $grid = [];
        $maxProb = 0.0;
        $totalSum = 0.0;

        for ($h = 0; $h <= $maxGoals; $h++) {
            for ($a = 0; $a <= $maxGoals; $a++) {
                $tau = 1.0;
                if ($h === 0 && $a === 0) {
                    $tau = 1.0 - ($lambdaH * $lambdaA * $rho);
                } elseif ($h === 0 && $a === 1) {
                    $tau = 1.0 + ($lambdaH * $rho);
                } elseif ($h === 1 && $a === 0) {
                    $tau = 1.0 + ($lambdaA * $rho);
                } elseif ($h === 1 && $a === 1) {
                    $tau = 1.0 - $rho;
                }

                $p = max(0.0, $tau * $homeProbs[$h] * $awayProbs[$a]);
                $grid[$h][$a] = $p;
                $totalSum += $p;
            }
        }

        // Normalize & format
        for ($h = 0; $h <= $maxGoals; $h++) {
            for ($a = 0; $a <= $maxGoals; $a++) {
                $normP = $totalSum > 0 ? ($grid[$h][$a] / $totalSum) : 0.0;
                $pct = round($normP * 100, 1);
                $grid[$h][$a] = [
                    'prob' => $normP,
                    'pct' => $pct,
                    'outcome' => ($h > $a ? 'home' : ($h === $a ? 'draw' : 'away')),
                ];
                if ($normP > $maxProb) {
                    $maxProb = $normP;
                }
            }
        }

        return [
            'grid' => $grid,
            'max_prob' => $maxProb,
        ];
    }

    /**
     * Compute Poisson probability P(k; lambda) = (lambda^k * e^-lambda) / k!
     */
    protected function poissonProbability(float $lambda, int $k): float
    {
        if ($lambda <= 0) {
            return $k === 0 ? 1.0 : 0.0;
        }

        $logProb = -$lambda + ($k * log($lambda)) - $this->logFactorial($k);
        return exp($logProb);
    }

    /**
     * Natural log of k! for numerical stability.
     */
    protected function logFactorial(int $k): float
    {
        static $cache = [0 => 0.0, 1 => 0.0];
        if (isset($cache[$k])) {
            return $cache[$k];
        }

        $res = 0.0;
        for ($i = 2; $i <= $k; $i++) {
            $res += log($i);
        }

        return $cache[$k] = $res;
    }
}
