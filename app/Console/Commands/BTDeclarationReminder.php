<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

use App\Models\BusinessTrip;
use App\Mail\BTDeclarationReminderNotification;

class BTDeclarationReminder extends Command
{
    protected $signature = "bt:declaration-reminder";
    protected $description = "BT Declaration Reminder";

    public function handle()
    {
        $now = Carbon::now();

        Log::info("⏰ [Command] Running BTDeclarationReminder at " . $now);

        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath) ?? '';
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        $today = $now->startOfDay();
        $dates = collect([1, 3, 5, 7])
            ->map(fn ($d) => $today->copy()->subDays($d)->toDateString())
            ->all();

        $transactions = BusinessTrip::with([
                'employee',
                'manager1',
                'manager2',
            ])
            ->whereIn('kembali', $dates)
            ->whereIn('status', ['Approved', 'Declaration L1'])
            ->whereNull('deleted_at')
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

            $estimate = Carbon::parse($transaction->kembali)->startOfDay();
            $diff = (int) $estimate->diffInDays($today, false);
            $recipients = [];

            $recipients[] = [
                'email' => $employee->email
            ];

            $isL1 = false;
            $isL2 = false;

            if ($transaction->manager1) {
                $isL1 = true;
            }

            if ($transaction->manager2) {
                $isL2 = true;
            }

            if ($diff === 3) {
                if ($isL1) {
                    $recipients[] = [
                        'email' => $transaction->manager1->email
                    ];
                }
            } else if ($diff === 5 || $diff === 7) {
                if ($isL1) {
                    $recipients[] = [
                        'email' => $transaction->manager1->email
                    ];
                }
                if ($isL2) {
                    $recipients[] = [
                        'email' => $transaction->manager2->email
                    ];
                }
            }

            foreach($recipients as $recipient) {
                $email = $recipient['email'] ?? null;
                if (!$email || empty($email)) {
                    continue;
                }

                Mail::to($email)->bcc('dali.kewara@kpn-corp.com')->queue(
                    (new BTDeclarationReminderNotification(
                        $employee,
                        $transaction,
                        $base64Image,
                    ))
                );
            }
        }

        Log::info("✔ [Command] BTDeclarationReminder DONE at " . Carbon::now());
        $this->info("✔ [Command] BTDeclarationReminder DONE at " . Carbon::now());
    }
}
