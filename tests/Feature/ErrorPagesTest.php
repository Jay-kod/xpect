<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-error-403', fn () => abort(403));
        Route::get('/test-error-419', fn () => abort(419));
        Route::get('/test-error-429', fn () => abort(429));
        Route::get('/test-error-500', fn () => abort(500));
        Route::get('/test-error-503', fn () => abort(503));
    }

    public function test_404_error_page_renders_with_custom_xpect_design(): void
    {
        $response = $this->get('/non-existent-route-for-xpect-404');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('FIXTURE NOT FOUND');
        $response->assertSee('Requested Resource Not Found');
        $response->assertSee('Return to Dashboard');
        $response->assertSee('xpect');
    }

    public function test_403_forbidden_error_page_renders(): void
    {
        $response = $this->get('/test-error-403');

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Access Restricted');
        $response->assertSee('RESTRICTED BOUNDARY');
    }

    public function test_419_page_expired_renders(): void
    {
        $response = $this->get('/test-error-419');

        $response->assertStatus(419);
        $response->assertSee('419');
        $response->assertSee('Security Token Expired');
    }

    public function test_429_rate_limit_renders(): void
    {
        $response = $this->get('/test-error-429');

        $response->assertStatus(429);
        $response->assertSee('429');
        $response->assertSee('API Quota Guard Active');
    }

    public function test_500_server_error_page_renders(): void
    {
        $response = $this->get('/test-error-500');

        $response->assertStatus(500);
        $response->assertSee('500');
        $response->assertSee('Engine Calculation Error');
    }

    public function test_503_service_unavailable_page_renders(): void
    {
        $response = $this->get('/test-error-503');

        $response->assertStatus(503);
        $response->assertSee('503');
        $response->assertSee('Engine Under Maintenance');
    }
}
