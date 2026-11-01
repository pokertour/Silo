<div x-data="megaDownloader()" @start-mega-download.window="startDownload($event.detail.url, $event.detail.filename)"
     x-show="downloading" style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-6 right-6 w-96 bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl z-50 overflow-hidden">
    
    <div class="p-5">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-bold text-slate-200 flex items-center gap-2">
                <svg class="animate-bounce" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-hex, #6366f1)" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                {{ __('app.Téléchargement en cours...') }}
            </h4>
            <div class="text-xs font-mono font-bold text-indigo-400" x-text="progressPercent + '%'"></div>
        </div>
        
        <p class="text-xs text-slate-400 truncate mb-4" x-text="filename"></p>
        
        <div class="h-2 w-full bg-slate-800 rounded-full overflow-hidden mb-2 relative">
            <div class="h-full bg-indigo-500 transition-all duration-300 ease-out" :style="`width: ${progressPercent}%`"></div>
            <!-- Animated shine effect -->
            <div class="absolute top-0 bottom-0 left-0 right-0 bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-[shimmer_1.5s_infinite]"></div>
        </div>
        
        <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium uppercase tracking-wider">
            <span x-text="formatBytes(loadedBytes) + ' / ' + formatBytes(totalBytes)"></span>
            <span x-text="speed + ' MB/s'"></span>
        </div>
        
        <div x-show="error" class="mt-3 p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-xs text-red-400" x-text="error"></div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('megaDownloader', () => ({
                downloading: false,
                progressPercent: 0,
                loadedBytes: 0,
                totalBytes: 0,
                speed: 0,
                filename: '',
                error: null,
                
                formatBytes(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },
                
                async startDownload(url, expectedFilename) {
                    this.filename = expectedFilename;
                    this.error = null;
                    this.progressPercent = 0;
                    this.loadedBytes = 0;
                    this.totalBytes = 0;
                    
                    let handle = null;
                    let writable = null;
                    
                    // Si l'API moderne est supportée, demander l'emplacement TOUT DE SUITE pour éviter l'expiration du geste utilisateur
                    if (window.showSaveFilePicker) {
                        try {
                            handle = await window.showSaveFilePicker({ suggestedName: this.filename });
                            writable = await handle.createWritable();
                        } catch (err) {
                            if (err.name === 'AbortError') return; // L'utilisateur a annulé la popup
                            console.warn("L'API File System a échoué, passage au mode RAM (Fallback)", err);
                        }
                    }
                    
                    this.downloading = true;
                    
                    try {
                        const response = await fetch(url);
                        if (!response.ok) throw new Error("{{ __('app.Erreur de connexion au serveur') }}");
                        
                        const contentLength = response.headers.get('content-length');
                        this.totalBytes = contentLength ? parseInt(contentLength, 10) : 0;
                        
                        const reader = response.body.getReader();
                        
                        let lastTime = Date.now();
                        let lastLoaded = 0;
                        let chunks = [];
                        
                        while (true) {
                            const { done, value } = await reader.read();
                            if (done) break;
                            
                            this.loadedBytes += value.length;
                            if (this.totalBytes > 0) {
                                this.progressPercent = Math.round((this.loadedBytes / this.totalBytes) * 100);
                            }
                            
                            const now = Date.now();
                            if (now - lastTime > 500) {
                                const diffBytes = this.loadedBytes - lastLoaded;
                                const diffTime = (now - lastTime) / 1000;
                                this.speed = (diffBytes / 1048576 / diffTime).toFixed(1);
                                lastTime = now;
                                lastLoaded = this.loadedBytes;
                            }
                            
                            if (writable) {
                                await writable.write(value);
                            } else {
                                chunks.push(value);
                            }
                        }
                        
                        if (writable) {
                            await writable.close();
                        } else {
                            // Fallback (RAM -> Téléchargement navigateur invisible à la fin)
                            const blob = new Blob(chunks);
                            const downloadUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = downloadUrl;
                            a.download = this.filename;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(downloadUrl);
                            a.remove();
                        }
                        
                        setTimeout(() => {
                            this.downloading = false;
                            window.dispatchEvent(new CustomEvent('toast-magic', { detail: { type: 'success', message: "{{ __('app.Téléchargement terminé avec succès !') }}" }}));
                        }, 1500);
                        
                    } catch (e) {
                        if (e.name !== 'AbortError') {
                            this.error = "{{ __('app.Erreur lors du téléchargement :') }} " + e.message;
                            console.error(e);
                        } else {
                            this.downloading = false;
                        }
                    }
                }
            }));
        });
    </script>
    <style>
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
    </style>
</div>
