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
        Schema::create('bt_attendance_backups', function (Blueprint $table) {
            $table->id();
            $table->string('date', 25);
            $table->string('employee_id', 25);
            $table->string('name', 100);
            $table->text('shift_name');
            $table->text('policy_name');
            $table->text('assigned_weekly_off');
            $table->string('clock_in', 10);
            $table->string('clock_out', 10);
            $table->text('edit_comment');
            $table->enum('backup_status', ['Y','N'])->default('N');
            $table->enum('update_db', ['Y','N'])->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bt_attendance_backups');
    }
};
