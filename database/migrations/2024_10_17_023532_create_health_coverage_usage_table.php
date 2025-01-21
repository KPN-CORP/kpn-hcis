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
        Schema::create('health_coverage_usage', function (Blueprint $table) {
            $table->uuid('usage_id')->primary();
            $table->string('employee_id');
            $table->string('no_medic');
            $table->string('no_invoice');
            $table->string('hospital_name');
            $table->string('patient_name');
            $table->string('disease');
            $table->date('date');
            $table->string('coverage_detail');
            $table->integer('period');
            $table->integer('glasses')->nullable();
            $table->integer('child_birth')->nullable();
            $table->integer('inpatient')->nullable();
            $table->integer('outpatient')->nullable();
            $table->integer('total_coverage')->nullable();
            $table->integer('glasses_uncover')->nullable();
            $table->integer('child_birth_uncover')->nullable();
            $table->integer('inpatient_uncover')->nullable();
            $table->integer('outpatient_uncover')->nullable();
            $table->integer('total_uncoverage')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_coverage_usage');
    }
};
