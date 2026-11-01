<?php

namespace Database\Seeders;

use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Admin User : mot de passe fourni via ADMIN_PASSWORD, sinon généré
        // aléatoirement et affiché une seule fois (jamais de mot de passe par défaut).
        $email = strtolower(env('ADMIN_EMAIL', 'admin@silo.local'));
        $admin = User::where('email', $email)->first();

        if (! $admin) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(20);

            $admin = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => $password,
            ]);

            if (! env('ADMIN_PASSWORD')) {
                $this->command?->warn("Compte administrateur créé : {$email}");
                $this->command?->warn("Mot de passe généré : {$password}");
                $this->command?->warn('Notez-le maintenant et changez-le après la première connexion.');
            }
        }

        $admin->assignRole($adminRole);

        // Stockage par défaut : PRIVÉ. Les ISOs ne doivent jamais être placés
        // sous storage/app/public, exposé publiquement via le lien /storage.
        StorageLocation::firstOrCreate(
            ['name' => 'local_private'],
            [
                'driver' => 'local',
                'configuration' => [
                    'root' => storage_path('app/private'),
                    'visibility' => 'private',
                    'throw' => false,
                ],
                'is_default' => ! StorageLocation::where('is_default', true)->exists(),
            ]
        );
    }
}
