<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('ordering_cost', 12, 2)->default(0); // S
            $table->decimal('holding_cost', 12, 2)->default(0);  // H
            $table->integer('annual_demand')->default(0);         // D
            $table->integer('reorder_point')->nullable();
            $table->integer('safety_stock')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ordering_cost','holding_cost','annual_demand','reorder_point','safety_stock']);
        });
    }
};
