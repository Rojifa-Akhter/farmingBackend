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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->nullable()->constrained('users')->cascadeOnDelete()->comment('customer or user');
            $table->foreignId('farmer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->string('quantity')->nullable();
            $table->decimal('total_price',8,2)->nullable();
            $table->enum('order_status',['pending', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable()->comment('Payment method used for the order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
