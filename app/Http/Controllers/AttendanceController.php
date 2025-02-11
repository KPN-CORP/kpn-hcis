<?php

namespace App\Http\Controllers;

use App\Models\bt_attendance_backup;
use App\Models\Employee;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Jobs\UpdateBTtoDBJob;

class AttendanceController extends Controller
{
    public function UpdateBTtoDB()
    {
        Log::info('AttendanceController start');
        UpdateBTtoDBJob::dispatch();

        return response()->json(['message' => 'Job dispatched successfully!']);
        
        // $user = Auth::user();
        // $today = date('Y-m-d');
        // $bisnisTrips = BusinessTrip::with('employee')
        // ->select('user_id', 'mulai', 'kembali','update_db','no_sppd')
        // ->where('mulai','<=',$today)
        // ->where('update_db', '=','N')
        // ->whereIn('status',['Approved','Declaration Draft','Declaration Approved','Declaration L1','Declaration L2','Doc Accepted','Verified'])
        // ->where('user_id', '=','23780')
        // ->orderBy('mulai', 'asc')
        // ->get();

        // foreach ($bisnisTrips as $bisnisTrip) {
        //     $from = $bisnisTrip->mulai;
        //     $to = $bisnisTrip->kembali;
        //     $employeeId = $bisnisTrip->employee->employee_id;
        //     $no_sppd = $bisnisTrip->no_sppd;

        //     $this->GenerateWeeklyShiftOff($from, $to, $employeeId, $no_sppd);
        //     $this->BackupDailyAttendance($from, $to, $employeeId);

        //     $model = BusinessTrip::where('no_sppd', $bisnisTrip->no_sppd)->first();
        //     if ($model) {
        //         $model->update_db = 'Y';
        //         $model->save();
        //     }

        //     $AttdUpdates = bt_attendance_backup::where('employee_id',$employeeId)
        //     ->whereBetween('date', [$from, $to])
        //     ->get();

        //     foreach ($AttdUpdates as $AttdUpdate) {
        //         $dateformat = Carbon::parse($AttdUpdate->date)->format('d-m-Y');
        //         $attendanceData = [
        //             "employee_no" => $AttdUpdate->employee_id,
        //             "shift_date" => $dateformat,
        //             "in_time_date" => $dateformat,
        //             "in_time" => $AttdUpdate->shift_in,
        //             "out_time_date" => $dateformat,
        //             "out_time" => $AttdUpdate->shift_out,
        //             "shift_name" => $AttdUpdate->shift_name,
        //             "policy_name" => $AttdUpdate->policy_name,
        //             "weekly_off_name" => $AttdUpdate->assigned_weekly_off,
        //             "comments" => "Business Trip"
        //         ];

        //         $this->AddBackdatedAttendance($attendanceData);
        //     }
        // }
    }
}
