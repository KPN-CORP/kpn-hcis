<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        $group_companies = Location::select("company_name")
            ->orderBy("company_name")
            ->distinct()
            ->pluck("company_name");

        $companies = Company::select(
            "contribution_level",
            "contribution_level_code",
        )
            ->orderBy("contribution_level_code")
            ->get();

        $employees = Employee::select(
                "employee_id",
                "fullname",
                "group_company",
                "company_name",
                "contribution_level_code",
                "designation_name"
            )
            ->whereIn('designation_name', [
                'HCO Wilayah',
                'HCO Manager',
                'Kepala Tata Usaha',
                'Koordinator KTU'
            ])
            ->orderBy("company_name")
            ->get()
            ->groupBy(function ($item) {
                return in_array($item->designation_name, ['HCO Wilayah', 'HCO Manager'])
                    ? 'hcga'
                    : 'ktu';
            });

        $hcga_employees = $employees['hcga'] ?? collect();
        $ktu_employees = $employees['ktu'] ?? collect();

        return view(
            "pages.admin.approvalSetting",
            compact(
                "link",
                "parentLink",
                "active",
                "locations",
                "group_companies",
                "companies",
                "hcga_employees",
                "ktu_employees",
            ),
        );
    }

    public function create(Request $request): RedirectResponse; {
        $validated = $request->validate([
            'approval_name' => 'required|string|max:100',
            'approval_type' => 'required|string|max:100',
            'group_companies' => 'string',
            'contribution_level_codes' => 'string',
            'work_areas' => 'string',
            'hcga_employee_id' => 'required|exists:employees,employee_id',
            'ktu_employee_id' => 'required|exists:employees,employee_id',
        ], [
            'approval_name.required' => 'Nama approval wajib diisi.',
            'approval_name.string' => 'Nama approval harus berupa teks.',
            'approval_name.max' => 'Nama approval maksimal 100 karakter.',
            'approval_type.required' => 'Approval type wajib diisi.',
            'approval_type.string' => 'Approval type harus berupa teks.',
            'approval_type.max' => 'Approval type maksimal 100 karakter.',
            'group_companies.string' => 'Group company harus berupa teks.',
            'contribution_level_codes.string' => 'Company harus berupa teks.',
            'work_areas.string' => 'Location harus berupa teks.',
            'hcga_employee_id.required' => 'HCGA wajib dipilih.',
            'hcga_employee_id.exists' => 'HCGA yang dipilih tidak valid atau tidak ditemukan.',
            'ktu_employee_id.required' => 'KTU wajib dipilih.',
            'ktu_employee_id.exists' => 'KTU yang dipilih tidak valid atau tidak ditemukan.',
        ]);

        ApprovalSetting::create([
            'approval_name' => $request->approval_name,
            'approval_type' => $request->approval_type,
            'group_companies' => $request->group_companies,
            'contribution_level_codes' => $request->contribution_level_codes,
            'work_areas' => $request->work_areas,
            'hcga_employee_id' => $request->hcga_employee_id,
            'ktu_employee_id' => $request->ktu_employee_id,
        ]);

        return redirect()
            ->route("admin_approval_setting")
            ->with("success", "Approval setting creates successfully!");
    }
}
