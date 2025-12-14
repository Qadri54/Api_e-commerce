<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary();
            // Foreign Key ke User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->decimal('total_price', 12, 2);

            // Status pembayaran (settlement, deny, pending, cancel, null)
            // ->nullable() digunakan karena request Anda menyertakan 'null' sebagai opsi
            $table->enum('status_pembayaran', ['settlement', 'deny', 'pending', 'cancel'])
                ->nullable()
                ->default('pending');

            $table->timestamps(); // Ini otomatis membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
