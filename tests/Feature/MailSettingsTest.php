<?php

use App\Livewire\Admin\Settings;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('l\'admin enregistre la configuration SMTP, mot de passe chiffré', function () {
    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('activeTab', 'mail')
        ->set('mail_enabled', true)
        ->set('mail_host', 'smtp.example.com')
        ->set('mail_port', 465)
        ->set('mail_encryption', 'ssl')
        ->set('mail_username', 'silo')
        ->set('mail_password', 's3cret-pass')
        ->set('mail_from_address', 'silo@example.com')
        ->set('mail_from_name', 'Silo')
        ->call('saveMail')
        ->assertHasNoErrors()
        ->assertSet('mail_password', '')
        ->assertSet('mail_password_set', true);

    $stored = SystemSetting::where('key', 'mail')->sole()->value;
    expect($stored['host'])->toBe('smtp.example.com')
        ->and($stored['password'])->not->toBe('s3cret-pass')
        ->and(Crypt::decryptString($stored['password']))->toBe('s3cret-pass');

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.mailers.smtp.password'))->toBe('s3cret-pass')
        ->and(config('mail.from.address'))->toBe('silo@example.com');
});

test('un mot de passe vide conserve celui enregistré', function () {
    MailSettings::save(['enabled' => true, 'host' => 'smtp.example.com', 'from_address' => 'a@example.com'], 'first');

    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('mail_host', 'smtp2.example.com')
        ->call('saveMail')
        ->assertHasNoErrors();

    $stored = MailSettings::stored();
    expect($stored['host'])->toBe('smtp2.example.com')
        ->and(Crypt::decryptString($stored['password']))->toBe('first');
});

test('le serveur et l\'expéditeur sont requis quand la configuration est activée', function () {
    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('mail_enabled', true)
        ->set('mail_host', '')
        ->set('mail_from_address', '')
        ->call('saveMail')
        ->assertHasErrors(['mail_host', 'mail_from_address']);
});

test('désactivée, la configuration du .env reste utilisée', function () {
    config(['mail.default' => 'log']);
    MailSettings::save(['enabled' => false, 'host' => 'smtp.example.com']);

    MailSettings::apply();

    expect(config('mail.default'))->toBe('log');
});

test('l\'admin peut envoyer un e-mail de test', function () {
    Mail::fake();

    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->call('sendTestMail')
        ->assertDispatched('toastMagic', status: 'success');
});

test('l\'onglet À propos affiche les informations système', function () {
    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('activeTab', 'about')
        ->assertSee('Laravel')
        ->assertSee(PHP_VERSION);
});
