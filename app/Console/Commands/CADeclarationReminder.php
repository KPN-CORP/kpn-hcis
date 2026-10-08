<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

use App\Models\CATransaction;
use App\Mail\CADeclarationReminderNotification;

class CADeclarationReminder extends Command
{
    protected $signature = "ca:declaration-reminder";
    protected $description = "CA Declaration Reminder";

    public function handle()
    {
        $now = Carbon::now();

        Log::info("⏰ [Command] Running CADeclarationReminder at " . $now);

        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath) ?? '';
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        $today = $now->startOfDay();
        $dates = collect([1, 3, 5, 7])
            ->map(fn ($d) => Carbon::today()->addDays($d)->toDateString())
            ->all();

        $transactions = CATransaction::with([
                'employee',
                'approvals' => fn ($q) => $q->whereNull('deleted_at'),
                'approvals.employee',
            ])
            ->whereNull('no_sppd')
            ->whereIn('declare_estimate', $dates)
            ->whereIn('approval_sett', ['Pending'])
            ->whereIn('ca_status', ['-', 'On Progress'])
            ->whereNull('deleted_at')
            ->whereHas('approvals', fn ($q) => $q->whereNull('deleted_at'))
            ->get();

        foreach ($transactions as $transaction) {
            $id = $transaction->id ?? null;
            if (!$id || empty($id)) {
                continue;
            }

            $employee = $transaction->employee ?? null;
            if (!$employee) {
                continue;
            }

            $approvals = $transaction->approvals ?? null;
            if (!$approvals) {
                continue;
            }

            $estimate = Carbon::parse($transaction->declare_estimate)->startOfDay();
            $diff = $today->diffInDays($estimate, false);
            $recipients = [];

            $recipients[] = [
                'email' => $employee->email
            ];

            foreach($approvals as $approval) {
                $approvalEmployee = $approval->employee ?? null;
                if (!$approvalEmployee) {
                    continue;
                }

                $roleName = $approval->role_name;
                if (!$roleName || empty($roleName)) {
                    continue;
                }

                $isHCGAOrHCO = false;
                $isL1 = false;
                $isL2 = false;
                $isHeadAP = false;
                $isHeadHC = false;

                if (str_contains($roleName, 'Dept Head HC GA')) {
                    $isHCGAOrHCO = true;
                } else if (str_contains($roleName, 'Dept Head AR & AP')) {
                    $isHeadAP = true;
                } else if (str_contains($roleName, 'Div Head HC')) {
                    $isHeadHC = true;
                } else if (str_contains($roleName, 'Dept Head')) {
                    $isL1 = true;
                } else if (str_contains($roleName, 'Div Head')) {
                    $isL2 = true;
                }

                if ($diff === 1) {
                    if ($isHCGAOrHCO) {
                        $recipients[] = [
                            'email' => $approvalEmployee->email
                        ];
                    }
                } else if ($diff === 3) {
                    if ($isL1 || $isHCGAOrHCO) {
                        $recipients[] = [
                            'email' => $approvalEmployee->email
                        ];
                    }
                } else if ($diff === 5) {
                    if ($isL1 || $isL2 || $isHCGAOrHCO || $isHeadAP) {
                        $recipients[] = [
                            'email' => $approvalEmployee->email
                        ];
                    }
                } else if ($diff === 7) {
                    if ($isL1 || $isL2 || $isHCGAOrHCO || $isHeadAP || $isHeadHC) {
                        $recipients[] = [
                            'email' => $approvalEmployee->email
                        ];
                    }
                } else {
                    continue;
                }
            }

            foreach($recipients as $recipient) {
                $email = $recipient['email'] ?? null;
                if (!$email || empty($email)) {
                    continue;
                }

                Mail::to($email)->bcc('dali.kewara@kpn-corp.com')->queue(
                    (new CADeclarationReminderNotification(
                        $base64Image
                    ))
                );
            }
        }

        $this->info("✔ [Command] CADeclarationReminder DONE at " . Carbon::now());
    }
}
