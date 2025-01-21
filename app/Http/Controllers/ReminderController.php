<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ca_approval;
use App\Models\CATransaction;
use App\Models\Employee;
use Carbon\Carbon;  
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\CashAdvancedReminder;

class ReminderController extends Controller
{
    function emailReminder()  
    {  
        $today = Carbon::now()->startOfDay(); // Hari ini
        $yesterday = Carbon::now()->subDay()->startOfDay(); // Kemarin  
        $tomorrow = Carbon::now()->addDay()->startOfDay(); // Besok

        if ($today->isSaturday() || $today->isSunday()) {
            return; // Tidak melakukan apa-apa
        }

        // Mendapatkan transaksi sesuai kriteria
        $ca_transaction = CATransaction::where('approval_status', '<>', 'Rejected')
            ->where('ca_status', '<>', 'Done')
            ->where('deleted_at', null) // Deleted_at harus NULL
            ->where('approval_status', 'Approved') // Hanya yang sudah di-approve
            ->where('approval_sett', '') // approval_sett harus kosong
            ->where('declare_estimate', '<=', $tomorrow) // Hari ini atau besok
            ->get();

        if ($ca_transaction->isNotEmpty()) {   
            foreach ($ca_transaction as $transaction) {  
                // $CANotificationLayer = Employee::where('id', $transaction->user_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                // Jika $transaction tidak null, coba akses employee_id  
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);

                if ($CANotificationLayer) {
                    $textNotification = "{$transaction->employee->fullname} Submit an Extend Service with the following details :";
                    $reminder = "Extend";
                    $declaration = "Declaration";

                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedReminder(
                            $transaction,
                            $textNotification,
                            $reminder,
                            $base64Image,
                            $declaration,
                        ));       
                    } catch (\Exception $e) {
                        Log::error('Email Reminder tidak terkirim: ' . $e->getMessage());
                    }
                }
            }  

            return redirect()->route('reimbursements');
        } else {  
            return redirect()->route('reimbursements');
        }  
    }  
}
