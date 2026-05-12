<div class="pl-0 pr-3 sm:pr-4">
    <h1 class="app-title mb-6 text-2xl font-semibold">{{ __('Settings') }}</h1>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Queue timing') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Average consultation length used for queue estimates.') }}</p>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('Average consultation (minutes)') }}</label>
                    <div class="mt-1 flex flex-row flex-wrap items-end gap-3">
                        <input type="number" min="1" max="480" wire:model="average_consultation_minutes"
                               class="app-input min-w-0 flex-1 px-3 py-2 text-sm shadow-sm" />
                        <button type="submit" class="app-btn-primary shrink-0 px-4 py-2 text-sm font-medium">{{ __('Save') }}</button>
                    </div>
                    @error('average_consultation_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
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
    </div>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Manual backup') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Create a manual database backup.') }}</p>

            <div class="space-y-3">
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('PostgreSQL bin path') }}</label>
                    <input type="text" wire:model="pgBinDir"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                </div>
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('Backup destination folder') }}</label>
                    <input type="text" wire:model="backupPath"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                    @error('backupPath') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="button" wire:click="createBackup"
                        class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium">
                    {{ __('Create backup') }}
                </button>
            </div>
        </div>

        <div class="app-card p-6 shadow-sm">
            <h2 class="app-title mb-1 text-lg font-medium">{{ __('Automatic backups') }}</h2>
            <p class="app-subtitle mb-4 text-sm">{{ __('Run backups automatically in the background.') }}</p>

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="autoBackupEnabled" id="autoBackupEnabled"
                           class="h-4 w-4 rounded border-gray-300" />
                    <label for="autoBackupEnabled" class="app-text-gray text-sm font-medium">{{ __('Enable') }}</label>
                </div>
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('Interval') }}</label>
                    <select wire:model="autoInterval"
                            class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm">
                        @foreach($this->intervalOptions as $opt)
                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="app-text-gray block text-xs font-medium">{{ __('Keep backups for (days)') }}</label>
                    <input type="number" min="1" max="365" wire:model="autoRetentionDays"
                           class="app-input mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                    @error('autoRetentionDays') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="button" wire:click="saveAutoBackup"
                        class="app-btn-primary inline-flex items-center justify-center px-4 py-2 text-sm font-medium">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>

    @if ($backupMessage)
        <div class="mt-4 rounded-md border px-3 py-2 text-sm" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
             style="border-color: {{ $backupSuccess ? 'var(--color-accent-success)' : 'var(--color-accent-danger, #dc2626)' }}; color: {{ $backupSuccess ? 'var(--color-accent-success)' : 'var(--color-accent-danger, #dc2626)' }}; background-color: {{ $backupSuccess ? 'color-mix(in srgb, var(--color-accent-success) 12%, white)' : '#fef2f2' }}">
            {{ $backupMessage }}
        </div>
    @endif
</div>
