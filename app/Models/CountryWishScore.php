<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryWishScore extends Model
{
    protected $fillable = ['country_id', 'wish_id', 'score', 'explanation'];

    protected $casts = ['score' => 'integer'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function wish(): BelongsTo
    {
        return $this->belongsTo(Wish::class);
    }
}
