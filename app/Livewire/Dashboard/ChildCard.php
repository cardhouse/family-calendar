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

    /**
     * @var list<string>
     */
    public array $celebrationMessages = [
        'Awesome focus. You crushed every step!',
        'Checklist complete. Morning mission accomplished!',
        'Everything is done. Great teamwork!',
        'You finished every routine item. Nice work!',
    ];

    public ?string $celebrationMessage = null;

    public bool $confettiEnabled = true;

    public bool $shouldCelebrate = false;

    public bool $wasComplete = false;

    public function mount(Child $child): void
    {
        $this->child = $child;
        $this->reloadAssignments();
        $this->wasComplete = $this->isChecklistComplete();

        if ($this->wasComplete) {
            $this->celebrationMessage = $this->randomCelebrationMessage();
        }
    }

    public function hydrate(): void
    {
        $this->reloadAssignments();
        $this->syncCelebrationState(false);
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
        $this->syncCelebrationState(true);
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
        return $this->isChecklistComplete();
    }

    public function render(): mixed
    {
        return view('livewire.dashboard.child-card');
    }

    private function isChecklistComplete(): bool
    {
        return $this->assignments->isNotEmpty()
            && $this->assignments->every(fn ($assignment) => $assignment->todayCompletion !== null);
    }

    private function syncCelebrationState(bool $allowNewCelebration): void
    {
        $isComplete = $this->isChecklistComplete();

        if (! $isComplete) {
            $this->celebrationMessage = null;
            $this->shouldCelebrate = false;
            $this->wasComplete = false;

            return;
        }

        if ($allowNewCelebration && ! $this->wasComplete) {
            $this->celebrationMessage = $this->randomCelebrationMessage();
            $this->shouldCelebrate = true;
        } else {
            $this->shouldCelebrate = false;
        }

        $this->wasComplete = true;
    }

    private function randomCelebrationMessage(): string
    {
        return $this->celebrationMessages[array_rand($this->celebrationMessages)];
    }
}
