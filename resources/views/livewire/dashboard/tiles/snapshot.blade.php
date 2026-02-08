<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\NextDepartureService;
use App\Services\SchoolLunchService;
use Livewire\Attributes\Computed;
use Livewire\Component;

return new class extends Component
{
    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function nextDeparture(): ?array
    {
        return app(NextDepartureService::class)->determine();
    }

    /**
     * @return array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null
     */
    #[Computed]
    public function schoolLunch(): ?array
    {
        return app(SchoolLunchService::class)->forDate(now($this->adminTimezone()));
    }

    private function adminTimezone(): string
    {
        $timezone = Setting::get('timezone', config('app.timezone'));

        return is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone');
    }
};
?>

@placeholder
    <div class="space-y-4">
        <flux:skeleton.group animate="shimmer" class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-dash-border bg-dash-card p-5">
                <flux:skeleton class="h-12 w-12 rounded-xl" />
                <flux:skeleton.line class="mt-4 w-1/3" />
                <flux:skeleton.line size="lg" class="mt-2 w-2/3" />
            </div>
            <div class="rounded-2xl border border-dash-border bg-dash-card p-5">
                <flux:skeleton.line class="w-1/2" />
                <flux:skeleton.line size="lg" class="mt-3 w-3/4" />
                <flux:skeleton.line class="mt-2 w-2/5" />
            </div>
            <div class="rounded-2xl border border-dash-border bg-dash-card p-5">
                <flux:skeleton class="h-12 w-12 rounded-xl" />
                <flux:skeleton.line class="mt-4 w-2/5" />
                <flux:skeleton.line size="lg" class="mt-2 w-1/2" />
            </div>
        </flux:skeleton.group>
    </div>
@endplaceholder

<div>
    <livewire:dashboard.header :next-departure="$this->nextDeparture" :school-lunch="$this->schoolLunch" />
</div>
