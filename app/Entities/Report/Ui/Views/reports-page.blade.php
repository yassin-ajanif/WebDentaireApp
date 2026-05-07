<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    <div class="mb-6">
        <h1 class="app-title text-2xl font-semibold">{{ __('Reports') }}</h1>
        <p class="app-subtitle mt-1 text-sm">{{ __('Revenue and patient credits') }}</p>
    </div>

    <div class="app-card mb-6 p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="setRange('today')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $activePreset === 'today' ? 'app-btn-primary border-transparent' : 'app-btn-secondary' }}">
                {{ __('Today') }}
            </button>
            <button type="button" wire:click="setRange('last7')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $activePreset === 'last7' ? 'app-btn-primary border-transparent' : 'app-btn-secondary' }}">
                {{ __('Last 7 days') }}
            </button>
            <button type="button" wire:click="setRange('month')"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium {{ $activePreset === 'month' ? 'app-btn-primary border-transparent' : 'app-btn-secondary' }}">
                {{ __('This month') }}
            </button>
        </div>
        <div class="mt-4 flex flex-wrap items-end gap-3">
            <div>
                <label for="fromDate" class="mb-1 block text-xs app-text-gray">{{ __('From') }}</label>
                <input id="fromDate" type="date" wire:model.live="fromDate" class="app-input px-3 py-2 text-sm shadow-sm" />
            </div>
            <div>
                <label for="toDate" class="mb-1 block text-xs app-text-gray">{{ __('To') }}</label>
                <input id="toDate" type="date" wire:model.live="toDate" class="app-input px-3 py-2 text-sm shadow-sm" />
            </div>
        </div>
        <div class="mt-3 app-text-gray text-sm">
            {{ __('Period') }}: <span class="app-title font-medium">{{ $rangeLabel }}</span>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="app-card p-4 shadow-sm">
            <p class="app-subtitle text-xs uppercase">{{ __('Total Revenue (Cash)') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color:#16a34a;">{{ $totalRevenue }} DH</p>
        </div>
        <div class="app-card p-4 shadow-sm">
            <p class="app-subtitle text-xs uppercase">{{ __('Credits Total') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color:#dc2626;">{{ $totalCredits }} DH</p>
        </div>
    </div>

    <div class="app-card mb-6 overflow-x-auto shadow-sm">
        <div class="app-divider border-b px-4 py-3">
            <h2 class="app-title text-base font-semibold">{{ __('Revenue Details') }}</h2>
        </div>
        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Received (DH)') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($revenueRows as $row)
                    <tr class="hover:bg-white/40">
                        <td class="px-4 py-2.5 app-text-gray">{{ $row['label'] }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold" style="color:#16a34a;">{{ $row['received_total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="app-text-muted px-4 py-6 text-center">{{ __('No revenue in this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="app-card overflow-x-auto shadow-sm">
        <div class="app-divider border-b px-4 py-3">
            <h2 class="app-title text-base font-semibold">{{ __('Patient Credits') }}</h2>
        </div>
        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Patient') }}</th>
                    <th class="px-4 py-3">{{ __('Telephone') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Total plan (DH)') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Paid (DH)') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Credit (DH)') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($credits as $row)
                    <tr class="hover:bg-[color:color-mix(in_srgb,var(--color-raw-primary-blue)_12%,white)]">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('treatments.index', ['patient' => $row['patient_id']]) }}" class="app-title hover:underline">
                                {{ $row['name'] }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 app-text-gray">{{ $row['telephone'] }}</td>
                        <td class="px-4 py-2.5 text-right app-text-gray">{{ $row['total_plan'] }}</td>
                        <td class="px-4 py-2.5 text-right app-text-gray">{{ $row['paid'] }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold" style="color:#dc2626;">{{ $row['credit'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="app-text-muted px-4 py-6 text-center">{{ __('No patient credits.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
