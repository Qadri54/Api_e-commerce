<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('order_history', function (Blueprint $table) {
            $table->id();


            $table->string('order_id');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->string('status'); // Contoh: "pending", "paid", "shipping"
            $table->string('description')->nullable(); // Keterangan tambahan

            // Request Anda meminta field 'changed_at'.
            // useCurrent() akan otomatis mengisi waktu saat insert.
            $table->timestamp('changed_at')->useCurrent();

            // Opsi: Jika ingin standar Laravel bisa tetap pakai $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
