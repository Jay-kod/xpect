<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Prediction;
use App\Models\PredictionEvaluation;
use App\Services\Evaluation\Evaluator;
use App\Services\Evaluation\Metrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackRecordController extends Controller
{
    public function index(Request $request, Evaluator $evaluator, Metrics $metrics): View
    {
        $compSlug = $request->get('competition', 'premier-league');
        $competition = Competition::where('slug', $compSlug)->first();

        // 1. Fetch evaluated predictions (both live and backtests)
        $evaluationsQuery = PredictionEvaluation::with([
            'prediction.match.homeTeam',
            'prediction.match.awayTeam',
            'prediction.match.competition',
        ]);

        if ($competition) {
            $evaluationsQuery->whereHas('prediction.match', function ($q) use ($competition) {
                $q->where('competition_id', $competition->id);
            });
        }

        $totalEvaluations = $evaluationsQuery->count();

        // 2. Compute aggregate evaluation stats using Evaluator service if we have predictions
        $predictions = Prediction::with(['match.competition', 'match.homeTeam', 'match.awayTeam'])
            ->whereHas('evaluation')
            ->whereHas('match', function ($q) use ($competition) {
                if ($competition) {
                    $q->where('competition_id', $competition->id);
                }
                $q->where('status', 'finished');
            })
            ->get();

        $stats = $predictions->isNotEmpty() 
            ? $evaluator->evaluate($predictions, $competition) 
            : [
                'sample_size' => 0,
                'model' => ['accuracy' => 0, 'log_loss' => 0, 'brier' => 0],
                'baselines' => [
                    'historical_base_rates' => ['accuracy' => 0, 'log_loss' => 0, 'brier' => 0],
                    'always_home' => ['accuracy' => 0, 'log_loss' => 0, 'brier' => 0],
                    'uniform' => ['accuracy' => 0, 'log_loss' => 0, 'brier' => 0],
                ],
                'comparison' => [
                    'beats_always_home' => false,
                    'beats_uniform' => false,
                    'beats_historical_base_rate' => false,
                    'all_baselines_beaten' => false,
                ],
                'calibration_table' => [],
            ];

        // 3. Paginated list of recent evaluations
        $evaluations = (clone $evaluationsQuery)
            ->orderBy('evaluated_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('track-record.index', [
            'competition' => $competition,
            'totalEvaluations' => $totalEvaluations,
            'stats' => $stats,
            'evaluations' => $evaluations,
        ]);
    }
}

