<?php

declare(strict_types=1);

use App\Livewire\Admin\Children as AdminChildren;
use App\Livewire\Admin\Departures as AdminDepartures;
use App\Livewire\Admin\Events as AdminEvents;
use App\Livewire\Admin\Routines as AdminRoutines;
use App\Livewire\Admin\Weather as AdminWeather;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/children', AdminChildren::class)->name('children');
    Route::get('/departures', AdminDepartures::class)->name('departures');
    Route::get('/events', AdminEvents::class)->name('events');
    Route::get('/routines', AdminRoutines::class)->name('routines');
    Route::get('/weather', AdminWeather::class)->name('weather');
});
