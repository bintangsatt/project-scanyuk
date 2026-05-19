<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model_path',
        'thumbnail',
        'config_schema',
        'placeholders',
    ];

    protected $casts = [
        'config_schema' => 'array', // Otomatis decode JSON ke array
        'placeholders'  => 'array',
    ];

    /**
     * Relasi ke project yang menggunakan template ini
     */
    public function arProjects(): HasMany
    {
        return $this->hasMany(ArProject::class);
    }

    /**
     * URL publik file model .glb
     */
    public function getModelUrlAttribute(): string
    {
        return Storage::url($this->model_path);
    }

    /**
     * URL publik thumbnail
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? Storage::url($this->thumbnail) : null;
    }
}
