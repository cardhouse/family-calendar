<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\DepartureTime;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NextDepartureService
{
    /**
     * @return array<string, mixed>|null
     */
    public function determine(): ?array
    {
        $now = now();

        $candidates = $this->departureCandidates()
            ->merge($this->eventCandidates($now))
            ->sortBy('timestamp')
            ->values();

        $first = $candidates->first();

        if ($first === null) {
            return null;
        }

        $sameTime = $candidates
            ->filter(fn (array $candidate) => $candidate['timestamp']->equalTo($first['timestamp']))
            ->values();

        if ($sameTime->count() > 1) {
            return $this->mergeCandidates($sameTime, $first['timestamp']);
        }

        return $this->formatCandidate($first);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function departureCandidates(): Collection
    {
        return DepartureTime::query()
            ->where('is_active', true)
            ->ordered()
            ->with(['assignments' => function ($query) {
                $query->ordered()->with(['child', 'routineItem', 'todayCompletion']);
            }])
            ->get()
            ->map(function (DepartureTime $departure) {
                $timestamp = $departure->getNextOccurrence();

                if ($timestamp === null) {
                    return null;
                }

                return [
                    'type' => 'departure',
                    'label' => $departure->name,
                    'timestamp' => $timestamp,
                    'assignments' => $departure->assignments,
                ];
            })
            ->filter()
            ->values()
            ->toBase();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventCandidates(CarbonInterface $now): Collection
    {
        return CalendarEvent::query()
            ->upcoming()
            ->withDepartureTime()
            ->with(['assignments' => function ($query) {
                $query->ordered()->with(['child', 'routineItem', 'todayCompletion']);
            }])
            ->get()
            ->map(function (CalendarEvent $event) use ($now) {
                if ($event->departure_time === null || $event->starts_at === null) {
                    return null;
                }

                $timestamp = Carbon::parse($event->starts_at->toDateString().' '.$event->departure_time);

                if ($timestamp->lessThanOrEqualTo($now)) {
                    return null;
                }

                return [
                    'type' => 'event',
                    'label' => $event->name,
                    'timestamp' => $timestamp,
                    'assignments' => $event->assignments,
                ];
            })
            ->filter()
            ->values()
            ->toBase();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $candidates
     * @return array<string, mixed>
     */
    private function mergeCandidates(Collection $candidates, CarbonInterface $timestamp): array
    {
        $labels = $candidates->pluck('label')->values()->all();
        $assignments = $candidates
            ->flatMap(fn (array $candidate) => $candidate['assignments'])
            ->unique('id')
            ->values();

        return [
            'timestamp' => $timestamp,
            'label' => 'Multiple departures',
            'labels' => $labels,
            'assignments' => $assignments,
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function formatCandidate(array $candidate): array
    {
        return [
            'timestamp' => $candidate['timestamp'],
            'label' => $candidate['label'],
            'labels' => [$candidate['label']],
            'assignments' => $candidate['assignments'],
        ];
    }
}
