<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\UpdateLastActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ne faire confiance aux en-têtes X-Forwarded-* que s'ils viennent d'un
        // reverse proxy connu : sinon n'importe quel client peut usurper son IP
        // et contourner les limitations de débit (login, mots de passe de partage…).
        // Par défaut : réseaux privés (proxy Docker / LAN). « * » reste possible
        // via TRUSTED_PROXIES si l'application n'est joignable que via le proxy.
        $trustedProxies = env('TRUSTED_PROXIES', '127.0.0.1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,::1,fc00::/7');
        $middleware->trustProxies(
            at: $trustedProxies === '*' ? '*' : array_filter(array_map('trim', explode(',', $trustedProxies)))
        );

        $middleware->web(append: [
            SetLocale::class,
            UpdateLastActivity::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
