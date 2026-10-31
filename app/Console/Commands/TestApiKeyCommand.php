<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Api\ApiKeyTesterService;
use Illuminate\Console\Command;

class TestApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:key
                            {provider=all : Provider to test (claude, chatgpt, gemini, football_data, api_football, or all)}
                            {--key= : Explicit API key to test instead of reading from .env}
                            {--json : Output raw JSON representation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test whether an external API key is valid and active';

    public function handle(ApiKeyTesterService $tester): int
    {
        $providerInput = strtolower(trim((string) $this->argument('provider')));
        $explicitKey = $this->option('key') ? (string) $this->option('key') : null;
        $asJson = (bool) $this->option('json');

        $providers = $providerInput === 'all'
            ? ['claude', 'chatgpt', 'gemini', 'football_data', 'api_football']
            : [$providerInput];

        $results = [];
        $hasFailure = false;

        foreach ($providers as $prov) {
            $result = $tester->testKey($prov, $explicitKey);
            $results[$prov] = $result->toArray();
            if (!$result->isValid) {
                $hasFailure = true;
            }
        }

        if ($asJson) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $hasFailure ? self::FAILURE : self::SUCCESS;
        }

        $this->newLine();
        $this->line(" <fg=white;bg=bright-blue;options=bold> XPECT EXTERNAL API KEY VERIFICATION </>");
        $this->line(" <fg=gray>Live endpoint probe via LoggedHttpClient with credential redaction</>");
        $this->newLine();

        $rows = [];
        foreach ($results as $prov => $res) {
            $statusBadge = $res['is_valid']
                ? '<fg=green;options=bold>✓ ACTIVE</>'
                : '<fg=red;options=bold>✕ FAILED</>';

            $httpBadge = $res['status_code'] > 0
                ? "HTTP {$res['status_code']} ({$res['duration_ms']}ms)"
                : 'Not Sent';

            $rows[] = [
                $prov,
                $statusBadge,
                $httpBadge,
                $res['message'],
            ];
        }

        $this->table(['Provider', 'Status', 'HTTP Code', 'Message'], $rows);
        $this->newLine();

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}

