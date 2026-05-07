<div class="pl-0 pr-3 sm:pr-4">
    <div class="mb-6">
        <h1 class="app-title text-2xl font-semibold">{{ $patientId ? __('Edit patient') : __('New patient') }}</h1>
        <p class="app-subtitle mt-1 text-sm">{{ __('Telephone must be unique.') }}</p>
    </div>

    <form wire:submit="save" class="app-card max-w-xl space-y-4 p-6 shadow-sm">
        <div>
            <label class="app-text-gray block text-sm font-medium">{{ __('First name') }}</label>
            <input type="text" wire:model="first_name" class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="app-text-gray block text-sm font-medium">{{ __('Last name') }}</label>
            <input type="text" wire:model="last_name" class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="app-text-gray block text-sm font-medium">{{ __('Telephone') }}</label>
            <input type="text" wire:model="telephone" class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
            @error('telephone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="app-text-gray block text-sm font-medium">{{ __('Notes') }}</label>
            <textarea wire:model="notes" rows="3" class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm"></textarea>
            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
            <a href="{{ route('patients.index') }}" class="app-btn-secondary px-4 py-2 text-sm font-medium">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
