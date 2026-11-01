{{-- Carte d'image disque (vue grille). --}}
@props(['file', 'duplicate' => false, 'selectable' => false])
<article wire:key="file-card-{{ $file->id }}" {{ $attributes->merge(['class' => 'card card-hover group relative flex flex-col p-5 has-[input:checked]:border-accent-ink has-[input:checked]:bg-accent-soft/40']) }}>
    @if ($selectable)
        <input type="checkbox" wire:model.live="selected" value="{{ $file->id }}" aria-label="{{ __('app.Sélectionner') }} {{ $file->name }}"
               class="absolute top-3 right-3 z-20 size-[18px] cursor-pointer accent-[var(--accent)] transition sm:opacity-0 sm:group-hover:opacity-100 sm:checked:opacity-100 sm:focus-visible:opacity-100">
    @endif
    <div @class(["flex items-start gap-4", "pr-6" => $selectable])>
        <div class="relative">
            <x-ui.disc :file="$file" class="transition duration-500 group-hover:rotate-[60deg]" />
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="font-display text-[17px] leading-snug font-semibold text-ink">
                <a href="{{ route('isos.show', $file) }}" wire:navigate class="after:absolute after:inset-0 after:rounded-2xl focus:outline-none">{{ $file->name }}</a>
            </h3>
            <p class="mt-1 truncate font-mono text-[11.5px] text-ink-3">
                {{ collect([$file->os_type, $file->version ? 'v'.$file->version : null, $file->architecture])->filter()->implode(' · ') ?: '—' }}
            </p>
        </div>
    </div>

    @if ($file->description)
        <p class="mt-4 line-clamp-2 text-sm text-ink-2">{{ $file->description }}</p>
    @endif

    <div class="relative z-10 mt-4 flex flex-wrap gap-1.5">
        <x-file-status :file="$file" :duplicate="$duplicate" />
        @foreach ($file->tags as $tag)
            <span class="tag" style="--tag: {{ $tag->color }}">{{ $tag->name }}</span>
        @endforeach
    </div>

    <div class="min-h-5 flex-1"></div>
    <div class="flex items-center justify-between gap-3 border-t border-line pt-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <x-ui.avatar :user="$file->user" class="size-7 text-[11px]" />
            <div class="min-w-0 leading-tight">
                <p class="font-mono text-[13px] font-medium text-ink">{{ $file->size_human }}</p>
                <p class="truncate text-xs text-ink-3">{{ $file->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <x-file-actions :file="$file" compact class="relative z-10" />
    </div>
</article>
