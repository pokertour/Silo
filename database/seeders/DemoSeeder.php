<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Invitation;
use App\Models\IsoFile;
use App\Models\ShareLink;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\IsoImportFinished;
use App\Notifications\ShareLinkDownloaded;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Données de démonstration réalistes, utilisées pour générer les captures
 * d'écran du site GitHub Pages (voir .github/workflows/pages.yml).
 *
 * Usage : php artisan db:seed --class=DemoSeeder
 * Ne s'exécute jamais en production.
 */
class DemoSeeder extends Seeder
{
    private const GB = 1024 ** 3;

    private const MB = 1024 ** 2;

    public function run(): void
    {
        if (App::isProduction()) {
            $this->command?->error('DemoSeeder refuse de s\'exécuter en production.');

            return;
        }

        App::setLocale('en');
        $now = Carbon::now();

        $this->call(DatabaseSeeder::class);
        $admin = User::role('admin')->firstOrFail();
        $admin->update(['name' => 'Sarah Admin']);

        $users = collect([
            'alice' => 'Alice Martin',
            'bob' => 'Bob Chen',
            'carla' => 'Carla Rossi',
            'david' => 'David Kim',
            'emma' => 'Emma Novak',
        ])->map(fn ($name, $key) => User::create([
            'name' => $name,
            'email' => "{$key}@silo.demo",
            'password' => Str::password(24),
        ]))->put('admin', $admin);

        foreach ($users as $index => $user) {
            $user->forceFill(['last_activity_at' => $now->copy()->subMinutes(crc32($index) % 600)])->save();
        }

        $groups = [
            'infra' => Group::create(['name' => 'Infrastructure', 'description' => 'Hypervisors, servers and network appliances']),
            'security' => Group::create(['name' => 'Security Team', 'description' => 'Pentest and forensic tooling']),
            'workstations' => Group::create(['name' => 'Workstations', 'description' => 'Desktop images for the IT helpdesk']),
        ];
        $groups['infra']->users()->attach([$users['alice']->id, $users['bob']->id, $users['admin']->id]);
        $groups['security']->users()->attach([$users['carla']->id, $users['admin']->id]);
        $groups['workstations']->users()->attach([$users['david']->id, $users['emma']->id, $users['bob']->id]);

        $tags = collect([
            'linux' => '#22C55E', 'windows' => '#3B82F6', 'server' => '#6366F1', 'desktop' => '#EC4899',
            'lts' => '#F59E0B', 'hypervisor' => '#8B5CF6', 'rescue' => '#EF4444', 'cloud' => '#06B6D4',
        ])->map(fn ($color, $name) => Tag::create(['name' => $name, 'color' => $color]));

        $files = [
            ['Ubuntu Server 24.04.1 LTS', '24.04.1', 'amd64', 'Linux', 2.6 * self::GB, 'iso', 'alice', null, ['linux', 'server', 'lts'], 'Official Canonical release, used as the base for every new VM template.', 34],
            ['Debian 12.7 netinst', '12.7', 'amd64', 'Linux', 631 * self::MB, 'iso', 'bob', null, ['linux', 'server'], 'Minimal network installer.', 30],
            ['Rocky Linux 9.4 DVD', '9.4', 'x86_64', 'Linux', 10.2 * self::GB, 'iso', 'bob', 'infra', ['linux', 'server'], null, 27],
            ['Windows Server 2022 Datacenter', '21H2', 'x64', 'Windows', 4.7 * self::GB, 'iso', 'admin', 'infra', ['windows', 'server'], 'Volume licensing media — activation key in the vault.', 25],
            ['Proxmox VE 8.2', '8.2-2', 'amd64', 'Linux', 1.3 * self::GB, 'iso', 'alice', 'infra', ['hypervisor', 'linux'], 'Cluster nodes installer.', 22],
            ['Windows 11 Enterprise 23H2', '23H2', 'x64', 'Windows', 6.2 * self::GB, 'iso', 'david', 'workstations', ['windows', 'desktop'], 'Helpdesk master image with latest cumulative update.', 19],
            ['Kali Linux 2024.3', '2024.3', 'amd64', 'Linux', 4.1 * self::GB, 'iso', 'carla', 'security', ['linux'], 'Installer image for the red team laptops.', 16],
            ['Debian 12 generic cloud', '12', 'amd64', 'Linux', 412 * self::MB, 'qcow2', 'alice', 'infra', ['linux', 'cloud'], 'Cloud-init ready disk for Proxmox templates.', 14],
            ['TrueNAS SCALE 24.04', '24.04.2', 'amd64', 'Linux', 1.6 * self::GB, 'iso', 'bob', 'infra', ['server'], null, 12],
            ['Windows PE 10 rescue', '10.0.22621', 'x64', 'Windows', 498 * self::MB, 'wim', 'emma', 'workstations', ['windows', 'rescue'], 'Boot image for the PXE server.', 9],
            ['GParted Live 1.6.0', '1.6.0-3', 'amd64', 'Linux', 547 * self::MB, 'iso', 'emma', null, ['rescue', 'linux'], 'Partition editor live media.', 7],
            ['Fedora Workstation 40', '40', 'x86_64', 'Linux', 2.3 * self::GB, 'iso', 'david', 'workstations', ['linux', 'desktop'], null, 5],
            ['pfSense CE 2.7.2', '2.7.2', 'amd64', 'FreeBSD', 875 * self::MB, 'img', 'alice', 'infra', ['server'], 'Firewall appliance memstick image.', 3],
            ['Alpine Linux 3.20 virt', '3.20.3', 'x86_64', 'Linux', 63 * self::MB, 'iso', 'admin', null, ['linux', 'cloud'], 'Lightweight image for containers and test VMs.', 1],
        ];

        $isos = [];
        foreach ($files as $i => [$name, $version, $arch, $os, $size, $ext, $owner, $group, $tagNames, $description, $daysAgo]) {
            $owner = $users[$owner];
            Carbon::setTestNow($now->copy()->subDays($daysAgo)->subMinutes($i * 37));
            // L'auteur apparaît dans le journal d'activité
            Auth::setUser($owner);

            $checksum = hash('sha256', $name);
            $isos[$name] = $iso = IsoFile::create([
                'name' => $name,
                'version' => $version,
                'architecture' => $arch,
                'os_type' => $os,
                'description' => $description,
                'file_path' => 'iso_files/'.Str::random(40).'.'.$ext,
                'disk' => 'local_private',
                'size' => (int) $size,
                'checksum' => $checksum,
                'expected_checksum' => $i % 3 === 0 ? $checksum : null,
                'checksum_verified' => $i % 3 === 0 ? true : null,
                'user_id' => $owner->id,
                'group_id' => $group ? $groups[$group]->id : null,
            ]);
            $iso->tags()->sync($tags->only($tagNames)->pluck('id'));
        }

        // Quelques modifications pour enrichir le journal d'activité
        Carbon::setTestNow($now->copy()->subDays(2));
        Auth::setUser($users['alice']);
        $isos['Ubuntu Server 24.04.1 LTS']->update(['description' => 'Official Canonical release (point release 1), used as the base for every new VM template.']);
        Carbon::setTestNow($now->copy()->subHours(5));
        Auth::setUser($users['admin']);
        $isos['Rocky Linux 9.4 DVD']->update(['description' => 'Full DVD for offline installs in the lab.']);

        // Liens de partage
        $shares = [
            ['Ubuntu Server 24.04.1 LTS', 'alice', null, null, 4, 6, 'ubuntu'],
            ['Windows 11 Enterprise 23H2', 'david', 'helpdesk-2024', 10, 7, 3, null],
            ['Proxmox VE 8.2', 'alice', null, 5, 2, 8, null],
            ['Kali Linux 2024.3', 'carla', 'red-team-only', 1, 0, 2, null],
            ['GParted Live 1.6.0', 'emma', null, null, 12, 20, null],
        ];
        $publicShareToken = null;
        foreach ($shares as [$isoName, $owner, $password, $max, $count, $expiresInDays, $key]) {
            Carbon::setTestNow($now->copy()->subDays(3));
            Auth::setUser($users[$owner]);
            $link = ShareLink::create([
                'iso_file_id' => $isos[$isoName]->id,
                'user_id' => $users[$owner]->id,
                'token' => Str::random(32),
                'password' => $password,
                'max_downloads' => $max,
                'download_count' => $count,
                'expires_at' => $now->copy()->addDays($expiresInDays),
            ]);
            $publicShareToken ??= $key ? $link->token : null;
        }

        Carbon::setTestNow($now);
        Auth::forgetUser();

        // Invitations en attente
        foreach (['frank@silo.demo', 'grace@silo.demo'] as $i => $email) {
            Invitation::create([
                'email' => $email,
                'token' => 'ISO-'.strtoupper(Str::random(24)),
                'expires_at' => $now->copy()->addDays(5 + $i * 2),
            ]);
        }

        // Notifications de l'administrateur
        $ubuntuShare = ShareLink::where('iso_file_id', $isos['Ubuntu Server 24.04.1 LTS']->id)->first();
        $admin->notify(new IsoImportFinished(true, 'Alpine Linux 3.20 virt', $isos['Alpine Linux 3.20 virt']->id));
        $admin->notify(new ShareLinkDownloaded($ubuntuShare));

        // Jeton utilisé par le script de captures pour la page publique de partage
        file_put_contents(storage_path('app/demo-share-token'), $publicShareToken);

        $this->command?->info('Données de démonstration créées.');
    }
}
