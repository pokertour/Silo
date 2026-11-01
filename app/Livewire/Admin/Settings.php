<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\IsoFile;
use App\Models\StorageLocation;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Settings extends Component
{
    use AuthorizesAdmin, WithFileUploads;

    // App Settings
    public $app_name;

    public $primary_color;

    public $logo;

    public $current_logo_url;

    public $activeTab = 'general';

    // Storage Settings (from Storage.php)
    public $storage_name = '';

    public $storage_driver = 'local';

    public $storage_is_default = false;

    public $config_root = '';

    public $config_key = '';

    public $config_secret = '';

    public $config_region = '';

    public $config_bucket = '';

    public $config_host = '';

    public $config_username = '';

    public $config_password = '';

    public $config_port = '';

    public $config_endpoint = '';

    public function mount()
    {
        $this->app_name = SystemSetting::get('app_name', config('app.name'));
        $this->primary_color = SystemSetting::get('primary_color', '#6366f1');

        $logoPath = SystemSetting::get('app_logo');
        if ($logoPath) {
            $this->current_logo_url = asset('storage/'.$logoPath);
        } else {
            $this->current_logo_url = asset('assets/logo/logo.svg');
        }
    }

    public function deleteLogo()
    {
        $oldLogo = SystemSetting::get('app_logo');
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
            SystemSetting::set('app_logo', null);
        }

        $this->logo = null;
        $this->current_logo_url = asset('assets/logo/logo.svg');

        $this->dispatch('toastMagic',
            status: 'info',
            title: __('app.Info'),
            message: __('app.Logo supprimé. Retour au logo par défaut.')
        );
    }

    public function saveSettings()
    {
        $this->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // Pas de SVG : le logo est servi publiquement et un SVG peut embarquer du JavaScript
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        SystemSetting::set('app_name', $this->app_name);
        SystemSetting::set('primary_color', $this->primary_color);

        if ($this->logo) {
            $oldLogo = SystemSetting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $this->logo->store('logos', 'public');
            SystemSetting::set('app_logo', $path);
            $this->current_logo_url = asset('storage/'.$path);
            $this->logo = null;
        }

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Paramètres mis à jour.')
        );
    }

    public function saveStorage()
    {
        $this->validate([
            // Le nom sert de clé de disque dans config('filesystems.disks') :
            // il ne doit pas pouvoir écraser un disque système (local, public…).
            'storage_name' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:storage_locations,name', 'not_in:local,public,s3'],
            'storage_driver' => 'required|in:local,nfs,s3,ftp,sftp',
            'config_root' => 'nullable|string|max:1024',
            'config_port' => 'nullable|integer|min:1|max:65535',
            'config_endpoint' => 'nullable|url:http,https|max:2048',
        ]);

        $config = [];
        $driverName = $this->storage_driver;

        if ($this->storage_driver === 'local' || $this->storage_driver === 'nfs') {
            $config = [
                'root' => empty($this->config_root) ? storage_path('app/'.$this->storage_name) : $this->config_root,
                'visibility' => 'private',
                'display_driver' => $this->storage_driver,
            ];
            $driverName = 'local';
        } elseif ($this->storage_driver === 's3') {
            $config = [
                'key' => $this->config_key,
                'secret' => $this->config_secret,
                'region' => $this->config_region,
                'bucket' => $this->config_bucket,
                'endpoint' => $this->config_endpoint,
                'display_driver' => $this->storage_driver,
            ];
        } elseif (in_array($this->storage_driver, ['ftp', 'sftp'])) {
            $config = [
                'host' => $this->config_host,
                'username' => $this->config_username,
                'password' => $this->config_password,
                'port' => empty($this->config_port) ? ($this->storage_driver === 'ftp' ? 21 : 22) : $this->config_port,
                'root' => empty($this->config_root) ? '/' : $this->config_root,
                'display_driver' => $this->storage_driver,
            ];
        }

        if ($this->storage_is_default) {
            StorageLocation::where('is_default', true)->update(['is_default' => false]);
        }

        StorageLocation::create([
            'name' => $this->storage_name,
            'driver' => $driverName,
            'configuration' => $config,
            'is_default' => StorageLocation::count() === 0 ? true : $this->storage_is_default,
        ]);

        $this->reset(['storage_name', 'storage_driver', 'storage_is_default', 'config_root', 'config_key', 'config_secret', 'config_region', 'config_bucket', 'config_endpoint', 'config_host', 'config_username', 'config_password', 'config_port']);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Stockage ajouté.')
        );
    }

    public function setAsDefault($id)
    {
        $location = StorageLocation::findOrFail($id);
        StorageLocation::where('is_default', true)->update(['is_default' => false]);
        $location->update(['is_default' => true]);
    }

    public function deleteLocation($id)
    {
        $location = StorageLocation::find($id);
        if (! $location || $location->is_default) {
            return;
        }

        // Les ISOs référencent le stockage par son nom : le supprimer les
        // rendrait inaccessibles.
        if (IsoFile::where('disk', $location->name)->exists()) {
            $this->dispatch('toastMagic',
                status: 'error',
                title: __('app.Erreur'),
                message: __('app.Ce stockage contient encore des fichiers ISO.')
            );

            return;
        }

        $location->delete();
    }

    public function testConnection($id)
    {
        $location = StorageLocation::findOrFail($id);
        $diskName = 'test_'.$location->id;

        config(["filesystems.disks.{$diskName}" => array_merge(
            ['driver' => $location->driver],
            $location->configuration ?? []
        )]);

        try {
            // Un simple check de fichier/répertoire racine suffit pour tester la connexion
            Storage::disk($diskName)->files('/');

            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Succès'),
                message: __('app.Connexion réussie !')
            );
        } catch (\Exception $e) {
            report($e);

            $this->dispatch('toastMagic',
                status: 'error',
                title: __('app.Erreur'),
                // Le toast est rendu en HTML (innerHTML) : on échappe le message d'erreur
                message: __('app.Échec de la connexion').': '.e(str($e->getMessage())->limit(200))
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'locations' => StorageLocation::latest()->get(),
        ]);
    }
}
