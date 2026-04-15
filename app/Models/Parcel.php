<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'reference_number',
        'sender_name',
        'sender_phone',
        'sender_email',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_email',
        'receiver_address',
        'parcel_name',
        'parcel_description',
        'weight',
        'size',
        'parcel_type',
        'delivery_charge',
        'payment_method',
        'payment_status',
        'status_id',
        'source_hub_id',
        'assigned_rider_id',
        'assigned_at',
        'picked_up_at',
        'out_for_delivery_at',
        'delivered_at',
        'failed_delivery_at',
        'returned_at',
        'delivery_attempts',
        'failure_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'size' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_delivery_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * Get the current status of the parcel
     */
    public function status()
    {
        return $this->belongsTo(ParcelStatus::class, 'status_id');
    }

    /**
     * Get the source hub
     */
    public function sourceHub()
    {
        return $this->belongsTo(Hub::class, 'source_hub_id');
    }

    /**
     * Get the assigned rider
     */
    public function assignedRider()
    {
        return $this->belongsTo(Rider::class, 'assigned_rider_id');
    }

    /**
     * Get the admin who created this parcel
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get status history for this parcel
     */
    public function statusHistories()
    {
        return $this->hasMany(ParcelStatusHistory::class);
    }

    /**
     * Get payment details for this parcel
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Check if rider can update this parcel
     */
    public function canRiderUpdate($riderId)
    {
        return $this->assigned_rider_id === $riderId;
    }

    /**
     * Check if parcel can be updated to new status
     */
    public function canUpdateStatus($newStatusId)
    {
        if (!$this->assigned_rider_id) {
            return false;
        }

        $newStatus = ParcelStatus::find($newStatusId);

        if (!$newStatus || !$newStatus->is_rider_updatable) {
            return false;
        }

        // Define allowed status transitions for riders
        $allowedTransitions = [
            'assigned' => ['picked_up'],
            'picked_up' => ['out_for_delivery', 'returned_to_hub'],
            'out_for_delivery' => ['delivered', 'failed_delivery', 'returned_to_hub'],
            'failed_delivery' => ['out_for_delivery', 'returned_to_hub'],
        ];

        $currentStatus = $this->status ? $this->status->slug : 'pending';
        $newStatusSlug = $newStatus->slug;

        return isset($allowedTransitions[$currentStatus]) &&
               in_array($newStatusSlug, $allowedTransitions[$currentStatus]);
    }

    /**
     * Update parcel status with history
     */
    public function updateStatus($newStatusId, $userId, $notes = null, $location = null)
    {
        $oldStatusId = $this->status_id;

        // Update parcel
        $this->status_id = $newStatusId;

        // Update timestamp based on new status
        $newStatus = ParcelStatus::find($newStatusId);

        switch ($newStatus->slug) {
            case 'picked_up':
                $this->picked_up_at = now();
                break;
            case 'out_for_delivery':
                $this->out_for_delivery_at = now();
                break;
            case 'delivered':
                $this->delivered_at = now();
                break;
            case 'failed_delivery':
                $this->failed_delivery_at = now();
                $this->delivery_attempts++;
                break;
            case 'returned_to_hub':
            case 'returned_to_sender':
                $this->returned_at = now();
                break;
        }

        $this->save();

        // Create history record
        return ParcelStatusHistory::create([
            'parcel_id' => $this->id,
            'status_id' => $newStatusId,
            'from_status_id' => $oldStatusId,
            'notes' => $notes,
            'location_latitude' => $location['latitude'] ?? null,
            'location_longitude' => $location['longitude'] ?? null,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Generate unique tracking number
     */
    public static function generateTrackingNumber()
    {
        $prefix = 'HLD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return $prefix . $date . $random . $sequence;
    }

    /**
     * Check if parcel is delivered
     */
    public function isDelivered()
    {
        return $this->status && $this->status->slug === 'delivered';
    }

    /**
     * Check if parcel is pending
     */
    public function isPending()
    {
        return $this->status && $this->status->slug === 'pending';
    }

    /**
     * Check if parcel is in transit
     */
    public function isInTransit()
    {
        return $this->status && in_array($this->status->slug, ['picked_up', 'out_for_delivery']);
    }

    /**
     * Get formatted tracking number
     */
    public function getFormattedTrackingNumberAttribute()
    {
        return $this->tracking_number;
    }

    /**
     * Get delivery duration in minutes
     */
    public function getDeliveryDurationAttribute()
    {
        if ($this->delivered_at && $this->assigned_at) {
            return $this->delivered_at->diffInMinutes($this->assigned_at);
        }
        return null;
    }
}
