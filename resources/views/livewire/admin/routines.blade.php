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
