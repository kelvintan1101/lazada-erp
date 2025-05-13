<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'adjusted_quantity',
        'reason',
        'adjusted_by_user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function adjustedByUser()
    {
        return $this->belongsTo(User::class, 'adjusted_by_user_id');
    }
}