<?php

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::admin')]
    /**
     * @var Collection<int, CalendarEvent>
     */
    public Collection $events;

    /**
     * @var array<int, string>
     */
    public array $categoryOptions = ['school', 'sports', 'family', 'other'];

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $startsAt = '';

    public string $departureTime = '';

    public string $category = 'school';

    public string $color = '#38bdf8';

    public function mount(): void
    {
        $this->events = $this->loadEvents();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->startsAt = '';
        $this->departureTime = '';
        $this->category = 'school';
        $this->color = '#38bdf8';
        $this->showModal = true;
    }

    public function openEdit(int $eventId): void
    {
        $event = CalendarEvent::query()->findOrFail($eventId);

        $this->editingId = $event->id;
        $this->name = $event->name;
        $this->startsAt = $event->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->departureTime = $event->departure_time
            ? Carbon::parse($event->departure_time)->format('H:i')
            : '';
        $this->category = $event->category;
        $this->color = $event->color;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'startsAt' => ['required', 'date'],
            'departureTime' => ['nullable', 'date_format:H:i'],
            'category' => ['required', 'string', Rule::in($this->categoryOptions)],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'starts_at' => Carbon::parse($validated['startsAt'])->toDateTimeString(),
            'departure_time' => $validated['departureTime'] !== ''
                ? $this->normalizeTime($validated['departureTime'])
                : null,
            'category' => $validated['category'],
            'color' => $validated['color'],
        ];

        if ($this->editingId !== null) {
            CalendarEvent::query()->whereKey($this->editingId)->update($payload);
        } else {
            CalendarEvent::query()->create($payload);
        }

        $this->showModal = false;
        $this->events = $this->loadEvents();
    }

    public function delete(int $eventId): void
    {
        CalendarEvent::query()->whereKey($eventId)->delete();
        $this->events = $this->loadEvents();
    }

    /**
     * @return Collection<int, CalendarEvent>
     */
    private function loadEvents(): Collection
    {
        return CalendarEvent::query()->orderBy('starts_at')->get();
    }

    private function normalizeTime(string $time): string
    {
        if (strlen($time) === 5) {
            return $time.':00';
        }

        if (strlen($time) === 8) {
            return $time;
        }

        return Carbon::parse($time)->format('H:i:s');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="calendar" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Events</flux:heading>
                <flux:text>Manage calendar events and optional departures.</flux:text>
            </div>
        </div>
        <flux:button variant="primary" wire:click="openCreate">Add event</flux:button>
    </div>

    <div class="space-y-3">
        @forelse ($events as $event)
            <div
                wire:key="event-{{ $event->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm transition-all hover:border-amber-300/60 hover:shadow-md"
                style="border-left: 4px solid {{ $event->color }};"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-4 rounded-full" style="background-color: {{ $event->color }};"></span>
                        <div class="text-sm font-bold text-slate-900">{{ $event->name }}</div>
                        <flux:badge size="sm" color="zinc">{{ ucfirst($event->category) }}</flux:badge>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ $event->starts_at?->format('D M j, g:i A') }}
                        @if ($event->departure_time)
                            · Depart {{ \Illuminate\Support\Carbon::parse($event->departure_time)->format('g:i A') }}
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button size="xs" variant="subtle" wire:click="openEdit({{ $event->id }})">Edit</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $event->id }})">Delete</flux:button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                <flux:icon name="calendar" variant="outline" class="size-8 text-slate-300" />
                <span>No events scheduled.</span>
            </div>
        @endforelse
    </div>

    <flux:modal wire:model.self="showModal" class="md:w-xl">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit event' : 'Add event' }}</flux:heading>
                <flux:text class="mt-2">{{ $editingId ? 'Update event details.' : 'Add a new calendar event.' }}</flux:text>
            </div>

            <flux:input wire:model="name" label="Name" />

            <flux:input type="datetime-local" wire:model="startsAt" label="Start time" />

            <flux:input type="time" wire:model="departureTime" label="Departure time (optional)" />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="category" label="Category">
                    @foreach ($categoryOptions as $option)
                        <flux:select.option value="{{ $option }}">{{ ucfirst($option) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div>
                    <flux:label>Color</flux:label>
                    <div class="mt-2 flex items-center gap-3">
                        <input type="color" wire:model="color" class="h-10 w-14 rounded-lg border" />
                        <flux:text>{{ $color }}</flux:text>
                    </div>
                    <flux:error name="color" />
                </div>
            </div>

            <div class="flex">
                <flux:spacer />
                <div class="flex gap-3">
                    <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save event</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
