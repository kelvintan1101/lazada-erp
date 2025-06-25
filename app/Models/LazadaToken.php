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

    /**
     * Check if the token is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpiringSoon($buffer = 86400)
    {
        return $this->expires_at->subSeconds($buffer)->isPast();
    }
}
