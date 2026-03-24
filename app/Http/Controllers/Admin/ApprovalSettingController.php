<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $hcga_employees = Employee::select("employee_id", "fullname", "group_company", "company_name", "contribution_level_code")
            ->whereIn('designation_name', [
                'HCO Wilayah',
                'HCO Manager',
            ])
            ->orderBy("company_name")
            ->get();

        $ktu_employees = Employee::select("employee_id", "fullname", "group_company", "company_name", "contribution_level_code")
            ->whereIn('designation_name', [
                'Kepala Tata Usaha',
                'Koordinator KTU'
            ])
            ->orderBy("company_name")
            ->get();

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
}
