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
        Schema::create('htl_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_htl', 50);
            $table->string('no_sppd', 50);
            $table->string('user_id', 20);
            $table->string('unit', 50);
            $table->string('nama_htl', 50);
            $table->string('lokasi_htl', 50);
            $table->integer('jmlkmr_htl');
            $table->string('bed_htl', 50);
            $table->date('tgl_masuk_htl');
            $table->date('tgl_keluar_htl');
            $table->integer('total_hari');
            $table->string('approval_status', 50);
            $table->string('created_by', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('htl_transactions');
    }
};
