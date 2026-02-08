<?php

declare(strict_types=1);

use App\Enums\DashboardTheme;
use Livewire\Component;

return new class extends Component
{
    /**
     * @var list<array{id: string, title: string, hidden: bool}>
     */
    public array $hideableTiles = [];

    /**
     * @var list<array{id: string, title: string, hidden: bool, size: string}>
     */
    public array $configurableTiles = [];

    /**
     * @var array<string, string>
     */
    public array $tileSizeLabels = [];

    public bool $showCompletedTasks = true;

    public string $dashboardTheme = 'sunset-ember';

    /**
     * @param  list<array{id: string, title: string, hidden: bool}>  $hideableTiles
     * @param  list<array{id: string, title: string, hidden: bool, size: string}>  $configurableTiles
     * @param  array<string, string>  $tileSizeLabels
     */
    public function mount(array $hideableTiles = [], array $configurableTiles = [], array $tileSizeLabels = [], ?bool $showCompletedTasks = null, string $dashboardTheme = 'sunset-ember'): void
    {
        $this->dashboardTheme = $dashboardTheme;
        $this->hideableTiles = $this->normalizeHideableTiles($hideableTiles);
        $this->configurableTiles = $this->normalizeConfigurableTiles($configurableTiles);
        $this->tileSizeLabels = $tileSizeLabels;
        $this->showCompletedTasks = $showCompletedTasks ?? true;
    }

    public function toggleTileVisibility(string $tileId): void
    {
        foreach ($this->hideableTiles as $index => $hideableTile) {
            if ($hideableTile['id'] !== $tileId) {
                continue;
            }

            $this->hideableTiles[$index]['hidden'] = ! $hideableTile['hidden'];

            foreach ($this->configurableTiles as $configurableIndex => $configurableTile) {
                if ($configurableTile['id'] === $tileId) {
                    $this->configurableTiles[$configurableIndex]['hidden'] = ! $configurableTile['hidden'];
                    break;
                }
            }

            $this->dispatch('dashboard:toggle-tile-visibility', tileId: $tileId);

            return;
        }
    }

    public function updatedShowCompletedTasks(bool $show): void
    {
        $this->dispatch('dashboard:toggle-completed', show: $show);
    }

    public function updateTileSize(string $tileId, string $size): void
    {
        if (! array_key_exists($size, $this->tileSizeWidths())) {
            return;
        }

        $updated = false;
        $nextTiles = $this->configurableTiles;

        foreach ($nextTiles as $index => $configurableTile) {
            if ($configurableTile['id'] !== $tileId) {
                continue;
            }

            $nextTiles[$index]['size'] = $size;
            $updated = true;

            break;
        }

        if (! $updated) {
            return;
        }

        if (! $this->layoutIsValid($nextTiles)) {
            return;
        }

        $this->configurableTiles = $nextTiles;

        $this->dispatch('dashboard:update-tile-size', tileId: $tileId, size: $size);
    }

    /**
     * @param  array<int, mixed>  $hideableTiles
     * @return list<array{id: string, title: string, hidden: bool}>
     */
    private function normalizeHideableTiles(array $hideableTiles): array
    {
        $normalizedHideableTiles = [];

        foreach ($hideableTiles as $hideableTile) {
            if (! is_array($hideableTile)) {
                continue;
            }

            $tileId = $hideableTile['id'] ?? null;
            $title = $hideableTile['title'] ?? null;

            if (! is_string($tileId) || ! is_string($title)) {
                continue;
            }

            $normalizedHideableTiles[] = [
                'id' => $tileId,
                'title' => $title,
                'hidden' => (bool) ($hideableTile['hidden'] ?? false),
            ];
        }

        return $normalizedHideableTiles;
    }

    /**
     * @param  array<int, mixed>  $configurableTiles
     * @return list<array{id: string, title: string, hidden: bool, size: string}>
     */
    private function normalizeConfigurableTiles(array $configurableTiles): array
    {
        $normalizedConfigurableTiles = [];

        foreach ($configurableTiles as $configurableTile) {
            if (! is_array($configurableTile)) {
                continue;
            }

            $tileId = $configurableTile['id'] ?? null;
            $title = $configurableTile['title'] ?? null;
            $size = $configurableTile['size'] ?? null;

            if (! is_string($tileId) || ! is_string($title) || ! is_string($size)) {
                continue;
            }

            $normalizedConfigurableTiles[] = [
                'id' => $tileId,
                'title' => $title,
                'hidden' => (bool) ($configurableTile['hidden'] ?? false),
                'size' => $size,
            ];
        }

        return $normalizedConfigurableTiles;
    }

    /**
     * @param  list<array{id: string, title: string, hidden: bool, size: string}>  $tiles
     */
    private function layoutIsValid(array $tiles): bool
    {
        $visibleTiles = array_values(array_filter(
            $tiles,
            fn (array $tile): bool => $tile['id'] === 'settings' || ! $tile['hidden']
        ));

        $activeRowWidth = 0;
        $widths = $this->tileSizeWidths();

        foreach ($visibleTiles as $index => $tile) {
            $tileWidth = $widths[$tile['size']] ?? null;

            if (! is_int($tileWidth)) {
                return false;
            }

            if ($activeRowWidth + $tileWidth > 12) {
                return false;
            }

            $activeRowWidth += $tileWidth;
            $isLastTile = $index === count($visibleTiles) - 1;

            if ($activeRowWidth === 12) {
                $activeRowWidth = 0;

                continue;
            }

            if (! $isLastTile) {
                $remainingWidths = 0;

                for ($remainingIndex = $index + 1; $remainingIndex < count($visibleTiles); $remainingIndex++) {
                    $remainingTile = $visibleTiles[$remainingIndex];
                    $remainingWidth = $widths[$remainingTile['size']] ?? null;

                    if (! is_int($remainingWidth)) {
                        return false;
                    }

                    $remainingWidths += $remainingWidth;
                }

                if ($remainingWidths === 0 || ($remainingWidths + $activeRowWidth) < 12) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<string, int>
     */
    private function tileSizeWidths(): array
    {
        return [
            'compact' => 3,
            'small' => 4,
            'medium' => 6,
            'large' => 8,
            'full' => 12,
        ];
    }
};
?>

@placeholder
    <div class="space-y-3">
        <flux:skeleton.group animate="shimmer" class="space-y-3">
            <div class="rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <flux:skeleton.line class="w-1/3" />
                    <flux:skeleton class="h-8 w-20 rounded-full" />
                </div>
            </div>

            <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
                <div class="space-y-2">
                    <flux:skeleton.line class="w-1/2" />
                    <flux:skeleton.line class="w-2/3" />
                    <flux:skeleton.line class="w-3/5" />
                </div>
            </div>

            <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
                <div class="space-y-2">
                    <flux:skeleton.line class="w-2/5" />
                    <flux:skeleton class="h-9 w-full rounded-xl" />
                    <flux:skeleton class="h-9 w-full rounded-xl" />
                </div>
            </div>
        </flux:skeleton.group>
    </div>
@endplaceholder

<div wire:sort:ignore class="space-y-3">
    <div class="flex items-center justify-between gap-3 rounded-2xl border border-dash-border bg-dash-card px-4 py-3">
        <span class="font-semibold text-slate-100">Completed tasks</span>
        <flux:switch wire:model.live="showCompletedTasks" label="Toggle completed tasks" />
    </div>

    <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Mood</p>
        <div class="mt-3 grid grid-cols-1 gap-2">
            @foreach (DashboardTheme::cases() as $mood)
                <button
                    wire:key="mood-{{ $mood->value }}"
                    wire:click="$parent.setTheme('{{ $mood->value }}')"
                    @class([
                        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition',
                        'bg-white/15 text-white ring-1 ring-white/25' => $dashboardTheme === $mood->value,
                        'bg-dash-card-hover/40 text-slate-300 hover:bg-white/10 hover:text-white' => $dashboardTheme !== $mood->value,
                    ])
                >
                    <span @class([
                        'inline-block h-5 w-5 shrink-0 rounded-full',
                        'bg-gradient-to-br from-indigo-900 via-rose-800 to-amber-700' => $mood === DashboardTheme::SunsetEmber,
                        'bg-gradient-to-br from-slate-900 via-emerald-800 to-indigo-900' => $mood === DashboardTheme::NorthernLights,
                        'bg-gradient-to-br from-indigo-950 via-teal-800 to-rose-900' => $mood === DashboardTheme::TropicalDusk,
                    ])></span>
                    {{ $mood->label() }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Tile visibility</p>
        <div class="mt-3 space-y-2">
            @foreach ($hideableTiles as $hideableTile)
                <div wire:key="visibility-toggle-{{ $hideableTile['id'] }}" class="flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-slate-100">{{ $hideableTile['title'] }}</span>
                    <flux:button
                        variant="subtle"
                        size="sm"
                        wire:click="toggleTileVisibility('{{ $hideableTile['id'] }}')"
                    >
                        {{ $hideableTile['hidden'] ? 'Show' : 'Hide' }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Tile sizes</p>
        <div class="mt-3 space-y-2">
            @foreach ($configurableTiles as $configurableTile)
                <div wire:key="size-toggle-{{ $configurableTile['id'] }}" class="space-y-2 rounded-xl border border-dash-border/60 bg-dash-card-hover/30 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-slate-100">{{ $configurableTile['title'] }}</span>
                        @if ($configurableTile['hidden'])
                            <span class="rounded-full border border-dash-border px-2 py-0.5 text-xs text-slate-300">Hidden</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($tileSizeLabels as $size => $sizeLabel)
                            <flux:button
                                variant="subtle"
                                size="sm"
                                wire:click="updateTileSize('{{ $configurableTile['id'] }}', '{{ $size }}')"
                                @class([
                                    'justify-start',
                                    'ring-2 ring-fuchsia-300/60' => $configurableTile['size'] === $size,
                                ])
                            >
                                {{ $sizeLabel }}
                            </flux:button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-dash-border bg-dash-card p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300/80">Admin quick links</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            <flux:button variant="subtle" size="sm" href="{{ route('admin.settings') }}">Settings</flux:button>
            <flux:button variant="subtle" size="sm" href="{{ route('admin.children') }}">Children</flux:button>
            <flux:button variant="subtle" size="sm" href="{{ route('admin.departures') }}">Departures</flux:button>
            <flux:button variant="subtle" size="sm" href="{{ route('admin.events') }}">Events</flux:button>
            <flux:button variant="subtle" size="sm" href="{{ route('admin.routines') }}">Routines</flux:button>
            <flux:button variant="subtle" size="sm" href="{{ route('admin.weather') }}">Weather</flux:button>
        </div>
    </div>
</div>
