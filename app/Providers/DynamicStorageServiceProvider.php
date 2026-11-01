<?php

namespace App\Providers;

use App\Models\StorageLocation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DynamicStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('storage_locations')) {
                $locations = StorageLocation::all();

                foreach ($locations as $location) {
                    if ($location->configuration) {
                        config(["filesystems.disks.{$location->name}" => array_merge(
                            ['driver' => $location->driver],
                            $location->configuration
                        )]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log or ignore if DB is not ready during initial setup
        }
    }
}
