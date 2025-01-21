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
        Schema::create('tkt_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tkt_id', 50);
            $table->integer('role_id')->default(0);
            $table->string('role_name', 50);
            $table->string('employee_id');
            $table->integer('layer')->default(0);
            $table->string('approval_status');
            $table->datetime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tkt_approvals');
    }
};
