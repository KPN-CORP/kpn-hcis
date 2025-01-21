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
        Schema::create('matrix_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('group_company', 50);
            $table->string('modul', 50);
            $table->text('condt')->nullable();
            $table->integer('layer')->default(0);
            $table->text('desc')->nullable();
            $table->integer('role_id')->default(0);
            $table->text('employee_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrix_approvals');
    }
};
