<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_item';
    protected $primaryKey = 'item_ID';
    public $timestamps = false;

    protected $fillable = ['order_ID', 'product_ID', 'quantity', 'unit_price', 'size_selected'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_ID', 'order_ID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_ID', 'product_ID');
    }
}