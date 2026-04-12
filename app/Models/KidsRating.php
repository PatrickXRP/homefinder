<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsRating extends Model
{
    protected $fillable = ['property_id', 'kid_name', 'kid_emoji', 'rating'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
