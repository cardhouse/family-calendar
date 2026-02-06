<?php

declare(strict_types=1);

use App\Models\Setting;
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

it('renders school lunch banner when lunch data is available', function () {
    Livewire::test('dashboard.header', [
        'schoolLunch' => [
            'date' => '2026-02-06',
            'date_label' => 'Fri Feb 6',
            'menu_name' => 'Elementary K-4',
            'items' => ['Chicken Tacos', 'Cucumber Slices'],
        ],
    ])
        ->assertSee('School lunch')
        ->assertSee('Fri Feb 6')
        ->assertSee('Elementary K-4')
        ->assertSee('Chicken Tacos')
        ->assertSee('Cucumber Slices');
});

it('renders the weather widget in the header by default', function () {
    Livewire::test('dashboard.header')
        ->assertSee('Weather')
        ->assertSee('Set a location in admin weather settings.');
});

it('hides the weather widget when disabled in settings', function () {
    Setting::set('weather.enabled', false);

    Livewire::test('dashboard.header')
        ->assertDontSee('Set a location in admin weather settings.');
});
