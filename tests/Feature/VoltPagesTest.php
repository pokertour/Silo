<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('la page profil se charge', function () {
    $this->actingAs(User::factory()->create())
        ->get('/profile')
        ->assertOk();
});

test('la page audit admin se charge', function () {
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/audit')
        ->assertOk();
});
