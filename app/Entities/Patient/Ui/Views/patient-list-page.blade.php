<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="app-title text-2xl font-semibold">{{ __('Patients') }}</h1>
            <p class="app-subtitle mt-1 text-sm">{{ __('Search, open dossier, or add a patient.') }}</p>
        </div>
        <a href="{{ route('patients.create') }}" class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium shadow-sm">
            {{ __('New patient') }}
        </a>
    </div>

    <div class="mb-4">
        <label class="sr-only" for="search">{{ __('Search') }}</label>
        <input wire:model.live.debounce.300ms="search" id="search" type="search" placeholder="{{ __('Name or telephone…') }}"
               class="app-input block w-full max-w-md px-3 py-2 text-sm shadow-sm" />
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="setPaymentFilter('all')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $paymentFilter === 'all' ? 'app-btn-primary border-transparent' : 'app-btn-secondary' }}"
            >
                {{ __('Tous') }}
            </button>
            <button
                type="button"
                wire:click="setPaymentFilter('paid')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $paymentFilter === 'paid' ? 'border-transparent text-white' : '' }}"
                style="{{ $paymentFilter === 'paid' ? 'background-color: var(--color-accent-success);' : '' }}"
            >
                {{ __('Payés') }}
            </button>
            <button
                type="button"
                wire:click="setPaymentFilter('unpaid')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $paymentFilter === 'unpaid' ? 'border-transparent text-white' : '' }}"
                style="{{ $paymentFilter === 'unpaid' ? 'background-color: #f59e0b;' : '' }}"
            >
                {{ __('Impayés') }}
            </button>
        </div>
    </div>

    <div class="app-card overflow-x-auto shadow-sm">
        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Telephone') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($rows as $patient)
                    <tr wire:key="patient-{{ $patient->id }}" class="hover:bg-white/40">
                        <td class="app-title px-4 py-3 font-medium">{{ $patient->first_name }} {{ $patient->last_name }}</td>
                        <td class="app-text-gray px-4 py-3">{{ $patient->telephone }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('patients.edit', $patient) }}" class="app-title hover:underline">{{ __('Edit') }}</a>
                            <span class="app-text-muted">|</span>
                            <a href="{{ route('treatments.index', $patient) }}" class="app-title hover:underline">{{ __('Treatments') }}</a>
                            <span class="app-text-muted">|</span>
                            <button type="button" wire:click="delete({{ $patient->id }})" wire:confirm="{{ __('Delete this patient?') }}"
                                    class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="app-text-muted px-4 py-8 text-center">{{ __('No patients found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
</div>
