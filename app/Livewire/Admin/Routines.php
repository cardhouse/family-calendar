<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Routines extends Component
{
    public function render(): mixed
    {
        return view('livewire.admin.routines');
    }
}
