<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'dashboard')->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/settings', 'admin.settings')->name('settings');
    Route::livewire('/children', 'admin.children')->name('children');
    Route::livewire('/departures', 'admin.departures')->name('departures');
    Route::livewire('/events', 'admin.events')->name('events');
    Route::livewire('/routines', 'admin.routines')->name('routines');
    Route::livewire('/weather', 'admin.weather')->name('weather');
});
