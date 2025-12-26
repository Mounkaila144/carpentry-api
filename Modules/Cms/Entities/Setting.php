<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The database connection.
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'cms_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'options',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_public' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the casted value based on type.
     */
    public function getCastedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value
        };
    }

    /**
     * Scope to filter by group.
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get only public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->casted_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, string $group = 'general', string $type = 'string'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        return $setting;
    }
}
