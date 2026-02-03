<?php

declare(strict_types=1);

use App\Livewire\Admin\Children;
use App\Models\Child;
use Livewire\Livewire;

it('creates a child', function () {
    Livewire::test(Children::class)
        ->call('openCreate')
        ->set('name', 'Lila')
        ->set('avatarColor', '#1e3a8a')
        ->call('save');

    expect(Child::query()->where('name', 'Lila')->exists())->toBeTrue();
});

it('updates a child', function () {
    $child = Child::factory()->create(['name' => 'Max']);

    Livewire::test(Children::class)
        ->call('openEdit', $child->id)
        ->set('name', 'Maxwell')
        ->set('avatarColor', '#10b981')
        ->call('save');

    expect($child->refresh()->name)->toBe('Maxwell')
        ->and($child->avatar_color)->toBe('#10b981');
});

it('deletes a child', function () {
    $child = Child::factory()->create();

    Livewire::test(Children::class)
        ->call('delete', $child->id);

    expect(Child::query()->whereKey($child->id)->exists())->toBeFalse();
});

it('reorders children', function () {
    $first = Child::factory()->create(['display_order' => 1]);
    $second = Child::factory()->create(['display_order' => 2]);
    $third = Child::factory()->create(['display_order' => 3]);

    Livewire::test(Children::class)
        ->call('reorder', $third->id, 0);

    $ordered = Child::query()->ordered()->get();

    expect($ordered->first()->is($third))->toBeTrue()
        ->and($ordered->last()->is($second))->toBeTrue();
});
