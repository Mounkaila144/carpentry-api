<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_blocks';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'identifier',
        'type',
        'content',
        'settings',
        'page_id',
        'order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the page that owns the block.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Scope to get only active blocks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get blocks by identifier.
     */
    public function scopeByIdentifier($query, string $identifier)
    {
        return $query->where('identifier', $identifier);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
