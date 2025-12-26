<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_pages';

    /**
     * Page status constants.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'template',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'status',
        'published_at',
        'order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the blocks for this page.
     */
    public function blocks()
    {
        return $this->hasMany(Block::class)->ordered();
    }

    /**
     * Get the parent page.
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the children pages.
     */
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->ordered();
    }

    /**
     * Get the featured image media.
     */
    public function featuredMedia()
    {
        return $this->belongsTo(Media::class, 'featured_image');
    }

    /**
     * Scope to get only active records.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope to get only draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope to get root pages only.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to find by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Check if record is active.
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->is_active
            && (! $this->published_at || $this->published_at->lte(now()));
    }

    /**
     * Get the full URL path.
     */
    public function getFullSlugAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_slug.'/'.$this->slug;
        }

        return $this->slug;
    }
}
