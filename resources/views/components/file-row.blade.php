{{-- Ligne d'image disque (vue liste). --}}
@props(['file', 'duplicate' => false])
<div wire:key="file-row-{{ $file->id }}" class="group relative flex flex-col gap-3 px-4 py-3.5 transition hover:bg-surface-2 sm:flex-row sm:items-center sm:gap-5 sm:px-5">
    <div class="flex min-w-0 flex-1 items-center gap-3.5">
        <x-ui.disc :file="$file" size="sm" />
        <div class="min-w-0">
            <a href="{{ route('isos.show', $file) }}" wire:navigate class="block truncate font-medium text-ink after:absolute after:inset-0">{{ $file->name }}</a>
            <p class="truncate font-mono text-[11.5px] text-ink-3">{{ collect([$file->os_type, $file->version ? 'v'.$file->version : null, $file->architecture, $file->user?->name])->filter()->implode(' · ') }}</p>
        </div>
    </div>
    <div class="relative z-10 hidden flex-wrap gap-1.5 xl:flex"><x-file-status :file="$file" :duplicate="$duplicate" /></div>
    <span class="w-20 shrink-0 font-mono text-[13px] text-ink-2 sm:text-right">{{ $file->size_human }}</span>
    <x-file-actions :file="$file" compact class="relative z-10 shrink-0" />
</div>
