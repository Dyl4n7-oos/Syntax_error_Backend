<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'order_ID';
    public $timestamps = false;

    protected $fillable = [
        'user_ID', 'order_status', 'total_price', 'order_description', 'shipping_address'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'order_date' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_ID', 'user_ID');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_ID', 'order_ID');
    }
}