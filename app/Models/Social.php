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
        'company_id',
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
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Determine if the social link belongs to a user.
     *
     * @return bool
     */
    public function isUserSocial(): bool
    {
        return $this->entity_type === 'user';
    }

    /**
     * Determine if the social link belongs to a company.
     *
     * @return bool
     */
    public function isCompanySocial(): bool
    {
        return $this->entity_type === 'company';
    }
}
