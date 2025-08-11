<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFields extends Model
{
    protected $fillable = [
        'event_type_id',
        'name',
        'label',
        'type',
    ];

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }
}
