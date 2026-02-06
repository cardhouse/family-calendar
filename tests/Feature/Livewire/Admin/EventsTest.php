<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('creates an event', function () {
    $startsAt = now()->addDay()->format('Y-m-d\TH:i');

    Livewire::test('admin.events')
        ->call('openCreate')
        ->set('name', 'Field Trip')
        ->set('startsAt', $startsAt)
        ->set('departureTime', '07:15')
        ->set('category', 'school')
        ->set('color', '#0f172a')
        ->call('save');

    $event = CalendarEvent::query()->where('name', 'Field Trip')->first();

    expect($event)->not->toBeNull()
        ->and($event?->departure_time)->toBe('07:15:00')
        ->and($event?->category)->toBe('school');
});

it('updates an event', function () {
    $event = CalendarEvent::factory()->create([
        'name' => 'Practice',
        'category' => 'sports',
        'color' => '#f97316',
    ]);

    Livewire::test('admin.events')
        ->call('openEdit', $event->id)
        ->set('name', 'Morning Practice')
        ->set('startsAt', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('departureTime', '')
        ->set('category', 'sports')
        ->set('color', '#22c55e')
        ->call('save');

    $event->refresh();

    expect($event->name)->toBe('Morning Practice')
        ->and($event->departure_time)->toBeNull()
        ->and($event->color)->toBe('#22c55e');
});

it('deletes an event', function () {
    $event = CalendarEvent::factory()->create();

    Livewire::test('admin.events')
        ->call('delete', $event->id);

    expect(CalendarEvent::query()->whereKey($event->id)->exists())->toBeFalse();
});

it('stores event start times in utc based on the admin timezone', function () {
    Setting::set('timezone', 'America/Chicago');

    $local = Carbon::create(2026, 2, 5, 7, 30, 0, 'America/Chicago');
    $startsAt = $local->format('Y-m-d\TH:i');

    Livewire::test('admin.events')
        ->call('openCreate')
        ->set('name', 'Field Trip')
        ->set('startsAt', $startsAt)
        ->set('departureTime', '07:15')
        ->set('category', 'school')
        ->set('color', '#0f172a')
        ->call('save');

    $event = CalendarEvent::query()->where('name', 'Field Trip')->first();

    expect($event)->not->toBeNull()
        ->and($event?->starts_at?->format('Y-m-d H:i:s'))->toBe($local->format('Y-m-d H:i:s'))
        ->and($event?->getRawOriginal('starts_at'))->toBe(
            $local->copy()->setTimezone('UTC')->format('Y-m-d H:i:s')
        );
});
