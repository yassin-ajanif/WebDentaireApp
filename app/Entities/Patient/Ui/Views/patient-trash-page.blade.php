<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    <div class="mb-6">
        <h1 class="app-title text-2xl font-semibold">{{ __('Deleted patients') }}</h1>
        <p class="app-subtitle mt-1 text-sm">{{ __('Restore a previously deleted patient.') }}</p>
    </div>

    <div class="mb-4">
        <label class="sr-only" for="search">{{ __('Search') }}</label>
        <input wire:model.live.debounce.300ms="search" id="search" type="search" placeholder="{{ __('Name or telephone…') }}"
               class="app-input block w-full max-w-md px-3 py-2 text-sm shadow-sm" />
    </div>

    <div class="app-card overflow-x-auto shadow-sm">
        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Telephone') }}</th>
                    <th class="px-4 py-3">{{ __('Deleted') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($rows as $patient)
                    <tr wire:key="trashed-patient-{{ $patient->id }}" class="hover:bg-white/40">
                        <td class="px-4 py-3 font-medium text-gray-500 line-through">{{ $patient->first_name }} {{ $patient->last_name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $patient->telephone }}</td>
                        <td class="px-4 py-3 text-gray-400 text-sm">{{ $patient->deleted_at?->format('n/j/Y g:i A') }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="restore({{ $patient->id }})"
                                    class="app-title hover:underline font-medium">{{ __('Restore') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="app-text-muted px-4 py-8 text-center">{{ __('No deleted patients.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
</div>
