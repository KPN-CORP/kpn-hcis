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
        Schema::create('dependents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('employee_id');
            $table->string('name', 255);
            $table->string('array_id', 255);
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();  // Nullable for middle name if not always required
            $table->string('last_name', 255);
            $table->enum('relation_type', ['Spouse', 'Child', ' ']);  // Empty string allowed
            $table->string('contact_details', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->date('date_of_birth');
            $table->string('nationality', 255);
            $table->string('updated_on', 255);
            $table->string('jobs', 255)->nullable();
            $table->string('gender', 255)->nullable();
            $table->string('no_bpjs', 255)->nullable();
            $table->string('education', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependents');
    }
};
