<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DesignationController extends Controller
{
    protected $link;

    function __construct()
    {
        $this->link = 'Designations';
    }

    public function UpdateDesignation()
    {
        Log::info('UpdateDesignation method started.'); // Logging start

        // URL API
        $url = 'https://kpncorporation.darwinbox.com/orgmasterapi/designationlist';

        // Data untuk request
        $data = [
            "api_key" => "90cf9e8b2805425bb5a003220efdb7f275b8ef2c13ee2924546539c507dc3a39e2519b86187c038f84a29bbeb15f7e87d145b546e79b47d8271691b899d167e7"
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
                return response()->json(['message' => 'Failed to update designation data'], 500);
            }

            // Parse response
            $designations = $response->json('data');

            $number_data = 0;

            Log::info('API response received', ['designation_count' => count($designations)]);

            // Simpan data ke database
            foreach ($designations as $designation) {

                Designation::updateOrCreate(
                    ['job_code' => $designation['job_code']],
                    [
                        'parent_company_id' => $designation['parent_company_id'],
                        'designation_name' => $designation['designation_name'],
                        'job_code' => $designation['job_code'],
                        'department_name' => $designation['department_name'],
                        'department_code' => $designation['department_code'],
                        'department_level1' => $designation['department_level1'],
                        'department_level2' => $designation['department_level2'],
                        'department_level3' => $designation['department_level3'],
                        'department_level4' => $designation['department_level4'],
                        'department_level5' => $designation['department_level5'],
                        'department_level6' => $designation['department_level6'],
                        'department_level7' => $designation['department_level7'],
                        'department_level8' => $designation['department_level8'],
                        'department_level9' => $designation['department_level9'],
                        'type_of_staffing_model' => $designation['type_of_staffing_model'],
                        'number_of_positions' => $designation['number_of_positions'],
                        'number_of_existing_incumbents' => $designation['number_of_existing_incumbents'],
                        'department_hierarchy' => $designation['department_hierarchy'],
                        'status' => $designation['status']
                    ]
                );
                $number_data++;
            }

            Log::info('Update Designation data successfully saved', ['saved_count' => $number_data]);

            return response()->json(['message' => $number_data . ' Update Designation data successfully saved']);
        } catch (\Exception $e) {
            Log::error('Exception occurred in UpdateDesignation method', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Designations.
     *
     * Baris tabel tidak lagi dirender di sini: DataTables memintanya per
     * halaman ke data(). Versi lama merender SELURUH designation sekaligus
     * (6,8 ribuan baris) tanpa paging, pencarian, maupun sorting - kotak
     * "search.." di halaman itu bahkan tidak tersambung ke apa pun.
     *
     * // $designations = Designation::all();
     */
    public function index()
    {
        $parentLink = "Settings";
        $link = $this->link;

        // Diisi oleh DataTables server-side.
        $designations = collect();

        // Filter Business Unit: nilainya diambil dari parent_company_id yang
        // benar-benar ada di tabel. Jumlahnya sedikit (7), jadi aman dirender
        // langsung sebagai <option>.
        $businessUnits = Designation::select('parent_company_id')
            ->whereNotNull('parent_company_id')
            ->where('parent_company_id', '!=', '')
            ->distinct()
            ->orderBy('parent_company_id')
            ->pluck('parent_company_id');

        // Designation Name TIDAK ikut dirender: nilai uniknya 3.435, jadi
        // dropdown-nya dicari lewat designationNames() seperti dropdown
        // karyawan di halaman Approval Setting.

        return view('pages.designations.adminDesignations', compact('designations', 'parentLink', 'link', 'businessUnits'));
    }

    /**
     * Sumber data Select2 untuk filter Designation Name.
     *
     * Nilainya unik dan dipotong di SQL, 30 baris per permintaan. Kalau
     * Business Unit sedang dipilih, daftarnya ikut dipersempit supaya
     * dua filter itu nyambung.
     */
    public function designationNames(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 30;

        $query = Designation::query()
            ->select('designation_name')
            ->whereNotNull('designation_name')
            ->where('designation_name', '!=', '')
            ->distinct()
            ->orderBy('designation_name');

        $businessUnit = $request->input('business_unit');
        if ($businessUnit !== null && $businessUnit !== '') {
            $query->where('parent_company_id', $businessUnit);
        }

        if ($search !== '') {
            $query->where('designation_name', 'LIKE', '%' . $search . '%');
        }

        $total = (clone $query)->count('designation_name');

        $names = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->pluck('designation_name');

        return response()->json([
            'results' => $names
                ->map(fn($name) => ['id' => $name, 'text' => $name])
                ->values(),
            'pagination' => ['more' => $page * $perPage < $total],
        ]);
    }

    /**
     * Sumber data DataTables server-side untuk halaman Designations.
     */
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 0);

        $query = Designation::query();

        // Filter dari dua dropdown di atas tabel.
        $businessUnit = $request->input('business_unit');
        if ($businessUnit !== null && $businessUnit !== '') {
            $query->where('parent_company_id', $businessUnit);
        }

        $designationName = $request->input('designation_name');
        if ($designationName !== null && $designationName !== '') {
            $query->where('designation_name', $designationName);
        }

        // Dihitung sesudah dropdown filter, sebelum kotak pencarian - sama
        // seperti endpoint DataTables lain di proyek ini.
        $recordsTotal = (clone $query)->count();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                foreach (['parent_company_id', 'designation_name', 'job_code'] as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        $recordsFiltered = $search === '' ? $recordsTotal : (clone $query)->count();

        // Kolom 0 (No) tidak bisa diurutkan di SQL.
        $sortable = [
            1 => 'parent_company_id',
            2 => 'designation_name',
            3 => 'job_code',
            4 => 'dept_head_flag',
            5 => 'director_flag',
        ];

        $orderColumn = (int) $request->input('order.0.column', -1);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (isset($sortable[$orderColumn])) {
            $query->orderBy($sortable[$orderColumn], $orderDir);
        }

        // Tiebreaker wajib: tanpa ini urutan baris dengan nilai sama tidak
        // stabil antar halaman, sehingga baris bisa terlewat atau dobel.
        $query->orderBy('id');

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $data = [];
        foreach ($query->get() as $i => $designation) {
            $data[] = [
                (string) ($start + $i + 1),
                e($designation->parent_company_id),
                e($designation->designation_name),
                e($designation->job_code),
                $this->designationFlagCell($designation, 'dept'),
                $this->designationFlagCell($designation, 'director'),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Tombol toggle True/False untuk dept_head_flag / director_flag.
     *
     * Markup-nya mengikuti versi blade, termasuk id form dan input tersembunyi
     * yang dibaca handler .update-button.
     */
    private function designationFlagCell($designation, string $type): string
    {
        $isDept = $type === 'dept';

        $flag = $isDept ? $designation->dept_head_flag : $designation->director_flag;
        $isTrue = $flag === 'T';

        $action = $isDept
            ? route('designations.update.dept', ['id' => $designation->id])
            : route('designations.update.director', ['id' => $designation->id]);

        $formId = ($isDept ? 'dept-head-update-form-' : 'director-update-form-') . $designation->id;
        $inputId = ($isDept ? 'dept-designation-id-' : 'director-designation-id-') . $designation->id;

        return '<form action="' . e($action) . '" method="POST" style="display:inline;" id="' . e($formId) . '">' .
            csrf_field() .
            method_field('PUT') .
            '<input type="hidden" id="' . e($inputId) . '" value="' . e($designation->job_code) . '">' .
            '<button class="btn btn-sm rounded-pill btn-outline-' . ($isTrue ? 'success' : 'danger') .
            ' update-button" title="Change Boolean" data-id="' . e($designation->id) . '" type="button">' .
            ($isTrue ? 'True' : 'False') .
            '</button></form>';
    }
    public function updateDeptHead(Request $request, $id)
    {
        $designation = Designation::find($id);
        if ($designation) {
            $designation->dept_head_flag = ($designation->dept_head_flag === 'T') ? 'F' : 'T';
            $designation->save(); // Save the changes
        }

        return redirect()->route('designations')->with('success', 'Designation data changed.');
    }
    public function updateDirectorHead(Request $request, $id)
    {
        $designation = Designation::find($id);
        if ($designation) {
            $designation->director_flag = ($designation->director_flag === 'T') ? 'F' : 'T';
            $designation->save(); // Save the changes
        }

        return redirect()->route('designations')->with('success', 'Designation data changed.');
    }
}
