<?php

use App\Models\Group;
use App\Models\IsoFile;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('un fichier sans groupe est téléchargeable par tout utilisateur connecté', function () {
    $user = User::factory()->create();
    $iso = IsoFile::factory()->create();

    expect($user->can('download', $iso))->toBeTrue();
});

test('un fichier de groupe est refusé à un non-membre', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    expect($user->can('download', $iso))->toBeFalse();
});

test('un fichier de groupe est autorisé à un membre', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $user->groups()->attach($group);
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    expect($user->can('download', $iso))->toBeTrue();
});

test('un admin peut tout faire', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $group = Group::factory()->create();
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    expect($admin->can('download', $iso))->toBeTrue()
        ->and($admin->can('share', $iso))->toBeTrue()
        ->and($admin->can('update', $iso))->toBeTrue()
        ->and($admin->can('delete', $iso))->toBeTrue();
});

test('seul le propriétaire peut partager, modifier et supprimer', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('share', $iso))->toBeTrue()
        ->and($owner->can('update', $iso))->toBeTrue()
        ->and($owner->can('delete', $iso))->toBeTrue()
        ->and($other->can('share', $iso))->toBeFalse()
        ->and($other->can('update', $iso))->toBeFalse()
        ->and($other->can('delete', $iso))->toBeFalse();
});

test('la route de download refuse un non-membre du groupe', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    $this->actingAs($user)
        ->get(route('download', $iso))
        ->assertForbidden();
});
