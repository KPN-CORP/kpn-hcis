<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\ApprovalLayer;
use App\Models\Location;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class EmployeeController extends Controller
{
    function employee() {
        $link = 'employee';

        $employees = employee::all();
        $locations = Location::orderBy('area')->get();

        return view('pages.employees.employee', [
            'link' => $link,
            'employees' => $employees,
            'locations' => $locations,
        ]);        
    }
    public function EmployeeInactive()
    {
        Log::info('EmployeeInactive method started.'); // Logging start

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/masterapi/employee';

        // Data untuk request
        $data = [
            "api_key" => "08250fed4ef60d6c22fe007afd929c0f98ba0da2a73554921f8569a93ec25970e032fd4616f9d934251cba0489f868448c35017d84f7f6e80096610590d0e406",
            "datasetKey" => "11825c66855343b39a819a78eefc7bfb93d9ede4ca6f632e6f24a22295e24169f04eb795018eba86b448f35c886f866329b9acaac1b5e814814ca471a2a9460c"
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
                return response()->json(['message' => 'Failed to fetch employees data'], 500);
            }

            // Parse response
            $employees = $response->json('employee_data');

            $number_data = 0;

            Log::info('API response received', ['employee_count' => count($employees)]);

            // Simpan data ke database
            foreach ($employees as $employee) {
                $existingEmployee = Employee::where('employee_id', $employee['employee_id'])->first();
    
                if ($existingEmployee) {
                    // Convert the `deleted_at` to date format
                    $deletedAtDate = $existingEmployee->deleted_at ? date('Y-m-d', strtotime($existingEmployee->deleted_at)) : null;
                    
                    if ($deletedAtDate !== $employee['date_of_exit']) {
                        
                        DB::table('employees')
                        ->where('employee_id', $employee['employee_id'])
                        ->update([
                            'deleted_at' => $employee['date_of_exit'] . ' 00:00:00',
                            'email' => $existingEmployee->email . '_terminate',
                        ]);
                        DB::table('users')
                        ->where('employee_id', $employee['employee_id'])
                        ->update([
                            'email' => $existingEmployee->email . '_terminate',
                        ]);
                        $number_data++;
                    }
                }
            }

            Log::info('Inactive Employees data successfully saved', ['saved_count' => $number_data]);

            return response()->json(['message' => $number_data.' Inactive Employees data successfully saved']);
        } catch (\Exception $e) {
            Log::error('Exception occurred in EmployeeInactive method', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    public function fetchAndStoreEmployees()
    {
        Log::info('fetchAndStoreEmployees method started.');

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/masterapi/employee';

        // Header
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh'
        ];

        // Loop untuk handle multiple requests 0, 1801 , 3601, 5401
        $startOffsets = [0, 1801];
        $totalSaved = 0;

        foreach ($startOffsets as $start) {
            // Data untuk request dengan limit dan start
            $data = [
                "api_key" => "46313f36ab8a8bc5aad64ff1c80c769a07716d9af8f07850f6ad2465a0c991b4d42e84414b5775ba151e7f2833223bfb1e0ecf49b89c7d6d0f6a6d39231666f8",
                "datasetKey" => "11f8ded39d3f22e7c71900d90605c7bf8ef211ac94956b07cc2f8c340d61a4528342feb5afc4b9a9db0b980427007db0dd61db52ae857699d80d9c79a28078cc",
                "limit" => 1800,
                "start" => $start,
            ];

            try {
                Log::info('Sending request to API', ['url' => $url, 'data' => $data]);

                // Request ke API menggunakan Laravel Http Client
                $response = Http::withHeaders($headers)->post($url, $data);

                // Check response status
                if ($response->failed()) {
                    Log::error('API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                    continue; // Lanjutkan iterasi berikutnya
                }

                // Parse response
                $employees = $response->json('employee_data');
                if (empty($employees)) {
                    Log::info('No employees data returned from API', ['start' => $start]);
                    continue;
                }

                Log::info('API response received', ['employee_count' => count($employees)]);

                // Simpan data ke database
                foreach ($employees as $employee) {
                    User::updateOrCreate(
                        ['employee_id' => $employee['employee_id']],
                        [
                            'id' => $employee['user_unique_id'],
                            'employee_id' => $employee['employee_id'],
                            'name' => $employee['full_name'],
                            'email' => $employee['company_email_id']
                        ]
                    );

                    Employee::updateOrCreate(
                        ['employee_id' => $employee['employee_id']],
                        [
                            'id' => $employee['user_unique_id'],
                            'employee_id' => $employee['employee_id'],
                            'fullname' => $employee['full_name'],
                            'gender' => $employee['gender'],
                            'email' => $employee['company_email_id'],
                            'group_company' => $employee['group_company'],
                            'designation' => $employee['designation'],
                            'designation_code' => $employee['designation_code'],
                            'designation_name' => $employee['designation_name'],
                            'job_level' => $employee['job_level'],
                            'company_name' => $employee['contribution_level'],
                            'contribution_level_code' => $employee['contribution_level_code'],
                            'work_area_code' => $employee['work_area_code'],
                            'office_area' => $employee['office_area'],
                            'manager_l1_id' => $employee['direct_manager_employee_id'],
                            'manager_l2_id' => $employee['l2_manager_employee_id'],
                            'employee_type' => $employee['employee_type'],
                            'unit' => $employee['unit'],
                            'date_of_joining' => $employee['date_of_joining'],
                            'date_of_birth' => $employee['date_of_birth'],
                            'place_of_birth' => $employee['place_of_birth'],
                            'nationality' => $employee['nationality'],
                            'religion' => $employee['religion'],
                            'marital_status' => $employee['marital_status'],
                            'citizenship_status' => $employee['citizenship_status'],
                            'ethnic_group' => $employee['suku'],
                            'homebase' => $employee['homebase'],
                            'current_address' => $employee['current_address'],
                            'current_city' => $employee['current_city'],
                            'permanent_address' => $employee['permanent_address'],
                            'permanent_city' => $employee['permanent_city'],
                            'blood_group' => $employee['blood_group'],
                            'tax_status' => $employee['status_ptkp'],
                            'bpjs_tk' => $employee['badan_penyelenggara_jaminan_sosial_(bpjs)_tenaga_kerja_(bpjstk)'],
                            'bpjs_ks' => $employee['badan_penyelenggara_jaminan_sosial_(bpjs)_kesehatan_(bpjskes)'],
                            'ktp' => $employee['nomor_ktp'],
                            'kk' => $employee['family_card_number_(nomor_kk)'],
                            'npwp' => $employee['nomor_npwp'],
                            'mother_name' => $employee['mother_name'],
                            'bank_name' => $employee['nama_bank'],
                            'bank_account_number' => $employee['bank_account'],
                            'bank_account_name' => $employee['nama_pemilik_rekening'],
                            'users_id' => $employee['user_unique_id']
                        ]
                    );

                    $approvalLayerExists = ApprovalLayer::where('employee_id', $employee['employee_id'])->exists();

                    // If not exists, insert two records
                    if (!$approvalLayerExists) {
                        ApprovalLayer::create([
                            'employee_id' => $employee['employee_id'],
                            'approver_id' => $employee['direct_manager_employee_id'],
                            'layer' => '1'
                        ]);

                        ApprovalLayer::create([
                            'employee_id' => $employee['employee_id'],
                            'approver_id' => $employee['l2_manager_employee_id'],
                            'layer' => '2'
                        ]);
                    }

                    $totalSaved++;
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred during API fetch', ['start' => $start, 'error' => $e->getMessage()]);
                continue;
            }
        }

        Log::info('fetchAndStoreEmployees method completed.', ['total_saved' => $totalSaved]);
        return response()->json(['message' => $totalSaved . ' Employees data successfully saved']);
    }
    public function fetchAndStoreEmployees2()
    {
        Log::info('fetchAndStoreEmployees method started.');

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/masterapi/employee';

        // Header
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh'
        ];

        // Loop untuk handle multiple requests 0, 1801 , 3601, 5401
        $startOffsets = [3601, 5401];
        $totalSaved = 0;

        foreach ($startOffsets as $start) {
            // Data untuk request dengan limit dan start
            $data = [
                "api_key" => "46313f36ab8a8bc5aad64ff1c80c769a07716d9af8f07850f6ad2465a0c991b4d42e84414b5775ba151e7f2833223bfb1e0ecf49b89c7d6d0f6a6d39231666f8",
                "datasetKey" => "11f8ded39d3f22e7c71900d90605c7bf8ef211ac94956b07cc2f8c340d61a4528342feb5afc4b9a9db0b980427007db0dd61db52ae857699d80d9c79a28078cc",
                "limit" => 1800,
                "start" => $start,
            ];

            try {
                Log::info('Sending request to API', ['url' => $url, 'data' => $data]);

                // Request ke API menggunakan Laravel Http Client
                $response = Http::withHeaders($headers)->post($url, $data);

                // Check response status
                if ($response->failed()) {
                    Log::error('API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                    continue; // Lanjutkan iterasi berikutnya
                }

                // Parse response
                $employees = $response->json('employee_data');
                if (empty($employees)) {
                    Log::info('No employees data returned from API', ['start' => $start]);
                    continue;
                }

                Log::info('API response received', ['employee_count' => count($employees)]);

                // Simpan data ke database
                foreach ($employees as $employee) {
                    User::updateOrCreate(
                        ['employee_id' => $employee['employee_id']],
                        [
                            'id' => $employee['user_unique_id'],
                            'employee_id' => $employee['employee_id'],
                            'name' => $employee['full_name'],
                            'email' => $employee['company_email_id']
                        ]
                    );

                    Employee::updateOrCreate(
                        ['employee_id' => $employee['employee_id']],
                        [
                            'id' => $employee['user_unique_id'],
                            'employee_id' => $employee['employee_id'],
                            'fullname' => $employee['full_name'],
                            'gender' => $employee['gender'],
                            'email' => $employee['company_email_id'],
                            'group_company' => $employee['group_company'],
                            'designation' => $employee['designation'],
                            'designation_code' => $employee['designation_code'],
                            'designation_name' => $employee['designation_name'],
                            'job_level' => $employee['job_level'],
                            'company_name' => $employee['contribution_level'],
                            'contribution_level_code' => $employee['contribution_level_code'],
                            'work_area_code' => $employee['work_area_code'],
                            'office_area' => $employee['office_area'],
                            'manager_l1_id' => $employee['direct_manager_employee_id'],
                            'manager_l2_id' => $employee['l2_manager_employee_id'],
                            'employee_type' => $employee['employee_type'],
                            'unit' => $employee['unit'],
                            'date_of_joining' => $employee['date_of_joining'],
                            'place_of_birth' => $employee['place_of_birth'],
                            'nationality' => $employee['nationality'],
                            'religion' => $employee['religion'],
                            'marital_status' => $employee['marital_status'],
                            'citizenship_status' => $employee['citizenship_status'],
                            'ethnic_group' => $employee['suku'],
                            'homebase' => $employee['homebase'],
                            'current_address' => $employee['current_address'],
                            'current_city' => $employee['current_city'],
                            'permanent_address' => $employee['permanent_address'],
                            'permanent_city' => $employee['permanent_city'],
                            'blood_group' => $employee['blood_group'],
                            'tax_status' => $employee['status_ptkp'],
                            'bpjs_tk' => $employee['badan_penyelenggara_jaminan_sosial_(bpjs)_tenaga_kerja_(bpjstk)'],
                            'bpjs_ks' => $employee['badan_penyelenggara_jaminan_sosial_(bpjs)_kesehatan_(bpjskes)'],
                            'ktp' => $employee['nomor_ktp'],
                            'kk' => $employee['family_card_number_(nomor_kk)'],
                            'npwp' => $employee['nomor_npwp'],
                            'mother_name' => $employee['mother_name'],
                            'bank_name' => $employee['nama_bank'],
                            'bank_account_number' => $employee['bank_account'],
                            'bank_account_name' => $employee['nama_pemilik_rekening'],
                            'users_id' => $employee['user_unique_id']
                        ]
                    );

                    $approvalLayerExists = ApprovalLayer::where('employee_id', $employee['employee_id'])->exists();

                    // If not exists, insert two records
                    if (!$approvalLayerExists) {
                        ApprovalLayer::create([
                            'employee_id' => $employee['employee_id'],
                            'approver_id' => $employee['direct_manager_employee_id'],
                            'layer' => '1'
                        ]);

                        ApprovalLayer::create([
                            'employee_id' => $employee['employee_id'],
                            'approver_id' => $employee['l2_manager_employee_id'],
                            'layer' => '2'
                        ]);
                    }

                    $totalSaved++;
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred during API fetch', ['start' => $start, 'error' => $e->getMessage()]);
                continue;
            }
        }

        Log::info('fetchAndStoreEmployees method completed.', ['total_saved' => $totalSaved]);
        return response()->json(['message' => $totalSaved . ' Employees data successfully saved']);
    }
    public function updateEmployeeAccessMenu()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Get schedules with start_date or end_date equals today
        $schedules = DB::table('schedules')
            ->where(function($query) use ($today) {
                $query->where('start_date', $today)
                      //->orWhere('end_date', $today);
                      ->orWhere(DB::raw('DATE_ADD(end_date, INTERVAL 1 DAY)'), $today);
            })
            ->whereNull('deleted_at')
            ->get();
            // dd($schedules);

            foreach ($schedules as $schedule) {
                if ($schedule->start_date == $today) {
                    // Update employees' access_menu to {"goals":1}
                    $this->updateEmployees($schedule, '1');
                }
                
                //if ($schedule->end_date == $today) {
                if (Carbon::parse($schedule->end_date)->addDay()->format('Y-m-d') == $today) {
                    // Update employees' access_menu to {"goals":0}
                    $this->updateEmployees($schedule, '0');
                }
            }

        return 'Employee access menu updated successfully.';
    }
    protected function updateEmployees($schedule, $accessMenu)
    {
        $query = DB::table('employees');

        if ($schedule->employee_type) {
            $query->where('employee_type', $schedule->employee_type);
        }

        if ($schedule->bisnis_unit) {
            $query->whereIn('group_company', explode(',', $schedule->bisnis_unit));
        }

        if ($schedule->company_filter) {
            $query->whereIn('contribution_level_code', explode(',', $schedule->company_filter));
        }

        if ($schedule->location_filter) {
            $query->whereIn('work_area_code', explode(',', $schedule->location_filter));
        }

        if ($schedule->last_join_date) {
            $query->where('date_of_joining', '<=', $schedule->last_join_date);
        }

        $employees = $query->get();

        foreach ($employees as $employee) {
            $accessMenuJson = json_decode($employee->access_menu, true);

            $accessMenuJson['goals'] = $accessMenu;
            $accessMenuJson['doj'] = 1;

            DB::table('employees')
                ->where('id', $employee->id)
                ->update([
                    'access_menu' => json_encode($accessMenuJson),
                    'updated_at' => Carbon::now()  // Update the updated_at column
                ]);
        }
    }
}
