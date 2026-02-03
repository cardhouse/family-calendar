<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Header extends Component
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $nextDeparture = null;

    public function render(): mixed
    {
        return view('livewire.dashboard.header');
    }
}
