<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_media';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'title',
        'caption',
        'folder',
        'metadata',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope to get only active media.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by folder.
     */
    public function scopeInFolder($query, ?string $folder)
    {
        return $query->where('folder', $folder);
    }

    /**
     * Scope to filter by mime type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('mime_type', 'like', $type.'%');
    }

    /**
     * Scope to get only images.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope to get only documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'like', 'application/%');
    }

    /**
     * Scope to get only videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    /**
     * Get the full URL of the media.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?? 'public')->url($this->path);
    }

    /**
     * Get the full path of the media.
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk ?? 'public')->path($this->path);
    }

    /**
     * Check if the media is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the media is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if the media is a document.
     */
    public function isDocument(): bool
    {
        return str_starts_with($this->mime_type, 'application/');
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
