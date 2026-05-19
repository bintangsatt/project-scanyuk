<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArProject extends Model
{
    use HasFactory;

    protected $table = 'ar_projects';

    protected $fillable = [
        'marker_id',
        'type',
        'template_id',
        'model_path',
        'config',
        'scale',
        'position',
        'rotation',
        'status',
    ];

    protected $casts = [
        'config'   => 'array',
        'position' => 'array',
        'rotation' => 'array',
        'scale'   => 'float',
    ];

    /**
     * Default values untuk position dan rotation
     */
    protected $attributes = [
        'scale'    => 1.0,
        'position' => '[0,0,0]',
        'rotation' => '[0,0,0]',
    ];

    /**
     * Relasi ke marker
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(Marker::class);
    }

    /**
     * Relasi ke template (nullable)
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * URL model yang digunakan (template atau custom)
     */
    public function getModelUrlAttribute(): string
    {
        if ($this->type === 'template' && $this->template) {
            return Storage::url($this->template->model_path);
        }
        return $this->model_path ? Storage::url($this->model_path) : '';
    }

    /**
     * Apakah project ini menggunakan template?
     */
    public function isTemplate(): bool
    {
        return $this->type === 'template';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
