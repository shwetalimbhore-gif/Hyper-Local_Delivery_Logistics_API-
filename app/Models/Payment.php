<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcel_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'collected_by',
        'collected_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    /**
     * Get the parcel this payment belongs to
     */
    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    /**
     * Get the user who collected this payment (rider)
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }
}
