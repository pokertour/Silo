<?php

use App\Livewire\IsoList;
use App\Models\Group;
use App\Models\IsoFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Storage::fake('local');
});

test('la suppression groupée ne supprime que les fichiers autorisés', function () {
    $owner = User::factory()->create();
    $mine = IsoFile::factory()->create(['user_id' => $owner->id]);
    $notMine = IsoFile::factory()->create();

    Livewire::actingAs($owner)
        ->test(IsoList::class)
        ->set('selected', [$mine->id, $notMine->id])
        ->call('deleteSelected');

    expect(IsoFile::find($mine->id))->toBeNull()
        ->and(IsoFile::find($notMine->id))->not->toBeNull();
});

test('un admin peut tout supprimer en masse', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $isos = IsoFile::factory()->count(3)->create();

    Livewire::actingAs($admin)
        ->test(IsoList::class)
        ->set('selected', $isos->pluck('id')->all())
        ->call('deleteSelected');

    expect(IsoFile::count())->toBe(0);
});

test('le déplacement groupé refuse un groupe étranger pour un non-admin', function () {
    $owner = User::factory()->create();
    $foreignGroup = Group::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(IsoList::class)
        ->set('selected', [$iso->id])
        ->set('bulkGroupId', $foreignGroup->id)
        ->call('assignGroupSelected')
        ->assertHasErrors('bulkGroupId');

    expect($iso->fresh()->group_id)->toBeNull();
});

test('le déplacement groupé vers son propre groupe fonctionne', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create();
    $owner->groups()->attach($group);
    $iso = IsoFile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(IsoList::class)
        ->set('selected', [$iso->id])
        ->set('bulkGroupId', $group->id)
        ->call('assignGroupSelected');

    expect($iso->fresh()->group_id)->toBe($group->id);
});
