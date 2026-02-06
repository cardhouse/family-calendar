<?php

declare(strict_types=1);

use App\Models\DepartureTime;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('creates a departure time', function () {
    Livewire::test('admin.departures')
        ->call('openCreate')
        ->set('name', 'School Drop')
        ->set('departureTime', '07:45')
        ->set('applicableDays', ['mon', 'tue'])
        ->set('isActive', true)
        ->call('save');

    $departure = DepartureTime::query()->where('name', 'School Drop')->first();

    expect($departure)->not->toBeNull()
        ->and($departure?->departure_time)->toBe('07:45:00')
        ->and($departure?->applicable_days)->toMatchArray(['mon', 'tue']);
});

it('updates a departure time', function () {
    $departure = DepartureTime::factory()->create([
        'name' => 'Practice',
        'departure_time' => '08:00:00',
        'applicable_days' => ['fri'],
        'is_active' => true,
    ]);

    Livewire::test('admin.departures')
        ->call('openEdit', $departure->id)
        ->set('name', 'Early Practice')
        ->set('departureTime', '07:30')
        ->set('applicableDays', ['fri'])
        ->set('isActive', false)
        ->call('save');

    $departure->refresh();

    expect($departure->name)->toBe('Early Practice')
        ->and($departure->departure_time)->toBe('07:30:00')
        ->and($departure->is_active)->toBeFalse();
});

it('stores updated departure times in utc based on the admin timezone', function () {
    Setting::set('timezone', 'America/Chicago');

    Carbon::setTestNow(Carbon::create(2026, 2, 5, 0, 0, 0, 'America/Chicago'));

    $departure = DepartureTime::factory()->create([
        'name' => 'Practice',
        'departure_time' => '08:00:00',
        'applicable_days' => ['fri'],
        'is_active' => true,
    ]);

    Livewire::test('admin.departures')
        ->call('openEdit', $departure->id)
        ->set('name', 'Morning Practice')
        ->set('departureTime', '07:30')
        ->set('applicableDays', ['fri'])
        ->set('isActive', true)
        ->call('save');

    $departure->refresh();

    expect($departure->departure_time)->toBe('07:30:00')
        ->and($departure->getRawOriginal('departure_time'))->toBe('13:30:00');

    Carbon::setTestNow();
});

it('deletes a departure time', function () {
    $departure = DepartureTime::factory()->create();

    Livewire::test('admin.departures')
        ->call('delete', $departure->id);

    expect(DepartureTime::query()->whereKey($departure->id)->exists())->toBeFalse();
});

it('reorders departures', function () {
    $first = DepartureTime::factory()->create(['display_order' => 1]);
    $second = DepartureTime::factory()->create(['display_order' => 2]);
    $third = DepartureTime::factory()->create(['display_order' => 3]);

    Livewire::test('admin.departures')
        ->call('reorder', $third->id, 0);

    $ordered = DepartureTime::query()->ordered()->get();

    expect($ordered->first()->is($third))->toBeTrue()
        ->and($ordered->last()->is($second))->toBeTrue();
});
