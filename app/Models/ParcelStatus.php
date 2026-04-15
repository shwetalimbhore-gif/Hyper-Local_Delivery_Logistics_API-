<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelStatus extends Model
{
    use HasFactory;

    protected $table = 'parcel_statuses';

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'color_code',
        'is_rider_updatable',
        'sequence_order',
    ];

    protected $casts = [
        'is_rider_updatable' => 'boolean',
    ];

    /**
     * Get all parcels with this status
     */
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'status_id');
    }

    /**
     * Get status history entries for this status
     */
    public function statusHistories()
    {
        return $this->hasMany(ParcelStatusHistory::class, 'status_id');
    }

    /**
     * Scope for rider-updatable statuses
     */
    public function scopeRiderUpdatable($query)
    {
        return $query->where('is_rider_updatable', true);
    }

    /**
     * Get status by slug
     */
    public static function getBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }
}
