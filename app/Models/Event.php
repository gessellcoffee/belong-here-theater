<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type_id',
        'name',
        'slug',
        'description',
        'date',
    ];

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Get all affiliations for this event
     */
    public function affiliations()
    {
        return $this->morphMany(Affiliation::class, 'affiliatable');
    }

    /**
     * Get entities affiliated with this event
     */
    public function entities()
    {
        return $this->morphToMany(Entity::class, 'affiliatable', 'affiliations')
            ->withPivot(['type', 'role', 'confirmation_status'])
            ->withTimestamps();
    }

    /**
     * Get users affiliated with this event
     */
    public function users()
    {
        return $this->morphToMany(User::class, 'affiliatable', 'affiliations')
            ->withPivot(['type', 'role', 'confirmation_status'])
            ->withTimestamps();
    }

    /**
     * Get companies affiliated with this event
     */
    public function companies()
    {
        return $this->morphToMany(Company::class, 'affiliatable', 'affiliations')
            ->withPivot(['type', 'role', 'confirmation_status'])
            ->withTimestamps();
    }

    /**
     * Get locations affiliated with this event
     */
    public function locations()
    {
        return $this->morphToMany(Location::class, 'affiliatable', 'affiliations')
            ->withPivot(['type', 'role', 'confirmation_status'])
            ->withTimestamps();
    }

    /**
     * Helper methods to get specific roles
     */
    public function getVenue()
    {
        return $this->affiliations()->where('role', 'venue')->first()?->affiliatable;
    }

    public function getOrganizer()
    {
        return $this->affiliations()->where('role', 'organizer')->first()?->affiliatable;
    }

    public function getSponsors()
    {
        return $this->affiliations()->where('role', 'sponsor')->get()->pluck('affiliatable');
    }

    public function getPerformers()
    {
        return $this->affiliations()->where('role', 'performer')->get()->pluck('affiliatable');
    }

    public function eventFields()
    {
        return $this->hasMany(EventFields::class);
    }

    public function eventFieldValues()
    {
        return $this->hasMany(EventFieldValue::class);
    }

    public function getFieldValue($fieldId)
    {
        $fieldValue = $this->eventFieldValues()->where('event_field_id', $fieldId)->first();
        return $fieldValue ? $fieldValue->value : null;
    }

    public function setFieldValue($fieldId, $value)
    {
        $this->eventFieldValues()->updateOrCreate(
            ['event_field_id' => $fieldId],
            ['value' => $value]
        );
    }
}
