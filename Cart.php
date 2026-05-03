<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'cart_item_ID';
    public $timestamps = false;

    protected $fillable = ['user_ID', 'product_ID', 'quantity', 'size_selected'];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_ID', 'user_ID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_ID', 'product_ID');
    }
}