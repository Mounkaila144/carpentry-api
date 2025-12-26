<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_menus';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'location',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the menu items for this menu.
     */
    public function items()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->ordered()->with('children');
    }

    /**
     * Get all menu items (flat).
     */
    public function allItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Scope to get only active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get menu by identifier.
     */
    public function scopeByIdentifier($query, string $identifier)
    {
        return $query->where('identifier', $identifier);
    }

    /**
     * Scope to get menu by location.
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
}
