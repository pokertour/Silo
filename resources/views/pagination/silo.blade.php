{{-- Pagination Livewire au style Silo : {{ $items->links('pagination.silo') }} --}}
@if ($paginator->hasPages())
    @php $pageName = $paginator->getPageName(); @endphp
    <nav role="navigation" aria-label="{{ __('app.Pagination') }}" class="flex items-center justify-between gap-4">
        <p class="font-mono text-xs text-ink-3">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}
        </p>
        <div class="flex items-center gap-1">
            <button type="button" class="icon-btn border border-line" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) aria-label="{{ __('app.Précédent') }}">
                <i class="ri-arrow-left-s-line"></i>
            </button>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 font-mono text-xs text-ink-3">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                @class(['hidden h-9 min-w-9 rounded-xl px-2 font-mono text-sm transition sm:inline-block', 'bg-ink text-bg' => $page == $paginator->currentPage(), 'text-ink-2 hover:bg-surface-2' => $page != $paginator->currentPage()])
                                @if ($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</button>
                    @endforeach
                @endif
            @endforeach
            <button type="button" class="icon-btn border border-line" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) aria-label="{{ __('app.Suivant') }}">
                <i class="ri-arrow-right-s-line"></i>
            </button>
        </div>
    </nav>
@endif
