<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Footer extends Component
{
    /**
     * @var Collection<int, \App\Models\CalendarEvent>
     */
    public Collection $events;

    public function mount(Collection $events): void
    {
        $this->events = $events;
    }

    public function render(): mixed
    {
        return view('livewire.dashboard.footer');
    }
}
