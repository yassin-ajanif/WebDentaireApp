<div class="pb-8 pl-0 pr-3 pt-2 sm:pr-4">
    <h1 class="app-title mb-6 text-2xl font-semibold">{{ __('Treatment catalog') }}</h1>

    {{-- Treatments section --}}
    <div class="app-card mb-8 overflow-x-auto shadow-sm">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="app-title text-lg font-medium">{{ __('Treatments') }}</h2>
            <button type="button" wire:click="openTreatmentForm" class="app-btn-primary px-3 py-1.5 text-sm font-medium">
                {{ __('Add treatment') }}
            </button>
        </div>

        @if($showTreatmentForm)
            <div class="border-b px-4 py-3" style="background-color: #f9fafb;">
                <form wire:submit="saveTreatment" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="app-text-gray block text-xs font-medium">{{ __('Name') }}</label>
                        <input type="text" wire:model="treatmentName" class="app-input mt-1 block w-64 px-3 py-2 text-sm" />
                        @error('treatmentName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-text-gray block text-xs font-medium">{{ __('Price') }}</label>
                        <input type="text" wire:model="treatmentPrice" class="app-input mt-1 block w-32 px-3 py-2 text-sm" placeholder="0.00" />
                        @error('treatmentPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
                        <button type="button" wire:click="cancelTreatmentForm" class="app-btn-secondary px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        @endif

        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Price') }}</th>
                    <th class="px-4 py-3">{{ __('Activities') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($treatments as $treatment)
                    <tr wire:key="treatment-{{ $treatment->id }}" class="hover:bg-white/40">
                        <td class="app-title px-4 py-3 font-medium">{{ $treatment->name }}</td>
                        <td class="px-4 py-3">{{ $treatment->price ? number_format((float) $treatment->price, 2, '.', '').' DH' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $treatment->activities->count() }} {{ __('activities') }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="editTreatment({{ $treatment->id }})" class="app-title hover:underline">{{ __('Edit') }}</button>
                            <span class="app-text-muted">|</span>
                            <button type="button" wire:click="deleteTreatment({{ $treatment->id }})" wire:confirm="{{ __('Delete this treatment and all its activities?') }}" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="app-text-muted px-4 py-8 text-center">{{ __('No treatments yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Activities section --}}
    <div class="app-card overflow-x-auto shadow-sm">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="app-title text-lg font-medium">{{ __('Activities') }}</h2>
            <button type="button" wire:click="openActivityForm" class="app-btn-primary px-3 py-1.5 text-sm font-medium">
                {{ __('Add activity') }}
            </button>
        </div>

        @if($showActivityForm)
            <div class="border-b px-4 py-3" style="background-color: #f9fafb;">
                <form wire:submit="saveActivity" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="app-text-gray block text-xs font-medium">{{ __('Activity') }}</label>
                        <input type="text" wire:model="activityName" class="app-input mt-1 block w-72 px-3 py-2 text-sm" />
                        @error('activityName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-text-gray block text-xs font-medium">{{ __('Treatment') }}</label>
                        <select wire:model="activityTreatmentId" class="app-input mt-1 block w-64 px-3 py-2 text-sm">
                            <option value="">{{ __('Select treatment…') }}</option>
                            @foreach ($treatments as $treatment)
                                <option value="{{ $treatment->id }}">{{ $treatment->name }}</option>
                            @endforeach
                        </select>
                        @error('activityTreatmentId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
                        <button type="button" wire:click="cancelActivityForm" class="app-btn-secondary px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        @endif

        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide app-text-gray">
                <tr>
                    <th class="px-4 py-3">{{ __('Activity') }}</th>
                    <th class="px-4 py-3">{{ __('Treatment') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($activities as $activity)
                    <tr wire:key="activity-{{ $activity->id }}" class="hover:bg-white/40">
                        <td class="px-4 py-3">{{ $activity->activity_name }}</td>
                        <td class="app-title px-4 py-3">{{ $activity->treatment?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="editActivity({{ $activity->id }})" class="app-title hover:underline">{{ __('Edit') }}</button>
                            <span class="app-text-muted">|</span>
                            <button type="button" wire:click="deleteActivity({{ $activity->id }})" wire:confirm="{{ __('Delete this activity?') }}" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="app-text-muted px-4 py-8 text-center">{{ __('No activities yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
