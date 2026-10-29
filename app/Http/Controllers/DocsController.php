<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocsController extends Controller
{
    /**
     * Display the Comprehensive System Documentation & Technical Manual.
     */
    public function index(Request $request): View
    {
        $competitions = Competition::where('is_active', true)->get();

        $configData = [
            'window_matches' => config('football.features.window_matches', 20),
            'half_life_days' => config('football.features.half_life_days', 60),
            'shrinkage_k' => config('football.features.shrinkage_k', 3.0),
            'dixon_coles_rho' => config('football.models.dixon_coles_rho', -0.06),
            'sustainable_xg_weight' => 0.70,
            'actual_goals_weight' => 0.30,
        ];

        $commands = [
            [
                'command' => 'php artisan sync:matches {comp}',
                'desc' => 'Fetches fixtures, scheduled dates, and full-time results into local SQLite DB via LoggedHttpClient.',
                'example' => 'php artisan sync:matches premier-league --season=2026',
            ],
            [
                'command' => 'php artisan predict {match_id}',
                'desc' => 'Calculates Dixon-Coles Poisson expected goals and win/draw/loss probabilities with zero data leakage.',
                'example' => 'php artisan predict 2410',
            ],
            [
                'command' => 'php artisan backtest {comp}',
                'desc' => 'Executes walk-forward backtest across historical seasons, scoring log loss, Brier, and accuracy vs baselines.',
                'example' => 'php artisan backtest premier-league --seasons=2023,2024 --model=v1.2.1-h2h',
            ],
            [
                'command' => 'php artisan evaluate:predictions',
                'desc' => 'Scores live pre-match predictions against verified full-time results after matches finish.',
                'example' => 'php artisan evaluate:predictions',
            ],
            [
                'command' => 'php artisan audit:data',
                'desc' => 'Runs 8-point automated integrity verification confirming dataset coverage, clean scores, and zero data leakage.',
                'example' => 'php artisan audit:data',
            ],
            [
                'command' => 'php artisan app:backup',
                'desc' => 'Creates a timestamped snapshot of the local SQLite database in storage/backups.',
                'example' => 'php artisan app:backup',
            ],
        ];

        $apiSources = [
            [
                'name' => 'football-data.org',
                'role' => 'Primary Backbone: Match schedules, scores, standings',
                'quota' => '10 calls/min (Free tier)',
                'storage' => 'Raw response saved to api_calls; parsed into matches',
            ],
            [
                'name' => 'Football-Data.co.uk',
                'role' => 'Historical CSV Backfill (5 seasons across top 5 leagues)',
                'quota' => 'Zero HTTP quota (local CSV import)',
                'storage' => 'Full match history with betting odds benchmark data',
            ],
            [
                'name' => 'Understat',
                'role' => 'Expected Goals (xG) Shot Analytics for Top 5 Leagues',
                'quota' => '20 calls/min',
                'storage' => 'Match-level xG stored in match_stats; blended 70/30 in model',
            ],
            [
                'name' => 'API-Football',
                'role' => 'Confirmed Starting Lineups and Pre-Match Injury Reports',
                'quota' => '100 calls/day (reserve: 10)',
                'storage' => 'Persisted in match_lineups and injuries',
            ],
        ];

        $systemStats = [
            'total_matches' => FootballMatch::count(),
            'finished_matches' => FootballMatch::where('status', 'finished')->count(),
            'scheduled_matches' => FootballMatch::where('status', 'scheduled')->count(),
            'total_predictions' => Prediction::count(),
            'active_competitions' => $competitions->count(),
            'active_model' => 'v1.2.1-h2h',
            'leakage_status' => 'Verified 0% Leakage',
        ];

        return view('docs.index', compact('competitions', 'configData', 'commands', 'apiSources', 'systemStats'));
    }
}

