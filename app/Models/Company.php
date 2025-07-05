<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes, HasMedia;
    protected $fillable = [
        'name',
        'description',
        'logo',
        'user_id',
        'location_id',
        'website',
        'phone',
        'extension',
        'email',
        'vision',
        'mission',
        'values',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    
    public function locations()
    {
        return $this->belongsTo(Locations::class, 'location_id');
    }

    public function affiliations()
    {
        return $this->hasMany(Affiliation::class);
    }   

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmation_status()
    {
        return $this->belongsTo(AffiliationConfirmation::class);
    }
    
}
