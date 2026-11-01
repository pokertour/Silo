{{--
    Disque gravé représentant une image disque. La teinte dépend du système
    (Linux, Windows, BSD, macOS…), l'extension est affichée en étiquette.
--}}
@props(['file' => null, 'os' => null, 'ext' => null, 'size' => 'md', 'spin' => false])
@php
    $os = strtolower($os ?? $file?->os_type ?? '');
    $ext = $ext ?? ($file ? pathinfo($file->file_path, PATHINFO_EXTENSION) : null);
    $hue = match (true) {
        str_contains($os, 'win') => '#4cc2ff',
        str_contains($os, 'bsd') => '#ff7a59',
        str_contains($os, 'mac') || str_contains($os, 'darwin') => '#c3c9d4',
        str_contains($os, 'linux') || str_contains($os, 'ubuntu') || str_contains($os, 'debian') => '#f6c445',
        $os !== '' => '#b58cff',
        default => '#8b93a1',
    };
    $sizeClass = ['sm' => 'disc-sm', 'md' => '', 'lg' => 'disc-lg', 'xl' => 'disc-xl'][$size] ?? '';
@endphp
<span {{ $attributes->merge(['class' => "disc {$sizeClass}".($spin ? ' animate-spin-slow' : '')]) }} style="--hue: {{ $hue }}" aria-hidden="true">
    @if ($ext && $size !== 'sm')
        <span class="disc-ext">{{ \Illuminate\Support\Str::limit($ext, 5, '') }}</span>
    @endif
</span>
