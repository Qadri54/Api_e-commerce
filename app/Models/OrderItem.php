<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model {
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
    ];

    protected $casts = [
        'price_at_purchase' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relasi: Item milik satu Order
    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Item merujuk ke satu Produk
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
