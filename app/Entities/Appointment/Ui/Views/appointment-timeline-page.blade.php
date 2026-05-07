<div class="pb-4 pl-0 pr-3 pt-1 sm:pr-4">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="app-title text-xl font-semibold">{{ __('Chronologie des rendez-vous') }}</h1>
            <p class="app-subtitle mt-0.5 text-xs">{{ __('Grille horaire de 08:00 à 22:00') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="previousDay"
                class="app-btn-secondary px-3 py-1.5 text-sm">←</button>
            <button type="button" wire:click="today"
                @disabled($isToday)
                class="app-btn-secondary px-3 py-1.5 text-sm disabled:opacity-60">
                {{ __("Aujourd'hui") }}
            </button>
            <button type="button" wire:click="nextDay"
                class="app-btn-secondary px-3 py-1.5 text-sm">→</button>
        </div>
    </div>

    <div class="mb-2 rounded-lg border px-3 py-1.5 text-xs font-medium app-text-gray"
        style="border-color: color-mix(in srgb, var(--color-raw-primary-blue) 35%, white);">
        {{ ucfirst($selectedDateLabel) }}
    </div>

    @if($gapAlerts->isNotEmpty())
        <div class="mb-2 rounded-lg border px-3 py-2 text-xs"
            style="border-color: #f59e0b; background-color: color-mix(in srgb, #f59e0b 12%, white); color: #92400e;">
            <span class="font-semibold">{{ __('Écarts > 30 min détectés:') }}</span>
            <div class="mt-1 space-y-0.5">
                @foreach($gapAlerts as $gap)
                    <div>{{ $gap['from'] }}→{{ $gap['to'] }} ({{ number_format((float) $gap['minutes'], 0) }} min)</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border bg-white/80"
        style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
        <div class="grid border-b px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wide app-text-gray"
            style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
            <div class="whitespace-nowrap">{{ __('Patient') }}</div>
            <div class="whitespace-nowrap">{{ __('Horaire') }}</div>
            <div class="whitespace-nowrap text-right">{{ __('Reçu (DH)') }}</div>
        </div>

        <div class="px-2 py-0">
            @forelse($rows as $row)
                @if($row['patient_id'])
                    <a href="{{ route('treatments.index', ['patient' => $row['patient_id']]) }}"
                        class="grid border-b px-1 py-0.5 leading-tight transition-colors hover:rounded hover:bg-[color:color-mix(in_srgb,var(--color-raw-primary-blue)_12%,white)]"
                        style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);"
                        title="{{ __('Voir les traitements') }}">
                        <span class="whitespace-nowrap text-xs font-medium app-title">
                            {{ $row['off'] }}
                        </span>
                        <span class="whitespace-nowrap" style="font-size: 10px; font-weight: 700; color: #ea580c;">
                            {{ $row['started_at'] }} - {{ $row['completed_at'] }}
                        </span>
                        <span class="whitespace-nowrap text-right text-xs" style="color: {{ ((float) $row['received']) > 0 ? '#16a34a' : '#dc2626' }};">
                            {{ $row['received'] }}
                        </span>
                    </a>
                @else
                    <div class="grid border-b px-1 py-0.5 leading-tight"
                        style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                        <span class="whitespace-nowrap text-xs font-medium app-title">
                            {{ $row['off'] }}
                        </span>
                        <span class="whitespace-nowrap" style="font-size: 10px; font-weight: 700; color: #ea580c;">
                            {{ $row['started_at'] }} - {{ $row['completed_at'] }}
                        </span>
                        <span class="whitespace-nowrap text-right text-xs" style="color: {{ ((float) $row['received']) > 0 ? '#16a34a' : '#dc2626' }};">
                            {{ $row['received'] }}
                        </span>
                    </div>
                @endif
            @empty
                <div class="py-3 text-center text-xs app-text-muted">{{ __('Aucun rendez-vous pour cette date.') }}</div>
            @endforelse
        </div>

        <div class="border-t px-2 py-2"
            style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
            <div class="grid items-center" style="grid-template-columns: 1.2fr 1fr 0.8fr;">
                <span class="col-start-1 col-end-3 text-xs font-semibold app-text-gray">{{ __('Total à remettre au médecin:') }}</span>
                <span class="whitespace-nowrap text-sm font-semibold app-title" style="grid-column: 3 / 4; justify-self: end;">{{ $totalReceived }} DH</span>
            </div>
        </div>
    </div>
</div>