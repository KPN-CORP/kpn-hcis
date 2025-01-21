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
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('parent_company_id', 100);
            $table->string('designation_name', 100);
            $table->string('job_code', 100);
            $table->string('department_name', 100);
            $table->string('department_code', 100);
            $table->string('department_level1', 100);
            $table->string('department_level2', 100);
            $table->string('department_level3', 100);
            $table->string('department_level4', 100);
            $table->string('department_level5', 100);
            $table->string('department_level6', 100);
            $table->string('department_level7', 100);
            $table->string('department_level8', 100);
            $table->string('department_level9', 100);
            $table->string('type_of_staffing_model', 100);
            $table->string('number_of_positions', 100);
            $table->string('number_of_existing_incumbents', 100);
            $table->text('department_hierarchy')->nullable();
            $table->string('status', 50);
            $table->enum('dept_head_flag', ['T','F'])->default('F');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};
