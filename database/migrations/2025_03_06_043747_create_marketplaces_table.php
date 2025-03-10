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
        Schema::create('marketplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('quantity')->nullable();
            $table->enum('unit',['kg', 'ton', 'liters', 'pieces'])->nullable();
            $table->decimal('minimum_bid', 8, 2)->nullable()->comment('Minimum bidding price for the product');
            $table->enum('product_status',['available', 'sold', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplaces');
    }
};
