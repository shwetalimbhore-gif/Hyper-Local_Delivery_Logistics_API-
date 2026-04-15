<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderLocationTracking extends Model
{
    use HasFactory;

    protected $table = 'rider_location_trackings';

    protected $fillable = [
        'rider_id',
        'latitude',
        'longitude',
        'speed',
        'bearing',
        'accuracy',
        'is_moving',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'bearing' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'is_moving' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public $timestamps = false; // Using recorded_at instead

    /**
     * Get the rider who owns this location
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}
