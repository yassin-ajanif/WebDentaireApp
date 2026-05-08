<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    @php
        $queuePositionById = $items->values()->mapWithKeys(fn ($a, $i) => [$a->id => $i + 1]);
        $done = $items->where('status', \App\Entities\Appointment\Enums\AppointmentStatus::Done)->values();
        $waiting = $items->whereIn('status', [
            \App\Entities\Appointment\Enums\AppointmentStatus::Waiting,
            \App\Entities\Appointment\Enums\AppointmentStatus::InProgress,
        ])->values();
        $cancelled = $items->where('status', \App\Entities\Appointment\Enums\AppointmentStatus::Cancelled)->values();
        $avgDuration = max(1, (int) (($estimateMinutes ?? 0) / max(1, $waiting->count())));
        $firstStartedAt = optional($waiting->whereNotNull('started_at')->sortBy('started_at')->first())->started_at;
        $slotsById = [];
        if ($firstStartedAt !== null) {
            foreach ($waiting->values() as $index => $slotAppointment) {
                $slotStart = $firstStartedAt->copy()->addMinutes($index * $avgDuration);
                $slotsById[$slotAppointment->id] = [
                    'from' => $slotStart,
                    'to' => $slotStart->copy()->addMinutes($avgDuration),
                ];
            }
        }
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="app-title text-2xl font-semibold">{{ __('Liste des patients') }}</h1>
            <p class="app-subtitle mt-1 text-sm">{{ __('Estimation restante: :minutes min', ['minutes' => $estimateMinutes ?? 0]) }}</p>
        </div>
        <button type="button" wire:click="openNewDialog" class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium shadow-sm">
            {{ __('Nouveau') }}
        </button>
    </div>

    @if($showNewDialog)
        <div class="fixed inset-0 z-40 bg-black/20"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="app-card w-full max-w-md bg-white/95 p-4 shadow-xl">
                <div class="app-divider mb-3 border-b pb-2 text-sm font-semibold app-title">{{ __('Nouveau numéro') }}</div>
                <form wire:submit="saveNewDialog" class="space-y-3">
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="app-text-gray text-sm">{{ __('Nom') }}</label>
                        <div>
                            <input type="text" wire:model="newName" class="app-input w-full px-2 py-1.5 text-sm">
                            @error('newName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="app-text-gray text-sm">{{ __('Âge') }}</label>
                        <div>
                            <input type="number" wire:model="newAge" class="app-input w-full px-2 py-1.5 text-sm">
                            @error('newAge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="app-text-gray text-sm">{{ __('Adresse') }}</label>
                        <div>
                            <input type="text" wire:model="newAddress" class="app-input w-full px-2 py-1.5 text-sm">
                            @error('newAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="app-text-gray text-sm">{{ __('Téléphone') }}</label>
                        <div>
                            <input type="text" wire:model="newTelephone" placeholder="{{ __('Ex. 0612345678') }}" class="app-input w-full px-2 py-1.5 text-sm">
                            @error('newTelephone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="closeNewDialog" class="app-btn-secondary px-3 py-1.5 text-sm">{{ __('Annuler') }}</button>
                        <button type="submit" class="app-btn-primary px-3 py-1.5 text-sm">{{ __('Enregistrer') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @if($showExistingPatientConfirm)
            <div class="fixed inset-0 z-[60] bg-black/30" aria-hidden="true"></div>
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="existing-patient-confirm-title">
                <div class="app-card w-full max-w-md bg-white/95 p-4 shadow-xl">
                    <h2 id="existing-patient-confirm-title" class="app-title mb-2 text-base font-semibold">{{ __('Existing patient') }}</h2>
                    <p class="app-text-gray mb-4 text-sm leading-relaxed">{{ __('This phone number belongs to :name. Do you want to add :name to the queue?', ['name' => $existingPatientDisplayName]) }}</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancelExistingPatientConfirm" class="app-btn-secondary px-3 py-1.5 text-sm">{{ __('Back') }}</button>
                        <button type="button" wire:click="confirmAddExistingPatientToQueue" class="app-btn-primary px-3 py-1.5 text-sm">{{ __('Add to queue') }}</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($done as $appt)
                <div wire:key="done-{{ $appt->id }}"
                     class="app-card min-w-0 p-4 {{ $appt->patient_id ? 'cursor-pointer' : '' }}"
                     @if($appt->patient_id) ondblclick="window.location.href='{{ route('treatments.index', ['patient' => $appt->patient_id]) }}'" @endif>
                    <div class="mb-1 flex items-center justify-between">
                        <span class="app-text-gray inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-sm font-semibold" style="background-color: var(--color-raw-neutral-gray);">{{ $queuePositionById[$appt->id] }}</span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background-color: color-mix(in srgb, var(--color-raw-gray-stroke) 45%, white); color: var(--color-text-gray);">{{ __('Terminé') }}</span>
                    </div>
                    <div class="app-title min-w-0 truncate text-xl font-semibold tracking-tight sm:text-2xl" title="{{ $appt->queueDisplayName() }}">{{ $appt->queueDisplayName() }}</div>
                    @include('appointment::queue-inline-actions', ['appointment' => $appt, 'variant' => 'light'])
                </div>
            @empty
                <div class="app-text-muted py-4 text-sm">{{ __('Aucun patient terminé.') }}</div>
            @endforelse
        </div>

        <div class="flex gap-4">
            <div class="w-2 rounded-full" style="background-color: var(--color-accent-success);"></div>
            <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($waiting as $appt)
                    @php
                        $slot = $slotsById[$appt->id] ?? null;
                        $from = $slot['from'] ?? null;
                        $to = $slot['to'] ?? null;
                        $statusLabel = $appt->status === \App\Entities\Appointment\Enums\AppointmentStatus::InProgress ? __('En cours') : __('En attente');
                        $isInProgress = $appt->status === \App\Entities\Appointment\Enums\AppointmentStatus::InProgress;
                        $timeLabel = $from !== null && $to !== null
                            ? $from->format('H:i').' - '.$to->format('H:i')
                            : '────────';
                    @endphp
                    @if($isInProgress)
                        <div wire:key="active-{{ $appt->id }}"
                             class="app-queue-card-en-cours min-w-0 p-4 {{ $appt->patient_id ? 'cursor-pointer' : '' }}"
                             @if($appt->patient_id) ondblclick="window.location.href='{{ route('treatments.index', ['patient' => $appt->patient_id]) }}'" @endif>
                            <div class="flex items-center gap-4">
                                <span class="app-queue-card-en-cours-num">{{ $queuePositionById[$appt->id] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="app-queue-card-en-cours-badge">{{ $statusLabel }}</div>
                                    <div class="app-queue-card-en-cours-name" title="{{ $appt->queueDisplayName() }}">{{ $appt->queueDisplayName() }}</div>
                                </div>
                                <div class="app-queue-card-en-cours-time">{{ $timeLabel }}</div>
                            </div>
                            @include('appointment::queue-inline-actions', ['appointment' => $appt, 'variant' => 'dark'])
                        </div>
                    @else
                        <div wire:key="active-{{ $appt->id }}"
                             class="app-card min-w-0 p-4 {{ $appt->patient_id ? 'cursor-pointer' : '' }}"
                             @if($appt->patient_id) ondblclick="window.location.href='{{ route('treatments.index', ['patient' => $appt->patient_id]) }}'" @endif>
                            <div class="mb-1 flex items-center gap-3">
                                <span class="app-text-gray inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-sm font-semibold" style="background-color: var(--color-raw-neutral-gray);">{{ $queuePositionById[$appt->id] }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background-color: color-mix(in srgb, #f3c26f 35%, white); color: #9a620d;">{{ $statusLabel }}</span>
                            </div>
                            <div class="flex min-w-0 items-center justify-between gap-3">
                                <div class="app-title min-w-0 flex-1 truncate text-xl font-semibold tracking-tight sm:text-2xl" title="{{ $appt->queueDisplayName() }}">{{ $appt->queueDisplayName() }}</div>
                                <div class="app-subtitle shrink-0 text-sm">{{ $timeLabel }}</div>
                            </div>
                            @include('appointment::queue-inline-actions', ['appointment' => $appt, 'variant' => 'light'])
                        </div>
                    @endif
                @empty
                    <div class="app-text-muted py-4 text-sm">{{ __('Aucun patient en attente.') }}</div>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($cancelled as $appt)
                <div wire:key="cancel-{{ $appt->id }}"
                     class="app-card min-w-0 p-4 {{ $appt->patient_id ? 'cursor-pointer' : '' }}"
                     @if($appt->patient_id) ondblclick="window.location.href='{{ route('treatments.index', ['patient' => $appt->patient_id]) }}'" @endif>
                    <div class="mb-1 flex items-center justify-between">
                        <span class="app-text-gray inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-sm font-semibold" style="background-color: var(--color-raw-neutral-gray);">{{ $queuePositionById[$appt->id] }}</span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background-color: color-mix(in srgb, #ef4444 18%, white); color: #dc2626;">{{ __('Annulé') }}</span>
                    </div>
                    <div class="app-title min-w-0 truncate text-xl font-semibold tracking-tight sm:text-2xl" title="{{ $appt->queueDisplayName() }}">{{ $appt->queueDisplayName() }}</div>
                    @include('appointment::queue-inline-actions', ['appointment' => $appt, 'variant' => 'light'])
                </div>
            @empty
                <div class="app-text-muted py-4 text-sm">{{ __('Aucun patient annulé.') }}</div>
            @endforelse
        </div>
    </div>
</div>
