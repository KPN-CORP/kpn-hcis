<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Models\HealthPlan;
use App\Models\MasterPlafond;
use App\Mail\MedicalRemainingPlafondNotification;

class MedicalRemainingPlafondJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $today = Carbon::today();
            $year = $today->year;

            Log::info('MedicalRemainingPlafondJob START');

            $imagePath = public_path("images/kop.jpg");
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);

            $healthPlans = HealthPlan::with('employee')
                ->whereIn('medical_type', ['Outpatient', 'Inpatient'])
                ->where('period', $year)
                ->whereHas('employee', function ($query) {
                    $query->where('group_company', 'Downstream');
                })
                ->where(function ($query) use ($today) {
                    $query->whereNull('remaining_plafond_email_sent_date')
                        ->orWhere(
                            'remaining_plafond_email_sent_date',
                            '<=',
                            $today->copy()->subMonths(1)
                        );
                })
                ->whereNull("deleted_at")
                ->get();

            foreach($healthPlans as $healthPlan) {
                $employee = $healthPlan->employee;
                if (!$employee) {
                    continue;
                }

                $plafond = MasterPlafond::where("group_name", $employee->job_level)
                    ->where("medical_type", $healthPlan->medical_type)
                    ->where("active", "T")
                    ->first();
                if (!$plafond) {
                    continue;
                }

                $plafondBalance = $plafond->balance ?? 0;
                $currentBalance = $healthPlan->balance ?? 0;
                $isBelowThreshold = $currentBalance < ($plafondBalance * 0.20);

                if (!$isBelowThreshold) {
                    continue;
                }

                Mail::to($employee->email)->bcc('dali.kewara@kpn-corp.com')->queue(
                    (new MedicalRemainingPlafondNotification(
                        $plafond,
                        $healthPlan,
                        $employee,
                        $base64Image
                    ))->onQueue('hcis')
                );

                $healthPlan->remaining_plafond_email_sent_date = $today;

                $healthPlan->save();
            }

            Log::info('MedicalRemainingPlafondJob END', [
                'count' => $healthPlans->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in MedicalRemainingPlafondJob: ' . $e->getMessage());
        }
    }
}
