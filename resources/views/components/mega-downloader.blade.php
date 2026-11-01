{{-- Gestionnaire de téléchargement : progression, débit, reprise via File System Access API. --}}
<div x-data="megaDownloader()" @start-mega-download.window="startDownload($event.detail.url, $event.detail.filename)"
     x-show="downloading" x-cloak
     x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-4 opacity-0"
     x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="translate-y-4 opacity-0"
     class="fixed inset-x-3 bottom-24 z-[85] sm:inset-x-auto sm:bottom-5 sm:left-5 lg:left-[270px] sm:w-[380px]">
    <div class="overflow-hidden rounded-2xl border border-line-strong bg-elevated/95 shadow-2xl backdrop-blur-xl">
        <div class="flex items-center gap-3 p-4">
            <x-ui.disc size="sm" class="animate-spin-slow" />
            <div class="min-w-0 flex-1">
                <p class="spec">{{ __('app.Téléchargement en cours...') }}</p>
                <p class="truncate text-sm font-semibold text-ink" x-text="filename"></p>
            </div>
            <span class="font-display text-2xl font-semibold tabular-nums text-ink" x-text="progressPercent + '%'"></span>
        </div>
        <div class="px-4">
            <div class="meter"><span :style="`width: ${progressPercent}%`"></span></div>
        </div>
        <div class="flex justify-between px-4 pt-2 pb-4 font-mono text-[11px] text-ink-3">
            <span x-text="formatBytes(loadedBytes) + ' / ' + formatBytes(totalBytes)"></span>
            <span x-text="speed + ' MB/s'"></span>
        </div>
        <div x-show="error" class="flex items-start gap-2 border-t border-line bg-danger/10 px-4 py-3 text-xs text-danger">
            <i class="ri-error-warning-line text-sm"></i>
            <span class="flex-1" x-text="error"></span>
            <button type="button" class="font-semibold underline" @click="downloading = false; error = null">{{ __('app.Fermer') }}</button>
        </div>
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
                        if (!response.ok) throw new Error(@js(__('app.Erreur de connexion au serveur')));
                        
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
                            window.dispatchEvent(new CustomEvent('toast-magic', { detail: { type: 'success', title: this.filename, message: @js(__('app.Téléchargement terminé avec succès !')) }}));
                        }, 1500);
                        
                    } catch (e) {
                        if (e.name !== 'AbortError') {
                            this.error = @js(__('app.Erreur lors du téléchargement :')) + ' ' + e.message;
                            console.error(e);
                        } else {
                            this.downloading = false;
                        }
                    }
                }
            }));
        });
    </script>
</div>
