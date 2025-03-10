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
        Schema::create('logistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->decimal('shipping_cost', 8, 2)->nullable()->comment('Cost of shipping for the order');
            $table->enum('logistics_status',['in-transit', 'delivered', 'failed'])->default('in-transit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics');
    }
};
