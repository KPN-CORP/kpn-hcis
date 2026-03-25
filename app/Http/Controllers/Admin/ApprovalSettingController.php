<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\ApprovalSetting;
use App\Models\Company;
use App\Models\Location;
use App\Models\Employee;

class ApprovalSettingController extends Controller
{
    protected $link;

    function __construct() {}

    function index()
    {
        $parentLink = "Approval Setting";
        $link = $this->link;
        $active = "";

        $locations = Location::select("company_name", "area", "work_area")
            ->orderBy("area")
            ->get();

        $groupCompanies = Location::select("company_name")
            ->orderBy("company_name")
            ->distinct()
            ->pluck("company_name");

        $companies = Company::select(
            "contribution_level",
            "contribution_level_code",
        )
        ->orderBy("contribution_level_code")
        ->get();

        // $masterCodes = \App\Models\MasterKode::pluck('fullname', 'code');

        // $approvalSettings = ApprovalSetting::with(['hcga_employee', 'ktu_employee'])
        //     ->orderBy('created_at')
        //     ->get()
        //     ->map(function ($item) use ($masterCodes) {

        //         $codes = explode(',', $item->group_companies);

        //         $names = collect($codes)
        //             ->map(fn($code) => $masterCodes[$code] ?? $code)
        //             ->implode(', ');

        //         $item->group_companies_label = $names;

        //         return $item;
        //     });

        $approvalSettings = ApprovalSetting::with(["hcga_employee", "ktu_employee"])->orderBy("created_at")->get();

        // $employees = Employee::select(
        //         "employee_id",
        //         "fullname",
        //         "group_company",
        //         "company_name",
        //         "contribution_level_code",
        //         "designation_name"
        //     )
        //     ->whereIn('designation_name', [
        //         'HCO Wilayah',
        //         'HCO Manager',
        //         'Kepala Tata Usaha',
        //         'Koordinator KTU'
        //     ])
        //     ->orderBy("company_name")
        //     ->get()
        //     ->groupBy(function ($item) {
        //         return in_array($item->designation_name, ['HCO Wilayah', 'HCO Manager'])
        //             ? 'hcga'
        //             : 'ktu';
        //     });

        // $hcgaEmployees = $employees['hcga'] ?? collect();
        // $ktuEmployees = $employees['ktu'] ?? collect();

        $employees = Employee::select(
            "employee_id",
            "fullname",
            "group_company",
            "company_name",
            "contribution_level_code",
            "designation_name"
        )
        ->orderBy("company_name")
        ->get();

        $hcgaEmployees = $employees;
        $ktuEmployees = $employees;

        return view(
            "pages.admin.approvalSetting",
            compact(
                "link",
                "parentLink",
                "active",
                "locations",
                "groupCompanies",
                "companies",
                "hcgaEmployees",
                "ktuEmployees",
                "approvalSettings",
            ),
        );
    }

    public function create(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'approval_name' => 'required|string|max:100',
            'approval_type' => 'required|string|max:100',
            'group_companies' => 'string',
            'contribution_level_codes' => 'string',
            'work_areas' => 'string',
            'hcga_employee_id' => 'required|exists:employees,employee_id',
            'ktu_employee_id' => 'required|exists:employees,employee_id',
        ], [
            'approval_name.required' => 'Nama approval wajib diisi.',
            'approval_type.required' => 'Approval type wajib diisi.',
            'hcga_employee_id.required' => 'HCGA wajib dipilih.',
            'ktu_employee_id.required' => 'KTU wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data belum diisi dengan benar',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = ApprovalSetting::where('name', $request->approval_name)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data approval setting sudah ada!'
            ], 409);
        }

        ApprovalSetting::create([
            'name' => $request->approval_name,
            'approval_type' => $request->approval_type,
            'company_names' => $request->group_companies,
            'contribution_level_codes' => $request->contribution_level_codes,
            'work_areas' => $request->work_areas,
            'hcga_employee_id' => $request->hcga_employee_id,
            'ktu_employee_id' => $request->ktu_employee_id,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval setting creates successfully!'
        ]);
    }

    public function update(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:approval_setting,id',
            'approval_name' => 'required|string|max:100',
            'approval_type' => 'required|string|max:100',
            'hcga_employee_id' => 'required|exists:employees,employee_id',
            'ktu_employee_id' => 'required|exists:employees,employee_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data belum diisi dengan benar',
                'errors' => $validator->errors()
            ], 422);
        }

        $approvalSetting = ApprovalSetting::find($request->id);
        if (!$approvalSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Approval setting tidak ditemukan.'
            ], 404);
        }

        $exists = ApprovalSetting::where('name', $request->approval_name)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data approval setting sudah ada.'
            ], 409);
        }

        $approvalSetting->update([
            'name' => $request->approval_name,
            'approval_type' => $request->approval_type,
            'company_names' => $request->group_companies,
            'contribution_level_codes' => $request->contribution_level_codes,
            'work_areas' => $request->work_areas,
            'hcga_employee_id' => $request->hcga_employee_id,
            'ktu_employee_id' => $request->ktu_employee_id,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval setting updated successfully!'
        ]);
    }

    public function delete(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:approval_setting,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data belum diisi dengan benar',
                'errors' => $validator->errors()
            ], 422);
        }

        $approvalSetting = ApprovalSetting::find($request->id);
        if (!$approvalSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Approval setting tidak ditemukan.'
            ], 404);
        }

        $approvalSetting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Approval setting deleted successfully!'
        ]);
    }
}
