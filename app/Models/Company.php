<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'user_id',
        'name',
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

}
