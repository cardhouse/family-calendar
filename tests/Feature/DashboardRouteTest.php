<?php

declare(strict_types=1);

it('renders the dashboard route', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSeeLivewire('dashboard');
});
