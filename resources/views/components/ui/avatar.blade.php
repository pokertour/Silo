@props(['user'])
@if ($user?->avatar)
    <img src="{{ asset('storage/'.$user->avatar) }}" alt="" {{ $attributes->merge(['class' => 'avatar object-cover']) }}>
@else
    @php
        $initials = collect(preg_split('/\s+/', trim($user?->name ?? '?')))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    @endphp
    <span {{ $attributes->merge(['class' => 'avatar']) }}>{{ $initials ?: '?' }}</span>
@endif
