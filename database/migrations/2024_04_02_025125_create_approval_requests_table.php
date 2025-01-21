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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('form_id')->unique(); // uuid from submitted form table
            $table->string('category', 30); // uuid from submitted form table
            $table->string('current_approval_id');
            $table->string('employee_id');
            $table->enum('status', ['Pending','Approved','Sendback'])->default('Pending');
            $table->text('messages')->nullable();
            $table->text('sendback_messages')->nullable();
            $table->string('sendback_to')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
