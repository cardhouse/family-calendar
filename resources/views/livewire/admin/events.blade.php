<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Events</h1>
            <p class="text-sm text-slate-500">Manage calendar events and optional departures.</p>
        </div>
        <button
            type="button"
            wire:click="openCreate"
            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
        >
            Add event
        </button>
    </div>

    <div class="space-y-3">
        @forelse ($events as $event)
            <div
                wire:key="event-{{ $event->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" style="background-color: {{ $event->color }};"></span>
                        <div class="text-sm font-semibold text-slate-900">{{ $event->name }}</div>
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $event->starts_at?->format('D M j, g:i A') }}
                        @if ($event->departure_time)
                            · Depart {{ \Illuminate\Support\Carbon::parse($event->departure_time)->format('g:i A') }}
                        @endif
                    </div>
                    <div class="text-xs uppercase tracking-wide text-slate-400">{{ $event->category }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="openEdit({{ $event->id }})"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        wire:click="delete({{ $event->id }})"
                        class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                No events scheduled.
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <form
                wire:submit.prevent="save"
                class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ $editingId ? 'Edit event' : 'Add event' }}
                    </h2>
                    <button type="button" class="text-sm text-slate-500" wire:click="$set('showModal', false)">
                        Close
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Start time</label>
                        <input
                            type="datetime-local"
                            wire:model="startsAt"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('startsAt')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departure time (optional)</label>
                        <input
                            type="time"
                            wire:model="departureTime"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('departureTime')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                            <select
                                wire:model="category"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                            >
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Color</label>
                            <div class="mt-2 flex items-center gap-3">
                                <input type="color" wire:model="color" class="h-10 w-14 rounded-lg border" />
                                <span class="text-xs text-slate-500">{{ $color }}</span>
                            </div>
                            @error('color')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600"
                        wire:click="$set('showModal', false)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Save event
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
