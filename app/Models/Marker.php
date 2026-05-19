<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Marker extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'mind_path',
        'status',
        'error_message',
    ];

    // Status constants untuk kemudahan referensi
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY      = 'ready';
    const STATUS_FAILED     = 'failed';

    /**
     * Relasi ke ar_projects yang menggunakan marker ini
     */
    public function arProjects(): HasMany
    {
        return $this->hasMany(ArProject::class);
    }

    /**
     * URL publik gambar marker
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    /**
     * URL publik file .mind
     */
    public function getMindUrlAttribute(): ?string
    {
        return $this->mind_path ? Storage::url($this->mind_path) : null;
    }

    /**
     * Apakah marker sudah siap digunakan?
     */
    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }
}
