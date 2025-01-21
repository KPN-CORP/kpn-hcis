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
            'group_company' => $this->group_company,
            'unit' => $this->unit,
            'designation' => $this->designation,
            'job_level' => $this->job_level,
            'office_area' => $this->office_area,
            'company_email_id' => $this->company_email_id,
            'personal_mobile_number' => $this->personal_mobile_number,
            'gender' => $this->gender,
            'email' => $this->email,
            'company_name' => $this->company_name,
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
