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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('parent_company_id', 100);
            $table->string('department_name', 100);
            $table->string('department_code', 100);
            $table->string('parent_department_code', 100);
            $table->string('departments_hod', 100);
            $table->string('level1', 255);
            $table->string('level2', 255);
            $table->string('level3', 255);
            $table->string('level4', 255);
            $table->string('level5', 255);
            $table->string('level6', 255);
            $table->string('level7', 255);
            $table->string('level8', 255);
            $table->string('level9', 255);
            $table->string('status', 50);
            $table->enum('director_flag', ['T','F'])->default('F');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
