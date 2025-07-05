<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locations extends Model
{
    use SoftDeletes, HasMedia;
    
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
    
    public function companies()
    {
        return $this->hasMany(Company::class, 'location_id');
    }
}
