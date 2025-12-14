<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable(); // Text untuk deskripsi panjang

            // Decimal (10, 2) artinya total 10 digit, 2 di belakang koma.
            // Cocok untuk harga agar presisi.
            $table->decimal('price', 10, 2);

            $table->integer('stock');
            $table->string('image_url')->nullable(); // Nullable jika belum ada gambar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('products');
    }
};
