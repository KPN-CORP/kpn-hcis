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
        Schema::create('vt_transaction', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_vt');
            $table->string('no_sppd');
            $table->string('user_id');
            $table->string('unit');
            $table->integer('nominal_vt');
            $table->integer('keeper_vt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vt_transaction');
    }
};
