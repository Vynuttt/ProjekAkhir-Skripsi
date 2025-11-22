<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_batches', function (Blueprint $table) {
            $table->id();

            // Produk yang dibeli
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Pembelian mana batch ini berasal
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');

            // Harga beli per unit pada batch ini
            $table->integer('buy_price');

            // Jumlah barang yang dibeli di batch ini
            $table->integer('quantity');

            // Jumlah stok tersisa dari batch ini
            $table->integer('remaining_quantity');

            // Tanggal pembelian batch
            $table->date('batch_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_batches');
    }
};