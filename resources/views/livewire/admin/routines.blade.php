<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 lg:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-slate-900">Routines</h1>
                <p class="text-sm text-slate-500">
                    Build the routine library on the left, then drag items into child buckets on the right.
                </p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">
                    {{ $routineItems->count() }} library items
                </span>
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">
                    {{ $children->count() }} child buckets
                </span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(320px,360px)_1fr]">
        <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-5 xl:sticky xl:top-8 xl:self-start">
            <div class="space-y-3">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Routine library</h2>
                    <p class="mt-1 text-xs text-slate-400">Quick-add a routine, then drag it into a child bucket.</p>
                </div>

                <form wire:submit.prevent="addLibraryQuick" class="space-y-2">
                    <label class="sr-only" for="quick-library-name">Routine name</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="quick-library-name"
                            type="text"
                            wire:model="quickLibraryName"
                            placeholder="Add routine item"
                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        <button
                            type="submit"
                            class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                        >
                            Add
                        </button>
                    </div>
                    @error('quickLibraryName')
                        <p class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </form>

                <button
                    type="button"
                    wire:click="openLibraryCreate"
                    class="w-full rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Open full create form
                </button>
            </div>

            <div class="max-h-[60vh] space-y-2 overflow-y-auto pr-1" wire:sort="reorderLibrary">
                @forelse ($routineItems as $routineItem)
                    <article
                        wire:key="routine-item-{{ $routineItem->id }}"
                        wire:sort:item="{{ $routineItem->id }}"
                        class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 space-y-2">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:sort:handle
                                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Sort
                                    </button>
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $routineItem->name }}</p>
                                </div>

                                <button
                                    type="button"
                                    draggable="true"
                                    @dragstart="$event.dataTransfer.setData('routine-item', '{{ $routineItem->id }}'); $event.dataTransfer.effectAllowed = 'copy';"
                                    class="inline-flex items-center rounded-full border border-dashed border-slate-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Drag to assign
                                </button>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="openLibraryEdit({{ $routineItem->id }})"
                                    class="rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteLibrary({{ $routineItem->id }})"
                                    class="rounded-full border border-rose-200 px-2.5 py-1 text-[11px] font-semibold text-rose-600"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                        Add your first routine to start building assignments.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="min-w-0 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white p-3">
                <div class="flex flex-wrap gap-2 text-sm">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'daily')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $activeTab === 'daily' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Daily
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'events')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $activeTab === 'events' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Events
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'departures')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $activeTab === 'departures' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Departures
                    </button>
                </div>

                @if ($activeTab === 'events' && $events->isNotEmpty())
                    <label class="flex w-full items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:w-auto">
                        Event
                        <select
                            wire:model.live="selectedEventId"
                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-medium normal-case sm:min-w-72"
                        >
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">
                                    {{ $event->name }} - {{ $event->starts_at?->format('M j, g:i A') }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @elseif ($activeTab === 'departures' && $departures->isNotEmpty())
                    <label class="flex w-full items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:w-auto">
                        Departure
                        <select
                            wire:model.live="selectedDepartureId"
                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-medium normal-case sm:min-w-72"
                        >
                            @foreach ($departures as $departure)
                                <option value="{{ $departure->id }}">
                                    {{ $departure->name }} - {{ \Illuminate\Support\Carbon::parse($departure->departure_time)->format('g:i A') }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>

            @error('assignment')
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $message }}
                </div>
            @enderror

            @if ($activeTab === 'events' && $events->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                    Add an event to start assigning routines for special days.
                </div>
            @elseif ($activeTab === 'departures' && $departures->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                    Add a departure time to start assigning routines for departures.
                </div>
            @elseif ($children->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                    Add children before assigning routines.
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($children as $child)
                        <article
                            wire:key="routine-bucket-{{ $child->id }}"
                            class="rounded-2xl border border-slate-200 bg-white p-4"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="truncate text-sm font-semibold text-slate-900">{{ $child->name }}</h3>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-500">
                                    {{ count($assignmentBuckets[$child->id] ?? []) }}
                                </span>
                            </div>

                            <div
                                x-data="{ isOver: false }"
                                @dragover.prevent="isOver = true"
                                @dragleave.prevent="isOver = false"
                                @drop.prevent="isOver = false; const routineId = $event.dataTransfer.getData('routine-item'); if (routineId) { $wire.assignRoutine(parseInt(routineId, 10), {{ $child->id }}); }"
                                :class="isOver ? 'border-slate-500 bg-slate-50' : 'border-slate-200'"
                                class="mt-3 min-h-[180px] rounded-2xl border border-dashed p-3"
                            >
                                <div class="space-y-2" wire:sort="reorderAssignment">
                                    @forelse ($assignmentBuckets[$child->id] ?? [] as $assignment)
                                        <div
                                            wire:key="assignment-{{ $assignment->id }}"
                                            wire:sort:item="{{ $assignment->id }}"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <button
                                                        type="button"
                                                        wire:sort:handle
                                                        class="rounded-lg border border-slate-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                                    >
                                                        Sort
                                                    </button>
                                                    <p class="truncate text-sm font-medium text-slate-800">
                                                        {{ $assignment->routineItem->name }}
                                                    </p>
                                                </div>

                                                <button
                                                    type="button"
                                                    wire:click="removeAssignment({{ $assignment->id }})"
                                                    class="shrink-0 rounded-full border border-rose-200 px-2 py-0.5 text-[11px] font-semibold text-rose-600"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex min-h-[120px] items-center justify-center rounded-xl border border-dashed border-slate-200 px-2 text-center text-xs text-slate-400">
                                            Drop routine items here
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    @if ($showLibraryModal)
        <div class="fixed inset-0 z-40 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <form
                wire:submit.prevent="saveLibrary"
                class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ $editingLibraryId ? 'Edit routine' : 'Add routine' }}
                    </h2>
                    <button type="button" class="text-sm text-slate-500" wire:click="$set('showLibraryModal', false)">
                        Close
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                        <input
                            type="text"
                            wire:model="libraryName"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('libraryName')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600"
                        wire:click="$set('showLibraryModal', false)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Save routine
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
