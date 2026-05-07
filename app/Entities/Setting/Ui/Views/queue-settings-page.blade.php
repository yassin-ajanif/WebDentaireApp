<div class="pl-0 pr-3 sm:pr-4">
    <h1 class="app-title mb-2 text-2xl font-semibold">{{ __('Queue timing') }}</h1>
    <p class="app-subtitle mb-6 text-sm">{{ __('Average consultation length used for queue estimates.') }}</p>

    <form wire:submit="save" class="app-card max-w-md space-y-4 p-6 shadow-sm">
        <div>
            <label class="app-text-gray block text-sm font-medium">{{ __('Average consultation (minutes)') }}</label>
            <input type="number" min="1" max="480" wire:model="average_consultation_minutes"
                   class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
            @error('average_consultation_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
    </form>
</div>
