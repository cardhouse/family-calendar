<?php

declare(strict_types=1);

it('renders the dashboard route with default glass mood', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSeeLivewire('dashboard')
        ->assertSee('Live Snapshot')
        ->assertSee('mood-sunset-ember', false);
});
