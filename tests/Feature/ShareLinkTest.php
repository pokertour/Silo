<?php

use App\Models\ShareLink;

test('un lien sans contrainte est valide', function () {
    $share = ShareLink::factory()->create();

    expect($share->isValid())->toBeTrue();
});

test('un lien expiré est invalide et renvoie 410', function () {
    $share = ShareLink::factory()->create(['expires_at' => now()->subDay()]);

    expect($share->isValid())->toBeFalse();

    $this->get(route('share.show', $share->token))->assertStatus(410);
});

test('un lien ayant atteint sa limite de téléchargements est invalide', function () {
    $share = ShareLink::factory()->create([
        'max_downloads' => 3,
        'download_count' => 3,
    ]);

    expect($share->isValid())->toBeFalse();

    $this->get(route('share.download', $share->token))->assertStatus(410);
});

test('un lien protégé par mot de passe demande le mot de passe', function () {
    $share = ShareLink::factory()->create(['password' => 'secret123']);

    $this->get(route('share.show', $share->token))
        ->assertOk()
        ->assertViewIs('share.password');
});

test('un mauvais mot de passe est rejeté, un bon donne accès', function () {
    $share = ShareLink::factory()->create(['password' => 'secret123']);

    $this->post(route('share.verify', $share->token), ['password' => 'wrong'])
        ->assertSessionHasErrors('password');

    $this->post(route('share.verify', $share->token), ['password' => 'secret123'])
        ->assertRedirect(route('share.show', $share->token));

    $this->get(route('share.show', $share->token))
        ->assertOk()
        ->assertViewIs('share.show');
});

test('le téléchargement d\'un lien protégé sans session renvoie 403', function () {
    $share = ShareLink::factory()->create(['password' => 'secret123']);

    $this->get(route('share.download', $share->token))->assertForbidden();
});
