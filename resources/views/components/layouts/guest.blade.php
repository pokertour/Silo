<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? \App\Models\SystemSetting::get('app_name', 'Silo') }}</title>
    @php
        $logoPath = \App\Models\SystemSetting::get('app_logo');
        $logoUrl = $logoPath ? asset('storage/'.$logoPath) : asset('assets/logo/logo.svg');
    @endphp
    <link rel="icon" type="image/svg+xml" href="{{ $logoUrl }}">
    {{ $meta ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --primary-hex: {{ \App\Models\SystemSetting::get('primary_color', '#6366f1') }};
        }
        .text-indigo-500, .text-indigo-400 { color: var(--primary-hex) !important; }
        .bg-indigo-500, .bg-indigo-600 { background-color: var(--primary-hex) !important; }
        .border-indigo-500, .border-indigo-600 { border-color: var(--primary-hex) !important; }
        .ring-indigo-500 { --tw-ring-color: var(--primary-hex) !important; }
        .focus\:border-indigo-500:focus { border-color: var(--primary-hex) !important; }
        select option {
            background-color: #0f172a;
            color: #f1f5f9;
        }
    </style>
</head>
<body x-data class="aurora-bg text-slate-200 min-h-screen font-sans antialiased flex items-center justify-center p-6">
    <div class="absolute top-0 left-0 w-96 h-96 bg-indigo-600/20 aurora-glow"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600/20 aurora-glow"></div>

    {{ $slot }}
    @if(class_exists('\App\Support\ToastMagic'))
        {!! \App\Support\ToastMagic::scripts() !!}
    @endif
    <x-mega-downloader />
    @livewireScripts
</body>
</html>
