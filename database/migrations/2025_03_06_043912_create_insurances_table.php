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
            $table->string('provider')->nullable()->comment('any insurance company name');
            $table->string('policy_number')->nullable();
            $table->longText('coverage_details')->nullable();
            $table->string('premium')->nullable();
            $table->enum('insurance_status',['active', 'expired', 'claimed'])->default('active');
            $table->enum('claim_status',['none', 'pending', 'approved', 'rejected'])->default('none');
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
