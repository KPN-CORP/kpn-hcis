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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 25)->unique();
            $table->foreign("employee_id")->references("employee_id")->on("users");
            $table->string('fullname', 100);
            $table->enum('gender', ['Male', 'Female']);
            $table->string('email', 100);
            $table->string('group_company', 50);
            $table->string('designation', 255);
            $table->string('designation_code', 255);
            $table->string('designation_name', 255);
            $table->string('job_level', 50);
            $table->string('company_name', 50);
            $table->string('work_area_code', 50);
            $table->string('office_area', 50);
            $table->string('manager_l1_id', 25);
            $table->string('manager_l2_id', 25);
            $table->enum('employee_type', ['Permanent', 'Contract', 'Probation', 'Service Bond']);
            $table->string('unit', 255);

            $table->string('personal_email', 100);
            $table->string('personal_mobile_number', 100);
            $table->date('date_of_birth');
            $table->string('place_of_birth', 50);
            $table->string('nationality', 50);
            $table->string('religion', 50);
            $table->string('marital_status', 50);
            $table->string('citizenship_status', 50);
            $table->string('ethnic_group', 50);
            $table->string('homebase', 50);
            $table->text('current_address')->nullable();
            $table->string('current_city', 50);
            $table->text('permanent_address')->nullable();
            $table->string('permanent_city', 50);
            $table->string('blood_group', 5);
            $table->string('tax_status', 10);
            $table->string('bpjs_tk', 50);
            $table->string('bpjs_ks', 50);
            $table->string('ktp', 50);
            $table->string('kk', 50);
            $table->string('npwp', 50);
            $table->string('mother_name', 100);

            $table->string('bank_name', 50);
            $table->string('bank_account_number', 50);
            $table->string('bank_account_name', 50);
            $table->enum('status_dh_up', ['0', '1']);
            $table->json('access_menu')->nullable();
            $table->date('date_of_joining');
            $table->string('contribution_level_code')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
