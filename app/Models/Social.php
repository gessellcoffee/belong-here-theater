<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Social extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'icon',
        'entity_id',
        'user_id',
        'entity_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entity_type' => 'string',
    ];

    /**
     * Get the user that owns the social link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the social link.
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Determine if the social link belongs to a user.
     */
    public function isUserSocial(): bool
    {
        return $this->entity_type === 'user';
    }

    /**
     * Determine if the social link belongs to an entity.
     */
    public function isEntitySocial(): bool
    {
        return $this->entity_type === 'entity';
    }
}
