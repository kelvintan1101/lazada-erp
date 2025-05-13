<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'lazada_product_id',
        'name',
        'sku',
        'price',
        'stock_quantity',
        'description',
        'images',
        'raw_data_from_lazada',
        'synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'raw_data_from_lazada' => 'array',
        'synced_at' => 'datetime',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock()
    {
        $threshold = Setting::where('key', 'low_stock_threshold')->value('value') ?? 10;
        return $this->stock_quantity <= (int)$threshold;
    }
}