<?php

declare(strict_types=1);

use Livewire\Livewire;

it('renders clock and departure countdown', function () {
    $nextDeparture = [
        'timestamp' => now()->addMinutes(45),
        'label' => 'School Run',
        'labels' => ['School Run'],
        'assignments' => collect(),
    ];

    Livewire::test('dashboard.header', ['nextDeparture' => $nextDeparture])
        ->assertSee('School Run')
        ->assertSee('DashboardTime.clock()', false)
        ->assertSee('DashboardTime.countdown', false);
});
