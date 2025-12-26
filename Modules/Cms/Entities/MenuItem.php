<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_menu_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'route',
        'route_params',
        'page_id',
        'target',
        'icon',
        'css_class',
        'order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'route_params' => 'array',
            'order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the menu that owns this item.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the children menu items.
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->ordered()->with('children');
    }

    /**
     * Get the linked page.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Scope to get only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope to get root items only.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the computed URL.
     */
    public function getComputedUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->page_id && $this->page) {
            return '/'.$this->page->slug;
        }

        if ($this->route) {
            try {
                return route($this->route, $this->route_params ?? []);
            } catch (\Exception $e) {
                return '#';
            }
        }

        return '#';
    }
}
