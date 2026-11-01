<?php

use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\IsoDownloadController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SsoController;
use App\Livewire\Admin\Groups;
use App\Livewire\Admin\Invitations;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\StatsDashboard;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Iso\Upload;
use App\Livewire\IsoDetail;
use App\Livewire\IsoList;
use App\Livewire\ShareLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', '/dashboard');

// Language switcher
Route::get('/lang/{locale}', function (string $locale) {
    $supported = ['fr', 'en', 'es', 'de'];
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
    }

    // N'accepte qu'une URL de retour interne : l'en-tête Referer est contrôlé
    // par le client et permettrait sinon une redirection ouverte.
    $previous = url()->previous();
    $target = parse_url($previous, PHP_URL_HOST) === request()->getHost()
        ? $previous
        : route('dashboard');

    return redirect()->to($target)->withHeaders(['Vary' => 'Accept-Language']);
})->name('lang.switch');

// Public Share Routes (accessible to guests)
Route::get('/s/{token}', [ShareController::class, 'show'])->name('share.show');
Route::post('/s/{token}', [ShareController::class, 'verify'])
    ->name('share.verify')
    ->middleware('throttle:share-password'); // anti-bruteforce (par IP et par lien), voir AppServiceProvider
Route::get('/s/{token}/download', [ShareController::class, 'download'])
    ->name('share.download')
    ->middleware('throttle:10,1');

// Removed public download route for security reasons. Direct downloads now require authentication.

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Authentification unique OpenID Connect / OAuth 2
    Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect')->middleware('throttle:20,1');
    Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback')->middleware('throttle:20,1');
});

// auth.session : invalide les autres sessions quand le mot de passe change
Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/isos', IsoList::class)->name('isos');
    Route::get('/isos/{isoFile}', IsoDetail::class)->name('isos.show');
    Route::get('/upload', Upload::class)->name('upload');
    Route::match(['get', 'post'], '/upload-chunk', [ChunkUploadController::class, 'upload'])
        ->middleware('throttle:600,1')
        ->name('upload.chunk');
    Volt::route('/profile', 'profile')->name('profile');
    Route::get('/share-links', ShareLinks::class)->name('share-links');
    Route::get('/download/{isoFile}', [IsoDownloadController::class, 'download'])->name('download');
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    })->name('logout');

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/stats', StatsDashboard::class)->name('stats');
        Route::get('/users', Users::class)->name('users');
        Route::get('/groups', Groups::class)->name('groups');
        Route::get('/invitations', Invitations::class)->name('invitations');
        Route::get('/share-links', App\Livewire\Admin\ShareLinks::class)->name('share-links');
        Route::get('/settings', Settings::class)->name('settings');
        Volt::route('/audit', 'admin.audit-logs')->name('audit');
    });
});
