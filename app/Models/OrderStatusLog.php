<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'changed_by_id',
        'changed_by_type',
        'notes',
        'created_at',

    ];

    // Relatioship

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
