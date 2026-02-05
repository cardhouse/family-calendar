<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Services\NextDepartureService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Dashboard extends Component
{
    /**
     * @var Collection<int, Child>
     */
    public Collection $children;

    /**
     * @var Collection<int, CalendarEvent>
     */
    public Collection $upcomingEvents;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $nextDeparture = null;

    public function mount(): void
    {
        $this->children = $this->loadChildren();
        $this->upcomingEvents = $this->loadUpcomingEvents();
        $this->nextDeparture = app(NextDepartureService::class)->determine();
    }

    public function render(): mixed
    {
        return view('livewire.dashboard');
    }

    /**
     * @return Collection<int, Child>
     */
    private function loadChildren(): Collection
    {
        return Child::query()
            ->ordered()
            ->with([
                'dailyRoutineAssignments' => function ($query) {
                    $query->ordered()
                        ->with(['routineItem', 'todayCompletion']);
                },
            ])
            ->get();
    }

    /**
     * @return Collection<int, CalendarEvent>
     */
    private function loadUpcomingEvents(): Collection
    {
        return CalendarEvent::query()
            ->upcoming()
            ->limit(3)
            ->get();
    }
}
