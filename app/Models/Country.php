<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name', 'name_local', 'code', 'flag_emoji', 'continent', 'status',
        'match_score', 'match_summary', 'match_details',
        'foreigners_can_buy', 'foreigners_notes', 'eu_member',
        'avg_price_per_m2_eur', 'purchase_costs_pct', 'annual_property_tax_pct',
        'annual_costs_notes', 'realistic_budget_min_eur', 'realistic_budget_notes',
        'internet_quality', 'healthcare_quality', 'language_barrier',
        'expat_community', 'international_schools',
        'flight_hours_from_dubai', 'nearest_airport', 'flight_connections_notes',
        'ai_report', 'ai_report_generated_at', 'pros', 'cons', 'sources',
        'researched_at', 'notes', 'sort_order',
    ];

    protected $casts = [
        'match_details' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'sources' => 'array',
        'foreigners_can_buy' => 'boolean',
        'eu_member' => 'boolean',
        'international_schools' => 'boolean',
        'match_score' => 'integer',
        'avg_price_per_m2_eur' => 'integer',
        'realistic_budget_min_eur' => 'integer',
        'purchase_costs_pct' => 'decimal:2',
        'annual_property_tax_pct' => 'decimal:2',
        'flight_hours_from_dubai' => 'decimal:1',
        'ai_report_generated_at' => 'datetime',
        'researched_at' => 'datetime',
    ];

    public function propertySources(): HasMany
    {
        return $this->hasMany(PropertySource::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function wishScores(): HasMany
    {
        return $this->hasMany(CountryWishScore::class);
    }
}
