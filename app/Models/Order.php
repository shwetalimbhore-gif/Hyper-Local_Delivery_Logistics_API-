<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable =[
        'customer_name',
        'customer_phone',
        'drop_address',
        'drop_latitude',
        'drop_longitude',
        'total_amount',
        'status',
        'rider_id',
        'return_type',
        'hub_id',
        'return_address',
        'assigned_at',
        'picked_at',
        'delivered_at',
        'failed_at',
        'returned_at',

    ];

    //Relationships

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    public function statusLog()
    {
        return $this->hasmany(OrderStatusLog::class);
    }


}
