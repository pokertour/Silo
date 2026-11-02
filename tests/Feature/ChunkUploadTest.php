<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('un identifiant avec traversée de répertoire est rejeté', function () {
    $this->post(route('upload.chunk'), [
        'resumableIdentifier' => '../../evil',
        'resumableChunkNumber' => 1,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
        'file' => UploadedFile::fake()->create('chunk.part', 10),
    ])->assertSessionHasErrors('resumableIdentifier');
});

test('un numéro de chunk non numérique est rejeté', function () {
    $this->post(route('upload.chunk'), [
        'resumableIdentifier' => 'valid_id_123',
        'resumableChunkNumber' => '../1',
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
        'file' => UploadedFile::fake()->create('chunk.part', 10),
    ])->assertSessionHasErrors('resumableChunkNumber');
});

test('un chunk valide est stocké', function () {
    $this->post(route('upload.chunk'), [
        'resumableIdentifier' => 'valid_id_123',
        'resumableChunkNumber' => 1,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
        'file' => UploadedFile::fake()->create('chunk.part', 10),
    ])->assertOk()->assertJson(['status' => 'chunk saved']);

    Storage::disk('local')->assertExists("chunks/{$this->user->id}/valid_id_123/1.part");
});

test('le dernier chunk assemble le fichier final', function () {
    foreach ([1, 2] as $n) {
        $response = $this->post(route('upload.chunk'), [
            'resumableIdentifier' => 'assemble_test',
            'resumableChunkNumber' => $n,
            'resumableTotalChunks' => 2,
            'resumableFilename' => 'test.iso',
            'file' => UploadedFile::fake()->create("chunk{$n}.part", 10),
        ]);
    }

    $response->assertOk()->assertJson(['status' => 'done', 'original_name' => 'test.iso']);
    expect($response->json('temp_path'))->toStartWith("temp_iso/{$this->user->id}_");
    Storage::disk('local')->assertExists($response->json('temp_path'));
    Storage::disk('local')->assertMissing("chunks/{$this->user->id}/assemble_test/1.part");
});

test('les chunks d\'un utilisateur sont isolés de ceux des autres', function () {
    // Un autre utilisateur envoie un chunk avec le même identifiant (prévisible)
    $this->actingAs(User::factory()->create())->post(route('upload.chunk'), [
        'resumableIdentifier' => 'shared_id',
        'resumableChunkNumber' => 1,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'evil.iso',
        'file' => UploadedFile::fake()->create('chunk.part', 10),
    ])->assertOk();

    // Le premier utilisateur ne voit pas ce chunk
    $this->actingAs($this->user)->get(route('upload.chunk', [
        'resumableIdentifier' => 'shared_id',
        'resumableChunkNumber' => 1,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
    ]))->assertNotFound();
});

test('un numéro de chunk supérieur au total est rejeté', function () {
    $this->post(route('upload.chunk'), [
        'resumableIdentifier' => 'valid_id_123',
        'resumableChunkNumber' => 3,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
        'file' => UploadedFile::fake()->create('chunk.part', 10),
    ])->assertSessionHasErrors('resumableChunkNumber');
});

test('la route upload-chunk exige une authentification', function () {
    auth()->logout();

    $this->post(route('upload.chunk'), [])->assertRedirect(route('login'));
});

test('le test d\'un chunk absent répond 204 et non 404', function () {
    $query = [
        'resumableIdentifier' => 'probe_id',
        'resumableChunkNumber' => 1,
        'resumableTotalChunks' => 2,
        'resumableFilename' => 'test.iso',
    ];

    $this->get(route('upload.chunk', $query))->assertNoContent();

    Storage::disk('local')->put("chunks/{$this->user->id}/probe_id/1.part", 'data');

    $this->get(route('upload.chunk', $query))->assertOk()->assertJson(['status' => 'exists']);
});
