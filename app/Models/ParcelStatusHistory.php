<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'parcel_status_histories';

    protected $fillable = [
        'parcel_id',
        'status_id',
        'from_status_id',
        'location_latitude',
        'location_longitude',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
    ];

    /**
     * Get the parcel this history belongs to
     */
    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    /**
     * Get the new status
     */
    public function status()
    {
        return $this->belongsTo(ParcelStatus::class, 'status_id');
    }

    /**
     * Get the previous status
     */
    public function fromStatus()
    {
        return $this->belongsTo(ParcelStatus::class, 'from_status_id');
    }

    /**
     * Get the user who made this update
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
