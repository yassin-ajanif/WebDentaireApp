<div class="flex min-h-screen items-center justify-center px-4">
    <div class="app-card w-full max-w-sm bg-white/95 p-6 shadow-xl">
        <h1 class="app-title mb-6 text-center text-xl font-semibold">{{ __('Login') }}</h1>

        @if($error)
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900" role="alert">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-4">
            <div>
                <label class="app-text-gray block text-sm font-medium">{{ __('Email') }}</label>
                <input type="email" wire:model="email" class="app-input mt-1 block w-full px-3 py-2 text-sm" autocomplete="email" />
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="app-text-gray block text-sm font-medium">{{ __('Password') }}</label>
                <input type="password" wire:model="password" class="app-input mt-1 block w-full px-3 py-2 text-sm" autocomplete="current-password" />
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="app-btn-primary w-full px-4 py-2 text-sm font-medium">{{ __('Login') }}</button>
        </form>

        <p class="app-text-muted mt-4 text-center text-xs">
            <a href="{{ route('auth.reset-password') }}" class="hover:underline">{{ __('Forgot password?') }}</a>
        </p>
    </div>
</div>
