<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'lazada_order_id',
        'lazada_order_number',
        'customer_name',
        'order_date',
        'status',
        'total_amount',
        'shipping_address',
        'payment_method',
        'raw_data_from_lazada',
        'synced_at',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'raw_data_from_lazada' => 'array',
        'synced_at' => 'datetime',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeRecentOrders($query, $limit = 10)
    {
        return $query->orderBy('order_date', 'desc')->limit($limit);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}