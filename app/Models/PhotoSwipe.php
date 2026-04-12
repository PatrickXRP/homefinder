<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoSwipe extends Model
{
    protected $fillable = ['property_id', 'kid_name', 'kid_pin', 'photo_index', 'image_url', 'rating'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
