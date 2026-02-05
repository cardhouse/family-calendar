<?php

use App\Models\Child;
use App\Models\RoutineCompletion;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

return new class extends Component
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
};
?>

<div
    class="rounded-3xl border border-dash-border bg-dash-card p-6 shadow-sm transition-shadow"
    style="border-top: 4px solid {{ $child->avatar_color }}; background: linear-gradient(180deg, {{ $child->avatar_color }}08 0%, transparent 20%), var(--color-dash-card);"
    @class(['animate-complete-glow' => $this->isComplete])
>
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div
                class="flex h-14 w-14 items-center justify-center rounded-full text-2xl font-black text-white shadow-lg"
                style="background-color: {{ $child->avatar_color }};"
            >
                {{ strtoupper(mb_substr($child->name, 0, 1)) }}
            </div>
            <div>
                <flux:heading size="lg" class="font-extrabold text-slate-100">{{ $child->name }}</flux:heading>
                <flux:text class="text-sm text-slate-400">
                    {{ $this->assignments->filter(fn ($a) => $a->todayCompletion !== null)->count() }} of {{ $this->assignments->count() }} done
                </flux:text>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-200">{{ $this->progressPercent }}%</div>
    </div>

    <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-800">
        <div
            class="h-3 rounded-full transition-all duration-500 ease-out"
            style="width: {{ $this->progressPercent }}%; background: linear-gradient(90deg, #fbbf24, #34d399);"
        ></div>
    </div>

    <div class="mt-5 flex flex-col gap-3">
        @forelse ($this->visibleAssignments as $assignment)
            <button
                type="button"
                wire:key="assignment-{{ $assignment->id }}"
                wire:click="toggleCompletion({{ $assignment->id }})"
                @class([
                    'flex items-center gap-4 rounded-2xl border-2 px-5 py-4 text-left transition-all active:scale-[0.98] touch-target-lg',
                    'border-emerald-500/30 bg-emerald-500/5' => $assignment->todayCompletion,
                    'border-dash-border bg-dash-card hover:bg-dash-card-hover' => ! $assignment->todayCompletion,
                ])
            >
                <div
                    @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                        'border-emerald-400 bg-emerald-400 text-white' => $assignment->todayCompletion,
                        'border-slate-600' => ! $assignment->todayCompletion,
                    ])
                >
                    @if ($assignment->todayCompletion)
                        <svg class="h-4 w-4 animate-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                </div>

                <span
                    @class([
                        'text-base font-bold transition-colors',
                        'text-slate-500 line-through' => $assignment->todayCompletion,
                        'text-slate-100' => ! $assignment->todayCompletion,
                    ])
                >
                    {{ $assignment->routineItem->name }}
                </span>
            </button>
        @empty
            <div class="flex items-center justify-center gap-2 rounded-2xl border border-dashed border-dash-border px-4 py-8 text-sm text-slate-400">
                <flux:icon name="clipboard-document" variant="outline" class="size-5" />
                <span>No routine items yet.</span>
            </div>
        @endforelse
    </div>

    @if ($this->isComplete)
        <div class="mt-5 flex items-center justify-center gap-2 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 animate-celebration">
            <flux:icon name="star" variant="solid" class="size-6 text-amber-400" />
            <span class="text-base font-extrabold text-emerald-300">All done! Great job!</span>
            <flux:icon name="star" variant="solid" class="size-6 text-amber-400" />
        </div>
    @endif
</div>
