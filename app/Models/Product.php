<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2', // Pastikan outputnya selalu desimal 2 digit
        'stock' => 'integer',
    ];

    // Relasi: Product bisa ada di banyak Order Item
    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
