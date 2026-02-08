@props([
    'tileId',
    'title',
    'description' => null,
    'span' => 'lg:col-span-4',
])

<section
    wire:key="dashboard-tile-{{ $tileId }}"
    wire:sort:item="{{ $tileId }}"
    @class([
        'rounded-3xl border border-dash-border bg-dash-card/80 p-4 backdrop-blur',
        $span,
    ])
>
    <div class="flex items-start justify-between gap-3">
        <div class="max-w-[85%]">
            <flux:heading size="lg" class="font-black text-slate-100">{{ $title }}</flux:heading>

            @if ($description)
                <flux:text class="mt-1 text-slate-200/80">{{ $description }}</flux:text>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @isset($actions)
                {{ $actions }}
            @endisset

            <button
                type="button"
                wire:sort:handle
                class="inline-flex h-9 w-9 cursor-grab items-center justify-center rounded-xl border border-dash-border bg-dash-card text-slate-200 transition-colors hover:bg-dash-card-hover active:cursor-grabbing"
                title="Drag to reorder tile"
                aria-label="Drag to reorder tile"
            >
                <flux:icon name="bars-3" variant="solid" class="size-4" />
            </button>
        </div>
    </div>

    <div class="mt-4" wire:sort:ignore>
        {{ $slot }}
    </div>
</section>
