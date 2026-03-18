<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Location;

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

        return view(
            "pages.admin.approvalSetting",
            compact(
                "link",
                "parentLink",
                "active",
                "locations",
                "groupCompanies",
                "companies",
            ),
        );
    }
}
