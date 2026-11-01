<?php

namespace App\Models;

use Database\Factories\StorageLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StorageLocation extends Model
{
    /** @use HasFactory<StorageLocationFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'driver', 'configuration', 'is_default'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'driver', 'is_default'])
            ->logOnlyDirty();
    }

    /**
     * La configuration contient des identifiants (clés S3, mots de passe
     * FTP/SFTP) : chiffrée en base avec APP_KEY et jamais sérialisée.
     */
    protected $hidden = ['configuration'];

    protected $casts = [
        'configuration' => 'encrypted:array',
        'is_default' => 'boolean',
    ];
}
