<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutePlan extends Model
{
    protected $fillable = ['name', 'start_point', 'travel_date', 'notes'];

    protected $casts = ['travel_date' => 'date'];

    public function propertyEntries(): HasMany
    {
        return $this->hasMany(RoutePlanProperty::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'route_plan_properties')
            ->withPivot('sort_order', 'visit_time', 'duration_minutes', 'notes');
    }
}
