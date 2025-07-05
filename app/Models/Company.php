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

    public function requested_by_user()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function requested_by_company()
    {
        return $this->belongsTo(Company::class, 'requested_by_company_id');
    }

    public function confirmation_status()
    {
        return $this->belongsTo(AffiliationConfirmation::class);
    }
    
}
