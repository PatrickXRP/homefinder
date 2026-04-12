<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KidsAccount extends Model
{
    protected $fillable = [
        'name', 'pin', 'emoji', 'color', 'age', 'is_active',
        'module_photo_swiper', 'module_property_swiper', 'module_property_overview',
        'allowed_country_ids', 'allowed_regions',
        'filter_price_min', 'filter_price_max', 'filter_bedrooms_min',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'module_photo_swiper' => 'boolean',
        'module_property_swiper' => 'boolean',
        'module_property_overview' => 'boolean',
        'allowed_country_ids' => 'array',
        'allowed_regions' => 'array',
    ];

    public function photoSwipes(): HasMany
    {
        return $this->hasMany(PhotoSwipe::class, 'kid_name', 'name');
    }

    /**
     * Get filtered properties query based on this account's settings.
     */
    public function filteredProperties()
    {
        $query = Property::whereNotNull('images')->where('asking_price_eur', '>', 0);

        if (!empty($this->allowed_country_ids)) {
            $query->whereIn('country_id', $this->allowed_country_ids);
        }

        if (!empty($this->allowed_regions)) {
            $query->whereIn('region', $this->allowed_regions);
        }

        if ($this->filter_price_min) {
            $query->where('asking_price_eur', '>=', $this->filter_price_min);
        }

        if ($this->filter_price_max) {
            $query->where('asking_price_eur', '<=', $this->filter_price_max);
        }

        if ($this->filter_bedrooms_min) {
            $query->where('bedrooms', '>=', $this->filter_bedrooms_min);
        }

        return $query;
    }
}
