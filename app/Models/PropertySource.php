<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertySource extends Model
{
    protected $fillable = ['country_id', 'name', 'base_url', 'search_url_template', 'scraper_class', 'is_active', 'last_scraped_at', 'scrape_interval_hours', 'notes'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_scraped_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'source_id');
    }
}
