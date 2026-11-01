<div @if ($hasActive) wire:poll.2s @endif>
    @if ($imports->isNotEmpty())
        @php
            $states = [
                'pending' => ['label' => __('app.En attente'), 'icon' => 'ri-time-line', 'color' => 'bg-[#8e8e93]'],
                'downloading' => ['label' => __('app.Téléchargement'), 'icon' => 'ri-download-cloud-2-line', 'color' => 'bg-accent'],
                'processing' => ['label' => __('app.Enregistrement'), 'icon' => 'ri-hard-drive-3-line', 'color' => 'bg-[#5856d6]'],
                'completed' => ['label' => __('app.Terminé'), 'icon' => 'ri-check-line', 'color' => 'bg-[#34c759]'],
                'failed' => ['label' => __('app.Échec'), 'icon' => 'ri-error-warning-line', 'color' => 'bg-[#ff3b30]'],
                'cancelled' => ['label' => __('app.Annulé'), 'icon' => 'ri-close-line', 'color' => 'bg-[#8e8e93]'],
            ];
        @endphp
        <section class="card mb-7 overflow-hidden" aria-live="polite">
            <div class="flex items-center justify-between px-[18px] pt-4 pb-1.5">
                <h2 class="flex items-center gap-2 text-[15px] font-semibold text-ink">
                    {{ __('app.Imports en cours') }}
                    @if ($hasActive)
                        <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-accent"></span></span>
                    @endif
                </h2>
                <a href="{{ route('upload', ['source' => 'url']) }}" wire:navigate class="btn btn-ghost btn-sm"><i class="ri-add-line"></i>{{ __('app.Nouvel import') }}</a>
            </div>

            @foreach ($imports as $import)
                @php
                    $state = $states[$import->status] ?? $states['pending'];
                    $progress = $import->progress();
                    $remaining = $import->secondsRemaining();
                @endphp
                <div wire:key="import-{{ $import->id }}" class="list-row items-start sm:items-center">
                    <span class="sym {{ $state['color'] }}"><i class="{{ $state['icon'] }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline gap-2">
                            <p class="truncate text-[13.5px] font-medium text-ink">{{ $import->name }}</p>
                            <span class="shrink-0 text-xs text-ink-3">{{ $state['label'] }}</span>
                        </div>
                        <p class="truncate text-xs text-ink-3" title="{{ $import->url }}">{{ $import->host() }}</p>

                        @if ($import->isActive())
                            <div class="mt-2 flex items-center gap-3">
                                <div class="meter flex-1">
                                    @if ($progress !== null)
                                        <span style="width: {{ $progress }}%"></span>
                                    @else
                                        <span class="w-1/3 animate-pulse"></span>
                                    @endif
                                </div>
                                <span class="shrink-0 font-mono text-xs text-ink-2">{{ $progress !== null ? $progress.' %' : '…' }}</span>
                            </div>
                            <p class="mt-1 font-mono text-[11.5px] text-ink-3">
                                {{ \Illuminate\Support\Number::fileSize($import->bytes_downloaded, precision: 1) }}@if ($import->bytes_total) / {{ \Illuminate\Support\Number::fileSize($import->bytes_total, precision: 1) }}@endif
                                @if ($import->bytes_per_second) · {{ \Illuminate\Support\Number::fileSize($import->bytes_per_second, precision: 1) }}/s @endif
                                @if ($remaining !== null) · {{ __('app.environ') }} {{ \Carbon\CarbonInterval::seconds($remaining)->cascade()->forHumans(short: true, parts: 2) }} @endif
                            </p>
                        @elseif ($import->status === 'failed' && $import->error)
                            <p class="mt-1 line-clamp-2 text-xs text-danger">{{ $import->error }}</p>
                        @elseif ($import->status === 'completed')
                            <p class="mt-0.5 font-mono text-[11.5px] text-ink-3">{{ \Illuminate\Support\Number::fileSize($import->bytes_total ?? $import->bytes_downloaded, precision: 1) }} · {{ $import->finished_at?->diffForHumans() }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        @if ($import->isActive())
                            <button type="button" class="btn btn-secondary btn-sm"
                                    @click="$store.dialog.confirm({ title: @js(__('app.Annuler l\'import ?')), message: @js($import->name), confirm: @js(__('app.Annuler l\'import')), cancel: @js(__('app.Continuer')), tone: 'danger', icon: 'ri-close-circle-line' }).then(ok => ok && $wire.cancel({{ $import->id }}))">
                                {{ __('app.Annuler') }}
                            </button>
                        @else
                            @if ($import->status === 'completed' && $import->isoFile)
                                <a href="{{ route('isos.show', $import->isoFile) }}" wire:navigate class="btn btn-secondary btn-sm">{{ __('app.Ouvrir') }}</a>
                            @elseif ($import->isRetryable())
                                <button type="button" wire:click="retry({{ $import->id }})" class="btn btn-secondary btn-sm"><i class="ri-refresh-line"></i>{{ __('app.Réessayer') }}</button>
                            @endif
                            <button type="button" wire:click="dismiss({{ $import->id }})" class="icon-btn" title="{{ __('app.Masquer') }}"><i class="ri-close-line"></i></button>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</div>
