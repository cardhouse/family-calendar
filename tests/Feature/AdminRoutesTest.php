<?php

declare(strict_types=1);

it('renders admin routes', function () {
    $this->get('/admin/children')
        ->assertSuccessful()
        ->assertSeeLivewire('admin.children');

    $this->get('/admin/departures')
        ->assertSuccessful()
        ->assertSeeLivewire('admin.departures');

    $this->get('/admin/events')
        ->assertSuccessful()
        ->assertSeeLivewire('admin.events');

    $this->get('/admin/routines')
        ->assertSuccessful()
        ->assertSeeLivewire('admin.routines');

    $this->get('/admin/weather')
        ->assertSuccessful()
        ->assertSeeLivewire('admin.weather');
});
