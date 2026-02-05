<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Weather</h1>
        <p class="text-sm text-slate-500">Configure location, widget style, and dashboard weather display options.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6">
        <div class="space-y-2">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Location search</label>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Search city or town"
                class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
            />
            @if ($searchResults !== [])
                <div class="rounded-2xl border border-slate-200 bg-white p-2">
                    <div class="space-y-1">
                        @foreach ($searchResults as $index => $result)
                            <button
                                type="button"
                                wire:key="location-result-{{ $index }}"
                                wire:click="selectLocation({{ $index }})"
                                class="w-full rounded-xl px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100"
                            >
                                {{ $result['name'] }}
                                @if ($result['admin1'])
                                    , {{ $result['admin1'] }}
                                @endif
                                @if ($result['country'])
                                    , {{ $result['country'] }}
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($selectedLocation)
                <p class="text-xs text-slate-500">
                    Selected:
                    {{ $selectedLocation['name'] ?? 'Saved location' }}
                    @if (! empty($selectedLocation['admin1']))
                        , {{ $selectedLocation['admin1'] }}
                    @endif
                    @if (! empty($selectedLocation['country']))
                        , {{ $selectedLocation['country'] }}
                    @endif
                </p>
            @endif
            @error('selectedLocation')
                <p class="text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Units</label>
                <select
                    wire:model="units"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                >
                    <option value="fahrenheit">Fahrenheit</option>
                    <option value="celsius">Celsius</option>
                </select>
                @error('units')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Widget size</label>
                <select
                    wire:model="widgetSize"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                >
                    <option value="compact">Compact</option>
                    <option value="medium">Medium</option>
                    <option value="large">Large</option>
                </select>
                @error('widgetSize')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" wire:model="widgetEnabled" class="rounded" />
                <span class="text-slate-600">Widget enabled</span>
            </label>
            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" wire:model="showFeelsLike" class="rounded" />
                <span class="text-slate-600">Show feels-like</span>
            </label>
            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" wire:model="precipitationAlerts" class="rounded" />
                <span class="text-slate-600">Precipitation alert</span>
            </label>
        </div>

        @if ($statusMessage !== '')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $statusMessage }}
            </div>
        @endif

        <div class="flex justify-end">
            <button
                type="submit"
                class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
            >
                Save settings
            </button>
        </div>
    </form>

    <section class="space-y-3 rounded-3xl border border-slate-200 bg-white p-6">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Preview</h2>
            <p class="text-xs text-slate-500">This reflects your saved configuration.</p>
        </div>
        @if ($widgetEnabled)
            <livewire:weather-widget
                :size="$widgetSize"
                :units="$units"
                :show-feels-like="$showFeelsLike"
                :show-precipitation-alerts="$precipitationAlerts"
                :location="$selectedLocation"
                wire:key="weather-preview-{{ $widgetSize }}-{{ $units }}-{{ (int) $showFeelsLike }}-{{ (int) $precipitationAlerts }}"
            />
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                Widget is disabled. Enable it to show weather on the dashboard.
            </div>
        @endif
    </section>
</div>
