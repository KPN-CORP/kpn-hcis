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
        Schema::create('bt_transaction', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('user_id');
            $table->string('jns_dinas');
            $table->string('nama');
            $table->string('no_sppd');
            $table->string('unit_1');
            $table->string('atasan_1');
            $table->string('email_1');
            $table->string('unit_2');
            $table->string('atasan_2');
            $table->string('email_2');
            $table->string('divisi');
            $table->date('mulai');
            $table->date('kembali');
            $table->string('tujuan');
            $table->string('keperluan');
            $table->string('bb_perusahaan');
            $table->integer('norek_krywn');
            $table->string('nama_bank');
            $table->string('nama_pemilik_rek');
            $table->string('ca');
            $table->string('tiket');
            $table->string('hotel');
            $table->string('taksi');
            $table->integer('id_ca');
            $table->integer('id_tiket');
            $table->integer('id_hotel');
            $table->integer('id_taksi');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bt_transaction');
    }
};
