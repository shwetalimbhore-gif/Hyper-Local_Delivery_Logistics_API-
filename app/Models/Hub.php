<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hub extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',  // This ensures is_active is treated as boolean
        'deleted_at' => 'datetime', // This allows for soft deletes if implemented
    ];

    /**
     * Get riders assigned to this hub
     */
    public function riders()
    {
        return $this->hasMany(Rider::class);
    }

    /**
     * Get parcels that originated from this hub
     */
    public function sourceParcels()
    {
        return $this->hasMany(Parcel::class, 'source_hub_id');
    }

    /**
     * Get parcels currently at this hub
     */
    public function currentParcels()
    {
        return $this->hasMany(Parcel::class, 'current_hub_id');
    }

     public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Check if hub is active
     */
    public function isActive()
    {
        return $this->is_active;
    }
}
