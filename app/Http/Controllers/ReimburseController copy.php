<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\ca_transaction;
use App\Models\CAApproval;
use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Employee;
use App\Models\MatrixApproval;
use App\Models\ListPerdiem;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;
use App\Models\CATransaction;
use App\Http\Controllers\Log;
use App\Models\ca_approval;
use App\Models\htl_transaction;
use App\Models\tkt_transaction;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReimburseController extends Controller
{
    function reimbursements()
    {

        $userId = Auth::id();

        return view('hcis.reimbursements.dash', [
            'userId' => $userId,
        ]);
    }
    public function cashadvanced()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';
        $ca_transactions = CATransaction::with('employee')->where('user_id', $userId)->get();
        $pendingCACount = CATransaction::where('user_id', $userId)->where('approval_status', 'Pending')->count();

        // Memformat tanggal
        foreach ($ca_transactions as $transaction) {
            $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
            $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
        }

        return view('hcis.reimbursements.cashadv.cashadv', [
            'pendingCACount' => $pendingCACount,
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
        ]);
    }
    function cashadvancedCreate()
    {

        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = BusinessTrip::orderBy('no_sppd')->get();

        function findDepartmentHead($employee)
        {
            $manager = Employee::where('employee_id', $employee->manager_l1_id)->first();

            if (!$manager) {
                return null;
            }

            $designation = Designation::where('job_code', $manager->designation_code)->first();

            if ($designation->dept_head_flag == 'T') {
                return $manager;
            } else {
                return findDepartmentHead($manager);
            }
            return null;
        }
        $deptHeadManager = findDepartmentHead($employee_data);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        $cek_director_id = Employee::select([
            'dsg.department_level2',
            'dsg2.director_flag',
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1) AS department_director"),
            'dsg2.designation_name',
            'dsg2.job_code',
            'emp.fullname',
            'emp.employee_id',
        ])
            ->leftJoin('designations as dsg', 'dsg.job_code', '=', 'employees.designation_code')
            ->leftJoin('designations as dsg2', 'dsg2.department_code', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1)"))
            ->leftJoin('employees as emp', 'emp.designation_code', '=', 'dsg2.job_code')
            ->where('employees.designation_code', '=', $employee_data->designation_code)
            ->where('dsg2.director_flag', '=', 'T')
            ->get();

        $director_id = "";

        if ($cek_director_id->isNotEmpty()) {
            $director_id = $cek_director_id->first()->employee_id;
        }

        return view('hcis.reimbursements.cashadv.formCashadv', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'managerL1' => $managerL1,
            'managerL2' => $managerL2,
            'director_id' => $director_id,
        ]);
    }
    public function cashadvancedSubmit(Request $req)
    {
        $userId = Auth::id();
        $employee_data = Employee::where('id', $userId)->first();
        $currentYear = date('Y');
        $currentYearShort = date('y'); // Mengambil 2 digit terakhir dari tahun
        $prefix = 'CA';

        // Ambil nomor urut terakhir dari tahun berjalan menggunakan Eloquent
        $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
            ->orderBy('no_ca', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $newNoCa = "$prefix$currentYearShort$newNumber";

        $uuid = Str::uuid();
        $model = new CATransaction;
        $model->id = $uuid;
        $model->type_ca = $req->ca_type;
        $model->no_ca = $newNoCa;
        $model->no_sppd = $req->bisnis_numb;
        $model->user_id = $userId;
        $model->unit = $req->unit;
        $model->contribution_level_code = $req->companyFilter;
        $model->destination = $req->locationFilter;
        $model->others_location = $req->others_location;
        $model->ca_needs = $req->ca_needs;
        $model->start_date = $req->start_date;
        $model->end_date = $req->end_date;
        $model->date_required = $req->ca_required;
        $model->declare_estimate = $req->ca_decla;
        $model->total_days = $req->totaldays;
        if ($req->ca_type == 'dns') {
            // Menyiapkan array untuk menyimpan detail dari setiap bagian
            $detail_perdiem = [];
            $detail_transport = [];
            $detail_penginapan = [];
            $detail_lainnya = [];

            // Loop untuk Perdiem
            if ($req->has('start_bt_perdiem')) {
                // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);
                foreach ($req->start_bt_perdiem as $key => $startDate) {
                    $endDate = $req->end_bt_perdiem[$key];
                    $totalDays = $req->total_days_bt_perdiem[$key];
                    $location = $req->location_bt_perdiem[$key];
                    $other_location = $req->other_location_bt_perdiem[$key];
                    $companyCode = $req->company_bt_perdiem[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_perdiem[$key]);
                    // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);

                    $for_perdiem[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'location' => $location,
                        'other_location' => $other_location,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Transport
            if ($req->has('tanggal_bt_transport')) {
                foreach ($req->tanggal_bt_transport as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_transport[$key];
                    $companyCode = $req->company_bt_transport[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_transport[$key]);

                    $detail_transport[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Penginapan
            if ($req->has('start_bt_penginapan')) {
                foreach ($req->start_bt_penginapan as $key => $startDate) {
                    $endDate = $req->end_bt_penginapan[$key];
                    $totalDays = $req->total_days_bt_penginapan[$key];
                    $hotelName = $req->hotel_name_bt_penginapan[$key];
                    $companyCode = $req->company_bt_penginapan[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_penginapan[$key]);

                    $detail_penginapan[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'hotel_name' => $hotelName,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Lainnya
            if ($req->has('tanggal_bt_lainnya')) {
                foreach ($req->tanggal_bt_lainnya as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_lainnya[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_lainnya[$key]);

                    $detail_lainnya[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Konversi array menjadi JSON untuk disimpan di database
            $detail_ca = [
                'detail_perdiem' => $detail_perdiem,
                'detail_transport' => $detail_transport,
                'detail_penginapan' => $detail_penginapan,
                'detail_lainnya' => $detail_lainnya,
            ];

            $model->detail_ca = json_encode($detail_ca);
        } else if ($req->ca_type == 'ndns') {
            $detail_ndns = [];
            if ($req->has('tanggal_nbt')) {
                foreach ($req->tanggal_nbt as $key => $tanggal) {
                    $keterangan_nbt = $req->keterangan_nbt[$key];
                    $nominal_nbt = str_replace('.', '', $req->nominal_nbt[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_ndns[] = [
                        'tanggal_nbt' => $tanggal,
                        'keterangan_nbt' => $keterangan_nbt,
                        'nominal_nbt' => $nominal_nbt,
                    ];
                }
            }
            $detail_ndns_json = json_encode($detail_ndns);
            $model->detail_ca = $detail_ndns_json;
        } else if ($req->ca_type == 'entr') {
            $detail_e = [];
            $relation_e = [];

            // Mengumpulkan detail entertain
            if ($req->has('enter_type_e_detail')) {
                foreach ($req->enter_type_e_detail as $key => $type) {
                    $fee_detail = $req->enter_fee_e_detail[$key];
                    $nominal = str_replace('.', '', $req->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_e[] = [
                        'type' => $type,
                        'fee_detail' => $fee_detail,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Mengumpulkan detail relation
            if ($req->has('rname_e_relation')) {
                foreach ($req->rname_e_relation as $key => $name) {
                    $relation_e[] = [
                        'name' => $name,
                        'position' => $req->rposition_e_relation[$key],
                        'company' => $req->rcompany_e_relation[$key],
                        'purpose' => $req->rpurpose_e_relation[$key],
                        'relation_type' => array_filter([
                            'Food' => in_array('food', $req->food_e_relation ?? [$key]),
                            'Transport' => in_array('transport', $req->transport_e_relation ?? [$key]),
                            'Accommodation' => in_array('accommodation', $req->accommodation_e_relation ?? [$key]),
                            'Gift' => in_array('gift', $req->gift_e_relation ?? [$key]),
                            'Fund' => in_array('fund', $req->fund_e_relation ?? [$key]),
                        ], fn($checked) => $checked),
                    ];
                }
            }

            // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
            $detail_ca = [
                'detail_e' => $detail_e,
                'relation_e' => $relation_e,
            ];
            $model->detail_ca = json_encode($detail_ca);
        }

        $model->total_ca = str_replace('.', '', $req->totalca);
        $model->total_real = "0";
        $model->total_cost = str_replace('.', '', $req->totalca);

        if ($req->input('action_ca_draft')) {
            $model->approval_status = $req->input('action_ca_draft');
        }
        if ($req->input('action_ca_submit')) {
            $model->approval_status = $req->input('action_ca_submit');
        }

        $model->created_by = $userId;

        if ($req->input('action_ca_submit')) {
            function findDepartmentHead($employee)
            {
                $manager = Employee::where('employee_id', $employee->manager_l1_id)->first();

                if (!$manager) {
                    return null;
                }

                $designation = Designation::where('job_code', $manager->designation_code)->first();

                if ($designation->dept_head_flag == 'T') {
                    return $manager;
                } else {
                    return findDepartmentHead($manager);
                }
                return null;
            }
            $deptHeadManager = findDepartmentHead($employee_data);

            $managerL1 = $deptHeadManager->employee_id;
            $managerL2 = $deptHeadManager->manager_l1_id;

            $model->status_id = $managerL1;

            $cek_director_id = Employee::select([
                'dsg.department_level2',
                'dsg2.director_flag',
                DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1) AS department_director"),
                'dsg2.designation_name',
                'dsg2.job_code',
                'emp.fullname',
                'emp.employee_id',
            ])
                ->leftJoin('designations as dsg', 'dsg.job_code', '=', 'employees.designation_code')
                ->leftJoin('designations as dsg2', 'dsg2.department_code', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1)"))
                ->leftJoin('employees as emp', 'emp.designation_code', '=', 'dsg2.job_code')
                ->where('employees.designation_code', '=', $employee_data->designation_code)
                ->where('dsg2.director_flag', '=', 'T')
                ->get();

            $director_id = "";

            if ($cek_director_id->isNotEmpty()) {
                $director_id = $cek_director_id->first()->employee_id;
            }
            //cek matrix approval
            $total_ca = str_replace('.', '', $req->totalca);
            $data_matrix_approvals = MatrixApproval::where(function ($query) use ($req) {
                if ($req->ca_type === 'dns') {
                    $query->where('modul', 'dns');
                } else {
                    $query->where('modul', 'like', '%' . $req->ca_type . '%');
                }
            })
                ->where('group_company', 'like', '%' . $employee_data->group_company . '%')
                ->where('contribution_level_code', 'like', '%' . $req->companyFilter . '%')
                ->whereRaw(
                    '
            ? BETWEEN
            CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND
            CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
                    [$total_ca]
                )
                ->get();

            foreach ($data_matrix_approvals as $data_matrix_approval) {

                if ($data_matrix_approval->employee_id == "cek_L1") {
                    $employee_id = $managerL1;
                } else if ($data_matrix_approval->employee_id == "cek_L2") {
                    $employee_id = $managerL2;
                } else if ($data_matrix_approval->employee_id == "cek_director") {
                    $employee_id = $director_id;
                } else {
                    $employee_id = $data_matrix_approval->employee_id;
                }

                $model_approval = new ca_approval;
                $model_approval->ca_id = $uuid;
                $model_approval->role_name = $data_matrix_approval->desc;
                $model_approval->employee_id = $employee_id;
                $model_approval->layer = $data_matrix_approval->layer;
                $model_approval->approval_status = 'Pending';

                // Simpan data ke database
                $model_approval->save();
            }
        }

        $model->save();

        Alert::success('Success');
        return redirect()->intended(route('cashadvanced', absolute: false));
    }
    function cashadvancedEdit($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        // $transactions = CATransaction::find($key);
        $transactions = CATransaction::findByRouteKey($key);
        // dd($key);
        return view('hcis.reimbursements.cashadv.editCashadv', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    function cashadvancedUpdate(Request $req, $key)
    {
        $userId = Auth::id();
        $uuid = Str::uuid();
        $model = CATransaction::findByRouteKey($key);
        $employee_data = Employee::where('id', $userId)->first();
        // $model->type_ca = $req->ca_type;
        $model->no_ca = $req->no_ca;
        $model->no_sppd = $req->bisnis_numb;
        // $model->user_id         = $req->id;
        $model->unit = $req->unit;
        $model->contribution_level_code = $req->companyFilter;
        $model->destination = $req->locationFilter;
        $model->others_location = $req->others_location;
        $model->ca_needs = $req->ca_needs;
        $model->start_date = $req->start_date;
        $model->end_date = $req->end_date;
        $model->date_required = $req->ca_required;
        $model->declare_estimate = $req->ca_decla;
        $model->total_days = $req->totaldays;
        if ($req->ca_type == 'dns') {
            // Menyiapkan array untuk menyimpan detail dari setiap bagian
            $detail_perdiem = [];
            $detail_transport = [];
            $detail_penginapan = [];
            $detail_lainnya = [];

            // Loop untuk Perdiem
            if ($req->has('start_bt_perdiem')) {
                // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);
                foreach ($req->start_bt_perdiem as $key => $startDate) {
                    $endDate = $req->end_bt_perdiem[$key];
                    $totalDays = $req->total_days_bt_perdiem[$key];
                    $location = $req->location_bt_perdiem[$key];
                    $other_location = $req->other_location_bt_perdiem[$key];
                    $companyCode = $req->company_bt_perdiem[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_perdiem[$key]);
                    // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);

                    $for_perdiem[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'location' => $location,
                        'other_location' => $other_location,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Transport
            if ($req->has('tanggal_bt_transport')) {
                foreach ($req->tanggal_bt_transport as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_transport[$key];
                    // $companyCode = $req->company_bt_transport[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_transport[$key]);

                    $detail_transport[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Penginapan
            if ($req->has('start_bt_penginapan')) {
                foreach ($req->start_bt_penginapan as $key => $startDate) {
                    $endDate = $req->end_bt_penginapan[$key];
                    $totalDays = $req->total_days_bt_penginapan[$key];
                    $hotelName = $req->hotel_name_bt_penginapan[$key];
                    $companyCode = $req->company_bt_penginapan[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_penginapan[$key]);

                    $detail_penginapan[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'hotel_name' => $hotelName,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Lainnya
            if ($req->has('tanggal_bt_lainnya')) {
                foreach ($req->tanggal_bt_lainnya as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_lainnya[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_lainnya[$key]);

                    $detail_lainnya[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Konversi array menjadi JSON untuk disimpan di database
            $detail_ca = [
                'detail_perdiem' => $detail_perdiem,
                'detail_transport' => $detail_transport,
                'detail_penginapan' => $detail_penginapan,
                'detail_lainnya' => $detail_lainnya,
            ];

            $model->detail_ca = json_encode($detail_ca);
        } else if ($req->ca_type == 'ndns') {
            $detail_ndns = [];
            if ($req->has('tanggal_nbt')) {
                foreach ($req->tanggal_nbt as $key => $tanggal) {
                    $keterangan_nbt = $req->keterangan_nbt[$key];
                    $nominal_nbt = str_replace('.', '', $req->nominal_nbt[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_ndns[] = [
                        'tanggal_nbt' => $tanggal,
                        'keterangan_nbt' => $keterangan_nbt,
                        'nominal_nbt' => $nominal_nbt,
                    ];
                }
            }
            $detail_ndns_json = json_encode($detail_ndns);
            $model->detail_ca = $detail_ndns_json;
        } else if ($req->ca_type == 'entr') {
            $detail_e = [];
            $relation_e = [];

            // Mengumpulkan detail entertain
            if ($req->has('enter_type_e_detail')) {
                foreach ($req->enter_type_e_detail as $key => $type) {
                    $fee_detail = $req->enter_fee_e_detail[$key];
                    $nominal = str_replace('.', '', $req->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_e[] = [
                        'type' => $type,
                        'fee_detail' => $fee_detail,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Mengumpulkan detail relation
            if ($req->has('rname_e_relation')) {
                foreach ($req->rname_e_relation as $key => $name) {
                    $relation_e[] = [
                        'name' => $name,
                        'position' => $req->rposition_e_relation[$key],
                        'company' => $req->rcompany_e_relation[$key],
                        'purpose' => $req->rpurpose_e_relation[$key],
                        'relation_type' => array_filter([
                            'Food' => in_array('food', $req->food_e_relation ?? [$key]),
                            'Transport' => in_array('transport', $req->transport_e_relation ?? [$key]),
                            'Accommodation' => in_array('accommodation', $req->accommodation_e_relation ?? [$key]),
                            'Gift' => in_array('gift', $req->gift_e_relation ?? [$key]),
                            'Fund' => in_array('fund', $req->fund_e_relation ?? [$key]),
                        ], fn($checked) => $checked),
                    ];
                }
            }

            // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
            $detail_ca = [
                'detail_e' => $detail_e,
                'relation_e' => $relation_e,
            ];
            $model->detail_ca = json_encode($detail_ca);
        }
        $model->total_ca = str_replace('.', '', $req->totalca);
        $model->total_real = "0";
        $model->total_cost = str_replace('.', '', $req->totalca);
        if ($req->input('action_ca_draft')) {
            $model->approval_status = $req->input('action_ca_draft');
        }
        if ($req->input('action_ca_submit')) {
            $model->approval_status = $req->input('action_ca_submit');
        }
        if ($req->input('action_ca_submit')) {
            function findDepartmentHead($employee)
            {
                $manager = Employee::where('employee_id', $employee->manager_l1_id)->first();

                if (!$manager) {
                    return null;
                }

                $designation = Designation::where('job_code', $manager->designation_code)->first();

                if ($designation->dept_head_flag == 'T') {
                    return $manager;
                } else {
                    return findDepartmentHead($manager);
                }
                return null;
            }
            $deptHeadManager = findDepartmentHead($employee_data);

            $managerL1 = $deptHeadManager->employee_id;
            $managerL2 = $deptHeadManager->manager_l1_id;

            $model->status_id = $managerL1;

            $cek_director_id = Employee::select([
                'dsg.department_level2',
                'dsg2.director_flag',
                DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1) AS department_director"),
                'dsg2.designation_name',
                'dsg2.job_code',
                'emp.fullname',
                'emp.employee_id',
            ])
                ->leftJoin('designations as dsg', 'dsg.job_code', '=', 'employees.designation_code')
                ->leftJoin('designations as dsg2', 'dsg2.department_code', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1)"))
                ->leftJoin('employees as emp', 'emp.designation_code', '=', 'dsg2.job_code')
                ->where('employees.designation_code', '=', $employee_data->designation_code)
                ->where('dsg2.director_flag', '=', 'T')
                ->get();

            $director_id = "";

            if ($cek_director_id->isNotEmpty()) {
                $director_id = $cek_director_id->first()->employee_id;
            }
            //cek matrix approval
            $total_ca = str_replace('.', '', $req->totalca);
            $data_matrix_approvals = MatrixApproval::where(function ($query) use ($req) {
                if ($req->ca_type === 'dns') {
                    $query->where('modul', 'dns');
                } else {
                    $query->where('modul', 'like', '%' . $req->ca_type . '%');
                }
            })
                ->where('group_company', 'like', '%' . $employee_data->group_company . '%')
                ->where('contribution_level_code', 'like', '%' . $req->companyFilter . '%')
                ->whereRaw(
                    '
            ? BETWEEN
            CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND
            CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
                    [$total_ca]
                )
                ->get();

            foreach ($data_matrix_approvals as $data_matrix_approval) {

                if ($data_matrix_approval->employee_id == "cek_L1") {
                    $employee_id = $managerL1;
                } else if ($data_matrix_approval->employee_id == "cek_L2") {
                    $employee_id = $managerL2;
                } else if ($data_matrix_approval->employee_id == "cek_director") {
                    $employee_id = $director_id;
                } else {
                    $employee_id = $data_matrix_approval->employee_id;
                }

                $model_approval = new ca_approval;
                $model_approval->ca_id = $req->no_id;
                $model_approval->role_name = $data_matrix_approval->desc;
                $model_approval->employee_id = $employee_id;
                $model_approval->layer = $data_matrix_approval->layer;
                $model_approval->approval_status = 'Pending';

                // Simpan data ke database
                $model_approval->save();
            }
        }
        $model->created_by = $userId;
        $model->save();

        Alert::success('Success Update');
        return redirect()->intended(route('cashadvanced', absolute: false));
    }
    function cashadvancedDelete($id)
    {
        $model = ca_transaction::find($id);
        $model->delete();
        return redirect()->intended(route('cashadvanced', absolute: false));
    }
    function cashadvancedDownload($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        // $kantor = Company::where('contribution_level', $companies->contribution_level_code)->first();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = CATransaction::find($key);

        // return view('hcis.reimbursements.cashadv.downloadCashadv', [
        $pdf = PDF::loadView('hcis.reimbursements.cashadv.printCashadv', [
            'link' => $link,
            // 'pdf' => $pdf,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);

        return $pdf->stream('Cash Advanced ' . $key . '.pdf');
    }

    public function cashadvancedDeklarasi($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced Approval';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = CATransaction::findByRouteKey($key);

        return view('hcis.reimbursements.cashadv.deklarasiCashadv', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    function cashadvancedDeclare(Request $req, $key)
    {
        $userId = Auth::id();
        $uuid = Str::uuid();
        $model = CATransaction::findByRouteKey($key);
        $employee_data = Employee::where('id', $userId)->first();

        // Validasi file yang diupload
        $req->validate([
            'prove_declare' => 'required|image|mimes:jpeg,png,jpg,gif,pdf|max:2048', // Aturan validasi file gambar
        ]);

        if ($req->hasFile('prove_declare')) {
            $file = $req->file('prove_declare');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/proofs'), $filename);
            $model->prove_declare = $filename;
        } else {
            $model->prove_declare = $req->input('existing_prove_declare');
        }

        $model->no_ca = $req->no_ca;
        $model->no_sppd = $req->bisnis_numb;

        if ($req->ca_type == 'dns') {
            $detail_perdiem = [];
            $detail_transport = [];
            $detail_penginapan = [];
            $detail_lainnya = [];

            // Loop untuk Perdiem
            if ($req->has('start_bt_perdiem')) {
                // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);
                foreach ($req->start_bt_perdiem as $key => $startDate) {
                    $endDate = $req->end_bt_perdiem[$key];
                    $totalDays = $req->total_days_bt_perdiem[$key];
                    $location = $req->location_bt_perdiem[$key];
                    $other_location = $req->other_location_bt_perdiem[$key];
                    $companyCode = $req->company_bt_perdiem[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_perdiem[$key]);
                    // $totalPerdiem = str_replace('.', '', $req->total_bt_perdiem[]);

                    $for_perdiem[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'location' => $location,
                        'other_location' => $other_location,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Transport
            if ($req->has('tanggal_bt_transport')) {
                foreach ($req->tanggal_bt_transport as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_transport[$key];
                    // $companyCode = $req->company_bt_transport[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_transport[$key]);

                    $detail_transport[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Penginapan
            if ($req->has('start_bt_penginapan')) {
                foreach ($req->start_bt_penginapan as $key => $startDate) {
                    $endDate = $req->end_bt_penginapan[$key];
                    $totalDays = $req->total_days_bt_penginapan[$key];
                    $hotelName = $req->hotel_name_bt_penginapan[$key];
                    $companyCode = $req->company_bt_penginapan[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_penginapan[$key]);

                    $detail_penginapan[] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $totalDays,
                        'hotel_name' => $hotelName,
                        'company_code' => $companyCode,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Loop untuk Lainnya
            if ($req->has('tanggal_bt_lainnya')) {
                foreach ($req->tanggal_bt_lainnya as $key => $tanggal) {
                    $keterangan = $req->keterangan_bt_lainnya[$key];
                    $nominal = str_replace('.', '', $req->nominal_bt_lainnya[$key]);

                    $detail_lainnya[] = [
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Konversi array menjadi JSON untuk disimpan di database
            $declare_ca = [
                'detail_perdiem' => $detail_perdiem,
                'detail_transport' => $detail_transport,
                'detail_penginapan' => $detail_penginapan,
                'detail_lainnya' => $detail_lainnya,
            ];

            $model->declare_ca = json_encode($declare_ca);
        } else if ($req->ca_type == 'ndns') {
            $detail_ndns = [];
            if ($req->has('tanggal_nbt')) {
                foreach ($req->tanggal_nbt as $key => $tanggal) {
                    $keterangan_nbt = $req->keterangan_nbt[$key];
                    $nominal_nbt = str_replace('.', '', $req->nominal_nbt[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_ndns[] = [
                        'tanggal_nbt' => $tanggal,
                        'keterangan_nbt' => $keterangan_nbt,
                        'nominal_nbt' => $nominal_nbt,
                    ];
                }
            }
            $detail_ndns_json = json_encode($detail_ndns);
            $model->declare_ca = $detail_ndns_json;
        } else if ($req->ca_type == 'entr') {
            $detail_e = [];
            $relation_e = [];

            // Mengumpulkan detail entertain
            if ($req->has('enter_type_e_detail')) {
                foreach ($req->enter_type_e_detail as $key => $type) {
                    $fee_detail = $req->enter_fee_e_detail[$key];
                    $nominal = str_replace('.', '', $req->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    $detail_e[] = [
                        'type' => $type,
                        'fee_detail' => $fee_detail,
                        'nominal' => $nominal,
                    ];
                }
            }

            // Mengumpulkan detail relation
            if ($req->has('rname_e_relation')) {
                foreach ($req->rname_e_relation as $key => $name) {
                    $relation_e[] = [
                        'name' => $name,
                        'position' => $req->rposition_e_relation[$key],
                        'company' => $req->rcompany_e_relation[$key],
                        'purpose' => $req->rpurpose_e_relation[$key],
                        'relation_type' => array_filter([
                            'Food' => in_array('food', $req->food_e_relation ?? [$key]),
                            'Transport' => in_array('transport', $req->transport_e_relation ?? [$key]),
                            'Accommodation' => in_array('accommodation', $req->accommodation_e_relation ?? [$key]),
                            'Gift' => in_array('gift', $req->gift_e_relation ?? [$key]),
                            'Fund' => in_array('fund', $req->fund_e_relation ?? [$key]),
                        ], fn($checked) => $checked),
                    ];
                }
            }

            // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
            $declare_ca = [
                'detail_e' => $detail_e,
                'relation_e' => $relation_e,
            ];
            $model->declare_ca = json_encode($declare_ca);
        }
        $model->total_ca = str_replace('.', '', $req->totalca);
        $model->total_real = str_replace('.', '', $req->totalca_deklarasi);
        $model->total_cost = str_replace('.', '', $req->totalca);
        if ($req->input('action_ca_draft')) {
            $model->approval_sett = $req->input('action_ca_draft');
        }
        if ($req->input('action_ca_submit')) {
            // $model->approval_status = $req->input('action_ca_submit');
            $model->approval_sett = 'Pending';
        }
        // if ($req->input('action_ca_submit')) {
        //     function findDepartmentHead($employee)
        //     {
        //         $manager = Employee::where('employee_id', $employee->manager_l1_id)->first();

        //         if (!$manager) {
        //             return null;
        //         }

        //         $designation = Designation::where('job_code', $manager->designation_code)->first();

        //         if ($designation->dept_head_flag == 'T') {
        //             return $manager;
        //         } else {
        //             return findDepartmentHead($manager);
        //         }
        //         return null;
        //     }
        //     $deptHeadManager = findDepartmentHead($employee_data);

        //     $managerL1 = $deptHeadManager->employee_id;
        //     $managerL2 = $deptHeadManager->manager_l1_id;

        //     $model->status_id = $managerL1;

        //     $cek_director_id = Employee::select([
        //         'dsg.department_level2',
        //         'dsg2.director_flag',
        //         DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1) AS department_director"),
        //         'dsg2.designation_name',
        //         'dsg2.job_code',
        //         'emp.fullname',
        //         'emp.employee_id',
        //     ])
        //         ->leftJoin('designations as dsg', 'dsg.job_code', '=', 'employees.designation_code')
        //         ->leftJoin('designations as dsg2', 'dsg2.department_code', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(dsg.department_level2, '(', -1), ')', 1)"))
        //         ->leftJoin('employees as emp', 'emp.designation_code', '=', 'dsg2.job_code')
        //         ->where('employees.designation_code', '=', $employee_data->designation_code)
        //         ->where('dsg2.director_flag', '=', 'T')
        //         ->get();

        //     $director_id = "";

        //     if ($cek_director_id->isNotEmpty()) {
        //         $director_id = $cek_director_id->first()->employee_id;
        //     }
        //     //cek matrix approval
        //     $total_ca = str_replace('.', '', $req->totalca);
        //     $data_matrix_approvals = MatrixApproval::where(function ($query) use ($req) {
        //         if ($req->ca_type === 'dns') {
        //             $query->where('modul', 'dns');
        //         } else {
        //             $query->where('modul', 'like', '%' . $req->ca_type . '%');
        //         }
        //     })
        //         ->where('group_company', 'like', '%' . $employee_data->group_company . '%')
        //         ->where('contribution_level_code', 'like', '%' . $req->companyFilter . '%')
        //         ->whereRaw(
        //             '
        //     ? BETWEEN
        //     CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND
        //     CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
        //             [$total_ca]
        //         )
        //         ->get();

        //     foreach ($data_matrix_approvals as $data_matrix_approval) {

        //         if ($data_matrix_approval->employee_id == "cek_L1") {
        //             $employee_id = $managerL1;
        //         } else if ($data_matrix_approval->employee_id == "cek_L2") {
        //             $employee_id = $managerL2;
        //         } else if ($data_matrix_approval->employee_id == "cek_director") {
        //             $employee_id = $director_id;
        //         } else {
        //             $employee_id = $data_matrix_approval->employee_id;
        //         }

        //         $model_approval = new ca_approval;
        //         $model_approval->ca_id = $req->no_id;
        //         $model_approval->role_name = $data_matrix_approval->desc;
        //         $model_approval->employee_id = $employee_id;
        //         $model_approval->layer = $data_matrix_approval->layer;
        //         $model_approval->approval_status = 'Pending';

        //         // Simpan data ke database
        //         $model_approval->save();
        //     }
        // }
        $model->declaration_at = Carbon::now();
        $model->save();

        Alert::success('Success Update');
        return redirect()->intended(route('cashadvanced', absolute: false));
    }

    public function hotel()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Hotel';
        $transactions = Hotel::with('employee')->get();

        // foreach ($transactions as $transaction) {
        //     dd($transaction); // This will dump the first transaction and stop execution
        // }

        return view('hcis.reimbursements.hotel.hotel', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'transactions' => $transactions,
        ]);
    }


    function hotelCreate()
    {

        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Hotel';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = BusinessTrip::where('user_id', $userId)->where('status', '!=', 'Approved')->get();
        // $no_sppds = ca_transaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();


        return view('hcis.reimbursements.hotel.formHotel', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
        ]);
    }
    public function hotelSubmit(Request $req)
    {
        function getRomanMonth_htl($month)
        {
            $romanMonths = [
                1 => 'I',
                2 => 'II',
                3 => 'III',
                4 => 'IV',
                5 => 'V',
                6 => 'VI',
                7 => 'VII',
                8 => 'VIII',
                9 => 'IX',
                10 => 'X',
                11 => 'XI',
                12 => 'XII'
            ];
            return $romanMonths[$month];
        }
        $userId = Auth::id();
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = getRomanMonth_htl($currentMonth);

        // Ambil nomor urut terakhir dari tahun berjalan menggunakan Eloquent
        $lastTransaction = htl_transaction::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('no_htl', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/HTL-ACC\/' . $romanMonth . '\/\d{4}/', $lastTransaction->no_htl, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoHtl = "$newNumber/HTL-ACC/$romanMonth/$currentYear";

        $model = new htl_transaction;
        $model->id = Str::uuid();
        $model->no_htl = $newNoHtl;
        $model->no_sppd = $req->bisnis_numb;
        $model->user_id = $userId;
        $model->unit = $req->unit;
        $model->nama_htl = $req->nama_htl;
        $model->lokasi_htl = $req->lokasi_htl;
        $model->jmlkmr_htl = $req->jmlkmr_htl;
        $model->bed_htl = $req->bed_htl;
        $model->tgl_masuk_htl = $req->tgl_masuk_htl;
        $model->tgl_keluar_htl = $req->tgl_keluar_htl;
        $model->total_hari = $req->totaldays;
        $model->created_by = $userId;
        $model->save();

        Alert::success('Success');
        session()->flash('message', 'Berhasil di Tambahkan');
        return redirect()->intended(route('hotel', absolute: false));
    }
    function hotelEdit($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = htl_transaction::findByRouteKey($key);

        return view('hcis.reimbursements.hotel.editHotel', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    public function hotelUpdate(Request $req, $key)
    {
        $model = htl_transaction::findByRouteKey($key);

        if ($model) {
            $model->unit = $req->unit;
            $model->nama_htl = $req->nama_htl;
            $model->lokasi_htl = $req->lokasi_htl;
            $model->jmlkmr_htl = $req->jmlkmr_htl;
            $model->bed_htl = $req->bed_htl;
            $model->tgl_masuk_htl = $req->tgl_masuk_htl;
            $model->tgl_keluar_htl = $req->tgl_keluar_htl;
            $model->total_hari = $req->totaldays;
            $model->save();

            Alert::success('Success');
            session()->flash('message', 'Edit Berhasil');
            return redirect()->route('hotel');
        } else {
            return redirect()->back()->withErrors(['message' => 'Transaction not found']);
        }
    }
    function hotelDelete($key)
    {
        $model = htl_transaction::findByRouteKey($key);
        $model->delete();
        return redirect()->intended(route('hotel', absolute: false));
    }
    public function ticket()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Ticket';
        $transactions = tkt_transaction::with('employee')->get();

        return view('hcis.reimbursements.ticket.ticket', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'transactions' => $transactions,
        ]);
    }
    function ticketCreate()
    {

        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Ticket';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = BusinessTrip::where('user_id', $userId)->where('status', '!=', 'Approved')->get();
        // $no_sppds = ca_transaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();


        return view('hcis.reimbursements.ticket.formTicket', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
        ]);
    }
    public function ticketSubmit(Request $req)
    {
        function getRomanMonth_tkt($month)
        {
            $romanMonths = [
                1 => 'I',
                2 => 'II',
                3 => 'III',
                4 => 'IV',
                5 => 'V',
                6 => 'VI',
                7 => 'VII',
                8 => 'VIII',
                9 => 'IX',
                10 => 'X',
                11 => 'XI',
                12 => 'XII'
            ];
            return $romanMonths[$month];
        }
        $userId = Auth::id();
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = getRomanMonth_tkt($currentMonth);

        // Ambil nomor urut terakhir dari tahun berjalan menggunakan Eloquent
        $lastTransaction = tkt_transaction::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('no_tkt', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/TKT-ACC\/' . $romanMonth . '\/\d{4}/', $lastTransaction->no_tkt, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoHtl = "$newNumber/TKT-ACC/$romanMonth/$currentYear";

        $model = new tkt_transaction;
        $model->id = Str::uuid();
        $model->no_tkt = $newNoHtl;
        $model->no_sppd = $req->bisnis_numb;
        $model->user_id = $userId;
        $model->unit = $req->unit;
        $model->jk_tkt = $req->jk_tkt;
        $model->np_tkt = $req->np_tkt;
        $model->noktp_tkt = $req->noktp_tkt;
        $model->tlp_tkt = $req->tlp_tkt;
        $model->jenis_tkt = $req->jenis_tkt;
        $model->dari_tkt = $req->dari_tkt;
        $model->ke_tkt = $req->ke_tkt;
        $model->tgl_brkt_tkt = $req->tgl_brkt_tkt;
        $model->jam_brkt_tkt = $req->jam_brkt_tkt;
        $model->type_tkt = $req->type_tkt;
        $model->tgl_plg_tkt = $req->tgl_plg_tkt;
        $model->jam_plg_tkt = $req->jam_plg_tkt;
        $model->created_by = $userId;
        $model->save();

        Alert::success('Success');
        session()->flash('message', 'Berhasil di Tambahkan');
        return redirect()->intended(route('ticket', absolute: false));
    }
    function ticketEdit($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Ticket';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = tkt_transaction::findByRouteKey($key);

        return view('hcis.reimbursements.ticket.editTicket', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    public function ticketUpdate(Request $req, $key)
    {
        $userId = Auth::id();
        $model = tkt_transaction::findByRouteKey($key);
        $model->jk_tkt = $req->jk_tkt;
        $model->np_tkt = $req->np_tkt;
        $model->noktp_tkt = $req->noktp_tkt;
        $model->tlp_tkt = $req->tlp_tkt;
        $model->jenis_tkt = $req->jenis_tkt;
        $model->dari_tkt = $req->dari_tkt;
        $model->ke_tkt = $req->ke_tkt;
        $model->tgl_brkt_tkt = $req->tgl_brkt_tkt;
        $model->jam_brkt_tkt = $req->jam_brkt_tkt;
        $model->type_tkt = $req->type_tkt;
        $model->tgl_plg_tkt = $req->tgl_plg_tkt;
        $model->jam_plg_tkt = $req->jam_plg_tkt;
        $model->created_by = $userId;
        $model->save();

        Alert::success('Success');
        session()->flash('message', 'Berhasil di Edit');
        return redirect()->intended(route('ticket', absolute: false));
    }
    function ticketDelete($key)
    {
        $model = tkt_transaction::findByRouteKey($key);
        $model->delete();
        return redirect()->intended(route('ticket', absolute: false));
    }
}
