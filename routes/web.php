<?php

declare(strict_types=1);

use App\Livewire\Admin\Children as AdminChildren;
use App\Livewire\Admin\Departures as AdminDepartures;
use App\Livewire\Admin\Events as AdminEvents;
use App\Livewire\Admin\Routines as AdminRoutines;
use App\Livewire\Admin\Weather as AdminWeather;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Dashboard::class)->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/children', AdminChildren::class)->name('children');
    Route::livewire('/departures', AdminDepartures::class)->name('departures');
    Route::livewire('/events', AdminEvents::class)->name('events');
    Route::livewire('/routines', AdminRoutines::class)->name('routines');
    Route::livewire('/weather', AdminWeather::class)->name('weather');
});
