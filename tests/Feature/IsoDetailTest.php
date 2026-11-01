<?php

use App\Jobs\CalculateIsoChecksum;
use App\Livewire\IsoDetail;
use App\Models\Group;
use App\Models\IsoFile;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('la page de détail est accessible pour un fichier autorisé', function () {
    $user = User::factory()->create();
    $iso = IsoFile::factory()->create(['name' => 'Ubuntu Server']);

    $this->actingAs($user)
        ->get(route('isos.show', $iso))
        ->assertOk()
        ->assertSee('Ubuntu Server');
});

test('la page de détail refuse un non-membre du groupe', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    $this->actingAs($user)
        ->get(route('isos.show', $iso))
        ->assertForbidden();
});

test('le propriétaire peut supprimer un lien de partage', function () {
    $owner = User::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $owner->id]);
    $link = ShareLink::factory()->create(['iso_file_id' => $iso->id, 'user_id' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(IsoDetail::class, ['isoFile' => $iso])
        ->call('deleteShareLink', $link->id);

    expect(ShareLink::find($link->id))->toBeNull();
});

test('un non-propriétaire ne peut pas supprimer un lien de partage', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $owner->id]);
    $link = ShareLink::factory()->create(['iso_file_id' => $iso->id, 'user_id' => $owner->id]);

    Livewire::actingAs($other)
        ->test(IsoDetail::class, ['isoFile' => $iso])
        ->call('deleteShareLink', $link->id)
        ->assertForbidden();

    expect(ShareLink::find($link->id))->not->toBeNull();
});

test('le propriétaire peut relancer le calcul du checksum', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $owner->id, 'checksum' => 'abc']);

    Livewire::actingAs($owner)
        ->test(IsoDetail::class, ['isoFile' => $iso])
        ->call('recalculateChecksum');

    expect($iso->fresh()->checksum)->toBeNull();
    Queue::assertPushed(CalculateIsoChecksum::class);
});

test('les doublons sont listés sur la page de détail', function () {
    $user = User::factory()->create();
    $original = IsoFile::factory()->create(['name' => 'Original ISO', 'checksum' => 'same-hash']);
    $copy = IsoFile::factory()->create(['name' => 'Copy ISO', 'checksum' => 'same-hash']);

    $this->actingAs($user)
        ->get(route('isos.show', $copy))
        ->assertOk()
        ->assertSee('Original ISO');
});
