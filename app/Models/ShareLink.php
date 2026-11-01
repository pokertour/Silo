<?php

namespace App\Models;

use Database\Factories\ShareLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ShareLink extends Model
{
    /** @use HasFactory<ShareLinkFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['iso_file_id', 'user_id', 'max_downloads', 'expires_at'])
            ->logOnlyDirty();
    }

    protected $fillable = [
        'iso_file_id',
        'user_id',
        'token',
        'password',
        'max_downloads',
        'download_count',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isoFile()
    {
        return $this->belongsTo(IsoFile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid()
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_downloads && $this->download_count >= $this->max_downloads) {
            return false;
        }

        return true;
    }
}
