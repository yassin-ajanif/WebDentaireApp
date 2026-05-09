<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    <style>.qp-drop{display:none;position:absolute;z-index:9999;margin-top:2px;width:100%;border-radius:6px;border:1px solid #d1d5db;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.12);max-height:200px;overflow-y:auto}.qp-drop.show{display:block}.qp-item{cursor:pointer;padding:6px 10px;font-size:13px;color:#374151}.qp-item:hover,.qp-item.highlighted{background:#eff6ff;color:#1e3a8a}.qp-item small{color:#9ca3af;margin-left:6px}</style>
    <script>
        window.__patientsList = @json($allPatients);
        function qpItems(input){var q=(input.value||'').toLowerCase().trim();if(!q)return[];return(window.__patientsList||[]).filter(function(p){return p.first_name.toLowerCase().includes(q)||(p.last_name||'').toLowerCase().includes(q)||p.telephone.includes(q)})}
        function qpRender(input){var d=document.getElementById('qp-dropdown');if(!d)return;var items=qpItems(input);if(!items.length){d.classList.remove('show');d._idx=-1;return;}d.innerHTML='';d._idx=-1;items.forEach(function(p,i){var el=document.createElement('div');el.className='qp-item';el.innerHTML=(p.first_name+' '+(p.last_name||'')).trim()+' <small>'+p.telephone+'</small>';el.dataset.idx=i;el.addEventListener('mousedown',function(e){e.preventDefault();qpPick(input,p)});d.appendChild(el)});d.classList.add('show')}
        function qpPick(input,p){input.value=(p.first_name+' '+(p.last_name||'')).trim();input.dispatchEvent(new Event('input',{bubbles:true}));var tel=document.querySelector('[wire\\:model="newTelephone"]');if(tel){tel.value=p.telephone;tel.dispatchEvent(new Event('input',{bubbles:true}))}qpClose()}
        function qpClose(){var d=document.getElementById('qp-dropdown');if(d)d.classList.remove('show')}
        function qpKey(ev,input){var d=document.getElementById('qp-dropdown');if(!d)return;if(ev.key==='ArrowDown'){ev.preventDefault();qpNav(d,1)}else if(ev.key==='ArrowUp'){ev.preventDefault();qpNav(d,-1)}else if(ev.key==='Enter'&&d.classList.contains('show')){ev.preventDefault();qpSel(input,d)}else if(ev.key==='Escape'){d.classList.remove('show')}}
        function qpNav(d,dir){var items=d.querySelectorAll('.qp-item');if(!items.length)return;items.forEach(function(el){el.classList.remove('highlighted')});if(d._idx==null)d._idx=-1;d._idx=(d._idx+dir+items.length)%items.length;items[d._idx].classList.add('highlighted');items[d._idx].scrollIntoView({block:'nearest'})}
        function qpSel(input,d){var items=d.querySelectorAll('.qp-item');if(d._idx>=0&&items[d._idx]){var a=qpItems(input);var idx=parseInt(items[d._idx].dataset.idx);if(a[idx])qpPick(input,a[idx])}}
        function qpBlur(){setTimeout(qpClose,200)}
    </script>
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
                @if($dialogError)
                    <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900" role="alert">
                        {{ $dialogError }}
                    </div>
                @endif
                <form wire:submit="saveNewDialog" class="space-y-3">
                    <div class="grid grid-cols-[90px_1fr] items-start gap-2">
                        <label class="app-text-gray text-sm pt-1.5">{{ __('Nom') }}</label>
                        <div class="relative">
                            <input type="text" wire:model="newName" id="qp-name-input"
                                   oninput="qpRender(this)" onkeydown="qpKey(event,this)" onblur="qpBlur()"
                                   class="app-input w-full px-2 py-1.5 text-sm" autocomplete="off">
                            @error('newName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <div id="qp-dropdown" class="qp-drop"></div>
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
                    <h2 id="existing-patient-confirm-title" class="app-title mb-2 text-base font-semibold">{{ $existingPatientIsTrashed ? __('Restore patient') : __('Existing patient') }}</h2>
                    <p class="app-text-gray mb-4 text-sm leading-relaxed">
                        @if($existingPatientIsTrashed)
                            {{ __('This patient (:name) is in the archive. Restore and add to queue?', ['name' => $existingPatientDisplayName]) }}
                        @else
                            {{ __('This phone number belongs to :name. Do you want to add :name to the queue?', ['name' => $existingPatientDisplayName]) }}
                        @endif
                    </p>
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
