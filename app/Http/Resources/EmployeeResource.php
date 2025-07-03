<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'fullname' => $this->fullname,
            'gender' => $this->gender,
            'email' => $this->email,
            'group_company' => $this->group_company,
            'designation' => $this->designation,
            'designation_code' => $this->designation_code,
            'designation_name' => $this->designation_name,
            'job_level' => $this->job_level,
            'company_name' => $this->company_name,
            'contribution_level_code' => $this->contribution_level_code,
            'work_area_code' => $this->work_area_code,
            'office_area' => $this->office_area,
            'manager_l1_id' => $this->manager_l1_id,
            'manager_l2_id' => $this->manager_l2_id,
            'employee_type' => $this->employee_type,
            'unit' => $this->unit,
            'personal_email' => $this->personal_email,
            'personal_mobile_number' => $this->personal_mobile_number,
            'whatsapp_number' => $this->whatsapp_number,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'marital_status' => $this->marital_status,
            'citizenship_status' => $this->citizenship_status,
            'ethnic_group' => $this->ethnic_group,
            'homebase' => $this->homebase,
            'current_address' => $this->current_address,
            'current_city' => $this->current_city,
            'permanent_address' => $this->permanent_address,
            'permanent_city' => $this->permanent_city,
            'blood_group' => $this->blood_group,
            'tax_status' => $this->tax_status,
            'bpjs_tk' => $this->bpjs_tk,
            'bpjs_ks' => $this->bpjs_ks,
            'ktp' => $this->ktp,
            'kk' => $this->kk,
            'npwp' => $this->npwp,
            'mother_name' => $this->mother_name,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_name' => $this->bank_account_name,
            'date_of_joining' => $this->date_of_joining,
            'health_plans' => $this->healthPlans->map(function ($healthPlan) {
                return [
                    'medical_type' => $healthPlan->medical_type, // Mengakses properti individual dari $healthPlan
                    'balance' => $healthPlan->balance,
                    'period' => $healthPlan->period,
                ];
            }),
        ];
    }
}
