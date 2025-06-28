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
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'raw_data_from_lazada' => 'array',
        'synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Scopes for soft delete functionality
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithInactive($query)
    {
        // This scope explicitly includes both active and inactive products
        // Use this when you want to be explicit about including all products
        return $query;
    }

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

    public function isActive()
    {
        return $this->is_active;
    }

    public function markAsInactive()
    {
        $this->update(['is_active' => false]);
    }

    public function markAsActive()
    {
        $this->update(['is_active' => true]);
    }
}