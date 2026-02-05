<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Routines extends Component
{
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

    public function render(): mixed
    {
        return view('livewire.admin.routines', [
            'assignmentBuckets' => $this->assignmentBuckets(),
        ]);
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
    private function assignmentBuckets(): array
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
}
