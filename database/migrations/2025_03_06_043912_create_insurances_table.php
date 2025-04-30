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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->comment('Farmer or Investor');
            $table->string('provider')->nullable()->comment('Insurance company name');
            $table->string('policy_number')->nullable();
            $table->longText('coverage_details')->nullable();
            $table->decimal('coverage_amount', 8, 2)->nullable()->comment('Maximum coverage amount');
            $table->decimal('premium', 8, 2)->nullable()->comment('Insurance premium amount');
            $table->enum('insurance_status', ['active', 'expired', 'claimed'])->default('active');
            $table->enum('claim_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
