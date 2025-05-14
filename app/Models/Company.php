<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'description',
        'logo',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
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
