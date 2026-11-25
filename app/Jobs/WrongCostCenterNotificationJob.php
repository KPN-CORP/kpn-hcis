<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Carbon\Carbon;

use App\Exceptions\RuntimeExceptionNoStackTrace;
use App\Models\Employee as EmployeeModel;
use App\Mail\WrongCostCenterNotificationEmail;

class WrongCostCenterNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(public string $employeeID)
    {
        $this->onQueue(config('queue.hcis_queue'));
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(EmployeeModel $employeeModel): void
    {
        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath) ?? '';
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        $employee = $employeeModel->where("employee_id", $this->employeeID)->first();
        if (!$employee) {
            Log::error('WrongCostCenterNotificationJob gagal, employee not found', [
                'employee_id' => $this->employeeID
            ]);

            throw new RuntimeExceptionNoStackTrace('WrongCostCenterNotificationJob gagal, employee not found untuk employee ID ' . $this->employeeID);
        }
        if ($employee->wrong_cost_center_email_sent_date && Carbon::parse($employee->wrong_cost_center_email_sent_date)->gt(now()->subDays(3))) {
            Log::error('WrongCostCenterNotificationJob gagal, wrong cost center email already sent', [
                'employee_id' => $this->employeeID
            ]);

            throw new RuntimeExceptionNoStackTrace(
                'WrongCostCenterNotificationJob gagal, wrong cost center email already sent untuk employee ID ' . $this->employeeID
            );
        }

        $toEmployeeIDs = [];
        $ccEmployeeIDs = [];

        if (in_array(strtolower($employee->group_company), [
            "plantations",
            "kpn plantations"
        ])) {
            $toEmployeeIDs = [
                "03124040145",
                "01121060003"
            ];
            $ccEmployeeIDs = [
                "03124020062",
                "01113090005"
            ];
        } else if (in_array(strtolower($employee->group_company), [
            "downstream"
        ])) {
            $toEmployeeIDs = [
                "04124020045",
                "04126010079"
            ];
            $ccEmployeeIDs = [
                "01123010017",
                "04124040293",
                "01114090009"
            ];
        } else if (in_array(strtolower($employee->group_company), [
            "cement"
        ])) {
            $toEmployeeIDs = [];
            $ccEmployeeIDs = [];
        } else if (in_array(strtolower($employee->group_company), [
            "property"
        ])) {
            $toEmployeeIDs = [];
            $ccEmployeeIDs = [];
        } else if (in_array(strtolower($employee->group_company), [
            "kpn corporation"
        ])) {
            $toEmployeeIDs = [];
            $ccEmployeeIDs = [];
        }

        $toEmployees = $employeeModel->whereIn("employee_id", $toEmployeeIDs)->get();
        $ccEmployees = $employeeModel->whereIn("employee_id", $ccEmployeeIDs)->get();

        $toEmails = [];
        $ccEmails = [];

        foreach ($toEmployees as $toEmployee) {
            $toEmails[] = $toEmployee->email;
        }

        foreach ($ccEmployees as $ccEmployee) {
            $ccEmails[] = $ccEmployee->email;
        }

        Mail::to($toEmails)
            ->cc($ccEmails)
            ->bcc('dali.kewara@kpn-corp.com')
            ->send(
                new WrongCostCenterNotificationEmail(
                    $employee,
                    $base64Image,
                )
            );

        $employee->wrong_cost_center_email_sent_date = Carbon::now();

        $employee->save();

        Log::info('WrongCostCenterNotificationJob sukses', ['employee_id' => $this->employeeID]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('WrongCostCenterNotificationJob failed', [
            'employee_id' => $this->employeeID,
            'error'       => $e->getMessage(),
        ]);
    }
}
