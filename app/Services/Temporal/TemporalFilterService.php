<?php

declare(strict_types=1);

namespace App\Services\Temporal;

use App\Models\FootballMatch;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class TemporalFilterService
{
    /**
     * Resolve date and time filter parameters from request.
     *
     * @return array{
     *     selectedDate: ?string,
     *     selectedTimeSlot: ?string,
     *     selectedTime: ?string,
     *     dateStart: ?CarbonImmutable,
     *     dateEnd: ?CarbonImmutable,
     *     dateFilterInfo: ?array<string, mixed>,
     *     availableMatchDates: Collection
     * }
     */
    public function resolve(Request $request, ?int $competitionId = null, ?int $season = null): array
    {
        $nowUtc = CarbonImmutable::now('UTC');
        $preset = $request->filled('preset') ? trim((string) $request->get('preset')) : null;
        $selectedDate = null;

        if ($preset === 'today') {
            $selectedDate = $nowUtc->format('Y-m-d');
        } elseif ($preset === 'yesterday') {
            $selectedDate = $nowUtc->subDay()->format('Y-m-d');
        } elseif ($preset === 'tomorrow') {
            $selectedDate = $nowUtc->addDay()->format('Y-m-d');
        } elseif ($preset === 'weekend') {
            // If today is Sunday or Saturday, use today; else next Saturday
            $dayOfWeek = (int) $nowUtc->format('w'); // 0 = Sun, 6 = Sat
            if ($dayOfWeek === 6 || $dayOfWeek === 0) {
                $selectedDate = $nowUtc->format('Y-m-d');
            } else {
                $selectedDate = $nowUtc->next(CarbonImmutable::SATURDAY)->format('Y-m-d');
            }
        } elseif ($preset === 'next_matchday') {
            $next = FootballMatch::when($competitionId, fn($q) => $q->where('competition_id', $competitionId))
                ->where('status', 'scheduled')
                ->where('kickoff_at', '>=', $nowUtc)
                ->orderBy('kickoff_at', 'asc')
                ->first();
            $selectedDate = $next?->kickoff_at?->format('Y-m-d') ?? $nowUtc->format('Y-m-d');
        } elseif ($preset === 'last_matchday') {
            $last = FootballMatch::when($competitionId, fn($q) => $q->where('competition_id', $competitionId))
                ->where('status', 'finished')
                ->orderBy('kickoff_at', 'desc')
                ->first();
            $selectedDate = $last?->kickoff_at?->format('Y-m-d') ?? $nowUtc->format('Y-m-d');
        } elseif ($request->filled('date')) {
            $selectedDate = trim((string) $request->get('date'));
        }

        $selectedTimeSlot = $request->filled('time_slot') ? trim((string) $request->get('time_slot')) : null;
        if ($selectedTimeSlot === 'all') {
            $selectedTimeSlot = null;
        }

        $selectedTime = $request->filled('time') ? trim((string) $request->get('time')) : null;

        $dateFilterInfo = null;
        $dateStart = null;
        $dateEnd = null;

        if ($selectedDate) {
            try {
                $parsedDate = CarbonImmutable::parse($selectedDate, 'UTC');
                $selectedDate = $parsedDate->format('Y-m-d');

                // Compute exact start & end based on time or time_slot
                if ($selectedTime) {
                    $parsedTime = CarbonImmutable::parse($selectedDate . ' ' . $selectedTime, 'UTC');
                    $dateStart = $parsedTime->subMinutes(30);
                    $dateEnd = $parsedTime->addMinutes(30);
                } elseif ($selectedTimeSlot === 'early') {
                    $dateStart = $parsedDate->setTime(11, 0, 0);
                    $dateEnd = $parsedDate->setTime(13, 59, 59);
                } elseif ($selectedTimeSlot === 'afternoon') {
                    $dateStart = $parsedDate->setTime(14, 0, 0);
                    $dateEnd = $parsedDate->setTime(16, 59, 59);
                } elseif ($selectedTimeSlot === 'evening') {
                    $dateStart = $parsedDate->setTime(17, 0, 0);
                    $dateEnd = $parsedDate->setTime(23, 59, 59);
                } else {
                    $dateStart = $parsedDate->startOfDay();
                    $dateEnd = $parsedDate->endOfDay();
                }

                $slotLabel = match ($selectedTimeSlot) {
                    'early' => 'Early Kickoff (11:00 – 14:00 UTC)',
                    'afternoon' => 'Prime Afternoon (14:00 – 17:00 UTC)',
                    'evening' => 'Evening / Night (17:00+ UTC)',
                    default => $selectedTime ? "Kickoff around {$selectedTime} UTC" : null,
                };

                $dateFilterInfo = [
                    'date_str' => $selectedDate,
                    'formatted' => $parsedDate->format('l, d M Y'),
                    'prev_date' => $parsedDate->subDay()->format('Y-m-d'),
                    'next_date' => $parsedDate->addDay()->format('Y-m-d'),
                    'is_today' => $selectedDate === $nowUtc->format('Y-m-d'),
                    'is_yesterday' => $selectedDate === $nowUtc->subDay()->format('Y-m-d'),
                    'is_tomorrow' => $selectedDate === $nowUtc->addDay()->format('Y-m-d'),
                    'time_slot' => $selectedTimeSlot,
                    'time_slot_label' => $slotLabel,
                    'time_str' => $selectedTime,
                    'label' => $slotLabel ? $parsedDate->format('d M Y') . ' &bull; ' . $slotLabel : $parsedDate->format('l, d M Y'),
                ];
            } catch (Throwable) {
                $selectedDate = null;
                $dateFilterInfo = null;
                $dateStart = null;
                $dateEnd = null;
            }
        }

        // Available match dates with counts for quick selector dropdown
        $availableMatchDates = collect();
        try {
            $query = FootballMatch::query();
            if ($competitionId) {
                $query->where('competition_id', $competitionId);
            }
            if ($season) {
                $query->where('season', $season);
            }
            $availableMatchDates = $query->whereNotNull('kickoff_at')
                ->selectRaw("strftime('%Y-%m-%d', kickoff_at) as match_date, count(*) as count")
                ->groupBy('match_date')
                ->orderBy('match_date', 'desc')
                ->take(14)
                ->get();
        } catch (Throwable) {
            $availableMatchDates = collect();
        }

        return [
            'selectedDate' => $selectedDate,
            'selectedTimeSlot' => $selectedTimeSlot,
            'selectedTime' => $selectedTime,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'dateFilterInfo' => $dateFilterInfo,
            'availableMatchDates' => $availableMatchDates,
        ];
    }
}

