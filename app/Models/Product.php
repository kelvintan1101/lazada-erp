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
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'raw_data_from_lazada' => 'array',
        'synced_at' => 'datetime',
    ];

    // Product status constants (simplified)
    const STATUS_ACTIVE = 'active';
    const STATUS_DELETED_FROM_LAZADA = 'deleted_from_lazada';

    // Scopes for simplified status management
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDeletedFromLazada($query)
    {
        return $query->where('status', self::STATUS_DELETED_FROM_LAZADA);
    }

    public function scopeWithAllStatuses($query)
    {
        // Explicitly include all products regardless of status
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

    // Status checking methods (simplified)
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDeletedFromLazada()
    {
        return $this->status === self::STATUS_DELETED_FROM_LAZADA;
    }

    // Status changing methods (simplified)
    public function markAsActive()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function markAsDeletedFromLazada()
    {
        $this->update(['status' => self::STATUS_DELETED_FROM_LAZADA]);
    }

    // Helper method to get human-readable status
    public function getStatusLabel()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DELETED_FROM_LAZADA => 'Deleted from Lazada',
            default => 'Unknown'
        };
    }

    // Get status color for UI
    public function getStatusColor()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_DELETED_FROM_LAZADA => 'red',
            default => 'gray'
        };
    }
}