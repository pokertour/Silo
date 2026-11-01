<div class="p-6 lg:p-10 space-y-10">
    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Tableau de Bord') }}</h2>
            <p class="text-slate-500 mt-1">{{ __('app.Gérez vos fichiers ISO et suivez leur activité.') }}</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('upload') }}" wire:navigate class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-500/10 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                {{ __('app.Ajouter un ISO') }}
            </a>
        </div>
    </header>

    <!-- Stats Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            :title="__('app.ISOs Accessibles')" 
            :value="$totalFiles" 
            color="indigo"
            :trend="__('app.Tous les fichiers')"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.Espace Utilisé')" 
            :value="\Illuminate\Support\Number::fileSize($totalSize, precision: 1)" 
            color="emerald"
            :trend="__('app.Total')"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.ISOs Publics')" 
            :value="$publicFilesCount" 
            color="amber"
            :trend="__('app.Ouvert')"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.Groupes Actifs')" 
            :value="$userGroupsCount" 
            color="purple"
            :trend="__('app.Membre')"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </x-slot:icon>
        </x-stat-card>
    </section>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-8">
        <!-- ISO List (Spans 2) -->
        <div class="2xl:col-span-2">
            <x-iso-list 
                :isoFiles="$isoFiles" 
                :viewMode="$viewMode" 
                :filter="$filter" 
                :groups="auth()->user()->hasRole('admin') ? \App\Models\Group::all() : auth()->user()->groups" 
            />
        </div>

        <!-- Side Activities (Spans 1) -->
        <div class="space-y-6">
            <x-recent-activities :recentUploads="$recentUploads" />
        </div>
    </div>
    </div>
</div>
