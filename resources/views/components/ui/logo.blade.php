{{-- Logo de l'instance : logo personnalisé s'il existe, sinon le monogramme Silo. --}}
@props(['size' => 32, 'withName' => true])
@php
    $customLogo = \App\Models\SystemSetting::get('app_logo');
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'Silo')) ?: 'Silo';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    @if ($customLogo)
        <img src="{{ asset('storage/'.$customLogo) }}" alt="" width="{{ $size }}" height="{{ $size }}" class="shrink-0 object-contain" style="width: {{ $size }}px; height: {{ $size }}px">
    @else
        <x-ui.mark :size="$size" />
    @endif
    @if ($withName)
        <span class="text-[17px] font-semibold tracking-tight text-ink">{{ $appName }}</span>
    @endif
</span>
