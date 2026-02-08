<?php

declare(strict_types=1);

use Livewire\Livewire;

it('dispatches the completed visibility event when toggled off and on', function () {
    Livewire::test('dashboard.tiles.settings', [
        'hideableTiles' => [
            ['id' => 'events', 'title' => 'Calendar Lane', 'hidden' => false],
        ],
        'configurableTiles' => [
            ['id' => 'settings', 'title' => 'Dashboard Settings', 'hidden' => false, 'size' => 'small'],
        ],
        'tileSizeLabels' => [
            'small' => 'Small (1/3)',
        ],
        'showCompletedTasks' => true,
    ])
        ->set('showCompletedTasks', false)
        ->assertSet('showCompletedTasks', false)
        ->assertDispatched('dashboard:toggle-completed')
        ->set('showCompletedTasks', true)
        ->assertSet('showCompletedTasks', true)
        ->assertDispatched('dashboard:toggle-completed');
});
