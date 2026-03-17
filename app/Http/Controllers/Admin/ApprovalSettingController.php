<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApprovalSettingController extends Controller
{
    function __construct() {}

    function approval_deklarasi_view()
    {
        $parentLink = "Approval Deklarasi Setting";
        $link = "Approval Deklarasi Setting";
        $active = "";

        return view(
            "pages.approval_setting.deklarasi.view",
            compact("link", "parentLink", "active"),
        );
    }

    function approval_deklarasi_create_view()
    {
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

        $parentLink = $this->link;
        $link = "Create";
        $active = "create";

        return view(
            "pages.approval_setting.deklarasi.create.view",
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

    function approval_deklarasi_create_submit(Request $request)
    {
        $groupCompany = $request->input("group_company", []);
        $company = $request->input("contribution_level_code", []);
        $location = $request->input("work_area_code", []);

        $data = [
            "work_area_code" => empty($location) ? null : $location,
            "group_company" => empty($groupCompany) ? null : $groupCompany,
            "contribution_level_code" => empty($company) ? null : $company,
        ];

        $restriction = json_encode($data);

        return redirect()
            ->route("approval_setting.deklarasi.view")
            ->with(
                "success",
                "Approval deklarasi setting created successfully!",
            );
    }

    function approval_deklarasi_update_view()
    {
        return view(
            "pages.approval_setting.deklarasi.update.view",
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

    function approval_deklarasi_update_submit(Request $request)
    {
        return redirect()
            ->route("approval_setting.deklarasi.view")
            ->with(
                "success",
                "Approval deklarasi setting updated successfully!",
            );
    }

    function approval_deklarasi_delete_submit(Request $request)
    {
        return redirect()
            ->route("approval_setting.deklarasi.view")
            ->with(
                "success",
                "Approval deklarasi setting deleted successfully!",
            );
    }
}
