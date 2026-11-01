<?php

use App\Jobs\CalculateIsoChecksum;
use App\Models\IsoFile;
use App\Notifications\DuplicateIsoDetected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('le job calcule le checksum sha-256 en streaming', function () {
    Storage::disk('local')->put('iso_files/a.iso', 'hello world');
    $iso = IsoFile::factory()->create(['file_path' => 'iso_files/a.iso']);

    (new CalculateIsoChecksum($iso))->handle();

    expect($iso->fresh()->checksum)->toBe(hash('sha256', 'hello world'));
});

test('un doublon déclenche une notification à l\'uploadeur', function () {
    Notification::fake();

    Storage::disk('local')->put('iso_files/a.iso', 'same content');
    Storage::disk('local')->put('iso_files/b.iso', 'same content');

    $original = IsoFile::factory()->create([
        'file_path' => 'iso_files/a.iso',
        'checksum' => hash('sha256', 'same content'),
    ]);
    $duplicate = IsoFile::factory()->create(['file_path' => 'iso_files/b.iso']);

    (new CalculateIsoChecksum($duplicate))->handle();

    Notification::assertSentTo($duplicate->user, DuplicateIsoDetected::class);
});

test('aucune notification sans doublon', function () {
    Notification::fake();

    Storage::disk('local')->put('iso_files/a.iso', 'unique content');
    $iso = IsoFile::factory()->create(['file_path' => 'iso_files/a.iso']);

    (new CalculateIsoChecksum($iso))->handle();

    Notification::assertNothingSent();
});
