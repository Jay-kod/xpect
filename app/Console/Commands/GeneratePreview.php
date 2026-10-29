<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Services\Previews\PreviewGenerator;
use Illuminate\Console\Command;
use Throwable;

class GeneratePreview extends Command
{
    protected $signature = 'preview:generate {prediction_id : ID of the prediction record} {--model= : Override the default Anthropic model} {--force : Force generate another preview even if one exists} {--mock : Simulate Claude response for local testing without API key}';
    protected $description = 'Generate an AI match preview from structured prediction numbers via Claude API';

    public function handle(PreviewGenerator $previewGenerator): int
    {
        $predictionId = (int) $this->argument('prediction_id');
        $prediction = Prediction::with(['match.homeTeam', 'match.awayTeam', 'match.competition', 'previews'])->find($predictionId);

        if (!$prediction) {
            $this->error("Prediction #{$predictionId} not found in the database.");
            return self::FAILURE;
        }

        $match = $prediction->match;
        $homeName = $match?->homeTeam?->name ?? 'Home';
        $awayName = $match?->awayTeam?->name ?? 'Away';
        $compName = $match?->competition?->name ?? 'League';

        $this->info("========================================================");
        $this->info(" XPECT AI PREVIEW GENERATOR");
        $this->info("========================================================");
        $this->line("Fixture:       {$homeName} vs {$awayName} ({$compName})");
        $this->line("Prediction ID: #{$prediction->id}");
        $this->line("Model Version: {$prediction->model_version}");
        $this->line("Probabilities: H: " . round((float) $prediction->p_home * 100, 1) . "% | D: " . round((float) $prediction->p_draw * 100, 1) . "% | A: " . round((float) $prediction->p_away * 100, 1) . "%");
        $this->line("Expected Gls:  {$prediction->exp_home_goals} - {$prediction->exp_away_goals}");
        $this->newLine();

        $existing = $prediction->previews()->latest()->first();
        if ($existing && !$this->option('force')) {
            $this->warn("A preview already exists for this prediction (ID: #{$existing->id}). Use --force to generate a new one.");
            $this->newLine();
            $this->line("<comment>--- Existing Preview ({$existing->model}) ---</comment>");
            $this->line($existing->text);
            $this->newLine();
            $this->line("Flagged: " . ($existing->flagged ? '<error>YES</error>' : '<info>NO</info>'));
            $this->line("API Call ID: #{$existing->api_call_id}");
            return self::SUCCESS;
        }

        $modelOverride = $this->option('model');

        if ($this->option('mock')) {
            $this->warn('Using simulated Claude response (--mock mode).');
            $pHome = round((float) $prediction->p_home * 100, 1);
            $pDraw = round((float) $prediction->p_draw * 100, 1);
            $pAway = round((float) $prediction->p_away * 100, 1);
            $expHome = round((float) $prediction->exp_home_goals, 2);
            $expAway = round((float) $prediction->exp_away_goals, 2);
            $topScore = $prediction->top_scorelines[0] ?? ['home' => 1, 'away' => 1];
            $scoreStr = "{$topScore['home']}-{$topScore['away']}";

            $mockText = "{$homeName} host {$awayName} with the mathematical model projecting a {$pHome}% home win probability versus {$pAway}% for the away side and {$pDraw}% for the draw. Expected goals indicate {$expHome} for {$homeName} and {$expAway} for {$awayName}, yielding {$scoreStr} as the highest probability scoreline under the Poisson distribution. Note that high match-to-match variance applies.";

            \Illuminate\Support\Facades\Http::fake([
                'https://api.anthropic.com/v1/messages' => \Illuminate\Support\Facades\Http::response([
                    'id' => 'msg_simulated_' . uniqid(),
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [['type' => 'text', 'text' => $mockText]],
                    'model' => $modelOverride ?: 'claude-3-5-sonnet-20241022',
                    'usage' => ['input_tokens' => 295, 'output_tokens' => 74],
                ], 200),
            ]);
            config(['services.anthropic.key' => config('services.anthropic.key') ?: 'sk-ant-simulated-key']);
        }

        $this->info("Calling Anthropic Claude API via LoggedHttpClient...");

        try {
            $preview = $previewGenerator->generate($prediction, $modelOverride);
        } catch (Throwable $e) {
            $this->error("Failed generating preview: " . $e->getMessage());
            return self::FAILURE;
        }

        $apiCall = $preview->apiCall;

        $this->newLine();
        $this->info("--- AI Generated Match Preview ({$preview->model}) ---");
        $this->line($preview->text);
        $this->newLine();

        $this->info("--- Guard & Audit Verification ---");
        $this->line("Prompt Version:      {$preview->prompt_version}");
        $this->line("Number-Check Guard:  " . ($preview->flagged ? '<error>FLAGGED (unverified numbers detected)</error>' : '<info>PASSED (all numbers verified in model input)</info>'));
        $this->line("API Call ID:         #{$preview->api_call_id}");
        if ($apiCall) {
            $this->line("Duration:            {$apiCall->duration_ms} ms");
            $this->line("Tokens:              Input: {$apiCall->input_tokens} | Output: {$apiCall->output_tokens}");
        }
        $this->info("========================================================");

        return self::SUCCESS;
    }
}
