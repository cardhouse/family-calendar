<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Routines</h1>
            <p class="text-sm text-slate-500">Build routine libraries and assign them to daily, event, or departure lists.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Routine library</h2>
                    <p class="text-xs text-slate-400">Drag a routine into a child bucket to assign it.</p>
                </div>
                <button
                    type="button"
                    wire:click="openLibraryCreate"
                    class="rounded-full bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white"
                >
                    Add routine
                </button>
            </div>

            <div class="space-y-3" wire:sort="reorderLibrary">
                @forelse ($routineItems as $routineItem)
                    <div
                        wire:key="routine-item-{{ $routineItem->id }}"
                        wire:sort:item="{{ $routineItem->id }}"
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <button
                                    type="button"
                                    wire:sort:handle
                                    class="mt-1 rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Sort
                                </button>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $routineItem->name }}</div>
                                    <span
                                        class="mt-2 inline-flex items-center rounded-full border border-dashed border-slate-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                        draggable="true"
                                        @dragstart="$event.dataTransfer.setData('routine-item', '{{ $routineItem->id }}'); $event.dataTransfer.effectAllowed = 'copy';"
                                    >
                                        Drag to assign
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="openLibraryEdit({{ $routineItem->id }})"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteLibrary({{ $routineItem->id }})"
                                    class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                        Add your first routine to start building assignments.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Assignments</h2>
                    <p class="text-sm text-slate-500">Drop routines into each child bucket to build checklists.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 rounded-full border border-slate-200 bg-white p-2 text-sm">
                <button
                    type="button"
                    wire:click="$set('activeTab', 'daily')"
                    class="rounded-full px-4 py-1 text-sm font-semibold {{ $activeTab === 'daily' ? 'bg-slate-900 text-white' : 'text-slate-600' }}"
                >
                    Daily
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'events')"
                    class="rounded-full px-4 py-1 text-sm font-semibold {{ $activeTab === 'events' ? 'bg-slate-900 text-white' : 'text-slate-600' }}"
                >
                    Events
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'departures')"
                    class="rounded-full px-4 py-1 text-sm font-semibold {{ $activeTab === 'departures' ? 'bg-slate-900 text-white' : 'text-slate-600' }}"
                >
                    Departures
                </button>
            </div>

            @error('assignment')
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $message }}
                </div>
            @enderror

            @if ($activeTab === 'events')
                @if ($events->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                        Add an event to start assigning routines for special days.
                    </div>
                @else
                    <div class="max-w-xl rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Event</label>
                        <select
                            wire:model.live="selectedEventId"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        >
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">
                                    {{ $event->name }} - {{ $event->starts_at?->format('M j, g:i A') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @elseif ($activeTab === 'departures')
                @if ($departures->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                        Add a departure time to start assigning routines for departures.
                    </div>
                @else
                    <div class="max-w-xl rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departure</label>
                        <select
                            wire:model.live="selectedDepartureId"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        >
                            @foreach ($departures as $departure)
                                <option value="{{ $departure->id }}">
                                    {{ $departure->name }} - {{ \Illuminate\Support\Carbon::parse($departure->departure_time)->format('g:i A') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endif

            @if ($children->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                    Add children before assigning routines.
                </div>
            @elseif (($activeTab === 'events' && $events->isEmpty()) || ($activeTab === 'departures' && $departures->isEmpty()))
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($children as $child)
                        <div
                            wire:key="routine-bucket-{{ $child->id }}"
                            class="rounded-2xl border border-slate-200 bg-white p-4"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-slate-900">{{ $child->name }}</div>
                                <div class="text-xs text-slate-400">
                                    {{ count($assignmentBuckets[$child->id] ?? []) }} items
                                </div>
                            </div>
                            <div
                                x-data="{ isOver: false }"
                                @dragover.prevent="isOver = true"
                                @dragleave.prevent="isOver = false"
                                @drop.prevent="isOver = false; const routineId = $event.dataTransfer.getData('routine-item'); if (routineId) { $wire.assignRoutine(parseInt(routineId, 10), {{ $child->id }}); }"
                                :class="isOver ? 'border-slate-400 bg-slate-50' : 'border-slate-200'"
                                class="mt-3 min-h-[120px] rounded-2xl border border-dashed px-3 py-3"
                            >
                                <div class="space-y-2" wire:sort="reorderAssignment">
                                    @forelse ($assignmentBuckets[$child->id] ?? [] as $assignment)
                                        <div
                                            wire:key="assignment-{{ $assignment->id }}"
                                            wire:sort:item="{{ $assignment->id }}"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800"
                                        >
                                            {{ $assignment->routineItem->name }}
                                        </div>
                                    @empty
                                        <div class="flex min-h-[80px] items-center justify-center text-xs text-slate-400">
                                            Drag routines here
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
