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

        $masterLocations = Location::pluck("area", "work_area");
        $masterCompanies = Company::pluck("contribution_level", "contribution_level_code");

        $approvalSettings = ApprovalSetting::with(["hcga_employee", "ktu_employee"])
            ->orderBy("created_at")
            ->get()
            ->map(function ($item) use ($masterLocations, $masterCompanies) {
                $companyNames = explode(',', $item->company_names ?: '');
                $contributionLevelCodes = explode(',', $item->contribution_level_codes ?: '');
                $workAreas = explode(',', $item->work_areas ?: '');

                $item->company_names_label = collect($companyNames)
                    ->filter()
                    ->implode(', ');

                $item->contribution_levels_label = collect($contributionLevelCodes)
                    ->filter()
                    ->map(fn($val) => $masterCompanies[$val] ? $masterCompanies[$val] . " (".$val.")" : $val)
                    ->implode(', ');

                $item->work_areas_label = collect($workAreas)
                    ->filter()
                    ->map(fn($val) => $masterLocations[$val] ?: $val)
                    ->implode(', ');

                return $item;
            });

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
            'group_companies' => 'nullable|string',
            'contribution_level_codes' => 'nullable|string',
            'work_areas' => 'nullable|string',
            'hcga_employee_id' => 'nullable|exists:employees,employee_id',
            'ktu_employee_id' => 'nullable|exists:employees,employee_id',
        ], [
            'approval_name.required' => 'Nama approval wajib diisi',
            'approval_name.string' => 'Nama approval harus berupa text',
            'approval_name.max' => 'Nama approval maksimal 100 karakter',
            'approval_type.required' => 'Approval type wajib diisi.',
            'approval_type.string' => 'Approval type harus berupa text',
            'approval_type.max' => 'Approval type maksimal 100 karakter',
            'group_companies.string' => 'Group company harus berupa text',
            'contribution_level_codes.string' => 'Company harus berupa text',
            'work_areas.string' => 'Location harus berupa text',
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
            'message' => 'Approval setting berhasil dibuat!'
        ]);
    }

    public function update(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:approval_setting,id',
            'approval_name' => 'required|string|max:100',
            'approval_type' => 'required|string|max:100',
            'group_companies' => 'nullable|string',
            'contribution_level_codes' => 'nullable|string',
            'work_areas' => 'nullable|string',
            'hcga_employee_id' => 'nullable|exists:employees,employee_id',
            'ktu_employee_id' => 'nullable|exists:employees,employee_id',
        ], [
            'id.required' => 'ID wajib dikirim',
            'approval_name.required' => 'Nama approval wajib diisi',
            'approval_name.string' => 'Nama approval harus berupa text',
            'approval_name.max' => 'Nama approval maksimal 100 karakter',
            'approval_type.required' => 'Approval type wajib diisi.',
            'approval_type.string' => 'Approval type harus berupa text',
            'approval_type.max' => 'Approval type maksimal 100 karakter',
            'group_companies.string' => 'Group company harus berupa text',
            'contribution_level_codes.string' => 'Company harus berupa text',
            'work_areas.string' => 'Location harus berupa text',
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
            'message' => 'Data berhasil diupdate!'
        ]);
    }

    public function delete(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:approval_setting,id',
        ], [
            'id.required' => 'ID wajib dikirim',
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
            'message' => 'Data berhasil dihapus!'
        ]);
    }
}
