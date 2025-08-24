<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entity extends Model implements HasMedia 
{
    use InteractsWithMedia;
    use SoftDeletes;
    use HasFactory;

    protected $table = 'entities';

    protected $fillable = [
        'user_id',
        'entity_type_id',
        'name',
        'slug',
        'description',
        'logo',
        'location_id',
    ];

//    public function users()
//    {
//        return $this->belongsToMany(User::class);
//    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function affiliations(): HasMany
    {
        return $this->hasMany(Affiliation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity_type(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

}
