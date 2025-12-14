<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderHistory extends Model {
    use HasFactory;

    // Karena di migration tidak ada created_at & updated_at, set false
    public $timestamps = false;

    // Tentukan nama tabel secara eksplisit (opsional, tapi praktik bagus)
    protected $table = 'order_history';

    protected $fillable = [
        'order_id',
        'status',
        'description',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // Relasi: History milik satu Order
    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }
}
