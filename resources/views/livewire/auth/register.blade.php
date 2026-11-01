<div class="w-full max-w-md glass rounded-3xl p-8 shadow-2xl relative overflow-hidden">
    <!-- Decoration -->
    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>
    
    <div class="flex justify-center mb-8">
        <div class="bg-indigo-600/20 p-3 rounded-2xl shadow-lg shadow-indigo-500/20 ring-1 ring-indigo-500/30">
            <img src="{{ asset('assets/logo/logo.svg') }}" alt="Silo Logo" class="w-10 h-10">
        </div>
    </div>

    <h1 class="text-3xl font-bold text-center mb-2 bg-linear-to-r from-white to-slate-400 bg-clip-text text-transparent">Silo</h1>
    <p class="text-slate-400 text-center mb-10">{{ __('app.Créer un compte') }}</p>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Nom complet') }}</label>
            <input wire:model="name" type="text" id="name" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" placeholder="{{ __('app.Prénom Nom') }}">
            @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Email') }}</label>
            <input wire:model="email" type="email" id="email" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" placeholder="{{ __('app.votre@email.com') }}">
            @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="invitation_token" class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Jeton d\'invitation') }}</label>
            <input wire:model="invitation_token" type="text" id="invitation_token" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600 font-mono tracking-wider" placeholder="XXX-YYY-ZZZ">
            @error('invitation_token') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe') }}</label>
            <input wire:model="password" type="password" id="password" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" placeholder="••••••••">
            @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Confirmation') }}</label>
            <input wire:model="password_confirmation" type="password" id="password_confirmation" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" placeholder="••••••••">
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 mt-2 rounded-xl shadow-lg shadow-indigo-500/20 active:scale-[0.98] transition-all flex items-center justify-center">
            <span wire:loading.remove wire:target="register">{{ __('app.S\'inscrire') }}</span>
            <span wire:loading wire:target="register">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </span>
        </button>
    </form>

    <div class="mt-6 text-center border-t border-slate-800 pt-4">
        <p class="text-sm text-slate-400">
            {{ __('app.Déjà un compte ?') }} 
            <a href="{{ route('login') }}" wire:navigate class="text-indigo-400 font-semibold hover:text-indigo-300 ml-1">{{ __('app.Connexion') }}</a>
        </p>
    </div>

    <!-- Language Selector -->
    <div class="mt-8 pt-6 border-t border-slate-800/50 flex items-center justify-center gap-4">
        <a href="{{ route('lang.switch', 'fr') }}" class="group flex flex-col items-center gap-1.5 transition-all">
            <span class="text-2xl group-hover:scale-110 transition-transform {{ app()->getLocale() === 'fr' ? 'drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]' : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100' }}">🇫🇷</span>
            <span class="text-[10px] font-bold uppercase tracking-widest {{ app()->getLocale() === 'fr' ? 'text-indigo-400' : 'text-slate-600 group-hover:text-slate-400' }}">FR</span>
        </a>
        <a href="{{ route('lang.switch', 'en') }}" class="group flex flex-col items-center gap-1.5 transition-all">
            <span class="text-2xl group-hover:scale-110 transition-transform {{ app()->getLocale() === 'en' ? 'drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]' : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100' }}">🇬🇧</span>
            <span class="text-[10px] font-bold uppercase tracking-widest {{ app()->getLocale() === 'en' ? 'text-indigo-400' : 'text-slate-600 group-hover:text-slate-400' }}">EN</span>
        </a>
        <a href="{{ route('lang.switch', 'es') }}" class="group flex flex-col items-center gap-1.5 transition-all">
            <span class="text-2xl group-hover:scale-110 transition-transform {{ app()->getLocale() === 'es' ? 'drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]' : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100' }}">🇪🇸</span>
            <span class="text-[10px] font-bold uppercase tracking-widest {{ app()->getLocale() === 'es' ? 'text-indigo-400' : 'text-slate-600 group-hover:text-slate-400' }}">ES</span>
        </a>
        <a href="{{ route('lang.switch', 'de') }}" class="group flex flex-col items-center gap-1.5 transition-all">
            <span class="text-2xl group-hover:scale-110 transition-transform {{ app()->getLocale() === 'de' ? 'drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]' : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100' }}">🇩🇪</span>
            <span class="text-[10px] font-bold uppercase tracking-widest {{ app()->getLocale() === 'de' ? 'text-indigo-400' : 'text-slate-600 group-hover:text-slate-400' }}">DE</span>
        </a>
    </div>
</div>
