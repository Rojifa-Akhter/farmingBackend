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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->cascadeOnDelete();
            $table->decimal('amount', 8, 2)->nullable();
            $table->enum('invest_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('profit_share', 8, 2)->nullable()->comment('Profit share percentage for the investor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
