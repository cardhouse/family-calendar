<?php

declare(strict_types=1);

use App\Livewire\Admin\Departures;
use App\Models\DepartureTime;
use Livewire\Livewire;

it('creates a departure time', function () {
    Livewire::test(Departures::class)
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

    Livewire::test(Departures::class)
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

it('deletes a departure time', function () {
    $departure = DepartureTime::factory()->create();

    Livewire::test(Departures::class)
        ->call('delete', $departure->id);

    expect(DepartureTime::query()->whereKey($departure->id)->exists())->toBeFalse();
});

it('reorders departures', function () {
    $first = DepartureTime::factory()->create(['display_order' => 1]);
    $second = DepartureTime::factory()->create(['display_order' => 2]);
    $third = DepartureTime::factory()->create(['display_order' => 3]);

    Livewire::test(Departures::class)
        ->call('reorder', $third->id, 0);

    $ordered = DepartureTime::query()->ordered()->get();

    expect($ordered->first()->is($third))->toBeTrue()
        ->and($ordered->last()->is($second))->toBeTrue();
});
