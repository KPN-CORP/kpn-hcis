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
        Schema::create('health_plan', function (Blueprint $table) {
            $table->uuid('plan_id')->primary();
            $table->string('employee_id');
            $table->string('plan_name')->nullable();
            $table->integer('child_birth_balance')->nullable();
            $table->integer('inpatient_balance')->nullable();
            $table->integer('outpatient_balance')->nullable();
            $table->integer('glasses_balance')->nullable();
            $table->dateTime('period')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_plan');
    }
};
