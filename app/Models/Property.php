<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'country_id', 'source_id', 'name', 'external_id', 'url', 'status',
        'address', 'city', 'region', 'latitude', 'longitude',
        'asking_price', 'currency', 'asking_price_eur', 'price_per_m2',
        'year_built', 'living_area_m2', 'plot_area_m2', 'bedrooms', 'bathrooms',
        'energy_class', 'condition', 'water_type', 'water_name',
        'has_sauna', 'has_jetty', 'has_guest_house', 'year_round_accessible', 'own_road',
        'my_score', 'ai_score', 'ai_analysis',
        'listed_date', 'days_on_market', 'viewing_date', 'added_at',
        'notes', 'checklist', 'images', 'scraped_at', 'raw_data',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'asking_price' => 'integer',
        'asking_price_eur' => 'integer',
        'has_sauna' => 'boolean',
        'has_jetty' => 'boolean',
        'has_guest_house' => 'boolean',
        'year_round_accessible' => 'boolean',
        'own_road' => 'boolean',
        'checklist' => 'array',
        'images' => 'array',
        'raw_data' => 'array',
        'listed_date' => 'date',
        'viewing_date' => 'date',
        'added_at' => 'datetime',
        'scraped_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(PropertySource::class, 'source_id');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function kidsRatings(): HasMany
    {
        return $this->hasMany(KidsRating::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(PropertyEmail::class);
    }

    public function routePlanEntries(): HasMany
    {
        return $this->hasMany(RoutePlanProperty::class);
    }
}
