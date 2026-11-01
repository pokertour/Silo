<x-layouts.guest>
    <div class="w-full max-w-md glass rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="flex justify-center mb-8">
            <div class="bg-indigo-600 p-3 rounded-2xl shadow-lg shadow-indigo-500/20">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-center mb-2">{{ __('app.Lien Protégé') }}</h1>
        <p class="text-slate-400 text-center mb-8">{{ __('app.Ce lien de téléchargement est protégé par un mot de passe.') }}</p>
        <form action="{{ route('share.verify', $share->token) }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="password" class="block text-sm font-medium mb-2 text-slate-300">{{ __('app.Mot de passe') }}</label>
                <input type="password" name="password" id="password" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all" placeholder="••••••••" required autofocus>
                @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl shadow-lg transition-all cursor-pointer">{{ __('app.Accéder au fichier') }}</button>
        </form>
    </div>
</x-layouts.guest>
