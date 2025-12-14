<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model {
    use HasFactory;

    // App/Models/Order.php
    protected $fillable = ['id', 'user_id', 'total_price', 'status_pembayaran'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Relasi: Order milik satu User
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // Relasi: Order memiliki banyak Item
    public function items(): HasMany {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Relasi: Order memiliki riwayat status
    public function history(): HasMany {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }
}
