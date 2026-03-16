<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApprovalSettingController extends Controller
{
    function __construct()
    {
        $this->link = "Approval Setting";
    }

    function create_view()
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
            "pages.approval.create",
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

    function create(Request $request)
    {
        // $roleName = $request->roleName;
        // $guardName = "web";

        // $groupCompany = $request->input("group_company", []);
        // $company = $request->input("contribution_level_code", []);
        // $location = $request->input("work_area_code", []);

        // $data = [
        //     "work_area_code" => empty($location) ? null : $location,
        //     "group_company" => empty($groupCompany) ? null : $groupCompany,
        //     "contribution_level_code" => empty($company) ? null : $company,
        // ];

        // // Konversi ke JSON format
        // $restriction = json_encode($data);

        // $existingRole = Role::where("name", $roleName)->first();

        // if ($existingRole) {
        //     // Role with the same name already exists, handle accordingly (e.g., show error message)
        //     return redirect()
        //         ->back()
        //         ->with("error", "Role with the same name already exists.");
        // }

        // // $permissions = [
        // //     'adminMenu' => $request->input('adminMenu', false), // 9 = adminmenu
        // //     'onBehalfView' => $request->input('onBehalfView', false), // Use false as default value if not set
        // //     'onBehalfApproval' => $request->input('onBehalfApproval', false),
        // //     'onBehalfSendback' => $request->input('onBehalfSendback', false),
        // //     'reportView' => $request->input('reportView', false),
        // //     'settingView' => $request->input('settingView', false),
        // //     'scheduleView' => $request->input('scheduleView', false),
        // //     'layerView' => $request->input('layerView', false),
        // //     'roleView' => $request->input('roleView', false),
        // //     'addGuide' => $request->input('addGuide', false),
        // //     'removeGuide' => $request->input('removeGuide', false),
        // // ];
        // $permissionsFromDb = Permission::pluck("name")->toArray();

        // // Loop melalui setiap permission untuk mengisi data request
        // $permissions = [];
        // foreach ($permissionsFromDb as $permissionName) {
        //     // Setiap permission diambil dari request, default false jika tidak ada
        //     $permissions[$permissionName] = $request->input(
        //         $permissionName,
        //         false,
        //     );
        // }

        // // Build permission_id string
        // $permission_id = "";

        // $role = new Role();
        // $role->name = $roleName;
        // $role->guard_name = $guardName;
        // $role->restriction = $restriction;
        // $role->save();

        // // Loop through permissions and create new permission records
        // foreach ($permissions as $key) {
        //     if ($key) {
        //         // Create a new permission record
        //         $rolepermission = new RoleHasPermission();
        //         $rolepermission->role_id = $role->id;
        //         $rolepermission->permission_id = $key;
        //         $rolepermission->save();
        //     }
        // }

        // app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // return redirect()
        //     ->route("roles")
        //     ->with("success", "Role created successfully!");
    }
}
