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
    /**
     * Display the application settings, credentials, and maintenance panel.
     */
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

        $currentProvider = (string) env('AI_PROVIDER', 'claude');
        if (!in_array($currentProvider, ['claude', 'chatgpt', 'gemini'], true)) {
            $currentProvider = 'claude';
        }

        $keys = [
            'ai_provider' => $currentProvider,
            'ai_model' => (string) (env('AI_MODEL') ?: env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest')),
            'anthropic_key' => $this->maskSecret((string) env('ANTHROPIC_API_KEY')),
            'anthropic_has_key' => !empty(env('ANTHROPIC_API_KEY')),
            'anthropic_model' => (string) env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
            'openai_key' => $this->maskSecret((string) env('OPENAI_API_KEY')),
            'openai_has_key' => !empty(env('OPENAI_API_KEY')),
            'openai_model' => (string) env('OPENAI_MODEL', 'gpt-4o'),
            'gemini_key' => $this->maskSecret((string) env('GEMINI_API_KEY')),
            'gemini_has_key' => !empty(env('GEMINI_API_KEY')),
            'gemini_model' => (string) env('GEMINI_MODEL', 'gemini-1.5-pro'),
            'football_data_key' => $this->maskSecret((string) env('FOOTBALL_DATA_API_KEY')),
            'football_data_has_key' => !empty(env('FOOTBALL_DATA_API_KEY')),
            'api_football_key' => $this->maskSecret((string) env('API_FOOTBALL_KEY')),
            'api_football_has_key' => !empty(env('API_FOOTBALL_KEY')),
        ];

        $providers = [
            'claude' => [
                'name' => 'Claude (Anthropic)',
                'badge' => 'Anthropic Claude',
                'description' => 'Claude 3.5 Sonnet & Haiku with state-of-the-art statistical reasoning & strict number consistency.',
                'default_model' => 'claude-3-5-sonnet-latest',
                'models' => [
                    'claude-3-5-sonnet-latest' => 'claude-3-5-sonnet-latest (Recommended - Highest Reasoning)',
                    'claude-3-5-haiku-latest' => 'claude-3-5-haiku-latest (Fastest & Cheaper)',
                    'claude-3-opus-latest' => 'claude-3-opus-latest (Deep Analytical)',
                ],
            ],
            'chatgpt' => [
                'name' => 'ChatGPT (OpenAI)',
                'badge' => 'OpenAI ChatGPT',
                'description' => 'GPT-4o & o1 reasoning models with multimodal preview synthesis & analysis.',
                'default_model' => 'gpt-4o',
                'models' => [
                    'gpt-4o' => 'gpt-4o (Flagship Model - Fast & Accurate)',
                    'gpt-4o-mini' => 'gpt-4o-mini (Lightweight & Efficient)',
                    'o1-preview' => 'o1-preview (Extended Chain-of-Thought Reasoning)',
                    'o1-mini' => 'o1-mini (Fast Chain-of-Thought)',
                ],
            ],
            'gemini' => [
                'name' => 'Gemini (Google)',
                'badge' => 'Google Gemini',
                'description' => 'Gemini 1.5 Pro & 2.0 Flash with low latency & complex contextual summarization.',
                'default_model' => 'gemini-1.5-pro',
                'models' => [
                    'gemini-1.5-pro' => 'gemini-1.5-pro (Flagship Complex Reasoning)',
                    'gemini-1.5-flash' => 'gemini-1.5-flash (High Speed & Low Latency)',
                    'gemini-2.0-flash' => 'gemini-2.0-flash (Next-Gen Experimental)',
                ],
            ],
        ];

        return view('settings.index', [
            'stats' => $stats,
            'keys' => $keys,
            'providers' => $providers,
        ]);
    }

    /**
     * Update API credentials and model configuration in the local .env file.
     */
    public function updateKeys(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_provider' => 'nullable|string|in:claude,chatgpt,gemini,anthropic,openai,google',
            'ai_model' => 'nullable|string|max:100',
            'anthropic_key' => 'nullable|string|max:150',
            'anthropic_model' => 'nullable|string|max:100',
            'openai_key' => 'nullable|string|max:150',
            'gemini_key' => 'nullable|string|max:150',
            'football_data_key' => 'nullable|string|max:100',
            'api_football_key' => 'nullable|string|max:100',
        ]);

        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            return back()->with('error', '.env file not found.');
        }

        $content = (string) file_get_contents($envFile);
        $updates = [];

        // AI Provider mapping
        if (!empty($validated['ai_provider'])) {
            $provider = match (strtolower(trim($validated['ai_provider']))) {
                'openai', 'chatgpt' => 'chatgpt',
                'gemini', 'google' => 'gemini',
                default => 'claude',
            };
            $updates['AI_PROVIDER'] = $provider;
        }

        // Active AI Model
        if (!empty($validated['ai_model'])) {
            $model = trim($validated['ai_model']);
            $updates['AI_MODEL'] = $model;

            // Maintain ANTHROPIC_MODEL for backward compatibility
            if (($validated['ai_provider'] ?? 'claude') === 'claude') {
                $updates['ANTHROPIC_MODEL'] = $model;
            }
        } elseif (!empty($validated['anthropic_model'])) {
            $updates['ANTHROPIC_MODEL'] = trim($validated['anthropic_model']);
        }

        // API Keys (only update if non-empty string submitted)
        if (!empty($validated['anthropic_key'])) {
            $updates['ANTHROPIC_API_KEY'] = trim($validated['anthropic_key']);
        }
        if (!empty($validated['openai_key'])) {
            $updates['OPENAI_API_KEY'] = trim($validated['openai_key']);
        }
        if (!empty($validated['gemini_key'])) {
            $updates['GEMINI_API_KEY'] = trim($validated['gemini_key']);
        }
        if (!empty($validated['football_data_key'])) {
            $updates['FOOTBALL_DATA_API_KEY'] = trim($validated['football_data_key']);
        }
        if (!empty($validated['api_football_key'])) {
            $updates['API_FOOTBALL_KEY'] = trim($validated['api_football_key']);
        }

        foreach ($updates as $key => $val) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $content);
            } else {
                $content .= "\n{$key}={$val}";
            }
        }

        file_put_contents($envFile, $content);

        return back()->with('success', 'API credentials, AI provider, and model parameters updated in .env successfully.');
    }

    /**
     * Trigger manual system operations and database maintenance tasks.
     */
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

    /**
     * Mask API keys and secrets for safe UI rendering.
     */
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
