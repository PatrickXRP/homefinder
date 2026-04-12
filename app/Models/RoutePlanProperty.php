<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePlanProperty extends Model
{
    public $timestamps = false;

    protected $fillable = ['route_plan_id', 'property_id', 'sort_order', 'visit_time', 'duration_minutes', 'notes'];

    public function routePlan(): BelongsTo
    {
        return $this->belongsTo(RoutePlan::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
