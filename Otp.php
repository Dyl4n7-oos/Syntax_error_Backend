<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'otp';
    protected $primaryKey = 'otp_ID';
    public $timestamps = false;

    protected $fillable = ['email', 'otp_code', 'expires_at', 'is_used'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];
}