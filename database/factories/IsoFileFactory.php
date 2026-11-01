<?php

namespace Database\Factories;

use App\Models\IsoFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<IsoFile>
 */
class IsoFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'file_path' => 'iso_files/'.Str::random(40).'.iso',
            'disk' => 'local',
            'size' => fake()->numberBetween(1024, 10_000_000),
            'checksum' => null,
            'user_id' => User::factory(),
            'group_id' => null,
        ];
    }
}
