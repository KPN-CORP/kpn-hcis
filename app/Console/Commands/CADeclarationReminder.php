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
        $imageContent = file_get_contents($imagePath);
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        $today = $now->startOfDay();
        $dates = collect([1, 3, 5, 7])
            ->map(fn ($d) => Carbon::today()->addDays($d)->toDateString())
            ->all();

        $transactions = CATransaction::with('employee')
            ->whereNull('no_sppd')
            ->whereIn('declare_estimate', $dates)
            ->whereIn('approval_sett', ['Pending'])
            ->whereIn('ca_status', ['-', 'On Progress'])
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

            $estimate = Carbon::parse($transaction->declare_estimate)->startOfDay();
            $diff = $today->diffInDays($estimate, false);
            $recipients = [];

            if ($diff === 1) {
                // H+1
            } else if ($diff === 3) {
                // H+3
            } else if ($diff === 5) {
                // H+5
            } else if ($diff === 7) {
                // H+7
            } else {
                continue;
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
