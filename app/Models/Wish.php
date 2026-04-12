<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wish extends Model
{
    protected $fillable = ['category', 'label', 'weight', 'value', 'notes', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function countryScores(): HasMany
    {
        return $this->hasMany(CountryWishScore::class);
    }
}
