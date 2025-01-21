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
        Schema::create('ca_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type_ca', ['dns','ndns','entr',''])->default('');
            $table->string('no_ca', 50);
            $table->string('no_sppd', 50);
            $table->string('user_id', 20);
            $table->string('unit', 100);
            $table->string('contribution_level_code', 100);
            $table->string('destination', 100);
            $table->string('others_location', 255)->default('');
            $table->text('ca_needs')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('date_required');
            $table->date('declare_estimate');
            $table->integer('total_days')->default(0);
            $table->json('detail_ca')->nullable();
            $table->integer('total_ca')->default(0);
            $table->integer('total_real')->default(0);
            $table->integer('total_cost')->default(0);
            $table->enum('approval_status', ['Approved','Rejected','Pending',''])->default('');
            $table->enum('approval_sett', ['Approved','Rejected','Pending',''])->default('');
            $table->enum('approval_extend', ['Approved','Rejected','Pending',''])->default('');
            $table->string('created_by', 50);
            $table->datetime('declaration_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ca_transactions');
    }
};
