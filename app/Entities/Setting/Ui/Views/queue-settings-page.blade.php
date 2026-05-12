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

    <details class="app-card mt-6 overflow-hidden p-0 shadow-sm open:ring-1 open:ring-gray-200/60">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 marker:hidden hover:bg-black/[0.02] [&::-webkit-details-marker]:hidden">
            <div class="min-w-0">
                <h2 class="app-title text-lg font-medium">{{ __('Database backups') }}</h2>
                <p class="app-subtitle mt-0.5 text-sm">{{ __('Expand or collapse this section.') }}</p>
            </div>
            <span class="app-text-muted shrink-0 text-2xl leading-none select-none" aria-hidden="true">▾</span>
        </summary>

        <div class="space-y-6 border-t border-gray-200/70 px-6 pb-6 pt-5">
            @if ($this->backupPasswordRequired)
                <div class="rounded-lg border border-amber-200 bg-amber-50/80 p-4">
                    <h3 class="app-title mb-1 text-base font-medium">{{ __('Backup security') }}</h3>
                    <p class="app-subtitle mb-3 text-xs">{{ __('Enter the backup password to create a backup or save automatic backup settings. Required for every user.') }}</p>
                    <div>
                        <label class="app-text-gray block text-xs font-medium" for="backupPasswordInput">{{ __('Backup password') }}</label>
                        <input id="backupPasswordInput" type="password" wire:model="backupPassword" autocomplete="new-password"
                               class="app-input mt-1 block w-full max-w-md px-3 py-2 text-sm shadow-sm" />
                        @error('backupPassword') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200/80 bg-white/50 p-5 shadow-sm">
                    <h3 class="app-title mb-1 text-base font-medium">{{ __('Manual backup') }}</h3>
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

                <div class="rounded-lg border border-gray-200/80 bg-white/50 p-5 shadow-sm">
                    <h3 class="app-title mb-1 text-base font-medium">{{ __('Automatic backups') }}</h3>
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
        </div>
    </details>

    @if ($backupMessage)
        <div class="mt-4 rounded-md border px-3 py-2 text-sm" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
             style="border-color: {{ $backupSuccess ? 'var(--color-accent-success)' : 'var(--color-accent-danger, #dc2626)' }}; color: {{ $backupSuccess ? 'var(--color-accent-success)' : 'var(--color-accent-danger, #dc2626)' }}; background-color: {{ $backupSuccess ? 'color-mix(in srgb, var(--color-accent-success) 12%, white)' : '#fef2f2' }}">
            {{ $backupMessage }}
        </div>
    @endif
</div>
