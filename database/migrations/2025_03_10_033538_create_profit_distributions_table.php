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
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->nullable()->constrained('investments')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('total_profit', 8, 2)->nullable();
            $table->decimal('investor_share', 8, 2)->nullable()->comment('Investor’s share of the profit');
            $table->decimal('farmer_share', 8, 2)->nullable()->comment('Farmer’s share of the profit');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_distributions');
    }
};
