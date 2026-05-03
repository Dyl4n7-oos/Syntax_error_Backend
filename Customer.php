<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'customer';
    protected $primaryKey = 'user_ID';
    public $timestamps = false;

    protected $fillable = [
        'name', 'email', 'password_hash', 'phone', 'address', 'is_verified', 'role_level'
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_ID', 'user_ID');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'user_ID', 'user_ID');
    }
}