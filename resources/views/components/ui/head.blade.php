{{-- <head> commun aux layouts : méta, thème (sans flash), couleur d'accent de l'instance. --}}
@props(['title' => null])
@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'Silo')) ?: 'Silo';
    $customLogo = \App\Models\SystemSetting::get('app_logo');

    // Couleur d'accent choisie par l'administrateur (validée), et encre lisible dessus
    $accent = \App\Models\SystemSetting::get('primary_color', '#c5f82a');
    if (! is_string($accent) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
        $accent = '#c5f82a';
    }
    [$r, $g, $b] = sscanf($accent, '#%02x%02x%02x');
    $onAccent = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255 > 0.6 ? '#0b0c0f' : '#ffffff';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0b0c0f">
<title>{{ $title ? $title.' · '.$appName : $appName }}</title>
<link rel="icon" type="image/svg+xml" href="{{ $customLogo ? asset('storage/'.$customLogo) : asset('assets/logo/logo.svg') }}">
<script>
    (function () {
        var theme = localStorage.getItem('silo-theme') || 'dark';
        document.documentElement.classList.toggle('light', theme === 'light');
        document.documentElement.classList.toggle('dark', theme !== 'light');
    })();
</script>
<style>
    :root { --accent: {{ $accent }}; --on-accent: {{ $onAccent }}; }
</style>
{{ $slot }}
@vite(['resources/css/app.css', 'resources/js/app.js'])
