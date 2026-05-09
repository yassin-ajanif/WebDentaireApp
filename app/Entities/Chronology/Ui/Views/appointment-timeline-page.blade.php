<div class="pb-4 pl-0 pr-3 pt-1 sm:pr-4">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h1 class="app-title text-xl font-semibold">{{ __('Chronologie des rendez-vous') }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2">
                <label for="timeline-date" class="sr-only">{{ __('Date') }}</label>
                <input id="timeline-date" type="date" wire:model.live="selectedDate"
                    class="app-input px-2 py-1.5 text-sm shadow-sm" />
            </div>
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

    {{-- Table 1: Sessions du jour --}}
    <h2 class="mb-1 text-sm font-semibold app-title">{{ __('Séances du jour — montants reçus') }}</h2>
    <div class="mb-6 overflow-hidden rounded-xl border bg-white/80"
        style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
        <div class="grid border-b px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wide app-text-gray"
            style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
            <div class="whitespace-nowrap">{{ __('Patient') }}</div>
            <div class="whitespace-nowrap">{{ __('Horaire') }}</div>
            <div class="whitespace-nowrap text-right">{{ __('Reçu (DH)') }}</div>
        </div>

        <div class="px-2 py-0">
            @forelse($sessionRows as $row)
                <a href="{{ route('treatments.index', ['patient' => $row['patient_id'], 'treatment' => $row['treatment_info_id'], 'highlight_date' => $selectedDate]) }}"
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
            @empty
                <div class="py-3 text-center text-xs app-text-muted">{{ __('Aucune séance pour cette date.') }}</div>
            @endforelse
        </div>

        <div class="border-t px-2 py-2"
            style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
            <div class="grid items-center" style="grid-template-columns: 1.2fr 1fr 0.8fr;">
                <span class="col-start-1 col-end-3 text-xs font-semibold app-text-gray">{{ __('Total reçu:') }}</span>
                <span class="whitespace-nowrap text-sm font-semibold" style="grid-column: 3 / 4; justify-self: end; color: #16a34a;">+{{ $totalReceived }} DH</span>
            </div>
        </div>
    </div>

    {{-- Table 2: Traitements annulés --}}
    @if($cancelledTreatments->isNotEmpty())
        <h2 class="mb-1 text-sm font-semibold app-title">{{ __('Traitements annulés — remboursements') }}</h2>
        <div class="mb-6 overflow-hidden rounded-xl border bg-white/80"
            style="border-color: color-mix(in srgb, #dc2626 35%, white);">
            <div class="grid border-b px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wide app-text-gray"
                style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                <div class="whitespace-nowrap">{{ __('Patient') }}</div>
                <div class="whitespace-nowrap">{{ __('Traitement annulé') }}</div>
                <div class="whitespace-nowrap text-right">{{ __('Remboursement (DH)') }}</div>
            </div>
            <div class="px-2 py-0">
                @foreach($cancelledTreatments as $cancellation)
                    @php
                        $refund = is_array($cancellation) ? $cancellation['refund_amount'] : (float) $cancellation->refund_amount;
                        $patientId = is_array($cancellation) ? $cancellation['patient_id'] : $cancellation->patient_id;
                        $treatmentId = is_array($cancellation) ? $cancellation['treatment_id'] : $cancellation->treatment_id;
                        $patientName = is_array($cancellation) ? $cancellation['patient_name'] : $cancellation->patient_name;
                        $treatmentDesc = is_array($cancellation) ? $cancellation['treatment_description'] : $cancellation->treatment_description;
                    @endphp
                    @if($patientId)
                        <a href="{{ route('treatments.index', ['patient' => $patientId, 'treatment' => $treatmentId]) }}"
                            class="grid border-b px-1 py-0.5 leading-tight transition-colors hover:rounded hover:bg-[color:color-mix(in_srgb,#dc2626_12%,white)]"
                            style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);"
                            title="{{ __('Voir les traitements') }}">
                            <span class="whitespace-nowrap text-xs font-medium app-title">{{ $patientName }}</span>
                            <span class="whitespace-nowrap text-xs" style="color: #991b1b;">{{ $treatmentDesc }}</span>
                            <span class="whitespace-nowrap text-right text-xs font-semibold" style="color: #dc2626;">-{{ number_format($refund, 2, '.', '') }}</span>
                        </a>
                    @else
                        <div class="grid border-b px-1 py-0.5 leading-tight"
                            style="grid-template-columns: 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                            <span class="whitespace-nowrap text-xs font-medium app-title">{{ $patientName }}</span>
                            <span class="whitespace-nowrap text-xs" style="color: #991b1b;">{{ $treatmentDesc }}</span>
                            <span class="whitespace-nowrap text-right text-xs font-semibold" style="color: #dc2626;">-{{ number_format($refund, 2, '.', '') }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="border-t px-2 py-2"
                style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                <div class="grid items-center" style="grid-template-columns: 1.2fr 1fr 0.8fr;">
                    <span class="col-start-1 col-end-3 text-xs font-semibold app-text-gray">{{ __('Total remboursé traitements:') }}</span>
                    <span class="whitespace-nowrap text-sm font-semibold" style="grid-column: 3 / 4; justify-self: end; color: #dc2626;">-{{ $totalCancelledTreatments }} DH</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Table 3: Sessions annulées --}}
    @if($cancelledSessions->isNotEmpty())
        <h2 class="mb-1 text-sm font-semibold app-title">{{ __('Séances annulées — remboursements') }}</h2>
        <div class="mb-6 overflow-hidden rounded-xl border bg-white/80"
            style="border-color: color-mix(in srgb, #dc2626 35%, white);">
            <div class="grid border-b px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wide app-text-gray"
                style="grid-template-columns: 1.2fr 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                <div class="whitespace-nowrap">{{ __('Patient') }}</div>
                <div class="whitespace-nowrap">{{ __('Traitement') }}</div>
                <div class="whitespace-nowrap">{{ __('Séance annulée') }}</div>
                <div class="whitespace-nowrap text-right">{{ __('Remboursement (DH)') }}</div>
            </div>
            <div class="px-2 py-0">
                @foreach($cancelledSessions as $session)
                    @php
                        $sessionPatientId = $session->patient_id;
                        $sessionTreatmentId = $session->treatment_info_id;
                        $sessionRefund = (float) $session->refund_amount;
                        $sessionTime = Carbon\Carbon::parse($session->cancelled_at)->format('H:i');
                        $sessionPatientName = trim($session->first_name . ' ' . $session->last_name);
                        if ($sessionPatientName === '') {
                            $sessionPatientName = '#' . $sessionPatientId;
                        }
                    @endphp
                    @if($sessionPatientId)
                        <a href="{{ route('treatments.index', ['patient' => $sessionPatientId, 'treatment' => $sessionTreatmentId]) }}"
                            class="grid border-b px-1 py-0.5 leading-tight transition-colors hover:rounded hover:bg-[color:color-mix(in_srgb,#dc2626_12%,white)]"
                            style="grid-template-columns: 1.2fr 1.2fr 1fr 0.8fr; border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);"
                            title="{{ __('Voir les traitements') }}">
                            <span class="whitespace-nowrap text-xs font-medium app-title">{{ $sessionPatientName }}</span>
                            <span class="whitespace-nowrap text-xs" style="color: #991b1b;">{{ $session->treatment_description }}</span>
                            <span class="whitespace-nowrap text-xs" style="color: #991b1b;">{{ $sessionTime }}</span>
                            <span class="whitespace-nowrap text-right text-xs font-semibold" style="color: #dc2626;">-{{ number_format($sessionRefund, 2, '.', '') }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
            <div class="border-t px-2 py-2"
                style="border-color: color-mix(in srgb, var(--color-raw-gray-stroke) 35%, white);">
                <div class="grid items-center" style="grid-template-columns: 1.2fr 1.2fr 1fr 0.8fr;">
                    <span class="col-start-1 col-end-4 text-xs font-semibold app-text-gray">{{ __('Total remboursé séances:') }}</span>
                    <span class="whitespace-nowrap text-sm font-semibold" style="grid-column: 4 / 5; justify-self: end; color: #dc2626;">-{{ $totalCancelledSessions }} DH</span>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-4 rounded-xl border px-4 py-3"
        style="border-color: color-mix(in srgb, var(--color-raw-primary-blue) 45%, white); background-color: color-mix(in srgb, var(--color-raw-primary-blue) 8%, white);">
        <div class="flex items-center justify-between">
            <span class="text-sm font-bold app-title">{{ __('Net à remettre au médecin:') }}</span>
            <span class="text-lg font-bold" style="color: {{ $netTotal >= 0 ? '#16a34a' : '#dc2626' }};">{{ $netTotal }} DH</span>
        </div>
    </div>
</div>
