<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? \App\Models\SystemSetting::get('app_name', 'Silo') }}</title>
    @php
        $logoPath = \App\Models\SystemSetting::get('app_logo');
        $logoUrl = $logoPath ? asset('storage/' . $logoPath) : asset('assets/logo/logo.svg');
    @endphp
    <link rel="icon" type="image/svg+xml" href="{{ $logoUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! ToastMagic::styles() !!}

    <style>
        :root {
            --primary-hex: {{ \App\Models\SystemSetting::get('primary_color', '#6366f1') }};
        }

        .text-indigo-500,
        .text-indigo-400 {
            color: var(--primary-hex) !important;
        }

        .bg-indigo-500,
        .bg-indigo-600 {
            background-color: var(--primary-hex) !important;
        }

        .border-indigo-500,
        .border-indigo-600 {
            border-color: var(--primary-hex) !important;
        }

        .ring-indigo-500 {
            --tw-ring-color: var(--primary-hex) !important;
        }

        .select option {
            background-color: #0f172a;
            color: #f1f5f9;
        }

        .glass-heavy {
            background: #0f172a;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-hex);
        }

        .sidebar-active {
            background-color: color-mix(in srgb, var(--primary-hex), transparent 85%) !important;
            border: 1px solid color-mix(in srgb, var(--primary-hex), transparent 60%);
        }

        .sidebar-active svg {
            color: var(--primary-hex) !important;
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-200 min-h-screen font-sans antialiased flex" theme="dark">
    <!-- Sidebar -->
    <x-sidebar :logoUrl="$logoUrl" />

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 bg-slate-950 min-h-screen">
        {{ $slot }}
    </main>
    {!! ToastMagic::scripts() !!}
    @livewire('wire-elements-modal')
    <x-mega-downloader />
</body>

</html>
