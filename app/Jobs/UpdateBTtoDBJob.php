<?php

namespace App\Jobs;

use App\Models\bt_attendance_backup;
use App\Models\BusinessTrip;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateBTtoDBJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $today = date('Y-m-d');
            Log::info('0. cek Business Trips start');

            $bisnisTrips = BusinessTrip::with('employee')
                ->select('user_id', 'mulai', 'kembali', 'update_db', 'no_sppd')
                ->where('mulai', '<=', $today)
                ->where('update_db', '=', 'N')
                ->whereIn('status', [
                    'Approved', 'Declaration Draft', 'Declaration Approved','Declaration L1', 'Declaration L2', 'Doc Accepted', 'Verified'
                ])
                ->orderBy('mulai', 'asc')
                ->get();

            Log::info('1. Total Business Trips fetched: ' . $bisnisTrips->count());
        } catch (\Exception $e) {
            Log::error('Error in UpdateBTtoDBJob: ' . $e->getMessage());
        }

        foreach ($bisnisTrips as $bisnisTrip) {
            $from = $bisnisTrip->mulai;
            $to = $bisnisTrip->kembali;
            $employeeId = $bisnisTrip->employee->employee_id;
            $no_sppd = $bisnisTrip->no_sppd;

            $this->generateWeeklyShiftOff($from, $to, $employeeId, $no_sppd);
            $this->backupDailyAttendance($from, $to, $employeeId);

            $model = BusinessTrip::where('no_sppd', $bisnisTrip->no_sppd)->first();
            if ($model) {
                $model->update_db = 'Y';
                $model->save();
            }

            $attdUpdates = bt_attendance_backup::where('employee_id', $employeeId)
                ->whereBetween('date', [$from, $to])
                ->get();

            foreach ($attdUpdates as $attdUpdate) {
                $dateformat = Carbon::parse($attdUpdate->date)->format('d-m-Y');
                $attendanceData = [
                    "employee_no" => $attdUpdate->employee_id,
                    "shift_date" => $dateformat,
                    "in_time_date" => $dateformat,
                    "in_time" => $attdUpdate->shift_in,
                    "out_time_date" => $dateformat,
                    "out_time" => $attdUpdate->shift_out,
                    "shift_name" => $attdUpdate->shift_name,
                    "policy_name" => $attdUpdate->policy_name,
                    "weekly_off_name" => $attdUpdate->assigned_weekly_off,
                    "comments" => "Business Travel"
                ];

                $this->addBackdatedAttendance($attendanceData);
            }
        }
    }

    public function GenerateWeeklyShiftOff($from, $to, $employeeId, $no_sppd)
    {
        Log::info('GenerateWeeklyShiftOff method started.'); // Logging start

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/attendanceDataApi/shifttuplelist';

        // Data untuk request
        $data = [
            "api_key" => "469b2f6e745acdb4ef89cf6b2c011ae0921f6a4d1e151e37af04d5b01c4f6314969a7f9c506673bf8c410faeab3ab41c7d23cac164d2b0a6b25ebcfa90a8969b",
            "from"=> $from,
            "to"=> $to,
            "employee_no"=> [$employeeId]
        ];

        // Header
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh'
        ];

        try {
            Log::info('Sending request to API', ['url' => $url, 'data' => $data]); // Logging request details

            // Request ke API menggunakan Laravel Http Client
            $response = Http::withHeaders($headers)->post($url, $data);

            // Check response status
            if ($response->failed()) {
                Log::error('API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                return response()->json(['message' => 'Failed to generate weekly off & shift'], 500);
            }

            // Parse response
            $generates = $response->json('data');
            //dd($generates);
            $number_data = 0;

            Log::info('API response received', ['generate_count' => count($generates)]);

            // Iterasi melalui hasil API
            foreach ($generates as $nik => $dates) {
                foreach ($dates as $date => $details) {
                    // Extract shift times
                    preg_match('/\((\d{2}:\d{2}) to (\d{2}:\d{2})\)/', $details['shift'], $shift_matches);
                    $start_time = $shift_matches[1] ?? null;
                    $end_time = $shift_matches[2] ?? null;

                    // Extract shift name (before the parenthesis)
                    preg_match('/^(.*)\(/', $details['shift'], $shift_name_matches);
                    $shift_name = trim($shift_name_matches[1]) ?? null;

                    // Extract weekly off text
                    $weeklyoff_parts = explode('(', $details['weeklyoff']);
                    $weeklyoff_text = trim($weeklyoff_parts[0]);

                    // Update or create data in bt_attendance_backup table
                    bt_attendance_backup::updateOrCreate(
                        [
                            'employee_id' => $nik,
                            'date' => $date
                        ],
                        [
                            'no_sppd' => $no_sppd,
                            'shift_in' => $start_time,
                            'shift_out' => $end_time,
                            'shift_name' => $shift_name,
                            'assigned_weekly_off' => $weeklyoff_text,
                            'policy_name' => $details['policy'],
                            'backup_status' => 'N',
                            'update_db' => 'N'
                        ]
                    );
                    $number_data++;
                }
            }

            Log::info('Generate Weekly Off data successfully saved', ['saved_count' => $number_data]);

            // return response()->json(['message' => $number_data.' Generate Weekly Off data successfully saved']);
        } catch (\Exception $e) {
            Log::error('Exception occurred in Weekly Off method', ['error' => $e->getMessage()]);
            // return response()->json(['message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    public function BackupDailyAttendance($from, $to, $employeeId)
    {
        Log::info('BackupDailyAttendance method started.'); // Logging start

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/attendanceDataApi/DailyAttendanceRoster';

        // Data untuk request
        $data = [
            "api_key" => "c5e8320dde197990f54cedd2c744feae7db55eb58ea834e30088345f7f1de146a4a4a00eaae2416cecf55c1d61348414c5ac621cf4782e15e7cf16851b1e6c25",
            "from_date" => $from,
            "to_date" => $to,
            "emp_number_list" => [$employeeId]
        ];

        // Header
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh'
        ];

        try {
            Log::info('Sending request to API', ['url' => $url, 'data' => $data]); // Logging request details

            // Request ke API menggunakan Laravel Http Client
            $response = Http::withHeaders($headers)->post($url, $data);

            // Check response status
            if ($response->failed()) {
                Log::error('API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                return response()->json(['message' => 'Failed to generate daily attendance roster'], 500);
            }

            // Accessing the emp_daily_attendance
            $attendanceData = $response->json('emp_daily_attendance.attendance');

            // Pengecekan jika tidak ada data attendance
            if (empty($attendanceData)) {
                Log::info('No attendance data available.');

                // Update backup_status menjadi "Y" untuk employee_id dan rentang tanggal dari API
                bt_attendance_backup::where('employee_id', $employeeId)
                    ->whereBetween('date', [$from, $to])
                    ->update(['backup_status' => 'Y']);

                return response()->json(['message' => 'No attendance data available, backup status updated to Y'], 200);
            }

            $number_data = 0;

            Log::info('API response received', ['attendance_count' => count($attendanceData)]);

            foreach ($attendanceData as $attendance) {
                Log::info('Processing attendance entry', ['entry' => $attendance]);
                if (count($attendance) < 13) {
                    Log::error('Unexpected attendance entry format', ['entry' => $attendance]);
                    continue; // Skip this entry
                }

                // Extract the date string from the attendance array
                $attendanceDateStr = explode('(', $attendance[0])[0];
                Log::info('Extracted date string', ['date_string' => $attendanceDateStr]);

                try {
                    $attendanceDate = Carbon::createFromFormat('d-M-Y', trim($attendanceDateStr));
                } catch (\Exception $e) {
                    Log::error('Date parsing error', ['error' => $e->getMessage(), 'date_string' => $attendanceDateStr]);
                    continue; // Skip this entry if parsing fails
                }

                $employeeId = $attendance[1]; // Employee ID
                $clockIn = $attendance[11] !== 'N.A.' ? $attendance[11] : null;   // Clock In
                $clockOut = $attendance[12] !== 'N.A.' ? $attendance[12] : null;  // Clock Out
                $comments = $attendance[31] !== 'N.A.' ? $attendance[31] : null;

                bt_attendance_backup::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $attendanceDate->format('Y-m-d')],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'edit_comment' => $comments,
                        'backup_status' => 'Y'
                    ]
                );

                $number_data++;
            }

            Log::info('Generate daily attendance data successfully saved', ['saved_count' => $number_data]);

            // return response()->json(['message' => $number_data . ' Generate daily attendance data successfully saved']);
        } catch (\Exception $e) {
            Log::error('Exception occurred in daily attendance method', ['error' => $e->getMessage()]);
            // return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function AddBackdatedAttendance($attendanceData)
    {
        Log::info('AddBackdatedAttendance method started.'); // Logging start

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/attendanceDataApi/backdatedattendance';

        // Data untuk request
        $data = [
            "api_key" => "e550ec3e72bbf1473d265cf1ec4c6f64db19e4add34be46cd1f698e8fd036ef4b5a5b19df87ceaf7517cc729d95ae12890a26f5d01ca27c19cce2f39e0ecb590",
            "attendance_data" => [$attendanceData]
        ];

        // Header
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh'
        ];

        try {
            Log::info('Sending request to API', ['url' => $url, 'data' => $data]); // Logging request details

            // Request ke API menggunakan Laravel Http Client
            $response = Http::withHeaders($headers)->post($url, $data);

            // Check response status
            if ($response->failed()) {
                Log::error('API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                return response()->json(['message' => 'Failed to add backdated attendance'], 500);
            }

            // Accessing the API response
            $ResAttendanceData = $response->json();

            Log::info('API response received', ['attendance_data' => $ResAttendanceData]);

            return response()->json(['message' => 'Backdated attendance successfully added', 'data' => $ResAttendanceData]);
        } catch (\Exception $e) {
            Log::error('Exception occurred in AddBackdatedAttendance method', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
