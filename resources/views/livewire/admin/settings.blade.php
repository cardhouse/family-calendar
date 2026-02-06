<?php

declare(strict_types=1);

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::admin')]
    public string $timezone = '';

    /**
     * @var array<int, array{value: string, label: string}>
     */
    public array $timezoneOptions = [];

    public bool $saved = false;

    public function mount(): void
    {
        $this->timezoneOptions = $this->buildTimezoneOptions();
        $this->timezone = $this->currentTimezone();
    }

    public function updatedTimezone(): void
    {
        $this->saved = false;
    }

    public function save(): void
    {
        $this->saved = false;

        $validated = $this->validate([
            'timezone' => ['required', 'timezone'],
        ]);

        Setting::set('timezone', $validated['timezone']);

        $this->saved = true;
    }

    private function currentTimezone(): string
    {
        $timezone = Setting::get('timezone', config('app.timezone'));

        return is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function buildTimezoneOptions(): array
    {
        $now = now('UTC');

        return collect(\DateTimeZone::listIdentifiers())
            ->map(function (string $timezone) use ($now): array {
                $offset = (new \DateTimeZone($timezone))->getOffset($now);
                $sign = $offset >= 0 ? '+' : '-';
                $hours = intdiv(abs($offset), 3600);
                $minutes = intdiv(abs($offset) % 3600, 60);

                return [
                    'value' => $timezone,
                    'label' => sprintf('UTC%s%02d:%02d - %s', $sign, $hours, $minutes, $timezone),
                    'sort' => sprintf('%+08d-%s', $offset, $timezone),
                ];
            })
            ->sortBy('sort')
            ->values()
            ->map(fn (array $option): array => [
                'value' => $option['value'],
                'label' => $option['label'],
            ])
            ->all();
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="cog-6-tooth" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Settings</flux:heading>
                <flux:text>Control timezone and display preferences.</flux:text>
            </div>
        </div>
    </div>

    <flux:callout icon="clock">
        <flux:callout.heading>Timezones</flux:callout.heading>
        <flux:callout.text>
            All departure and event times are stored in UTC. Choose the timezone that should be used for inputs and display.
        </flux:callout.text>
    </flux:callout>

    <div class="rounded-2xl border border-slate-200/80 dark:border-zinc-700 bg-white dark:bg-zinc-900/70 px-6 py-5 shadow-sm">
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:select
                wire:model="timezone"
                searchable
                label="Timezone"
                placeholder="Select a timezone"
            >
                @foreach ($timezoneOptions as $option)
                    <flux:select.option value="{{ $option['value'] }}">
                        {{ $option['label'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="timezone" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">Save settings</flux:button>
                @if ($saved)
                    <flux:text class="text-sm text-emerald-600">Saved.</flux:text>
                @endif
            </div>
        </form>
    </div>
</div>
