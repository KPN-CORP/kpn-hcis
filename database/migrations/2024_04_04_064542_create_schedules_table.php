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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_name', 100);
            $table->string('event_type', 100);
            $table->text('employee_type')->nullable();
            $table->text('bisnis_unit')->nullable();
            $table->text('company_filter')->nullable();
            $table->text('location_filter')->nullable();
            $table->date('last_join_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('checkbox_reminder')->default(0);
            $table->string('inputState', 50)->nullable();
            $table->string('repeat_days', 50)->nullable();
            $table->string('before_end_date', 100)->nullable();
            $table->text('messages')->nullable();    
            $table->string('created_by', 20)->nullable();        
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
