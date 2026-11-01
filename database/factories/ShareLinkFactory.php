<?php

namespace Database\Factories;

use App\Models\IsoFile;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShareLink>
 */
class ShareLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'iso_file_id' => IsoFile::factory(),
            'user_id' => User::factory(),
            'token' => Str::random(32),
            'password' => null,
            'max_downloads' => null,
            'download_count' => 0,
            'expires_at' => null,
        ];
    }
}
