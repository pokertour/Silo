<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Import d'une image disque depuis une URL, suivi en temps réel.
 */
class IsoImport extends Model
{
    public const PENDING = 'pending';

    public const DOWNLOADING = 'downloading';

    public const PROCESSING = 'processing';

    public const COMPLETED = 'completed';

    public const FAILED = 'failed';

    public const CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [self::PENDING, self::DOWNLOADING, self::PROCESSING];

    protected $fillable = [
        'user_id',
        'url',
        'name',
        'options',
        'status',
        'bytes_downloaded',
        'bytes_total',
        'bytes_per_second',
        'error',
        'iso_file_id',
        'started_at',
        'finished_at',
        'dismissed_at',
    ];

    protected $casts = [
        'options' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isoFile(): BelongsTo
    {
        return $this->belongsTo(IsoFile::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isRetryable(): bool
    {
        return in_array($this->status, [self::FAILED, self::CANCELLED], true);
    }

    /** Pourcentage téléchargé, ou null si la taille totale est inconnue. */
    public function progress(): ?int
    {
        if ($this->status === self::COMPLETED || $this->status === self::PROCESSING) {
            return 100;
        }

        if (! $this->bytes_total) {
            return null;
        }

        return (int) min(100, floor($this->bytes_downloaded / $this->bytes_total * 100));
    }

    /** Temps restant estimé, en secondes. */
    public function secondsRemaining(): ?int
    {
        if (! $this->bytes_total || ! $this->bytes_per_second || $this->status !== self::DOWNLOADING) {
            return null;
        }

        return (int) ceil(max(0, $this->bytes_total - $this->bytes_downloaded) / $this->bytes_per_second);
    }

    /** Nom d'hôte de l'URL source, pour l'affichage. */
    public function host(): string
    {
        return (string) (parse_url($this->url, PHP_URL_HOST) ?: $this->url);
    }
}
