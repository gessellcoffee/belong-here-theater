<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    protected $fillable = [
        'type',
        'company_id',
        'user_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
