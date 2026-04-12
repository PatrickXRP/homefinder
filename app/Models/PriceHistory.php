<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    public $timestamps = false;

    protected $table = 'price_history';

    protected $fillable = ['property_id', 'price', 'price_eur', 'recorded_date', 'note', 'source'];

    protected $casts = [
        'price' => 'integer',
        'price_eur' => 'integer',
        'recorded_date' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
