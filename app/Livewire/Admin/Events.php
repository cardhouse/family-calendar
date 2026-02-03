<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Events extends Component
{
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

    public function render(): mixed
    {
        return view('livewire.admin.events');
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
}
