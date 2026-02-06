<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

it('renders upcoming events in order', function () {
    $first = CalendarEvent::factory()->create(['starts_at' => now()->addDay()]);
    $second = CalendarEvent::factory()->create(['starts_at' => now()->addDays(2)]);
    $third = CalendarEvent::factory()->create(['starts_at' => now()->addDays(3)]);
    CalendarEvent::factory()->create(['starts_at' => now()->addDays(4)]);

    $events = CalendarEvent::query()->upcoming()->limit(3)->get();

    Livewire::test('dashboard.footer', ['events' => $events])
        ->assertSeeInOrder([$first->name, $second->name, $third->name]);
});

it('does not render school lunch content in the footer panel', function () {
    Livewire::test('dashboard.footer', ['events' => new Collection])
        ->assertDontSee('School lunch');
});
