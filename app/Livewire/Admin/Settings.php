<?php

namespace App\Livewire\Admin;

use App\Auth\Ldap\LdapAuthenticator;
use App\Auth\Ldap\LdapClient;
use App\Auth\Oidc\OidcClient;
use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\IsoFile;
use App\Models\StorageLocation;
use App\Models\SystemSetting;
use App\Support\LdapSettings;
use App\Support\MailSettings;
use App\Support\OidcSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
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

    // Mail Settings
    public bool $mail_enabled = false;

    public string $mail_host = '';

    public $mail_port = 587;

    public string $mail_encryption = 'tls';

    public string $mail_username = '';

    public string $mail_password = '';

    public bool $mail_password_set = false;

    public bool $mail_clear_password = false;

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    // Authentification externe
    public array $ldap = [];

    public string $ldap_bind_password = '';

    public bool $ldap_bind_password_set = false;

    public bool $ldap_clear_bind_password = false;

    public array $oidc = [];

    public string $oidc_client_secret = '';

    public bool $oidc_client_secret_set = false;

    public function mount()
    {
        $this->app_name = SystemSetting::get('app_name', config('app.name'));
        $this->primary_color = SystemSetting::get('primary_color', '#0071e3');

        $logoPath = SystemSetting::get('app_logo');
        if ($logoPath) {
            $this->current_logo_url = asset('storage/'.$logoPath);
        } else {
            $this->current_logo_url = asset('assets/logo/logo.svg');
        }

        $this->loadMailSettings();
        $this->loadAuthSettings();
    }

    private function loadAuthSettings(): void
    {
        $this->ldap = Arr::except(LdapSettings::stored(), ['bind_password']);
        $this->ldap_bind_password = '';
        $this->ldap_clear_bind_password = false;
        $this->ldap_bind_password_set = LdapSettings::hasSecret('bind_password');

        $this->oidc = Arr::except(OidcSettings::stored(), ['client_secret']);
        $this->oidc_client_secret = '';
        $this->oidc_client_secret_set = OidcSettings::hasSecret('client_secret');
    }

    public function saveLdap()
    {
        $required = ($this->ldap['enabled'] ?? false) ? 'required' : 'nullable';

        $this->validate([
            'ldap.host' => [$required, 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-:\[\]]+$/'],
            'ldap.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ldap.encryption' => ['required', 'in:none,starttls,ssl'],
            'ldap.base_dn' => [$required, 'nullable', 'string', 'max:500'],
            'ldap.bind_dn' => ['nullable', 'string', 'max:500'],
            'ldap_bind_password' => ['nullable', 'string', 'max:500'],
            'ldap.user_filter' => ['required', 'string', 'max:1000', 'regex:/\{login\}/', 'starts_with:('],
            'ldap.email_attribute' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'ldap.name_attribute' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'ldap.admin_group' => ['nullable', 'string', 'max:500'],
        ], [
            'ldap.user_filter.regex' => __('app.Le filtre doit contenir {login}.'),
        ]);

        LdapSettings::save(
            array_map(fn ($v) => is_string($v) ? trim($v) : $v, $this->ldap),
            ['bind_password' => $this->ldap_bind_password],
            $this->ldap_clear_bind_password ? ['bind_password'] : [],
        );
        $this->loadAuthSettings();

        $this->toastSaved();
    }

    public function testLdap(LdapClient $client)
    {
        // Teste les valeurs saisies, avec le mot de passe enregistré si le champ est vide
        $settings = array_merge(LdapAuthenticator::settings(), $this->ldap, array_filter(['bind_password' => $this->ldap_bind_password]));

        try {
            $count = $client->test($settings);

            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Connexion réussie !'),
                message: trans_choice('app.:count utilisateur trouvé|:count utilisateurs trouvés', $count, ['count' => $count >= 50 ? '50+' : $count])
            );
        } catch (\Throwable $e) {
            $this->toastError(__('app.Échec de la connexion'), $e);
        }
    }

    public function saveOidc()
    {
        $required = ($this->oidc['enabled'] ?? false) ? 'required' : 'nullable';
        $manual = ($this->oidc['issuer'] ?? '') === '';

        $this->validate([
            'oidc.label' => ['required', 'string', 'max:40'],
            'oidc.issuer' => ['nullable', 'url:https,http', 'max:500'],
            'oidc.client_id' => [$required, 'nullable', 'string', 'max:255'],
            'oidc_client_secret' => ['nullable', 'string', 'max:1000'],
            'oidc.scopes' => ['required', 'string', 'max:255'],
            'oidc.authorize_url' => [$manual ? $required : 'nullable', 'nullable', 'url:https,http', 'max:500'],
            'oidc.token_url' => [$manual ? $required : 'nullable', 'nullable', 'url:https,http', 'max:500'],
            'oidc.userinfo_url' => ['nullable', 'url:https,http', 'max:500'],
            'oidc.email_claim' => ['required', 'string', 'max:64'],
            'oidc.name_claim' => ['required', 'string', 'max:64'],
            'oidc.allowed_domains' => ['nullable', 'string', 'max:500'],
            'oidc.admin_claim' => ['nullable', 'string', 'max:64'],
            'oidc.admin_value' => ['nullable', 'string', 'max:255'],
        ]);

        OidcSettings::save(
            array_map(fn ($v) => is_string($v) ? trim($v) : $v, $this->oidc),
            ['client_secret' => $this->oidc_client_secret],
        );
        $this->loadAuthSettings();

        $this->toastSaved();
    }

    public function testOidc(OidcClient $client)
    {
        try {
            $endpoints = $client->endpoints(array_merge($client->settings(), $this->oidc), fresh: true);

            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Connexion réussie !'),
                message: e(parse_url($endpoints['authorization_endpoint'], PHP_URL_HOST))
            );
        } catch (\Throwable $e) {
            $this->toastError(__('app.Échec de la connexion'), $e);
        }
    }

    private function toastSaved(): void
    {
        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Paramètres mis à jour.')
        );
    }

    private function toastError(string $title, \Throwable $e): void
    {
        report($e);

        $this->dispatch('toastMagic',
            status: 'error',
            title: __('app.Erreur'),
            // Le toast est rendu en HTML : on échappe le message d'erreur
            message: $title.' : '.e(Str::limit($e->getMessage(), 200))
        );
    }

    private function loadMailSettings(): void
    {
        $mail = MailSettings::stored();

        $this->mail_enabled = (bool) $mail['enabled'];
        $this->mail_host = (string) $mail['host'];
        $this->mail_port = (int) $mail['port'];
        $this->mail_encryption = (string) $mail['encryption'];
        $this->mail_username = (string) $mail['username'];
        $this->mail_from_address = (string) $mail['from_address'];
        $this->mail_from_name = (string) $mail['from_name'];
        $this->mail_password = '';
        $this->mail_clear_password = false;
        $this->mail_password_set = MailSettings::hasPassword();
    }

    public function saveMail()
    {
        $required = $this->mail_enabled ? 'required' : 'nullable';

        $this->validate([
            'mail_host' => [$required, 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-:\[\]]+$/'],
            'mail_port' => [$required, 'integer', 'min:1', 'max:65535'],
            'mail_encryption' => ['required', 'in:tls,ssl,none'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => [$required, 'nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:100'],
        ]);

        MailSettings::save([
            'enabled' => $this->mail_enabled,
            'host' => trim($this->mail_host),
            'port' => (int) ($this->mail_port ?: 587),
            'encryption' => $this->mail_encryption,
            'username' => trim($this->mail_username),
            'from_address' => trim($this->mail_from_address),
            'from_name' => trim($this->mail_from_name),
        ], $this->mail_password, $this->mail_clear_password);

        MailSettings::apply();
        $this->loadMailSettings();

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Paramètres e-mail enregistrés.')
        );
    }

    public function sendTestMail()
    {
        MailSettings::apply();

        $user = auth()->user();

        try {
            Mail::raw(
                __('app.Si vous lisez ce message, l\'envoi d\'e-mails depuis :app fonctionne.', ['app' => config('app.name')]),
                fn ($message) => $message->to($user->email)->subject(__('app.:app — e-mail de test', ['app' => config('app.name')]))
            );

            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Succès'),
                message: __('app.E-mail de test envoyé à :email.', ['email' => e($user->email)])
            );
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('toastMagic',
                status: 'error',
                title: __('app.Erreur'),
                // Le toast est rendu en HTML : on échappe le message d'erreur
                message: __('app.Échec de l\'envoi').': '.e(Str::limit($e->getMessage(), 200))
            );
        }
    }

    /** Informations système affichées dans l'onglet « À propos ». */
    private function systemInfo(): array
    {
        $mail = MailSettings::stored();
        $mailer = config('mail.default');

        $database = DB::connection()->getDriverName();
        try {
            $database .= ' '.Str::before((string) DB::connection()->getServerVersion(), '-');
        } catch (\Throwable) {
            // version indisponible (ex. driver sans support)
        }

        $default = StorageLocation::where('is_default', true)->first();
        $free = null;
        $root = $default && $default->driver === 'local'
            ? ($default->configuration['root'] ?? null)
            : storage_path('app/private');
        if ($root && is_dir($root)) {
            $free = @disk_free_space($root) ?: null;
        }

        return [
            'version' => config('version.current'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database' => trim($database),
            'cache' => config('cache.default'),
            'queue' => config('queue.default'),
            'mail' => $mailer === 'smtp' ? 'SMTP · '.config('mail.mailers.smtp.host') : $mailer,
            'mail_source' => $mail['enabled'] ? 'admin' : 'env',
            'environment' => app()->environment(),
            'files' => IsoFile::count(),
            'size' => (int) IsoFile::sum('size'),
            'free' => $free !== null ? Number::fileSize($free, precision: 1) : null,
            'storages' => StorageLocation::count(),
        ];
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
            'system' => $this->activeTab === 'about' ? $this->systemInfo() : null,
        ]);
    }
}
