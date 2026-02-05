<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Child;
use App\Models\RoutineCompletion;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ChildCard extends Component
{
    public Child $child;

    public bool $showCompleted = true;

    public function mount(Child $child): void
    {
        $this->child = $child;
    }

    public function hydrate(): void
    {
        $this->reloadAssignments();
    }

    #[On('dashboard:toggle-completed')]
    public function handleToggleCompleted(bool $show): void
    {
        $this->showCompleted = $show;
    }

    public function toggleCompletion(int $assignmentId): void
    {
        $assignment = $this->child->dailyRoutineAssignments->firstWhere('id', $assignmentId);

        if ($assignment === null) {
            return;
        }

        $completion = $assignment->todayCompletion;

        if ($completion !== null) {
            $completion->delete();
        } else {
            RoutineCompletion::query()->firstOrCreate(
                [
                    'routine_assignment_id' => $assignment->id,
                    'completion_date' => now()->toDateString(),
                ],
                ['completed_at' => now()]
            );
        }

        $this->reloadAssignments();
    }

    private function reloadAssignments(): void
    {
        $this->child->load([
            'dailyRoutineAssignments' => fn ($query) => $query
                ->ordered()
                ->with(['routineItem', 'todayCompletion']),
        ]);
    }

    /**
     * @return Collection<int, \App\Models\RoutineAssignment>
     */
    #[Computed]
    public function assignments(): Collection
    {
        return $this->child->dailyRoutineAssignments;
    }

    /**
     * @return Collection<int, \App\Models\RoutineAssignment>
     */
    #[Computed]
    public function visibleAssignments(): Collection
    {
        if ($this->showCompleted) {
            return $this->assignments;
        }

        return $this->assignments
            ->filter(fn ($assignment) => $assignment->todayCompletion === null)
            ->values();
    }

    #[Computed]
    public function progressPercent(): int
    {
        $total = $this->assignments->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->assignments
            ->filter(fn ($assignment) => $assignment->todayCompletion !== null)
            ->count();

        return (int) round(($completed / $total) * 100);
    }

    #[Computed]
    public function isComplete(): bool
    {
        return $this->assignments->isNotEmpty()
            && $this->assignments->every(fn ($assignment) => $assignment->todayCompletion !== null);
    }

    public function render(): mixed
    {
        return view('livewire.dashboard.child-card');
    }
}
