<?php

declare(strict_types=1);

use App\Models\Setting;
use Livewire\Livewire;

it('updates the admin timezone setting', function () {
    Setting::set('timezone', 'UTC');

    Livewire::test('admin.settings')
        ->set('timezone', 'America/Denver')
        ->call('save')
        ->assertSet('saved', true);

    expect(Setting::get('timezone'))->toBe('America/Denver');
});
