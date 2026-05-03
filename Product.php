<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'product_ID';
    public $timestamps = false;

    protected $fillable = [
        'name', 'description', 'price', 'category', 'color', 'size', 'stock', 'image_path', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_ID', 'product_ID');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'product_ID', 'product_ID');
    }
}