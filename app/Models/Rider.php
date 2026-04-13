<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'current_latitude',
        'current_longitude',
        'is_available',

    ];

    // Relationships

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
