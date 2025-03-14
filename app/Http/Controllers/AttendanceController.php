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
    }

    public function UpdateBTtoDBnextstep()
    {
        $attdUpdates = bt_attendance_backup::where('backup_status', 'N')
            ->whereDate('date', '<', Carbon::today()) // Tambahkan filter tanggal
            ->get();

        $no=0;
        $processedData = [];

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
            $no++;

            $processedData[] = $attendanceData;

            $attdUpdate->update(['backup_status' => 'Y']);
        }
        return response()->json([
            'message' => 'Job dispatched successfully!',
            'total_executed' => $no,
            'processed_data' => $processedData
        ]);
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
