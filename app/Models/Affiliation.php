<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    protected $fillable = [
        'type',
        'entity_id',
        'user_id',
        'affiliatable_type',
        'affiliatable_id',
        'role',
        'confirmation_status',
        'requested_by_user_id',
        'requested_by_entity_id',
        'confirmed_at',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requested_by_user()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function requested_by_entity()
    {
        return $this->belongsTo(Entity::class, 'requested_by_entity_id');
    }

    public function confirmation_status()
    {
        return $this->belongsTo(AffiliationConfirmation::class);
    }

    /**
     * Get the parent affiliatable model (Event, Entity, etc.)
     */
    public function affiliatable()
    {
        return $this->morphTo();
    }
}
