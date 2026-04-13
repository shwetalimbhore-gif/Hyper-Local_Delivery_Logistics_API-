<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hub extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
    ];

    // Relationships

    public function order()
    {
        return $this->hasMany(Order::class);
    }
}
