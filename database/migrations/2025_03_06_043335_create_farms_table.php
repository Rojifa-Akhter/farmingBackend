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
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('farm_name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('size', 8, 2)->nullable();
            $table->string('crop_type')->nullable();
            $table->json('image')->nullable();
            $table->json('video')->nullable();
            $table->enum('crop_status', ['available', 'invested', 'harvested'])->default('available');
            $table->decimal('operational_costs', 8, 2)->nullable()->comment('Operational costs for the farm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
