<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyEmail extends Model
{
    protected $fillable = ['property_id', 'type', 'subject', 'body', 'language', 'tone', 'status', 'sent_at', 'gmail_thread_id', 'notes'];

    protected $casts = ['sent_at' => 'datetime'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
