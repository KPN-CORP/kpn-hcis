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
        Schema::create('ca_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('ca_id', 50);
            $table->integer('role_id')->default(0);
            $table->string('role_name', 50);
            $table->integer('layer')->default(0);
            $table->enum('approval_status', ['Approved','Rejected','Pending',''])->default('');
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
        Schema::dropIfExists('ca_approvals');
    }
};
