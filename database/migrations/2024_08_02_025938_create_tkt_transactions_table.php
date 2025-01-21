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
        Schema::create('tkt_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_tkt', 50);
            $table->string('no_sppd', 50);
            $table->string('user_id', 20);
            $table->string('unit', 50);
            $table->string('jk_tkt', 50);
            $table->string('np_tkt', 50);
            $table->string('noktp_tkt', 255);
            $table->integer('tlp_tkt');
            $table->string('dari_tkt', 50);
            $table->string('ke_tkt', 50);
            $table->date('tgl_brkt_tkt');
            $table->date('tgl_plg_tkt');
            $table->time('jam_brkt_tkt');
            $table->time('jam_plg_tkt');
            $table->string('jenis_tkt', 50);
            $table->string('type_tkt', 50);
            $table->string('created_by', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tkt_transactions');
    }
};
