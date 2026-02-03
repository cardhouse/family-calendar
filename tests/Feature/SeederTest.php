<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;

it('seeds sample data for the morning dashboard', function () {
    $this->seed();

    expect(Child::query()->count())->toBeGreaterThanOrEqual(2)
        ->and(RoutineItemLibrary::query()->count())->toBeGreaterThanOrEqual(8)
        ->and(DepartureTime::query()->count())->toBeGreaterThanOrEqual(2)
        ->and(CalendarEvent::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(RoutineAssignment::query()->count())->toBeGreaterThan(0);
});
