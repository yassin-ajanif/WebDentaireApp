<div class="pl-0 pr-3 sm:pr-4">
    <h1 class="app-title mb-6 text-2xl font-semibold">{{ __('Settings') }}</h1>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Queue timing') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Average consultation length used for queue estimates.') }}</p>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('Average consultation (minutes)') }}</label>
                    <input type="number" min="1" max="480" wire:model="average_consultation_minutes"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                    @error('average_consultation_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
            </form>
        </div>

        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Trash') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('View and restore deleted patients.') }}</p>
            <a href="{{ route('patients.trash') }}" class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium">
                {{ __('Deleted patients') }}
            </a>
        </div>

        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Treatment catalog') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Manage treatments and their activities.') }}</p>
            <a href="{{ route('settings.treatments.catalog') }}" class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium">
                {{ __('Manage catalog') }}
            </a>
        </div>

        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Backup') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Create a manual database backup.') }}</p>
            <form wire:submit="createBackup" class="space-y-3">
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('PostgreSQL bin path') }}</label>
                    <input type="text" wire:model="pgBinDir" placeholder="D:\odoo\PostgreSQL\bin"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                </div>
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('Backup destination folder') }}</label>
                    <input type="text" wire:model="backupPath"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                    @error('backupPath') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium">
                    {{ __('Create backup') }}
                </button>
            </form>
        </div>
    </div>
</div>
