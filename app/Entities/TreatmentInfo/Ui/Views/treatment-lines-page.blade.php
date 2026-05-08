<div class="pl-0 pr-3 sm:pr-4">
    <div class="mb-6">
        <h1 class="app-title text-2xl font-semibold">{{ __('Treatments') }}</h1>
        @if($patientModel)
            <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
                <p class="app-subtitle text-lg">{{ $patientModel->first_name }} {{ $patientModel->last_name }} — {{ $patientModel->telephone }}</p>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <span class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-semibold" style="background-color: color-mix(in srgb, #22c55e 22%, white); border-color: color-mix(in srgb, #22c55e 45%, white); color: #166534;">
                        {{ __('Total payé: :amount DH', ['amount' => $totalPaidAmount]) }}
                    </span>
                    <span class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-semibold" style="background-color: color-mix(in srgb, #f59e0b 26%, white); border-color: color-mix(in srgb, #f59e0b 45%, white); color: #b45309;">
                        {{ __('Reste à payer: :amount DH', ['amount' => $totalRemainingAmount]) }}
                    </span>
                </div>
            </div>
        @endif
        <p class="app-text-muted mt-1 text-sm">{{ __('Treatments count: :count', ['count' => $treatmentsCount]) }}</p>
        <a href="{{ route('patients.index') }}" class="app-title mt-2 inline-block text-sm hover:underline">{{ __('Back to patients') }}</a>
        @if($showFinishAppointmentButton)
            <div class="mt-3">
                <button type="button" wire:click="finishAppointment" class="app-btn-primary px-4 py-2 text-sm font-medium">
                    {{ __('Terminer la consultation') }}
                </button>
            </div>
        @endif
    </div>

    <div class="mb-4">
        <button type="button" wire:click="openTreatmentForm" class="app-btn-primary px-4 py-2 text-sm font-medium">
            {{ __('Add new treatment') }}
        </button>
    </div>

    @if($showTreatmentForm)
        <div class="fixed inset-0 z-40 bg-black/25" aria-hidden="true"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="app-card w-full max-w-xl bg-white p-5 shadow-xl">
                <h2 class="app-title mb-4 text-lg font-medium">{{ $editingTreatmentId ? __('Edit treatment') : __('Add treatment') }}</h2>
                <form wire:submit="saveTreatment" class="space-y-4">
                    <div>
                        <label class="app-text-gray block text-sm font-medium">{{ __('Treatment type / description') }}</label>
                        <input type="text" wire:model="treatmentDescription" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                        @error('treatmentDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-text-gray block text-sm font-medium">{{ __('Global price') }}</label>
                        <input type="text" wire:model="globalPrice" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                        @error('globalPrice') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if($editingTreatmentId !== null)
                        <div>
                            <label class="app-text-gray block text-sm font-medium">{{ __('Correction reason') }}</label>
                            <textarea wire:model="correctionReason" rows="3" class="app-input mt-1 block w-full px-3 py-2 text-sm"></textarea>
                            @error('correctionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="flex items-end gap-2">
                        <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save treatment') }}</button>
                        <button type="button" wire:click="cancelTreatmentEdit" class="app-btn-secondary px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        @forelse ($treatments as $treatment)
            @php
                $totalPaid = number_format((float) $treatment->sessions->sum(fn($session) => (float) $session->received_payment), 2, '.', '');
                $remaining = number_format((float) $treatment->remaining_amount, 2, '.', '');
                $hasRemaining = (float) $remaining > 0;
                $isExpanded = (bool) ($expandedTreatments[$treatment->id] ?? false);
                $status = (float) $remaining <= 0 ? __('Paid') : ((float) $totalPaid > 0 ? __('Partially paid') : __('Non paid'));
                $statusStyle = (float) $remaining <= 0
                    ? 'background-color: color-mix(in srgb, #22c55e 22%, white); color: #166534;'
                    : ((float) $totalPaid > 0
                        ? 'background-color: color-mix(in srgb, #3b82f6 20%, white); color: #1d4ed8;'
                        : 'background-color: color-mix(in srgb, #f59e0b 26%, white); color: #b45309;');
                $form = $sessionForms[$treatment->id] ?? ['session_date' => now()->format('Y-m-d\TH:i'), 'received_payment' => '0.00', 'notes' => ''];
            @endphp
            <div class="app-card overflow-hidden shadow-sm" wire:key="treatment-{{ $treatment->id }}">
                <div class="border-b px-5 py-3" style="background-color: var(--color-action-primary); border-color: color-mix(in srgb, var(--color-action-primary) 82%, black);">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex items-center gap-2 overflow-x-auto">
                            <h2 class="shrink-0 text-lg font-semibold text-white">{{ $treatment->description }}</h2>
                            
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" style="background-color: color-mix(in srgb, white 16%, transparent); color: white;">
                            {{ __('Global: :value DH', ['value' => number_format((float) $treatment->global_price, 2, '.', '')]) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" style="background-color: color-mix(in srgb, white 16%, transparent); color: white;">
                            {{ __('Payé: :value DH', ['value' => $totalPaid]) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium {{ $hasRemaining ? 'border' : '' }}" style="background-color: color-mix(in srgb, white 16%, transparent); {{ $hasRemaining ? 'border-color: #fdba74;' : '' }} color: white;">
                            {{ __('Reste: :value DH', ['value' => $remaining]) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-semibold" style="{{ $statusStyle }}">
                            {{ $status }}
                        </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm whitespace-nowrap">
                            <button
                                type="button"
                                wire:click="toggleTreatmentExpanded({{ $treatment->id }})"
                                class="inline-flex items-center justify-center rounded-md border text-white hover:bg-white/15"
                                style="border-color: color-mix(in srgb, white 35%, transparent); width: 28px; height: 28px;"
                                title="{{ $isExpanded ? __('Reduce') : __('Expand') }}"
                                aria-label="{{ $isExpanded ? __('Reduce') : __('Expand') }}"
                            >
                                @if($isExpanded)
                                    <svg viewBox="0 0 20 20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                        <polyline points="4 13 10 7 16 13"></polyline>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 20 20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                        <polyline points="4 7 10 13 16 7"></polyline>
                                    </svg>
                                @endif
                            </button>
                            <button
                                type="button"
                                wire:click="startEditTreatment({{ $treatment->id }})"
                                class="inline-flex items-center justify-center rounded-md hover:bg-white/15"
                                style="width: 21px; height: 21px;"
                                title="{{ __('Modifier') }}"
                                aria-label="{{ __('Modifier') }}"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 21px; height: 21px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 1 1 2.97 2.97L8.25 18.04l-3.75.78.78-3.75L16.862 3.487z" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                wire:click="deleteTreatment({{ $treatment->id }})"
                                wire:confirm="{{ __('Supprimer ce traitement ?') }}"
                                class="inline-flex items-center justify-center rounded-md hover:bg-white/15"
                                style="width: 21px; height: 21px;"
                                title="{{ __('Supprimer') }}"
                                aria-label="{{ __('Supprimer') }}"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 21px; height: 21px;">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                    <path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                @if($isExpanded)
                    <div class="app-divider p-5 pt-3">
                        <div class="mb-3 flex items-center justify-center">
                            <button type="button" wire:click="openSessionForm({{ $treatment->id }})" class="app-btn-primary px-3 py-1.5 text-sm" title="{{ __('Ajouter une séance') }}">+</button>
                        </div>

                        @if($activeSessionFormTreatmentId === $treatment->id)
                            <form wire:submit.prevent="saveSession({{ $treatment->id }})" class="flex flex-wrap items-end gap-3">
                                <div class="min-w-[220px] flex-1">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Date') }}</label>
                                    <input type="datetime-local" wire:model="sessionForms.{{ $treatment->id }}.session_date" class="app-input mt-1 block w-full px-3 py-2 text-sm" value="{{ $form['session_date'] }}" />
                                </div>
                                <div class="min-w-[140px] w-[180px]">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Reçu') }}</label>
                                    <input type="text" wire:model="sessionForms.{{ $treatment->id }}.received_payment" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                                </div>
                                <div class="min-w-[260px] flex-[2]">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Natures des Opérations') }}</label>
                                    <input type="text" wire:model="sessionForms.{{ $treatment->id }}.notes" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                                </div>
                                @if($editingSessionId && $editingSessionTreatmentId === $treatment->id)
                                    <div class="min-w-[260px] flex-[2]">
                                        <label class="app-text-gray block text-sm font-medium">{{ __('Session correction reason') }}</label>
                                        <input type="text" wire:model="sessionCorrectionReason" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                                        @error('sessionCorrectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium" @disabled((float) $remaining <= 0)>{{ $editingSessionId && $editingSessionTreatmentId === $treatment->id ? __('Update session') : __('Add session payment') }}</button>
                                    <button type="button" wire:click="cancelSessionEdit({{ $treatment->id }})" class="app-btn-secondary px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="px-5 pb-5">
                        <div class="mt-2 overflow-x-auto">
                            <table class="app-divider min-w-full divide-y text-left text-sm">
                                <thead class="app-text-gray text-xs font-semibold uppercase">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Dates') }}</th>
                                        <th class="px-4 py-3">{{ __('Natures des Opérations') }}</th>
                                        <th class="px-4 py-3">{{ __('Reçu') }}</th>
                                        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="app-divider divide-y">
                                    @forelse ($treatment->sessions as $session)
                                        @php
                                            $sessionDateKey = $session->session_date?->format('Y-m-d');
                                            $createdDateKey = $session->created_at?->format('Y-m-d');
                                            $isHighlightedSession = $highlightSessionDate !== null
                                                && ($sessionDateKey === $highlightSessionDate || $createdDateKey === $highlightSessionDate);
                                        @endphp
                                        <tr wire:key="session-{{ $session->id }}"
                                            @if($isHighlightedSession)
                                                data-highlighted-session="true"
                                                style="background-color: color-mix(in srgb, #f59e0b 18%, white);"
                                            @endif>
                                            <td class="px-4 py-3">{{ $session->session_date?->format('n/j/Y g:i:s A') }}</td>
                                            <td class="px-4 py-3">{{ $session->notes }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $session->received_payment, 2, '.', '') }} DH</td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button" wire:click="startEditSession({{ $treatment->id }}, {{ $session->id }})" class="app-title hover:underline">{{ __('Edit') }}</button>
                                                <span class="app-text-muted">|</span>
                                                <button type="button" wire:click="deleteSession({{ $session->id }})" wire:confirm="{{ __('Delete this session payment?') }}" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="app-text-muted px-4 py-6 text-center">{{ __('No session payments yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            @php
                                $sessionCorrections = $treatment->sessions
                                    ->flatMap(fn($session) => $session->corrections)
                                    ->sortByDesc(fn($correction) => $correction->created_at?->timestamp ?? 0)
                                    ->values();
                                $hasAnyCorrections = $treatment->corrections->isNotEmpty() || $sessionCorrections->isNotEmpty();
                                $isHistoryExpanded = (bool) ($expandedHistorySections[$treatment->id] ?? true);
                            @endphp
                            @if($hasAnyCorrections)
                                <div class="rounded-lg border px-3 py-3"
                                    style="border-color: color-mix(in srgb, #dc2626 60%, white); background-color: color-mix(in srgb, #dc2626 6%, white);">
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="text-sm font-semibold app-title">{{ __('Treatment correction history') }}</div>
                                        <button
                                            type="button"
                                            wire:click="toggleHistorySection({{ $treatment->id }})"
                                            class="inline-flex items-center justify-center rounded-md border text-[var(--color-raw-primary-blue)] hover:bg-white/80"
                                            style="width: 26px; height: 26px; border-color: color-mix(in srgb, var(--color-raw-primary-blue) 35%, white);">
                                            @if($isHistoryExpanded)
                                                <svg viewBox="0 0 20 20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                                    <polyline points="4 13 10 7 16 13"></polyline>
                                                </svg>
                                            @else
                                                <svg viewBox="0 0 20 20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                                    <polyline points="4 7 10 13 16 7"></polyline>
                                                </svg>
                                            @endif
                                        </button>
                                    </div>

                                    @if($isHistoryExpanded)
                                        @if($treatment->corrections->isNotEmpty())
                                            <table class="app-divider min-w-full divide-y text-left text-sm">
                                                <thead class="app-text-gray text-xs font-semibold uppercase">
                                                    <tr>
                                                        <th class="px-4 py-3">{{ __('Date') }}</th>
                                                        <th class="px-4 py-3">{{ __('Old price') }}</th>
                                                        <th class="px-4 py-3">{{ __('New price') }}</th>
                                                        <th class="px-4 py-3">{{ __('Old description') }}</th>
                                                        <th class="px-4 py-3">{{ __('New description') }}</th>
                                                        <th class="px-4 py-3">{{ __('Reason') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="app-divider divide-y">
                                                    @foreach ($treatment->corrections as $correction)
                                                        <tr wire:key="correction-{{ $correction->id }}">
                                                            <td class="px-4 py-3">{{ $correction->created_at?->format('n/j/Y g:i A') }}</td>
                                                            <td class="px-4 py-3">{{ number_format((float) $correction->old_global_price, 2, '.', '') }} DH</td>
                                                            <td class="px-4 py-3">{{ number_format((float) $correction->new_global_price, 2, '.', '') }} DH</td>
                                                            <td class="px-4 py-3">{{ $correction->old_description }}</td>
                                                            <td class="px-4 py-3">{{ $correction->new_description }}</td>
                                                            <td class="px-4 py-3">{{ $correction->reason }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                        @if($sessionCorrections->isNotEmpty())
                                            <div class="mt-4 overflow-x-auto">
                                                <div class="mb-2 text-sm font-semibold app-title">{{ __('Session correction history') }}</div>
                                                <table class="app-divider min-w-full divide-y text-left text-sm">
                                                    <thead class="app-text-gray text-xs font-semibold uppercase">
                                                        <tr>
                                                            <th class="px-4 py-3">{{ __('Date') }}</th>
                                                            <th class="px-4 py-3">{{ __('Session') }}</th>
                                                            <th class="px-4 py-3">{{ __('Old received') }}</th>
                                                            <th class="px-4 py-3">{{ __('New received') }}</th>
                                                            <th class="px-4 py-3">{{ __('Old note') }}</th>
                                                            <th class="px-4 py-3">{{ __('New note') }}</th>
                                                            <th class="px-4 py-3">{{ __('Reason') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="app-divider divide-y">
                                                        @foreach ($sessionCorrections as $correction)
                                                            <tr wire:key="session-correction-{{ $correction->id }}">
                                                                <td class="px-4 py-3">{{ $correction->created_at?->format('n/j/Y g:i A') }}</td>
                                                                <td class="px-4 py-3">{{ $correction->new_session_date?->format('n/j/Y g:i A') }}</td>
                                                                <td class="px-4 py-3">{{ number_format((float) $correction->old_received_payment, 2, '.', '') }} DH</td>
                                                                <td class="px-4 py-3">{{ number_format((float) $correction->new_received_payment, 2, '.', '') }} DH</td>
                                                                <td class="px-4 py-3">{{ $correction->old_notes ?: '—' }}</td>
                                                                <td class="px-4 py-3">{{ $correction->new_notes ?: '—' }}</td>
                                                                <td class="px-4 py-3">{{ $correction->reason }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="app-card p-6 text-center app-text-muted">{{ __('No treatments yet.') }}</div>
        @endforelse
    </div>
</div>
