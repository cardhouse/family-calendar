<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Services\NextDepartureService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

return new class extends Component
{
    #[Computed]
    public function childrenCount(): int
    {
        return $this->children->count();
    }

    #[Computed]
    public function eventsCount(): int
    {
        return $this->upcomingEvents->count();
    }

    #[Computed]
    public function completedChildrenCount(): int
    {
        return $this->children
            ->filter(function (Child $child): bool {
                return $child->routineAssignments->isNotEmpty()
                    && $child->routineAssignments->every(
                        fn (RoutineAssignment $assignment): bool => $assignment->todayCompletion !== null
                    );
            })
            ->count();
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
     * @return Collection<int, CalendarEvent>
     */
    #[Computed]
    public function upcomingEvents(): Collection
    {
        return CalendarEvent::query()
            ->upcoming()
            ->limit(3)
            ->get();
    }

    /**
     * @return list<int>
     */
    private function activeAssignmentIds(): array
    {
        $activeAssignmentIds = [];

        foreach ($this->activeAssignmentIdsByChild() as $assignmentIds) {
            foreach ($assignmentIds as $assignmentId) {
                $activeAssignmentIds[(int) $assignmentId] = true;
            }
        }

        return array_map('intval', array_keys($activeAssignmentIds));
    }

    /**
     * @return array<int, list<int>>
     */
    private function activeAssignmentIdsByChild(): array
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
};
?>

@placeholder
    <div>
        <flux:skeleton.group animate="shimmer" class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
                <flux:skeleton.line class="w-1/3" />
                <flux:skeleton.line size="lg" class="mt-2 w-1/4" />
            </div>
            <div class="rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
                <flux:skeleton.line class="w-1/3" />
                <flux:skeleton.line size="lg" class="mt-2 w-1/4" />
            </div>
            <div class="col-span-full rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
                <flux:skeleton.line class="w-2/5" />
                <flux:skeleton.line size="lg" class="mt-2 w-1/5" />
            </div>
        </flux:skeleton.group>
    </div>
@endplaceholder

<div class="grid grid-cols-2 gap-3">
    <div class="rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Kids</div>
        <div class="mt-1 text-3xl font-black text-cyan-200">{{ $this->childrenCount }}</div>
    </div>
    <div class="rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Events</div>
        <div class="mt-1 text-3xl font-black text-amber-200">{{ $this->eventsCount }}</div>
    </div>
    <div class="col-span-full rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Completed routines</div>
        <div class="mt-1 text-3xl font-black text-emerald-200">{{ $this->completedChildrenCount }}</div>
    </div>
</div>
