<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    {{-- Absolute URLs for body backgrounds: root-relative /backgrounds/... breaks under Vite dev (wrong origin). --}}
    <style>
        :root {
            --app-bg-clinic: url("{{ asset('backgrounds/background.svg') }}");
            --app-bg-clinic-empty: url("{{ asset('backgrounds/emptyBackground.svg') }}");
        }
    </style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="app-body-clinic min-h-screen antialiased">
    <div class="app-clinic-main-shift w-full max-w-none py-6">
        <header class="app-divider mb-8 flex flex-row flex-wrap items-center justify-between gap-4 border-b pb-4">
            <nav class="flex flex-wrap items-end gap-8 text-sm font-medium" aria-label="{{ __('Main navigation') }}">
                <a href="{{ route('queue.index') }}"
                   class="inline-block border-b-2 pb-1 transition-colors {{ request()->routeIs('queue.index') ? 'app-title' : 'border-transparent app-text-gray hover:app-title' }}"
                   @if(request()->routeIs('queue.index')) style="border-color: var(--color-raw-primary-blue);" @endif>
                    {{ __('File d\'attente') }}
                </a>
                <a href="{{ route('patients.index') }}"
                   class="inline-block border-b-2 pb-1 transition-colors {{ request()->routeIs('patients.*') ? 'app-title' : 'border-transparent app-text-gray hover:app-title' }}"
                   @if(request()->routeIs('patients.*')) style="border-color: var(--color-raw-primary-blue);" @endif>
                    {{ __('Liste des patients') }}
                </a>
                <a href="{{ route('queue.timeline') }}"
                   class="inline-block border-b-2 pb-1 transition-colors {{ request()->routeIs('queue.timeline') ? 'app-title' : 'border-transparent app-text-gray hover:app-title' }}"
                   @if(request()->routeIs('queue.timeline')) style="border-color: var(--color-raw-primary-blue);" @endif>
                    {{ __('Chronologie') }}
                </a>
                <a href="{{ route('reports.index') }}"
                   class="inline-block border-b-2 pb-1 transition-colors {{ request()->routeIs('reports.*') ? 'app-title' : 'border-transparent app-text-gray hover:app-title' }}"
                   @if(request()->routeIs('reports.*')) style="border-color: var(--color-raw-primary-blue);" @endif>
                    {{ __('Reports') }}
                </a>
            </nav>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                <a href="{{ route('locale.switch', ['locale' => app()->getLocale() === 'ar' ? 'fr' : 'ar']) }}"
                   class="app-input inline-flex items-center justify-center rounded-md px-3 py-1.5 text-xs font-medium no-underline app-text-gray hover:app-title hover:bg-white/90"
                   aria-label="{{ app()->getLocale() === 'ar' ? __('Switch to French') : __('Switch to Arabic') }}">
                    {{ app()->getLocale() === 'ar' ? 'FR' : 'عربي' }}
                </a>
                <a href="{{ route('settings.queue') }}"
                   class="app-input inline-flex shrink-0 items-center justify-center rounded-md p-2 text-[var(--color-raw-primary-blue)] hover:bg-white/90"
                   aria-label="{{ __('Réglages') }}"
                   @if(request()->routeIs('settings.*')) style="box-shadow: 0 0 0 1px var(--color-border-default);" @endif>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.292.24-.437.613-.43.992a6.932 6.932 0 010 .255c-.007.378.138.75.43.99l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>
            </div>
        </header>
        <main class="w-full max-w-none px-0">
            @if (session('status'))
                <div class="mb-4 rounded-md border py-3 pl-0 pr-3 text-sm sm:pr-4" style="border-color: var(--color-accent-success); color: var(--color-accent-success); background-color: color-mix(in srgb, var(--color-accent-success) 12%, white);" role="status">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 py-3 pl-0 pr-3 text-sm text-red-900 sm:pr-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
