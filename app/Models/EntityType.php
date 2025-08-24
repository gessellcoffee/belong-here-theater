<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntityType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'entity_types';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }
}
