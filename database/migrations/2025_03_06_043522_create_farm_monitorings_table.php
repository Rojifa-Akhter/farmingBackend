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
        Schema::create('farm_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->cascadeOnDelete();
            $table->decimal('temperature',8,2)->nullable();
            $table-> decimal('soil_moisture',8,2)->nullable();
            $table-> decimal('rainfall',8,2)->nullable();
            $table->decimal('yield_prediction', 8, 2)->nullable()->comment('Predicted yield based on monitoring data');
            $table->enum('farm_status',['normal', 'warning', 'critical'])->default('normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_monitorings');
    }
};
