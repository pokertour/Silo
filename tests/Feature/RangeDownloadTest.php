<?php

use App\Models\IsoFile;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Storage::fake('local');
    Storage::disk('local')->put('iso_files/test.iso', 'ABCDEFGHIJ'); // 10 octets
    $this->iso = IsoFile::factory()->create(['file_path' => 'iso_files/test.iso', 'size' => 10]);
    $this->actingAs(User::factory()->create());
});

test('un téléchargement complet renvoie 200 avec Accept-Ranges', function () {
    $response = $this->get(route('download', $this->iso));

    $response->assertOk()
        ->assertHeader('Accept-Ranges', 'bytes')
        ->assertHeader('Content-Length', 10);

    expect($response->streamedContent())->toBe('ABCDEFGHIJ');
});

test('une Range request renvoie 206 avec la tranche demandée', function () {
    $response = $this->get(route('download', $this->iso), ['Range' => 'bytes=2-5']);

    $response->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 2-5/10')
        ->assertHeader('Content-Length', 4);

    expect($response->streamedContent())->toBe('CDEF');
});

test('une Range ouverte va jusqu\'à la fin du fichier', function () {
    $response = $this->get(route('download', $this->iso), ['Range' => 'bytes=7-']);

    $response->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 7-9/10');

    expect($response->streamedContent())->toBe('HIJ');
});

test('une Range hors limites renvoie 416', function () {
    $this->get(route('download', $this->iso), ['Range' => 'bytes=50-60'])
        ->assertStatus(416)
        ->assertHeader('Content-Range', 'bytes */10');
});

test('une reprise ne consomme pas de crédit max_downloads sur un lien partagé', function () {
    $share = ShareLink::factory()->create([
        'iso_file_id' => $this->iso->id,
        'max_downloads' => 1,
    ]);

    // Téléchargement initial : consomme le crédit
    $this->get(route('share.download', $share->token), ['Range' => 'bytes=0-4'])
        ->assertStatus(206);
    expect($share->fresh()->download_count)->toBe(1);

    // Reprise (offset > 0) dans la même session : ne compte pas, même limite atteinte
    $this->get(route('share.download', $share->token), ['Range' => 'bytes=5-'])
        ->assertStatus(206);
    expect($share->fresh()->download_count)->toBe(1);

    // Nouveau téléchargement complet : limite atteinte
    $this->get(route('share.download', $share->token))->assertStatus(410);
});

test('une Range sans téléchargement préalable consomme un crédit', function () {
    $share = ShareLink::factory()->create([
        'iso_file_id' => $this->iso->id,
        'max_downloads' => 1,
    ]);

    // Sans crédit consommé dans la session, une « reprise » compte comme un téléchargement
    $this->get(route('share.download', $share->token), ['Range' => 'bytes=1-'])
        ->assertStatus(206);
    expect($share->fresh()->download_count)->toBe(1);

    // Une autre session ne peut plus télécharger
    $this->flushSession();
    $this->get(route('share.download', $share->token), ['Range' => 'bytes=1-'])
        ->assertStatus(410);
});
