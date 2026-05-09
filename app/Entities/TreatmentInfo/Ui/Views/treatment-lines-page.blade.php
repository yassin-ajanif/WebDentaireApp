<div class="pl-0 pr-3 sm:pr-4">
    <style>.ac-dropdown{display:none;position:absolute;z-index:9999;margin-top:4px;width:100%;border-radius:6px;border:1px solid #d1d5db;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.12);max-height:200px;overflow-y:auto}.ac-dropdown.show{display:block}.ac-item{cursor:pointer;padding:6px 12px;font-size:14px;color:#374151}.ac-item:hover,.ac-item.highlighted{background:#eff6ff;color:#1e3a8a}</style>
    <script>
        window.__treatmentCatalog = @json($treatmentCatalog);
        function acItems(input) {
            var q = (input.value||'').toLowerCase().trim();
            if (!q) return [];
            return (window.__treatmentCatalog||[]).filter(function(i){return i.name.toLowerCase().includes(q)});
        }
        function acRender(input) {
            var d=document.getElementById('treatment-desc-dropdown'); if(!d)return;
            var items=acItems(input);
            if(!items.length){d.classList.remove('show');d._idx=-1;return;}
            d.innerHTML=''; d._idx=-1;
            items.forEach(function(it,i){
                var el=document.createElement('div'); el.className='ac-item'; el.textContent=it.name; el.dataset.idx=i;
                el.addEventListener('mousedown',function(e){e.preventDefault();acPick(input,it)});
                d.appendChild(el);
            });
            d.classList.add('show');
        }
        function acPick(input,item) {
            input.value=item.name; input.dispatchEvent(new Event('input',{bubbles:true}));
            var p=document.getElementById('global-price-input');
            if(p){p.value=item.price!=null?item.price:'';p.dispatchEvent(new Event('input',{bubbles:true}))}
            var d=document.getElementById('treatment-desc-dropdown'); if(d)d.classList.remove('show');
        }
        function acKey(ev,input) {
            var d=document.getElementById('treatment-desc-dropdown'); if(!d)return;
            if(ev.key==='ArrowDown'){ev.preventDefault();acNav(d,1)}
            else if(ev.key==='ArrowUp'){ev.preventDefault();acNav(d,-1)}
            else if(ev.key==='Enter'&&d.classList.contains('show')){ev.preventDefault();acSel(input,d)}
            else if(ev.key==='Escape'){d.classList.remove('show')}
        }
        function acNav(d,dir){
            var items=d.querySelectorAll('.ac-item'); if(!items.length)return;
            items.forEach(function(el){el.classList.remove('highlighted')});
            if(d._idx==null)d._idx=-1;
            d._idx=(d._idx+dir+items.length)%items.length;
            items[d._idx].classList.add('highlighted'); items[d._idx].scrollIntoView({block:'nearest'});
        }
        function acSel(input,d){
            var items=d.querySelectorAll('.ac-item');
            if(d._idx>=0&&items[d._idx]){
                var a=acItems(input); var idx=parseInt(items[d._idx].dataset.idx);
                if(a[idx])acPick(input,a[idx]);
            }
        }
        function acBlur(){setTimeout(function(){var d=document.getElementById('treatment-desc-dropdown');if(d)d.classList.remove('show')},180)}
        window.__allActivities=(window.__treatmentCatalog||[]).flatMap(function(c){return c.activities||[]}).filter(function(a,i,s){return s.indexOf(a)===i}).sort();
        function snItems(input){var q=(input.value||'').toLowerCase().trim();if(!q)return [];return (window.__allActivities||[]).filter(function(a){return a.toLowerCase().includes(q)})}
        function snRender(input){
            var did=input.id.replace(/(\w+)-(\d+)$/,'$1-dropdown-$2');var d=document.getElementById(did);if(!d)return;
            var items=snItems(input);if(!items.length){d.classList.remove('show');d._idx=-1;return;}
            d.innerHTML='';d._idx=-1;items.forEach(function(a,i){
                var el=document.createElement('div');el.className='ac-item';el.textContent=a;el.dataset.idx=i;
                el.addEventListener('mousedown',function(e){e.preventDefault();snPick(input,a)});d.appendChild(el)});
            d.classList.add('show');
        }
        function snPick(input,val){input.value=val;input.dispatchEvent(new Event('input',{bubbles:true}));snClose(input)}
        function snClose(input){var d=document.getElementById(input.id.replace(/(\w+)-(\d+)$/,'$1-dropdown-$2'));if(d)d.classList.remove('show')}
        function snKey(ev,input){
            var did=input.id.replace(/(\w+)-(\d+)$/,'$1-dropdown-$2');var d=document.getElementById(did);if(!d)return;
            if(ev.key==='ArrowDown'){ev.preventDefault();acNav(d,1)}
            else if(ev.key==='ArrowUp'){ev.preventDefault();acNav(d,-1)}
            else if(ev.key==='Enter'&&d.classList.contains('show')){ev.preventDefault();snSel(input,d)}
            else if(ev.key==='Escape'){d.classList.remove('show')}
        }
        function snSel(input,d){
            var items=d.querySelectorAll('.ac-item');
            if(d._idx>=0&&items[d._idx]){var a=snItems(input);var idx=parseInt(items[d._idx].dataset.idx);if(a[idx])snPick(input,a[idx])}
        }
        function snBlur(input){setTimeout(function(){snClose(input)},180)}
    </script>
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
                <button type="button" wire:click="finishAppointment"
                    class="app-btn-primary px-4 py-2 text-sm font-medium"
                    @if(!$hasSessions) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                    {{ __('Terminer la consultation') }}
                </button>
                @if(!$hasSessions)
                    <p class="mt-1 text-xs" style="color: #dc2626;">{{ __('Ajoutez au moins une séance avant de terminer.') }}</p>
                @endif
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
                    <div class="relative">
                        <label class="app-text-gray block text-sm font-medium">{{ __('Treatment type / description') }}</label>
                        <input type="text" wire:model="treatmentDescription" id="treatment-desc-input"
                               oninput="acRender(this)" onkeydown="acKey(event,this)" onblur="acBlur()"
                               class="app-input mt-1 block w-full px-3 py-2 text-sm"
                               autocomplete="off" />
                        @error('treatmentDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <div id="treatment-desc-dropdown" class="ac-dropdown"></div>
                    </div>
                    <div>
                        <label class="app-text-gray block text-sm font-medium">{{ __('Global price') }}</label>
                        <input type="text" wire:model="globalPrice" id="global-price-input" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
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
                $totalPaid = number_format((float) $treatment->sessions->filter(fn($s) => $s->status !== 'cancelled')->sum(fn($session) => (float) $session->received_payment), 2, '.', '');
                $remaining = number_format((float) $treatment->remaining_amount, 2, '.', '');
                $hasRemaining = (float) $remaining > 0;
                $isExpanded = (bool) ($expandedTreatments[$treatment->id] ?? false);
                $isCancelled = $treatment->status === \App\Entities\TreatmentInfo\Enums\TreatmentStatus::Cancelled;
                $status = match($treatment->status) {
                    \App\Entities\TreatmentInfo\Enums\TreatmentStatus::Paid => __('Paid'),
                    \App\Entities\TreatmentInfo\Enums\TreatmentStatus::Cancelled => __('Cancelled'),
                    default => (float) $totalPaid > 0 ? __('Partially paid') : __('Non paid'),
                };
                $statusStyle = match($treatment->status) {
                    \App\Entities\TreatmentInfo\Enums\TreatmentStatus::Paid => 'background-color: color-mix(in srgb, #22c55e 22%, white); color: #166534;',
                    \App\Entities\TreatmentInfo\Enums\TreatmentStatus::Cancelled => 'background-color: color-mix(in srgb, #ef4444 22%, white); color: #991b1b;',
                    default => (float) $totalPaid > 0
                        ? 'background-color: color-mix(in srgb, #3b82f6 20%, white); color: #1d4ed8;'
                        : 'background-color: color-mix(in srgb, #f59e0b 26%, white); color: #b45309;',
                };
                $form = $sessionForms[$treatment->id] ?? ['session_date' => now()->format('Y-m-d\TH:i'), 'received_payment' => '0.00', 'notes' => ''];
            @endphp
            <div class="app-card overflow-hidden shadow-sm {{ $isCancelled ? 'opacity-75' : '' }}" wire:key="treatment-{{ $treatment->id }}">
                <div class="border-b px-5 py-3" style="background-color: {{ $isCancelled ? '#6b7280' : 'var(--color-action-primary)' }}; border-color: color-mix(in srgb, {{ $isCancelled ? '#6b7280' : 'var(--color-action-primary)' }} 82%, black);">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex items-center gap-2 overflow-x-auto">
                            <h2 class="shrink-0 text-lg font-semibold text-white {{ $isCancelled ? 'line-through' : '' }}">{{ $treatment->description }}</h2>
                            
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" style="background-color: color-mix(in srgb, white 16%, transparent); color: white;">
                            {{ __('Global: :value DH', ['value' => number_format((float) $treatment->global_price, 2, '.', '')]) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" style="background-color: color-mix(in srgb, white 16%, transparent); color: white;">
                            {{ __('Payé: :value DH', ['value' => $totalPaid]) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium {{ $hasRemaining && !$isCancelled ? 'border' : '' }}" style="background-color: color-mix(in srgb, white 16%, transparent); {{ $hasRemaining && !$isCancelled ? 'border-color: #fdba74;' : '' }} color: white;">
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
                                @disabled($isCancelled)
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 21px; height: 21px; {{ $isCancelled ? 'opacity: 0.5;' : '' }}">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 1 1 2.97 2.97L8.25 18.04l-3.75.78.78-3.75L16.862 3.487z" />
                                </svg>
                            </button>
                            @if(!$isCancelled)
                            <button
                                type="button"
                                wire:click="cancelTreatment({{ $treatment->id }})"
                                wire:confirm="{{ __('This will cancel the treatment and refund all payments made on it. Continue?') }}"
                                class="inline-flex items-center justify-center rounded-md hover:bg-white/15"
                                style="width: 21px; height: 21px;"
                                title="{{ __('Annuler') }}"
                                aria-label="{{ __('Annuler') }}"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 21px; height: 21px;">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($isExpanded)
                    <div class="app-divider p-5 pt-3">
                        @if(!$isCancelled)
                        <div class="mb-3 flex items-center justify-center">
                            <button type="button" wire:click="openSessionForm({{ $treatment->id }})" class="app-btn-primary px-3 py-1.5 text-sm" title="{{ __('Ajouter une séance') }}" @if((float) $treatment->remaining_amount <= 0) disabled style="opacity:0.4;cursor:not-allowed" @endif>+</button>
                        </div>
                        @endif

                        @if($activeSessionFormTreatmentId === $treatment->id && !$isCancelled)
                            <form wire:submit.prevent="saveSession({{ $treatment->id }})" class="flex flex-wrap items-end gap-3">
                                <div class="min-w-[220px] flex-1">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Date') }}</label>
                                    <input type="datetime-local" wire:model="sessionForms.{{ $treatment->id }}.session_date" class="app-input mt-1 block w-full px-3 py-2 text-sm" value="{{ $form['session_date'] }}" />
                                </div>
                                <div class="min-w-[140px] w-[180px]">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Reçu') }}</label>
                                    <input type="text" wire:model="sessionForms.{{ $treatment->id }}.received_payment" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                                    @if(($amountExceedsRemaining[$treatment->id] ?? false))
                                        <p class="mt-1 text-xs" style="color: #dc2626;">{{ __('Le montant dépasse le reste à payer.') }}</p>
                                    @endif
                                </div>
                                <div class="min-w-[260px] flex-[2] relative">
                                    <label class="app-text-gray block text-sm font-medium">{{ __('Natures des Opérations') }}</label>
                                    <input type="text" wire:model="sessionForms.{{ $treatment->id }}.notes" id="session-notes-{{ $treatment->id }}"
                                           oninput="snRender(this)" onkeydown="snKey(event,this)" onblur="snBlur(this)"
                                           class="app-input mt-1 block w-full px-3 py-2 text-sm" autocomplete="off" />
                                    <div id="session-notes-dropdown-{{ $treatment->id }}" class="ac-dropdown"></div>
                                </div>
                                @if($editingSessionId && $editingSessionTreatmentId === $treatment->id)
                                    <div class="min-w-[260px] flex-[2]">
                                        <label class="app-text-gray block text-sm font-medium">{{ __('Session correction reason') }}</label>
                                        <input type="text" wire:model="sessionCorrectionReason" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                                        @error('sessionCorrectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ $editingSessionId && $editingSessionTreatmentId === $treatment->id ? __('Update session') : __('Add session payment') }}</button>
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
                                            @endif
                                            style="{{ $session->status === 'cancelled' ? 'opacity: 0.5; background-color: #e5e7eb; text-decoration: line-through;' : ($isHighlightedSession ? 'background-color: color-mix(in srgb, #f59e0b 18%, white);' : '') }}">
                                            <td class="px-4 py-3 {{ $session->status === 'cancelled' ? 'line-through' : '' }}">{{ $session->session_date?->format('n/j/Y g:i:s A') }}</td>
                                            <td class="px-4 py-3 {{ $session->status === 'cancelled' ? 'line-through' : '' }}">
                                                {{ $session->notes }}
                                                @if($session->status === 'cancelled')
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        {{ __('Cancelled') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 {{ $session->status === 'cancelled' ? 'line-through' : '' }}">{{ number_format((float) $session->received_payment, 2, '.', '') }} DH</td>
                                            <td class="px-4 py-3 text-right">
                                                @if($session->status !== 'cancelled' && !$isCancelled)
                                                    <button type="button" wire:click="startEditSession({{ $treatment->id }}, {{ $session->id }})" class="app-title hover:underline">{{ __('Edit') }}</button>
                                                    <span class="app-text-muted">|</span>
                                                    <button type="button" wire:click="cancelSession({{ $session->id }})" wire:confirm="{{ __('This will reverse this session payment. Continue?') }}" class="text-red-600 hover:underline">{{ __('Cancel') }}</button>
                                                @else
                                                    <span class="text-xs app-text-muted italic">{{ __('No actions available') }}</span>
                                                @endif
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
