<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Location extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'locations';

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'latitude',
        'longitude',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
