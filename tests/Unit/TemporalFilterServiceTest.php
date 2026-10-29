<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Temporal\TemporalFilterService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Tests\TestCase;

class TemporalFilterServiceTest extends TestCase
{
    private TemporalFilterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TemporalFilterService();
    }

    public function test_resolves_exact_date(): void
    {
        $request = Request::create('/', 'GET', ['date' => '2026-08-17']);
        $result = $this->service->resolve($request);

        $this->assertEquals('2026-08-17', $result['selectedDate']);
        $this->assertNotNull($result['dateFilterInfo']);
        $this->assertEquals('Monday, 17 Aug 2026', $result['dateFilterInfo']['formatted']);
        $this->assertEquals('2026-08-16', $result['dateFilterInfo']['prev_date']);
        $this->assertEquals('2026-08-18', $result['dateFilterInfo']['next_date']);
    }

    public function test_resolves_time_slots(): void
    {
        // Early slot
        $reqEarly = Request::create('/', 'GET', ['date' => '2026-08-17', 'time_slot' => 'early']);
        $resEarly = $this->service->resolve($reqEarly);
        $this->assertEquals('11:00:00', $resEarly['dateStart']?->format('H:i:s'));
        $this->assertEquals('13:59:59', $resEarly['dateEnd']?->format('H:i:s'));
        $this->assertStringContainsString('Early Kickoff', $resEarly['dateFilterInfo']['time_slot_label']);

        // Afternoon slot
        $reqAft = Request::create('/', 'GET', ['date' => '2026-08-17', 'time_slot' => 'afternoon']);
        $resAft = $this->service->resolve($reqAft);
        $this->assertEquals('14:00:00', $resAft['dateStart']?->format('H:i:s'));
        $this->assertEquals('16:59:59', $resAft['dateEnd']?->format('H:i:s'));
        $this->assertStringContainsString('Prime Afternoon', $resAft['dateFilterInfo']['time_slot_label']);

        // Evening slot
        $reqEve = Request::create('/', 'GET', ['date' => '2026-08-17', 'time_slot' => 'evening']);
        $resEve = $this->service->resolve($reqEve);
        $this->assertEquals('17:00:00', $resEve['dateStart']?->format('H:i:s'));
        $this->assertEquals('23:59:59', $resEve['dateEnd']?->format('H:i:s'));
        $this->assertStringContainsString('Evening / Night', $resEve['dateFilterInfo']['time_slot_label']);
    }

    public function test_resolves_exact_time(): void
    {
        $request = Request::create('/', 'GET', ['date' => '2026-08-17', 'time' => '15:00']);
        $result = $this->service->resolve($request);

        $this->assertEquals('14:30:00', $result['dateStart']?->format('H:i:s'));
        $this->assertEquals('15:30:00', $result['dateEnd']?->format('H:i:s'));
    }

    public function test_resolves_presets(): void
    {
        $now = CarbonImmutable::now('UTC');

        $reqToday = Request::create('/', 'GET', ['preset' => 'today']);
        $resToday = $this->service->resolve($reqToday);
        $this->assertEquals($now->format('Y-m-d'), $resToday['selectedDate']);

        $reqYest = Request::create('/', 'GET', ['preset' => 'yesterday']);
        $resYest = $this->service->resolve($reqYest);
        $this->assertEquals($now->subDay()->format('Y-m-d'), $resYest['selectedDate']);

        $reqTom = Request::create('/', 'GET', ['preset' => 'tomorrow']);
        $resTom = $this->service->resolve($reqTom);
        $this->assertEquals($now->addDay()->format('Y-m-d'), $resTom['selectedDate']);
    }

    public function test_handles_invalid_date_gracefully(): void
    {
        $request = Request::create('/', 'GET', ['date' => 'not-a-valid-date']);
        $result = $this->service->resolve($request);

        $this->assertNull($result['selectedDate']);
        $this->assertNull($result['dateFilterInfo']);
    }
}

