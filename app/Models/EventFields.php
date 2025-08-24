<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EventType;

class EventFields extends Model
{
    use HasFactory;
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
