<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFieldValue extends Model
{
    protected $fillable = [
        'event_id',
        'event_field_id',
        'value',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventField()
    {
        return $this->belongsTo(EventFields::class, 'event_field_id');
    }
}
