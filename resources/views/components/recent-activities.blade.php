@props(['recentUploads'])

<div class="space-y-6">
    <h3 class="text-xl font-bold">{{ __('app.Activité Récente') }}</h3>
    <div class="glass rounded-3xl p-6">
        <div class="space-y-6">
            @forelse($recentUploads as $upload)
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                </div>
                <div>
                    <div class="text-sm font-semibold">{{ __('app.Nouveau téléversement') }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ Str::limit($upload->name, 20) }} {{ __('app.par') }} <span class="text-indigo-400">{{ $upload->user->name }}</span></div>
                    <div class="text-[10px] text-slate-600 mt-2">{{ $upload->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="text-center text-slate-500 text-sm py-4">{{ __('app.Aucune activité récente.') }}</div>
            @endforelse
        </div>
    </div>
</div>
