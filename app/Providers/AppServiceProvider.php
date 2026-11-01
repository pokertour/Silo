<?php

namespace App\Providers;

use App\Auth\Ldap\LdapClient;
use App\Auth\Ldap\NativeLdapClient;
use App\Support\MailSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LdapClient::class, NativeLdapClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Les requêtes Livewire suivantes (/livewire/update) ne repassent pas
        // automatiquement par les middlewares de la route d'origine : on force
        // la ré-application des contrôles de rôle/permission Spatie.
        Livewire::addPersistentMiddleware([
            RoleMiddleware::class,
            PermissionMiddleware::class,
            RoleOrPermissionMiddleware::class,
        ]);

        Password::defaults(fn () => Password::min(10));

        // Paramètres SMTP définis par l'administrateur (sinon : .env)
        try {
            MailSettings::apply();
        } catch (\Throwable) {
            // Base indisponible (installation, migrations) : configuration du .env
        }

        // Mot de passe des liens de partage : limite par IP et par lien, pour
        // qu'un attaquant ne puisse pas contourner la limite en changeant d'IP.
        RateLimiter::for('share-password', function (Request $request) {
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(5)->by('share-pwd-ip|'.$token.'|'.$request->ip()),
                Limit::perHour(30)->by('share-pwd-token|'.$token),
            ];
        });
    }
}
