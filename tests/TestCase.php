<?php

namespace Tests;

use App\Services\Evaluation\Baselines;
use App\Services\Matches\MatchHistoryService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        MatchHistoryService::clearCache();
        Baselines::clearCache();
        parent::tearDown();
    }
}
