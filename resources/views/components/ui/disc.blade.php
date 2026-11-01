{{--
    CD argenté représentant une image disque. Le moyeu est teinté selon le système
    (Linux, Windows, BSD, macOS…), l'extension est affichée en étiquette.
--}}
@props(['file' => null, 'os' => null, 'ext' => null, 'size' => 'md', 'spin' => false])
@php
    $os = strtolower($os ?? $file?->os_type ?? '');
    $ext = $ext === false ? null : ($ext ?? ($file ? pathinfo($file->file_path, PATHINFO_EXTENSION) : null));
    $hue = match (true) {
        str_contains($os, 'win') => '#0a84ff',
        str_contains($os, 'bsd') => '#ff3b30',
        str_contains($os, 'mac') || str_contains($os, 'darwin') => '#8e8e93',
        str_contains($os, 'linux') || str_contains($os, 'ubuntu') || str_contains($os, 'debian') => '#ff9500',
        $os !== '' => '#af52de',
        default => '#8e8e93',
    };
    $sizeClass = ['sm' => 'disc-sm', 'md' => '', 'lg' => 'disc-lg', 'xl' => 'disc-xl'][$size] ?? '';
@endphp
<span {{ $attributes->merge(['class' => "disc {$sizeClass}".($spin ? ' animate-spin-slow' : '')]) }} style="--hue: {{ $hue }}" aria-hidden="true">
    @if ($ext && $size !== 'sm')
        <span class="disc-ext">{{ \Illuminate\Support\Str::limit($ext, 5, '') }}</span>
    @endif
</span>
