<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApprovalSettingSyncOldApprovalExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalSetting;
use App\Models\Company;
use App\Models\Location;
use App\Models\Employee;
use App\Models\BTApproval;
use App\Models\ca_approval;
use App\Models\ca_sett_approval;

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

    public function syncOldApproval() {
        try {
            $bt_approvals = BTApproval::with([
                    'employee',
                    'oldEmployee',
                    'businessTrip' => function ($query) {
                        $query->with([
                            'employee',
                        ]);
                    },
                ])
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where("role_name", "Dept Head HC GA")
                            ->orWhere("role_name", "HC GA");
                    })->orWhere(function ($query) {
                        $query->where("role_name", "Dept Head AR & AP");
                    });
                })
                ->where(function ($query) {
                    $query->whereNull('approved_at')->where('approval_status', '!=', 'Approved');
                })
                ->whereHas('businessTrip', function ($query) {
                    $query->whereNot('status', 'Approved')->where('deleted_at', null);
                })
                ->whereHas('businessTrip.employee', function ($query) {
                    $query->where("group_company", "like", "%Plantation%");
                })
                ->get();

            $ca_approvals = ca_approval::with([
                    'employee',
                    'oldEmployee',
                    'caTransaction' => function ($query) {
                        $query->with([
                            'employee',
                        ]);
                    },
                ])
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where("role_name", "Dept Head HC GA")
                            ->orWhere("role_name", "HC GA");
                    })->orWhere(function ($query) {
                        $query->where("role_name", "Dept Head AR & AP");
                    });
                })
                ->where(function ($query) {
                    $query->whereNull('approved_at')->where('approval_status', '!=', 'Approved');
                })
                ->whereHas('caTransaction', function ($query) {
                    $query->whereNot('ca_status', 'Done')->whereNot('approval_status', 'Approved')->where('deleted_at', null);
                })
                ->whereHas('caTransaction.employee', function ($query) {
                    $query->where("group_company", "like", "%Plantation%");
                })
                ->get();

            $ca_sett_approvals = ca_sett_approval::with([
                    'employee',
                    'oldEmployee',
                    'caTransaction' => function ($query) {
                        $query->with([
                            'employee',
                        ]);
                    },
                ])
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where("role_name", "Dept Head HC GA")
                            ->orWhere("role_name", "HC GA");
                    })->orWhere(function ($query) {
                        $query->where("role_name", "Dept Head AR & AP");
                    });
                })
                ->where(function ($query) {
                    $query->whereNull('approved_at')->where('approval_status', '!=', 'Approved');
                })
                ->whereHas('caTransaction', function ($query) {
                    $query->whereNot('ca_status', 'Done')->whereNot('approval_sett', 'Approved')->where('deleted_at', null);
                })
                ->whereHas('caTransaction.employee', function ($query) {
                    $query->where("group_company", "like", "%Plantation%");
                })
                ->get();

            foreach ($bt_approvals as $item) {
                $contribution_level_code = null;
                $group_company = null;

                if ($item->businessTrip && $item->businessTrip->employee && $item->businessTrip->employee->group_company) {
                    $group_company = $item->businessTrip->employee->group_company;
                }

                if ($item->businessTrip && $item->businessTrip->employee && $item->businessTrip->employee->contribution_level_code) {
                    $contribution_level_code = $item->businessTrip->employee->contribution_level_code;
                }

                $data_approval_setting = null;
                $location_work_area = null;

                if ($item->businessTrip && $item->businessTrip->employee && $item->businessTrip->employee->work_area_code) {
                    $location_work_area = $item->businessTrip->employee->work_area_code;
                }

                if ($group_company) {
                    $data_approval_setting = ApprovalSetting::with(['hcga_employee', 'ktu_employee'])->where("company_names", "like", "%" . $group_company . "%")
                        ->where(function ($query) use ($contribution_level_code) {
                            $query->where("contribution_level_codes", "like", "%" . $contribution_level_code . "%")
                                ->orWhere("contribution_level_codes", null);
                        })
                        ->where(function ($query) use ($location_work_area) {
                            $query->where("work_areas", "like", "%" . $location_work_area . "%")
                                ->orWhere("work_areas", null);
                        })
                        ->first();
                }

                $new_employee_id = null;
                $new_employee_name = null;
                $approval_setting_name = null;
                $approval_setting_company_names = null;
                $approval_setting_contibution_level_codes = null;
                $approval_setting_work_areas = null;

                if ($data_approval_setting) {
                    if ($data_approval_setting->hcga_employee_id && ($item->role_name == "Dept Head HC GA" || $item->role_name == "HC GA")) {
                        $new_employee_id = $data_approval_setting->hcga_employee_id;
                        $new_employee_name = $data_approval_setting->hcga_employee ? ($data_approval_setting->hcga_employee->fullname ?? null) : null;
                    }

                    if ($data_approval_setting->ktu_employee_id && $item->role_name == "Dept Head AR & AP") {
                        $new_employee_id = $data_approval_setting->ktu_employee_id;
                        $new_employee_name = $data_approval_setting->ktu_employee ? ($data_approval_setting->ktu_employee->fullname ?? null) : null;
                    }

                    $approval_setting_name = $data_approval_setting->name;
                    $approval_setting_company_names = $data_approval_setting->company_names;
                    $approval_setting_contibution_level_codes = $data_approval_setting->contribution_level_codes;
                    $approval_setting_work_areas = $data_approval_setting->work_areas;
                }

                if ($new_employee_id) {
                    // $item->new_employee_id = $new_employee_id;
                    // $item->new_employee_fullname = $new_employee_name;
                    // $item->approval_setting_name = $approval_setting_name;
                    // $item->approval_setting_company_names = $approval_setting_company_names;
                    // $item->approval_setting_contibution_level_codes = $approval_setting_contibution_level_codes;
                    // $item->approval_setting_work_areas = $approval_setting_work_areas;

                    $item->old_employee_id = $item->employee_id;
                    $item->employee_id = $new_employee_id;
                    $item->save();
                }

                // $item->bt_tujuan_area = $location_work_area;
            }

            foreach ($ca_approvals as $item) {
                $contribution_level_code = null;
                $group_company = null;

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->group_company) {
                    $group_company = $item->caTransaction->employee->group_company;
                }

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->contribution_level_code) {
                    $contribution_level_code = $item->caTransaction->employee->contribution_level_code;
                }

                $data_approval_setting = null;
                $location_work_area = null;

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->work_area_code) {
                    $location_work_area = $item->caTransaction->employee->work_area_code;
                }

                if ($group_company) {
                    $data_approval_setting = ApprovalSetting::with(['hcga_employee', 'ktu_employee'])->where("company_names", "like", "%" . $group_company . "%")
                        ->where(function ($query) use ($contribution_level_code) {
                            $query->where("contribution_level_codes", "like", "%" . $contribution_level_code . "%")
                                ->orWhere("contribution_level_codes", null);
                        })
                        ->where(function ($query) use ($location_work_area) {
                            $query->where("work_areas", "like", "%" . $location_work_area . "%")
                                ->orWhere("work_areas", null);
                        })
                        ->first();
                }

                $new_employee_id = null;
                $approval_setting_name = null;
                $approval_setting_company_names = null;
                $approval_setting_contibution_level_codes = null;
                $approval_setting_work_areas = null;

                if ($data_approval_setting) {
                    if ($data_approval_setting->hcga_employee_id && ($item->role_name == "Dept Head HC GA" || $item->role_name == "HC GA")) {
                        $new_employee_id = $data_approval_setting->hcga_employee_id;
                        $new_employee_name = $data_approval_setting->hcga_employee ? ($data_approval_setting->hcga_employee->fullname ?? null) : null;
                    }

                    if ($data_approval_setting->ktu_employee_id && $item->role_name == "Dept Head AR & AP") {
                        $new_employee_id = $data_approval_setting->ktu_employee_id;
                        $new_employee_name = $data_approval_setting->ktu_employee ? ($data_approval_setting->ktu_employee->fullname ?? null) : null;
                    }

                    $approval_setting_name = $data_approval_setting->name;
                    $approval_setting_company_names = $data_approval_setting->company_names;
                    $approval_setting_contibution_level_codes = $data_approval_setting->contribution_level_codes;
                    $approval_setting_work_areas = $data_approval_setting->work_areas;
                }

                if ($new_employee_id) {
                    // $item->new_employee_id = $new_employee_id;
                    // $item->new_employee_fullname = $new_employee_name;
                    // $item->approval_setting_name = $approval_setting_name;
                    // $item->approval_setting_company_names = $approval_setting_company_names;
                    // $item->approval_setting_contibution_level_codes = $approval_setting_contibution_level_codes;
                    // $item->approval_setting_work_areas = $approval_setting_work_areas;

                    $item->old_employee_id = $item->employee_id;
                    $item->employee_id = $new_employee_id;
                    $item->save();
                }

                // $item->ca_destination_area = $location_work_area;
            }

            foreach ($ca_sett_approvals as $item) {
                $contribution_level_code = null;
                $group_company = null;

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->group_company) {
                    $group_company = $item->caTransaction->employee->group_company;
                }

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->contribution_level_code) {
                    $contribution_level_code = $item->caTransaction->employee->contribution_level_code;
                }

                $data_approval_setting = null;
                $location_work_area = null;

                if ($item->caTransaction && $item->caTransaction->employee && $item->caTransaction->employee->work_area_code) {
                    $location_work_area = $item->caTransaction->employee->work_area_code;
                }

                if ($group_company) {
                    $data_approval_setting = ApprovalSetting::with(['hcga_employee', 'ktu_employee'])->where("company_names", "like", "%" . $group_company . "%")
                        ->where(function ($query) use ($contribution_level_code) {
                            $query->where("contribution_level_codes", "like", "%" . $contribution_level_code . "%")
                                ->orWhere("contribution_level_codes", null);
                        })
                        ->where(function ($query) use ($location_work_area) {
                            $query->where("work_areas", "like", "%" . $location_work_area . "%")
                                ->orWhere("work_areas", null);
                        })
                        ->first();
                }

                $new_employee_id = null;
                $approval_setting_name = null;
                $approval_setting_company_names = null;
                $approval_setting_contibution_level_codes = null;
                $approval_setting_work_areas = null;

                if ($data_approval_setting) {
                    if ($data_approval_setting->hcga_employee_id && ($item->role_name == "Dept Head HC GA" || $item->role_name == "HC GA")) {
                        $new_employee_id = $data_approval_setting->hcga_employee_id;
                        $new_employee_name = $data_approval_setting->hcga_employee ? ($data_approval_setting->hcga_employee->fullname ?? null) : null;
                    }

                    if ($data_approval_setting->ktu_employee_id && $item->role_name == "Dept Head AR & AP") {
                        $new_employee_id = $data_approval_setting->ktu_employee_id;
                        $new_employee_name = $data_approval_setting->ktu_employee ? ($data_approval_setting->ktu_employee->fullname ?? null) : null;
                    }

                    $approval_setting_name = $data_approval_setting->name;
                    $approval_setting_company_names = $data_approval_setting->company_names;
                    $approval_setting_contibution_level_codes = $data_approval_setting->contribution_level_codes;
                    $approval_setting_work_areas = $data_approval_setting->work_areas;
                }

                if ($new_employee_id) {
                    // $item->new_employee_id = $new_employee_id;
                    // $item->new_employee_fullname = $new_employee_name;
                    // $item->approval_setting_name = $approval_setting_name;
                    // $item->approval_setting_company_names = $approval_setting_company_names;
                    // $item->approval_setting_contibution_level_codes = $approval_setting_contibution_level_codes;
                    // $item->approval_setting_work_areas = $approval_setting_work_areas;

                    $item->old_employee_id = $item->employee_id;
                    $item->employee_id = $new_employee_id;
                    $item->save();
                }

                // $item->ca_destination_area = $location_work_area;
            }

            Excel::store(
                new ApprovalSettingSyncOldApprovalExport($bt_approvals, $ca_approvals, $ca_sett_approvals),
                'all_approvals.xlsx',
                'local'
            );

            return "SUCCESS";
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
