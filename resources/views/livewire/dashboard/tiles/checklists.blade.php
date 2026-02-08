<?php

declare(strict_types=1);

use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Services\NextDepartureService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

return new class extends Component
{
    public bool $showCompletedTasks = true;

    public function mount(?bool $showCompletedTasks = null): void
    {
        if (is_bool($showCompletedTasks)) {
            $this->showCompletedTasks = $showCompletedTasks;
        }
    }

    #[On('dashboard:toggle-completed')]
    public function handleToggleCompleted(bool $show): void
    {
        $this->showCompletedTasks = $show;
    }

    /**
     * @return Collection<int, Child>
     */
    #[Computed]
    public function children(): Collection
    {
        $activeAssignmentIds = $this->activeAssignmentIds();

        return Child::query()
            ->ordered()
            ->with([
                'routineAssignments' => function ($query) use ($activeAssignmentIds): void {
                    $query
                        ->where(function ($assignmentQuery) use ($activeAssignmentIds): void {
                            $assignmentQuery
                                ->whereNull('assignable_type')
                                ->whereNull('assignable_id');

                            if ($activeAssignmentIds !== []) {
                                $assignmentQuery->orWhereIn('routine_assignments.id', $activeAssignmentIds);
                            }
                        })
                        ->ordered()
                        ->with(['routineItem', 'todayCompletion']);
                },
            ])
            ->get();
    }

    /**
     * @return array<int, list<int>>
     */
    #[Computed]
    public function activeAssignmentIdsByChild(): array
    {
        $nextDeparture = app(NextDepartureService::class)->determine();

        if ($nextDeparture === null || ! array_key_exists('assignments', $nextDeparture)) {
            return [];
        }

        $assignments = $nextDeparture['assignments'];

        if (! is_iterable($assignments)) {
            return [];
        }

        $assignmentIdsByChild = [];

        foreach ($assignments as $assignment) {
            if (! $assignment instanceof RoutineAssignment) {
                continue;
            }

            $childId = (int) $assignment->child_id;
            $assignmentId = (int) $assignment->id;

            if (! isset($assignmentIdsByChild[$childId])) {
                $assignmentIdsByChild[$childId] = [];
            }

            if (! in_array($assignmentId, $assignmentIdsByChild[$childId], true)) {
                $assignmentIdsByChild[$childId][] = $assignmentId;
            }
        }

        return $assignmentIdsByChild;
    }

    /**
     * @return list<int>
     */
    private function activeAssignmentIds(): array
    {
        $activeAssignmentIds = [];

        foreach ($this->activeAssignmentIdsByChild as $assignmentIds) {
            foreach ($assignmentIds as $assignmentId) {
                $activeAssignmentIds[(int) $assignmentId] = true;
            }
        }

        return array_map('intval', array_keys($activeAssignmentIds));
    }
};
?>

@placeholder
    <div>
        <flux:skeleton.group animate="shimmer" class="grid gap-4 md:grid-cols-2">
            @foreach (range(1, 2) as $skeletonCard)
                <div wire:key="child-card-skeleton-{{ $skeletonCard }}" class="rounded-3xl border border-dash-border bg-dash-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <flux:skeleton class="h-14 w-14 rounded-full" />
                            <div class="w-36 space-y-2">
                                <flux:skeleton.line class="w-full" />
                                <flux:skeleton.line class="w-2/3" />
                            </div>
                        </div>
                        <flux:skeleton.line class="w-10" />
                    </div>

                    <flux:skeleton class="mt-4 h-3 w-full rounded-full" />

                    <div class="mt-5 space-y-3">
                        <flux:skeleton class="h-14 w-full rounded-2xl" />
                        <flux:skeleton class="h-14 w-full rounded-2xl" />
                        <flux:skeleton class="h-14 w-4/5 rounded-2xl" />
                    </div>
                </div>
            @endforeach
        </flux:skeleton.group>
    </div>
@endplaceholder

<div class="grid gap-4 md:grid-cols-2">
    @forelse ($this->children as $child)
        <livewire:dashboard.child-card
            :child="$child"
            :active-assignment-ids="$this->activeAssignmentIdsByChild[$child->id] ?? []"
            :show-completed="$this->showCompletedTasks"
            wire:key="child-{{ $child->id }}"
        />
    @empty
        <div class="col-span-full flex flex-col items-center gap-3 rounded-3xl border border-dashed border-dash-border bg-dash-card p-10 text-center text-slate-300">
            <flux:icon name="user-plus" variant="outline" class="size-8" />
            <span>Add children to start planning morning routines.</span>
        </div>
    @endforelse
</div>
