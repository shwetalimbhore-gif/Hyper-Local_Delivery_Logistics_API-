<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rider extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'user_id',
        'hub_id',
        'employee_id',
        'vehicle_type',
        'vehicle_number',
        'vehicle_model',
        'license_number',
        'max_weight_capacity',
        'max_size_capacity',
        'current_latitude',
        'current_longitude',
        'status',
        'total_deliveries',
        'successful_deliveries',
        'failed_deliveries',
        'rating',
        'earnings',
        'is_verified',
        'joined_date',
        'deleted_by',
    ];

    protected $casts = [
        'max_weight_capacity' => 'decimal:2',
        'max_size_capacity' => 'decimal:2',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'earnings' => 'decimal:2',
        'is_verified' => 'boolean',
        'joined_date' => 'date',
        'deleted_at' => 'datetime',    // This allows for soft deletes if implemented
    ];

    /**
     * Get the user account associated with the rider
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hub this rider belongs to
     */
    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    /**
     * Get parcels assigned to this rider
     */
    public function assignedParcels()
    {
        return $this->hasMany(Parcel::class, 'assigned_rider_id');
    }

     public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get location tracking history
     */
    public function locationTrackings()
    {
        return $this->hasMany(RiderLocationTracking::class);
    }

    /**
     * Check if rider is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Check if rider is busy
     */
    public function isBusy()
    {
        return $this->status === 'busy';
    }

    /**
     * Update rider status
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
        return $this;
    }

    /**
     * Get rider's full name
     */
    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    /**
     * Get rider's phone number
     */
    public function getPhoneAttribute()
    {
        return $this->user ? $this->user->phone : 'N/A';
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_deliveries === 0) {
            return 100;
        }
        return round(($this->successful_deliveries / $this->total_deliveries) * 100, 2);
    }
}
