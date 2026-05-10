<div class="flex min-h-screen items-center justify-center px-4">
    <div class="app-card w-full max-w-sm bg-white/95 p-6 shadow-xl">
        <h1 class="app-title mb-6 text-center text-xl font-semibold">{{ __('Reset password') }}</h1>

        @if($error)
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900" role="alert">
                {{ $error }}
            </div>
        @endif

        @if($success)
            <div class="mb-4 rounded-md border px-3 py-2 text-sm" style="border-color: var(--color-accent-success); color: var(--color-accent-success); background-color: color-mix(in srgb, var(--color-accent-success) 12%, white);" role="status">
                {{ __('Password reset successfully.') }}
            </div>
            <a href="{{ route('login') }}" class="app-btn-primary block px-4 py-2 text-center text-sm font-medium">{{ __('Back to login') }}</a>
        @else
            <form wire:submit="resetPassword" class="space-y-4">
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('Recovery code') }}</label>
                    <input type="text" wire:model="recovery_code" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                    @error('recovery_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('Email') }}</label>
                    <input type="email" wire:model="email" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('New password') }}</label>
                    <input type="password" wire:model="password" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="app-text-gray block text-sm font-medium">{{ __('Confirm password') }}</label>
                    <input type="password" wire:model="password_confirmation" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                </div>
                <button type="submit" class="app-btn-primary w-full px-4 py-2 text-sm font-medium">{{ __('Reset password') }}</button>
            </form>
            <p class="app-text-muted mt-4 text-center text-xs">
                <a href="{{ route('login') }}" class="hover:underline">{{ __('Back to login') }}</a>
            </p>
        @endif
    </div>
</div>
