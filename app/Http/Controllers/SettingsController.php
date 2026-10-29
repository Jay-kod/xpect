<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApiCall;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\PredictionEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function index(): View
    {
        $dbPath = config('database.connections.sqlite.database');
        $dbSize = file_exists($dbPath) ? round(filesize($dbPath) / 1024 / 1024, 2) . ' MB' : 'N/A';

        $backupDir = storage_path('backups');
        $backupCount = is_dir($backupDir) ? count(glob($backupDir . '/*.sqlite')) : 0;

        $stats = [
            'db_size' => $dbSize,
            'matches_count' => FootballMatch::count(),
            'predictions_count' => Prediction::count(),
            'evaluations_count' => PredictionEvaluation::count(),
            'api_calls_count' => ApiCall::count(),
            'backups_count' => $backupCount,
        ];

        $keys = [
            'football_data_key' => $this->maskSecret((string) env('FOOTBALL_DATA_API_KEY')),
            'api_football_key' => $this->maskSecret((string) env('API_FOOTBALL_KEY')),
            'anthropic_key' => $this->maskSecret((string) env('ANTHROPIC_API_KEY')),
            'anthropic_model' => (string) env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ];

        return view('settings.index', [
            'stats' => $stats,
            'keys' => $keys,
        ]);
    }

    public function updateKeys(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'football_data_key' => 'nullable|string|max:100',
            'api_football_key' => 'nullable|string|max:100',
            'anthropic_key' => 'nullable|string|max:150',
            'anthropic_model' => 'nullable|string|max:100',
        ]);

        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            return back()->with('error', '.env file not found.');
        }

        $content = (string) file_get_contents($envFile);

        $updates = [];
        if (!empty($validated['football_data_key'])) {
            $updates['FOOTBALL_DATA_API_KEY'] = trim($validated['football_data_key']);
        }
        if (!empty($validated['api_football_key'])) {
            $updates['API_FOOTBALL_KEY'] = trim($validated['api_football_key']);
        }
        if (!empty($validated['anthropic_key'])) {
            $updates['ANTHROPIC_API_KEY'] = trim($validated['anthropic_key']);
        }
        if (!empty($validated['anthropic_model'])) {
            $updates['ANTHROPIC_MODEL'] = trim($validated['anthropic_model']);
        }

        foreach ($updates as $key => $val) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $content);
            } else {
                $content .= "\n{$key}={$val}";
            }
        }

        file_put_contents($envFile, $content);

        return back()->with('success', 'API Keys and settings updated in .env successfully.');
    }

    public function runAction(Request $request): RedirectResponse
    {
        $action = (string) $request->input('action');

        try {
            $output = match ($action) {
                'sync_matches' => (function () {
                    Artisan::call('sync:matches', ['competition' => 'premier-league']);
                    return Artisan::output();
                })(),
                'rebuild_standings' => (function () {
                    Artisan::call('standings:rebuild', ['competition' => 'premier-league']);
                    return Artisan::output();
                })(),
                'evaluate_predictions' => (function () {
                    Artisan::call('evaluate:predictions', ['--include-backtest' => true]);
                    return Artisan::output();
                })(),
                'app_backup' => (function () {
                    Artisan::call('app:backup');
                    return Artisan::output();
                })(),
                default => throw new \InvalidArgumentException("Invalid action: {$action}"),
            };

            return back()->with('success', "Action [{$action}] completed successfully:\n" . trim($output));
        } catch (Throwable $e) {
            return back()->with('error', "Action failed: " . $e->getMessage());
        }
    }

    protected function maskSecret(string $secret): string
    {
        $trimmed = trim($secret);
        if ($trimmed === '') {
            return 'Not Configured';
        }

        if (strlen($trimmed) <= 8) {
            return '********';
        }

        return substr($trimmed, 0, 4) . '...' . substr($trimmed, -4);
    }
}

