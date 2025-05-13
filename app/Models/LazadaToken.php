<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LazadaToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id_on_lazada',
        'access_token',
        'refresh_token',
        'expires_at',
        'country_user_info',
    ];

    protected $casts = [
        'country_user_info' => 'array',
        'expires_at' => 'datetime',
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isExpiringSoon($buffer = 86400)
    {
        return $this->expires_at->subSeconds($buffer)->isPast();
    }
}
