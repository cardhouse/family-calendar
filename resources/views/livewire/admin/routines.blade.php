<?php

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::admin')]
    /**
     * @var Collection<int, RoutineItemLibrary>
     */
    public Collection $routineItems;

    /**
     * @var Collection<int, Child>
     */
    public Collection $children;

    /**
     * @var Collection<int, CalendarEvent>
     */
    public Collection $events;

    /**
     * @var Collection<int, DepartureTime>
     */
    public Collection $departures;

    public bool $showLibraryModal = false;

    public ?int $editingLibraryId = null;

    public string $libraryName = '';

    public string $activeTab = 'daily';

    public ?int $selectedEventId = null;

    public ?int $selectedDepartureId = null;

    public function mount(): void
    {
        $this->routineItems = $this->loadRoutineItems();
        $this->children = $this->loadChildren();
        $this->events = $this->loadEvents();
        $this->departures = $this->loadDepartures();
        $this->selectedEventId = $this->events->first()?->id;
        $this->selectedDepartureId = $this->departures->first()?->id;
    }

    public function openLibraryCreate(): void
    {
        $this->editingLibraryId = null;
        $this->libraryName = '';
        $this->showLibraryModal = true;
    }

    public function openLibraryEdit(int $routineItemId): void
    {
        $routineItem = RoutineItemLibrary::query()->findOrFail($routineItemId);

        $this->editingLibraryId = $routineItem->id;
        $this->libraryName = $routineItem->name;
        $this->showLibraryModal = true;
    }

    public function saveLibrary(): void
    {
        $validated = $this->validate([
            'libraryName' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingLibraryId !== null) {
            RoutineItemLibrary::query()->whereKey($this->editingLibraryId)->update([
                'name' => $validated['libraryName'],
            ]);
        } else {
            $order = (int) RoutineItemLibrary::query()->max('display_order');

            RoutineItemLibrary::query()->create([
                'name' => $validated['libraryName'],
                'display_order' => $order + 1,
            ]);
        }

        $this->showLibraryModal = false;
        $this->routineItems = $this->loadRoutineItems();
    }

    public function deleteLibrary(int $routineItemId): void
    {
        RoutineItemLibrary::query()->whereKey($routineItemId)->delete();

        $this->resequenceLibrary();
        $this->routineItems = $this->loadRoutineItems();
    }

    public function reorderLibrary(int $routineItemId, int $position): void
    {
        $items = RoutineItemLibrary::query()->ordered()->get()->values();
        $moving = $items->firstWhere('id', $routineItemId);

        if ($moving === null) {
            return;
        }

        $items = $items->reject(function (RoutineItemLibrary $item) use ($routineItemId): bool {
            return $item->id === $routineItemId;
        })->values();

        $items->splice($position, 0, [$moving]);

        $items->values()->each(function (RoutineItemLibrary $item, int $index): void {
            $item->update(['display_order' => $index + 1]);
        });

        $this->routineItems = $this->loadRoutineItems();
    }

    public function assignRoutine(int $routineItemId, int $childId): void
    {
        $this->resetErrorBag('assignment');

        $routineItem = RoutineItemLibrary::query()->findOrFail($routineItemId);
        $child = Child::query()->findOrFail($childId);

        [$assignableType, $assignableId] = $this->currentAssignable();

        if ($assignableType !== null && $assignableId === null) {
            $this->addError('assignment', 'Select an event or departure before assigning routines.');

            return;
        }

        $duplicateQuery = RoutineAssignment::query()
            ->where('routine_item_id', $routineItem->id)
            ->where('child_id', $child->id);

        $this->applyAssignableScope($duplicateQuery, $assignableType, $assignableId);

        if ($duplicateQuery->exists()) {
            $this->addError('assignment', 'This routine is already assigned to '.$child->name.'.');

            return;
        }

        $orderQuery = RoutineAssignment::query()->where('child_id', $child->id);
        $this->applyAssignableScope($orderQuery, $assignableType, $assignableId);

        $order = (int) $orderQuery->max('display_order');

        RoutineAssignment::query()->create([
            'routine_item_id' => $routineItem->id,
            'child_id' => $child->id,
            'assignable_type' => $assignableType,
            'assignable_id' => $assignableId,
            'display_order' => $order + 1,
        ]);
    }

    public function reorderAssignment(int $assignmentId, int $position): void
    {
        $assignment = RoutineAssignment::query()->findOrFail($assignmentId);

        $assignments = RoutineAssignment::query()
            ->where('child_id', $assignment->child_id);

        $this->applyAssignableScope($assignments, $assignment->assignable_type, $assignment->assignable_id);

        $assignments = $assignments->ordered()->get()->values();
        $moving = $assignments->firstWhere('id', $assignmentId);

        if ($moving === null) {
            return;
        }

        $assignments = $assignments->reject(function (RoutineAssignment $item) use ($assignmentId): bool {
            return $item->id === $assignmentId;
        })->values();

        $assignments->splice($position, 0, [$moving]);

        $assignments->values()->each(function (RoutineAssignment $item, int $index): void {
            $item->update(['display_order' => $index + 1]);
        });
    }

    public function removeAssignment(int $assignmentId): void
    {
        $assignment = RoutineAssignment::query()->findOrFail($assignmentId);

        RoutineAssignment::query()->whereKey($assignmentId)->delete();

        $this->resequenceAssignments(
            $assignment->child_id,
            $assignment->assignable_type,
            $assignment->assignable_id
        );
    }

    public function updatedActiveTab(): void
    {
        $this->resetErrorBag('assignment');
    }

    public function updatedSelectedEventId(): void
    {
        $this->resetErrorBag('assignment');
    }

    public function updatedSelectedDepartureId(): void
    {
        $this->resetErrorBag('assignment');
    }

    /**
     * @return array<int, Collection<int, RoutineAssignment>>
     */
    public function assignmentBuckets(): array
    {
        if ($this->children->isEmpty()) {
            return [];
        }

        [$assignableType, $assignableId] = $this->currentAssignable();

        if ($assignableType !== null && $assignableId === null) {
            return $this->emptyBuckets();
        }

        $query = RoutineAssignment::query()->with('routineItem')->ordered();

        $this->applyAssignableScope($query, $assignableType, $assignableId);

        $grouped = $query->get()->groupBy('child_id');

        return $this->children->mapWithKeys(function (Child $child) use ($grouped): array {
            return [$child->id => $grouped->get($child->id, new Collection)];
        })->all();
    }

    /**
     * @return Collection<int, RoutineItemLibrary>
     */
    private function loadRoutineItems(): Collection
    {
        return RoutineItemLibrary::query()->ordered()->get();
    }

    /**
     * @return Collection<int, Child>
     */
    private function loadChildren(): Collection
    {
        return Child::query()->ordered()->get();
    }

    /**
     * @return Collection<int, CalendarEvent>
     */
    private function loadEvents(): Collection
    {
        return CalendarEvent::query()->orderBy('starts_at')->get();
    }

    /**
     * @return Collection<int, DepartureTime>
     */
    private function loadDepartures(): Collection
    {
        return DepartureTime::query()->ordered()->get();
    }

    /**
     * @return array<int, Collection<int, RoutineAssignment>>
     */
    private function emptyBuckets(): array
    {
        return $this->children->mapWithKeys(function (Child $child): array {
            return [$child->id => new Collection];
        })->all();
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    private function currentAssignable(): array
    {
        return match ($this->activeTab) {
            'events' => [CalendarEvent::class, $this->selectedEventId],
            'departures' => [DepartureTime::class, $this->selectedDepartureId],
            default => [null, null],
        };
    }

    /**
     * @param  Builder<RoutineAssignment>  $query
     * @return Builder<RoutineAssignment>
     */
    private function applyAssignableScope(Builder $query, ?string $assignableType, ?int $assignableId): Builder
    {
        if ($assignableType === null) {
            $query->whereNull('assignable_type');
        } else {
            $query->where('assignable_type', $assignableType);
        }

        if ($assignableId === null) {
            $query->whereNull('assignable_id');
        } else {
            $query->where('assignable_id', $assignableId);
        }

        return $query;
    }

    private function resequenceLibrary(): void
    {
        $items = RoutineItemLibrary::query()->ordered()->get();

        $items->values()->each(function (RoutineItemLibrary $item, int $index): void {
            $item->update(['display_order' => $index + 1]);
        });
    }

    private function resequenceAssignments(int $childId, ?string $assignableType, ?int $assignableId): void
    {
        $assignments = RoutineAssignment::query()->where('child_id', $childId);

        $this->applyAssignableScope($assignments, $assignableType, $assignableId);

        $assignments->ordered()->get()->values()->each(function (RoutineAssignment $item, int $index): void {
            $item->update(['display_order' => $index + 1]);
        });
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="clipboard-document-check" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Routines</flux:heading>
                <flux:text>Build routine libraries and assign them to daily, event, or departure lists.</flux:text>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <section class="space-y-4 rounded-2xl border border-amber-200/60 bg-amber-50/30 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <flux:heading size="sm">Routine library</flux:heading>
                    <flux:text class="text-xs">Drag a routine into a child bucket to assign it.</flux:text>
                </div>
                <flux:button size="sm" variant="primary" wire:click="openLibraryCreate">Add routine</flux:button>
            </div>

            <div class="space-y-2" wire:sort="reorderLibrary">
                @forelse ($routineItems as $routineItem)
                    <div
                        wire:key="routine-item-{{ $routineItem->id }}"
                        wire:sort:item="{{ $routineItem->id }}"
                        class="rounded-xl border border-slate-200/80 bg-white px-3 py-2.5 shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:sort:handle
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                >
                                    <span class="sr-only">Drag to reorder</span>
                                    <flux:icon name="bars-3" variant="outline" class="size-4" />
                                </button>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $routineItem->name }}</div>
                                    <span
                                        class="mt-1 inline-flex cursor-grab items-center gap-1 rounded-full border border-dashed border-amber-300/80 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-600"
                                        draggable="true"
                                        @dragstart="$event.dataTransfer.setData('routine-item', '{{ $routineItem->id }}'); $event.dataTransfer.effectAllowed = 'copy';"
                                    >
                                        <flux:icon name="arrows-pointing-out" variant="outline" class="size-2.5" />
                                        Drag to assign
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2" wire:sort:ignore>
                                <flux:button size="xs" variant="subtle" wire:click="openLibraryEdit({{ $routineItem->id }})">Edit</flux:button>
                                <flux:button size="xs" variant="danger" wire:click="deleteLibrary({{ $routineItem->id }})">Delete</flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                        <flux:icon name="clipboard-document" variant="outline" class="size-6 text-slate-300" />
                        <span>Add your first routine to start building assignments.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Assignments</flux:heading>
                    <flux:text>Drop routines into each child bucket to build checklists.</flux:text>
                </div>
            </div>

            <flux:tabs wire:model.live="activeTab" variant="segmented">
                <flux:tab name="daily">Daily</flux:tab>
                <flux:tab name="events">Events</flux:tab>
                <flux:tab name="departures">Departures</flux:tab>
            </flux:tabs>

            @error('assignment')
                <flux:callout variant="danger" icon="exclamation-triangle">
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            @if ($activeTab === 'events')
                @if ($events->isEmpty())
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                        <flux:icon name="calendar" variant="outline" class="size-6 text-slate-300" />
                        <span>Add an event to start assigning routines for special days.</span>
                    </div>
                @else
                    <div class="max-w-xl">
                        <flux:select wire:model.live="selectedEventId" label="Event">
                            @foreach ($events as $event)
                                <flux:select.option value="{{ $event->id }}">
                                    {{ $event->name }} - {{ $event->starts_at?->format('M j, g:i A') }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
            @elseif ($activeTab === 'departures')
                @if ($departures->isEmpty())
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                        <flux:icon name="clock" variant="outline" class="size-6 text-slate-300" />
                        <span>Add a departure time to start assigning routines for departures.</span>
                    </div>
                @else
                    <div class="max-w-xl">
                        <flux:select wire:model.live="selectedDepartureId" label="Departure">
                            @foreach ($departures as $departure)
                                <flux:select.option value="{{ $departure->id }}">
                                    {{ $departure->name }} - {{ \Illuminate\Support\Carbon::parse($departure->departure_time)->format('g:i A') }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
            @endif

            @if ($children->isEmpty())
                <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                    <flux:icon name="face-smile" variant="outline" class="size-6 text-slate-300" />
                    <span>Add children before assigning routines.</span>
                </div>
            @elseif (($activeTab === 'events' && $events->isEmpty()) || ($activeTab === 'departures' && $departures->isEmpty()))
            @else
                @php $assignmentBuckets = $this->assignmentBuckets(); @endphp
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($children as $child)
                        <div
                            wire:key="routine-bucket-{{ $child->id }}"
                            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-2 px-4 py-3"
                                style="border-top: 3px solid {{ $child->avatar_color }};"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-black text-white"
                                        style="background-color: {{ $child->avatar_color }};"
                                    >
                                        {{ strtoupper(mb_substr($child->name, 0, 1)) }}
                                    </div>
                                    <div class="text-sm font-bold text-slate-900">{{ $child->name }}</div>
                                </div>
                                <flux:badge size="sm" color="zinc">
                                    {{ count($assignmentBuckets[$child->id] ?? []) }} items
                                </flux:badge>
                            </div>
                            <div
                                x-data="{ isOver: false }"
                                @dragover.prevent="isOver = true"
                                @dragleave.prevent="isOver = false"
                                @drop.prevent="isOver = false; const routineId = $event.dataTransfer.getData('routine-item'); if (routineId) { $wire.assignRoutine(parseInt(routineId, 10), {{ $child->id }}); }"
                                :class="isOver ? 'border-amber-400 bg-amber-50/50' : 'border-slate-200'"
                                class="mx-3 mb-3 min-h-[120px] rounded-xl border border-dashed px-3 py-3 transition-colors"
                            >
                                <div class="space-y-2" wire:sort="reorderAssignment">
                                    @forelse ($assignmentBuckets[$child->id] ?? [] as $assignment)
                                        <div
                                            wire:key="assignment-{{ $assignment->id }}"
                                            wire:sort:item="{{ $assignment->id }}"
                                            class="flex items-center justify-between gap-2 rounded-lg border border-slate-200/80 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm"
                                        >
                                            <span>{{ $assignment->routineItem->name }}</span>
                                            <button
                                                type="button"
                                                wire:click="removeAssignment({{ $assignment->id }})"
                                                wire:sort:ignore
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                                                aria-label="Remove routine"
                                            >
                                                <flux:icon name="x-mark" variant="outline" class="size-3.5" />
                                            </button>
                                        </div>
                                    @empty
                                        <div class="flex min-h-[80px] flex-col items-center justify-center gap-1 text-xs text-slate-400">
                                            <flux:icon name="plus-circle" variant="outline" class="size-5" />
                                            <span>Drag routines here</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <flux:modal wire:model.self="showLibraryModal" class="md:w-96">
        <form wire:submit.prevent="saveLibrary" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingLibraryId ? 'Edit routine' : 'Add routine' }}</flux:heading>
                <flux:text class="mt-2">{{ $editingLibraryId ? 'Update routine name.' : 'Add a new routine item.' }}</flux:text>
            </div>

            <flux:input wire:model="libraryName" label="Name" />

            <div class="flex">
                <flux:spacer />
                <div class="flex gap-3">
                    <flux:button wire:click="$set('showLibraryModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save routine</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
