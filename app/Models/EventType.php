<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventFields;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventType extends Model
{
    use HasFactory;
    protected $table = 'event_types';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function eventFields()
    {
        return $this->hasMany(EventFields::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
