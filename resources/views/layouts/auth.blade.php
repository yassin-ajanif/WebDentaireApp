<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root {
            --app-bg-clinic: url("{{ asset('backgrounds/background.svg') }}");
        }
    </style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="app-body-clinic min-h-screen antialiased">
    <main class="w-full max-w-none px-0">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
