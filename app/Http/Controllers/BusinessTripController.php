<?php

namespace App\Http\Controllers;

use App\Exports\BusinessTripExport;
use App\Exports\UsersExport;
use App\Models\BTApproval;
use App\Models\BusinessTrip;
use App\Models\ca_transaction;
use App\Models\CATransaction;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Taksi;
use App\Models\Mess;
use App\Models\Tiket;
use App\Models\ca_sett_approval;
use App\Models\ListPerdiem;
use App\Models\TiketApproval;
use App\Models\HotelApproval;
use App\Models\MessApproval;
use App\Models\TaksiApproval;
use App\Models\HealthCoverage;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\DB;
use App\Models\MatrixApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use App\Models\ca_approval;
use Illuminate\Support\Facades\Mail;
use App\Mail\BusinessTripNotification;
use App\Mail\DeclarationNotification;
use App\Mail\RefundNotification;


class BusinessTripController extends Controller
{
    protected $groupCompanies;
    protected $companies;
    protected $locations;
    protected $permissionGroupCompanies;
    protected $permissionCompanies;
    protected $permissionLocations;
    protected $roles;

    public function __construct()
    {
        // $this->category = 'Goals';
        $this->roles = Auth()->user()->roles;

        $restrictionData = [];
        if (!is_null($this->roles) && $this->roles->isNotEmpty()) {
            $restrictionData = json_decode($this->roles->first()->restriction, true);
        }

        $this->permissionGroupCompanies = $restrictionData['group_company'] ?? [];
        $this->permissionCompanies = $restrictionData['contribution_level_code'] ?? [];
        $this->permissionLocations = $restrictionData['work_area_code'] ?? [];

        $groupCompanyCodes = $restrictionData['group_company'] ?? [];

        $this->groupCompanies = Location::select('company_name')
            ->when(!empty($groupCompanyCodes), function ($query) use ($groupCompanyCodes) {
                return $query->whereIn('company_name', $groupCompanyCodes);
            })
            ->orderBy('company_name')->distinct()->pluck('company_name');

        $workAreaCodes = $restrictionData['work_area_code'] ?? [];

        $this->locations = Location::select('company_name', 'area', 'work_area')
            ->when(!empty($workAreaCodes) || !empty($groupCompanyCodes), function ($query) use ($workAreaCodes, $groupCompanyCodes) {
                return $query->where(function ($query) use ($workAreaCodes, $groupCompanyCodes) {
                    if (!empty($workAreaCodes)) {
                        $query->whereIn('work_area', $workAreaCodes);
                    }
                    if (!empty($groupCompanyCodes)) {
                        $query->orWhereIn('company_name', $groupCompanyCodes);
                    }
                });
            })
            ->orderBy('area')
            ->get();

        $companyCodes = $restrictionData['contribution_level_code'] ?? [];

        $this->companies = Company::select('contribution_level', 'contribution_level_code')
            ->when(!empty($companyCodes), function ($query) use ($companyCodes) {
                return $query->whereIn('contribution_level_code', $companyCodes);
            })
            ->orderBy('contribution_level_code')->get();
    }
    public function businessTrip(Request $request)
    {
        $user = Auth::user();
        $query = BusinessTrip::where('user_id', $user->id)->orderBy('created_at', 'desc');

        $disableBT = BusinessTrip::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', '!=', 'Verified');
            })
            ->count();

        // Get the filter value, default to 'all' if not provided
        $filter = $request->input('filter', 'all');

        if ($filter === 'request') {
            // Show all data where the date is < today and status is in ['Pending L1', 'Pending L2', 'Draft']
            $query->where(function ($query) {
                $query->whereDate('kembali', '<', now())
                    ->whereIn('status', ['Pending L1', 'Pending L2']);
            });
        } elseif ($filter === 'declaration') {
            // Show data with Approved, Declaration L1, Declaration L2, Draft Declaration
            $query->where(function ($query) {
                $query->whereIn('status', ['Approved', 'Declaration L1', 'Declaration L2', 'Declaration Approved', 'Declaration Draft']);
            });
        } elseif ($filter === 'rejected') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Rejected', 'Declaration Rejected']);
            });
        } elseif ($filter === 'done') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Return/Refund', 'Doc Accepted', 'Verified']);
            });
        } elseif ($filter === 'draft') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Draft', 'Declaration Draft']);
            });
        }

        // If 'all' is selected or no filter is applied, just get all data
        if ($filter === 'all') {
            // No additional where clauses needed for 'all'
        }

        $sppd = $query->get();

        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();
        // Log::info('Ticket Approvals:', $btApprovals->toArray());

        $btApprovals = $btApprovals->keyBy('bt_id');
        // dd($btApprovals);
        // Log::info('BT Approvals:', $btApprovals->toArray());

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');
        // Fetch related data
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $mess = Mess::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        // Get manager names
        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $parentLink = 'Reimbursement';
        $link = 'Business Trip';

        return view('hcis.reimbursements.businessTrip.businessTrip', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'managerL1Names', 'managerL2Names', 'filter', 'btApprovals', 'employeeName', 'disableBT', 'mess'));
    }

    public function delete($id)
    {
        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);

        // Check if the business trip exists
        if ($businessTrip) {
            // Get the sppd for the business trip
            $sppd = $businessTrip->no_sppd; // Assuming 'sppd' is a property of the BusinessTrip model

            // Soft delete related CA transactions
            CATransaction::where('no_sppd', $sppd)->delete();

            // Soft delete related Tiket records
            Tiket::where('no_sppd', $sppd)->delete();

            // Soft delete related Hotel records
            Hotel::where('no_sppd', $sppd)->delete();

            // Soft delete related Taksi records
            Taksi::where('no_sppd', $sppd)->delete();

            Mess::where('no_sppd', $sppd)->delete();

            // Perform soft delete on the business trip
            $businessTrip->delete();
        }

        // Redirect back with a success message
        return redirect()->route('businessTrip')->with('success', 'Business Trip marked as deleted along with related records.');
    }

    public function deleteAdmin($id)
    {
        $businessTrip = BusinessTrip::findOrFail($id);

        // Check if the business trip exists
        if ($businessTrip) {
            // Get the sppd for the business trip
            $sppd = $businessTrip->no_sppd; // Assuming 'sppd' is a property of the BusinessTrip model

            // Soft delete related CA transactions
            CATransaction::where('no_sppd', $sppd)->delete();

            // Soft delete related Tiket records
            Tiket::where('no_sppd', $sppd)->delete();

            // Soft delete related Hotel records
            Hotel::where('no_sppd', $sppd)->delete();

            // Soft delete related Taksi records
            Taksi::where('no_sppd', $sppd)->delete();

            Mess::where('no_sppd', $sppd)->delete();

            // Perform soft delete on the business trip
            $businessTrip->delete();
        }

        return redirect()->route('businessTrip.admin')->with('success', 'Business Trip marked as deleted.');
    }

    public function formUpdate($id)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $employee_data = Employee::where('id', $userId)->first();
        $employees = Employee::orderBy('ktp')->get();
        $cas = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $date = CATransaction::where('no_sppd', $n->no_sppd)->first();
        $group_company = Employee::where('id', $userId)->pluck('group_company')->first();
        $bt_sppd = BusinessTrip::where('status', '!=', 'Done')->where('status', '!=', 'Rejected')->where('status', '!=', 'Draft')->orderBy('no_sppd', 'desc')->get();

        $isApproved = CATransaction::where('user_id', $userId)->where('approval_status', '!=', 'Done')->where('approval_status', '!=', 'Rejected')->get();

        $isDisabled = $isApproved->count() >= 1;

        if ($employee_data->group_company == 'Plantations' || $employee_data->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }

        // Initialize caDetail with an empty array if it's null
        // $caDetail = $ca ? json_decode($ca->detail_ca, true) : [];
        $caDetail = [];
        foreach ($cas as $ca) {
            $currentDetail = json_decode($ca->detail_ca, true);
            if (is_array($currentDetail)) {
                $caDetail = array_merge($caDetail, $currentDetail);
            }
        }

        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();

        // Retrieve all hotels for the specific BusinessTrip
        $hotels = Hotel::where('no_sppd', $n->no_sppd)->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)
            ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')->first();
        $job_level = Employee::where('id', $userId)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);

        if ($job_level) {
            // Extract numeric part of the job level
            $numericPart = intval(preg_replace('/[^0-9]/', '', $job_level));
            $isAllowed = $numericPart >= 8;
        }

        $parentLink = 'Business Trip';
        $link = 'Business Trip Edit';

        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();
        $taksiLuarKota = null;
        $taksiDalamKota = null;

        if ($n->jns_dinas === 'luar kota') {
            $taksiLuarKota = $taksi;
        } else if ($n->jns_dinas === 'dalam kota') {
            $taksiDalamKota = $taksi;
        }

        $revisiInfo = null;
        if ($n->status === 'Request Revision') {
            $revisiInfo = BTApproval::where('bt_id', $n->id)
                ->where('approval_status', 'Request Revision')
                ->orderBy('created_at', 'desc') // Mengurutkan dari terbaru
                ->pluck('reject_info')
                ->first();
        }
        if ($n->status === 'Declaration Revision') {
            $revisiInfo = BTApproval::where('bt_id', $n->id)
                ->where('approval_status', 'Declaration Revision')
                ->orderBy('created_at', 'desc') // Mengurutkan dari terbaru
                ->pluck('reject_info')
                ->first();
        }

        // dd($revisiInfo);
        // dd($taksiLuarKota->no_vt ?? 'No', $taksiDalamKota-> no_vt ?? 'No');

        // Prepare hotel data for the view
        $hotelData = [];
        foreach ($hotels as $index => $hotel) {
            $hotelData[] = [
                'nama_htl' => $hotel->nama_htl,
                'lokasi_htl' => $hotel->lokasi_htl,
                'jmlkmr_htl' => $hotel->jmlkmr_htl,
                'bed_htl' => $hotel->bed_htl,
                'tgl_masuk_htl' => $hotel->tgl_masuk_htl,
                'tgl_keluar_htl' => $hotel->tgl_keluar_htl,
                'total_hari' => $hotel->total_hari,
                'no_sppd_htl' => $hotel->no_sppd_htl,
                'more_htl' => ($index < count($hotels) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve all tickets for the specific BusinessTrip
        $tickets = Tiket::where('no_sppd', $n->no_sppd)->get();

        // Prepare ticket data for the view
        $ticketData = [];
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'noktp_tkt' => $ticket->noktp_tkt,
                'tlp_tkt' => $ticket->tlp_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < count($tickets) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        $messes = Mess::where('no_sppd', $n->no_sppd)->get();

        // Prepare mess data for the view
        $messData = [];
        foreach ($messes as $index => $mess) {
            $messData[] = [
                'lokasi_mess' => $mess->lokasi_mess,
                'jmlkmr_mess' => $mess->jmlkmr_mess,
                'tgl_masuk_mess' => $mess->tgl_masuk_mess,
                'tgl_keluar_mess' => $mess->tgl_keluar_mess,
                'total_hari_mess' => $mess->total_hari_mess,
            ];
        }

        // Retrieve locations and companies data for the dropdowns
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();

        return view('hcis.reimbursements.businessTrip.editFormBt', [
            'n' => $n,
            'hotelData' => $hotelData,
            'messData' => $messData,
            'taksiData' => $taksi,
            'taksiLuarKota' => $taksiLuarKota,
            'taksiDalamKota' => $taksiDalamKota,
            'ticketData' => $ticketData,
            'employee_data' => $employee_data,
            'employees' => $employees,
            'companies' => $companies,
            'locations' => $locations,
            'caDetail' => $caDetail,
            'allowance' => $allowance,
            'ca' => $cas,
            'date' => $date,
            'group_company' => $group_company,
            'perdiem' => $perdiem,
            'parentLink' => $parentLink,
            'link' => $link,
            'isAllowed' => $isAllowed,
            'bt_sppd' => $bt_sppd,
            'job_level_number' => $job_level_number,
            'isDisabled' => $isDisabled,
            'revisiInfo' => $revisiInfo
        ]);
    }


    public function update(Request $request, $id)
    {
        // Fetch the business trip record to update
        $n = BusinessTrip::find($id);
        if (!$n) {
            return redirect()->back()->with('error', 'Business trip not found');
        }
        if ($request->tujuan === 'Others' && !empty($request->others_location)) {
            $tujuan = $request->others_location;  // Use the value from the text box
        } else {
            $tujuan = $request->tujuan;  // Use the selected dropdown value
        }

        if ($request->has('action_draft')) {
            $statusValue = 'Draft';  // When "Save as Draft" is clicked
        } elseif ($request->has('action_submit')) {
            $statusValue = 'Pending L1';  // When "Submit" is clicked
        }


        // Store old SPPD number for later use
        $oldNoSppd = $n->no_sppd;
        $userId = Auth::id();
        $employee = Employee::where('id', $userId)->first();
        if ($employee->group_company == 'Plantations' || $employee->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }

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

        $deptHeadManager = findDepartmentHead($employee);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        $isJobLevel = MatrixApproval::where('modul', 'businesstrip')
            ->where('group_company', 'like', '%' . $employee->group_company . '%')
            ->where('job_level', 'like', '%' . $employee->job_level . '%')
            ->get();

        if ($request->jns_dinas == 'dalam kota') {
            $tktDalam = $request->tiket_dalam_kota;
            $htlDalam = $request->hotel_dalam_kota;
            $vtDalam = $request->taksi_dalam_kota;
            $messDalam = $request->mess_dalam_kota;
        } else {
            $tktDalam = $request->tiket;
            $htlDalam = $request->hotel;
            $vtDalam = $request->taksi;
            $messDalam = $request->mess;
        }

        // dd($request->jns_dinas, $tktDalam, $htlDalam, $vtDalam);
        // dd($request->all());
        // Update business trip record
        $n->update([
            'nama' => $request->nama,
            'jns_dinas' => $request->jns_dinas,
            'divisi' => $request->divisi,
            'unit_1' => $request->unit_1,
            'atasan_1' => $request->atasan_1,
            'email_1' => $request->email_1,
            'unit_2' => $request->unit_2,
            'atasan_2' => $request->atasan_2,
            'email_2' => $request->email_2,
            'no_sppd' => $oldNoSppd,  // Preserve old SPPD number
            'mulai' => $request->mulai,
            'kembali' => $request->kembali,
            'tujuan' => $tujuan,
            'keperluan' => $request->keperluan,
            'bb_perusahaan' => $request->bb_perusahaan,
            'norek_krywn' => $request->norek_krywn,
            'nama_pemilik_rek' => $request->nama_pemilik_rek,
            'nama_bank' => $request->nama_bank,
            'ca' => $request->ca === 'Tidak' ? $request->ent : $request->ca,
            'tiket' => $tktDalam,
            'hotel' => $htlDalam,
            'taksi' => $vtDalam,
            'mess' => $messDalam,
            'status' => $statusValue,
            'manager_l1_id' => $managerL1,
            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
        ]);

        // Handle "Taksi" update
        if ($vtDalam === 'Ya') {
            // Fetch existing Taksi records
            $existingTaksi = Taksi::where('no_sppd', $oldNoSppd)->get()->keyBy('id');

            if ($request->jns_dinas === 'dalam kota') {
                $noVt = $request->input('no_vt_dalam_kota');
                $vtDetail = $request->input('vt_detail_dalam_kota');
            } else if ($request->jns_dinas === 'luar kota') {
                $noVt = $request->input('no_vt');
                $vtDetail = $request->input('vt_detail');
            }

            if (isset($noVt)) {
                // Prepare the data for update
                $taksiData = [
                    'id' => (string) Str::uuid(),
                    'no_vt' => $noVt,
                    'vt_detail' => $vtDetail,
                    'user_id' => Auth::id(),
                    'unit' => $request->divisi,
                    'no_sppd' => $oldNoSppd,
                    'approval_status' => $statusValue,
                ];
                // dd($taksiData);

                // Check if there's an existing Taksi record to update
                $existingTaksiRecord = $existingTaksi->first();

                if ($existingTaksiRecord) {
                    // Update existing Taksi record
                    $existingTaksiRecord->update($taksiData);
                } else {
                    // Create a new Taksi record
                    Taksi::create($taksiData);
                }
            } else {
                // If 'Taksi' is set to 'Ya' but no data provided, clear existing records
                Taksi::where('no_sppd', $oldNoSppd)->delete();
            }
        } else {
            // Remove all Taksi records if 'Taksi' is not selected
            Taksi::where('no_sppd', $oldNoSppd)->delete();
        }


        // Handle "Hotel" update
        if ($messDalam === 'Ya') {
            // Get all existing hotels for this business trip
            $existingMesses = Mess::where('no_sppd', $oldNoSppd)->get()->keyBy('id');
            $newNoMess = null;

            if ($existingMesses->isNotEmpty()) {
                $firstMess = $existingMesses->first();
                $newNoMess = $firstMess->no_mess;
            }

            $processedMessIds = [];

            if ($request->jns_dinas === 'dalam kota') {
                $messData = [
                    'lokasi_mess' => $request->lokasi_mess_dalam_kota,
                    'jmlkmr_mess' => $request->jmlkmr_mess_dalam_kota,
                    'tgl_masuk_mess' => $request->tgl_masuk_mess_dalam_kota,
                    'tgl_keluar_mess' => $request->tgl_keluar_mess_dalam_kota,
                    'total_hari_mess' => $request->total_hari_mess_dalam_kota,
                    'approval_status' => $statusValue,
                ];
            } else {
                $messData = [
                    'lokasi_mess' => $request->lokasi_mess,
                    'jmlkmr_mess' => $request->jmlkmr_mess,
                    'tgl_masuk_mess' => $request->tgl_masuk_mess,
                    'tgl_keluar_mess' => $request->tgl_keluar_mess,
                    'total_hari_mess' => $request->total_hari_mess,
                    'approval_status' => $statusValue,
                ];
            }

            foreach ($messData['lokasi_mess'] as $key => $value) {
                if (!empty($value)) {
                    $messId = $request->mess_id[$key] ?? null;

                    if ($messId && isset($existingMesses[$messId])) {
                        // Update existing hotel record
                        $mess = $existingMesses[$messId];
                        $mess->update([
                            'lokasi_mess' => $value,
                            'jmlkmr_mess' => $messData['jmlkmr_mess'][$key],
                            'tgl_masuk_mess' => $messData['tgl_masuk_mess'][$key],
                            'tgl_keluar_mess' => $messData['tgl_keluar_mess'][$key],
                            'total_hari_mess' => $messData['total_hari_mess'][$key],
                            'approval_status' => $statusValue,
                            "contribution_level_code" => $request->bb_perusahaan,
                            "manager_l1_id" => $managerL1,
                            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                        ]);

                        $processedMessIds[] = $messId;
                    } else {

                        if (!$newNoMess) {
                            $newNoMess = $this->generateNoSppdMess(); // Generate a new no_htl only if not set
                        }

                        $newMess = Mess::create([
                            'id' => (string) Str::uuid(),
                            'no_mess' => $newNoMess,
                            'user_id' => Auth::id(),
                            'unit' => $request->divisi,
                            'no_sppd' => $oldNoSppd,
                            'lokasi_mess' => $value,
                            'jmlkmr_mess' => $messData['jmlkmr_mess'][$key],
                            'tgl_masuk_mess' => $messData['tgl_masuk_mess'][$key],
                            'tgl_keluar_mess' => $messData['tgl_keluar_mess'][$key],
                            'total_hari_mess' => $messData['total_hari_mess'][$key],
                            'approval_status' => $statusValue,
                            "contribution_level_code" => $request->bb_perusahaan,
                            "manager_l1_id" => $managerL1,
                            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                        ]);

                        $processedMessIds[] = $newMess->id;
                    }
                }
            }
            Mess::where('no_sppd', $oldNoSppd)->whereNotIn('id', $processedMessIds)->delete();
        } else {
            Mess::where('no_sppd', $oldNoSppd)->delete();
        }

        if ($htlDalam === 'Ya') {
            // Get all existing hotels for this business trip
            $existingHotels = Hotel::where('no_sppd', $oldNoSppd)->get()->keyBy('id');
            $newNoHtl = null;

            // If there are existing hotels, use the first one’s no_htl
            if ($existingHotels->isNotEmpty()) {
                $firstHotel = $existingHotels->first();
                $newNoHtl = $firstHotel->no_htl; // Use existing no_htl
            }

            $processedHotelIds = [];

            if ($request->jns_dinas === 'dalam kota') {
                $hotelData = [
                    'nama_htl' => $request->nama_htl_dalam_kota,
                    'lokasi_htl' => $request->lokasi_htl_dalam_kota,
                    'jmlkmr_htl' => $request->jmlkmr_htl_dalam_kota,
                    'bed_htl' => $request->bed_htl_dalam_kota,
                    'tgl_masuk_htl' => $request->tgl_masuk_htl_dalam_kota,
                    'tgl_keluar_htl' => $request->tgl_keluar_htl_dalam_kota,
                    'total_hari' => $request->total_hari_dalam_kota,
                    'no_sppd_htl' => $request->no_sppd_dalam_kota,
                    'approval_status' => $statusValue,
                ];
            } else {
                $hotelData = [
                    'nama_htl' => $request->nama_htl,
                    'lokasi_htl' => $request->lokasi_htl,
                    'jmlkmr_htl' => $request->jmlkmr_htl,
                    'bed_htl' => $request->bed_htl,
                    'tgl_masuk_htl' => $request->tgl_masuk_htl,
                    'tgl_keluar_htl' => $request->tgl_keluar_htl,
                    'total_hari' => $request->total_hari,
                    'no_sppd_htl' => $request->no_sppd,
                    'approval_status' => $statusValue,
                ];
            }

            foreach ($hotelData['nama_htl'] as $key => $value) {
                if (!empty($value)) {
                    $hotelId = $request->hotel_id[$key] ?? null;

                    if ($hotelId && isset($existingHotels[$hotelId])) {
                        // Update existing hotel record
                        $hotel = $existingHotels[$hotelId];
                        $hotel->update([
                            'nama_htl' => $value,
                            'lokasi_htl' => $hotelData['lokasi_htl'][$key],
                            'jmlkmr_htl' => $hotelData['jmlkmr_htl'][$key],
                            'bed_htl' => $hotelData['bed_htl'][$key],
                            'tgl_masuk_htl' => $hotelData['tgl_masuk_htl'][$key],
                            'tgl_keluar_htl' => $hotelData['tgl_keluar_htl'][$key],
                            'total_hari' => $hotelData['total_hari'][$key],
                            'no_sppd_htl' => $hotelData['no_sppd_htl'][$key],
                            'approval_status' => $statusValue,
                            "contribution_level_code" => $request->bb_perusahaan,
                            "manager_l1_id" => $managerL1,
                            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                        ]);

                        $processedHotelIds[] = $hotelId;
                    } else {

                        if (!$newNoHtl) {
                            $newNoHtl = $this->generateNoSppdHtl(); // Generate a new no_htl only if not set
                        }

                        $newHotel = Hotel::create([
                            'id' => (string) Str::uuid(),
                            'no_htl' => $newNoHtl,
                            'user_id' => Auth::id(),
                            'unit' => $request->divisi,
                            'no_sppd' => $oldNoSppd,
                            'nama_htl' => $value,
                            'lokasi_htl' => $hotelData['lokasi_htl'][$key],
                            'jmlkmr_htl' => $hotelData['jmlkmr_htl'][$key],
                            'bed_htl' => $hotelData['bed_htl'][$key],
                            'tgl_masuk_htl' => $hotelData['tgl_masuk_htl'][$key],
                            'tgl_keluar_htl' => $hotelData['tgl_keluar_htl'][$key],
                            'total_hari' => $hotelData['total_hari'][$key],
                            'no_sppd_htl' => $hotelData['no_sppd_htl'][$key],
                            'approval_status' => $statusValue,
                            "contribution_level_code" => $request->bb_perusahaan,
                            "manager_l1_id" => $managerL1,
                            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                        ]);

                        $processedHotelIds[] = $newHotel->id;
                        // dd($newHotel);
                    }
                }
            }

            // Remove hotels that are no longer in the request
            Hotel::where('no_sppd', $oldNoSppd)->whereNotIn('id', $processedHotelIds)->delete();
        } else {
            Hotel::where('no_sppd', $oldNoSppd)->delete();  // Remove all hotels if not selected
        }

        // Handle "Ticket" update
        if ($tktDalam === 'Ya') {
            // Get all existing tickets for this business trip
            $existingTickets = Tiket::where('no_sppd', $oldNoSppd)->get()->keyBy('noktp_tkt');

            $newNoTkt = null;
            // If there are existing tickets, use the first one’s no_tkt
            if ($existingTickets->isNotEmpty()) {
                $firstTicket = $existingTickets->first();
                $newNoTkt = $firstTicket->no_tkt; // Use existing no_tkt
            }

            $processedTicketIds = [];

            if ($request->jns_dinas === 'dalam kota') {
                $ticketFields = [
                    'noktp_tkt' => $request->noktp_tkt_dalam_kota,
                    'dari_tkt' => $request->dari_tkt_dalam_kota,
                    'ke_tkt' => $request->ke_tkt_dalam_kota,
                    'tgl_brkt_tkt' => $request->tgl_brkt_tkt_dalam_kota,
                    'tgl_plg_tkt' => $request->tgl_plg_tkt_dalam_kota,
                    'jam_brkt_tkt' => $request->jam_brkt_tkt_dalam_kota,
                    'jam_plg_tkt' => $request->jam_plg_tkt_dalam_kota,
                    'jenis_tkt' => $request->jenis_tkt_dalam_kota,
                    'type_tkt' => $request->type_tkt_dalam_kota,
                    'ket_tkt' => $request->ket_tkt_dalam_kota,
                ];
            } else {
                $ticketFields = [
                    'noktp_tkt' => $request->noktp_tkt,
                    'dari_tkt' => $request->dari_tkt,
                    'ke_tkt' => $request->ke_tkt,
                    'tgl_brkt_tkt' => $request->tgl_brkt_tkt,
                    'tgl_plg_tkt' => $request->tgl_plg_tkt,
                    'jam_brkt_tkt' => $request->jam_brkt_tkt,
                    'jam_plg_tkt' => $request->jam_plg_tkt,
                    'jenis_tkt' => $request->jenis_tkt,
                    'type_tkt' => $request->type_tkt,
                    'ket_tkt' => $request->ket_tkt,
                ];
            }

            foreach ($ticketFields['noktp_tkt'] as $key => $value) {
                if (!empty($value)) {
                    // Prepare ticket data
                    $ticketData = [
                        'no_sppd' => $oldNoSppd,
                        'user_id' => Auth::id(),
                        'unit' => $request->divisi,
                        'dari_tkt' => $ticketFields['dari_tkt'][$key] ?? null,
                        'ke_tkt' => $ticketFields['ke_tkt'][$key] ?? null,
                        'tgl_brkt_tkt' => $ticketFields['tgl_brkt_tkt'][$key] ?? null,
                        'jam_brkt_tkt' => $ticketFields['jam_brkt_tkt'][$key] ?? null,
                        'jenis_tkt' => $ticketFields['jenis_tkt'][$key] ?? null,
                        'type_tkt' => $ticketFields['type_tkt'][$key] ?? null,
                        'tgl_plg_tkt' => $ticketFields['tgl_plg_tkt'][$key] ?? null,
                        'jam_plg_tkt' => $ticketFields['jam_plg_tkt'][$key] ?? null,
                        'ket_tkt' => $ticketFields['ket_tkt'][$key] ?? null,
                        'approval_status' => $statusValue,
                        'jns_dinas_tkt' => 'Dinas',
                        'jk_tkt' => $employee_data->gender ?? null,
                        'np_tkt' => $employee_data->fullname ?? null,
                        'tlp_tkt' => $employee_data->personal_mobile_number ?? null,
                        "contribution_level_code" => $request->bb_perusahaan,
                        "manager_l1_id" => $managerL1,
                        'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                    ];

                    // dd($ticketData);

                    // Fetch employee data to get jk_tkt
                    $employee_data = Employee::where('ktp', $value)->first();

                    if (!$employee_data) {
                        return redirect()->back()->with('error', "NIK $value not found");
                    }

                    // Ensure jk_tkt is included in the data
                    $ticketData['jk_tkt'] = $employee_data->gender ?? null;
                    $ticketData['np_tkt'] = $employee_data->fullname ?? null;
                    $ticketData['tlp_tkt'] = $employee_data->personal_mobile_number ?? null;

                    if (isset($existingTickets[$value])) {
                        // Update existing ticket
                        $existingTicket = $existingTickets[$value];
                        $existingTicket->update($ticketData);
                    } else {
                        if (!$newNoTkt) {
                            $newNoTkt = $this->generateNoSppdTkt();
                        }
                        Tiket::create(array_merge($ticketData, [
                            'id' => (string) Str::uuid(),
                            'no_tkt' => $newNoTkt, // Assign the generated no_tkt
                            'noktp_tkt' => $value,
                            'approval_status' => $statusValue,
                            'manager_l1_id' => $managerL1,
                            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                        ]));
                    }

                    // Track the processed ticket IDs
                    $processedTicketIds[] = $value;
                }
            }
            // Remove tickets that are no longer in the request
            Tiket::where('no_sppd', $oldNoSppd)
                ->whereNotIn('noktp_tkt', $processedTicketIds)
                ->delete();
        } else {
            // Remove all tickets if not selected
            Tiket::where('no_sppd', $oldNoSppd)->delete();
        }

        // Handle "CA Transaction" update
        $oldNoCa = $request->old_no_ca; // Ensure you have the old `no_ca`

        if ($request->ca === 'Ya') {
            $businessTripStatus = $request->input('status');
            $ca = CATransaction::where('no_sppd', $oldNoSppd)->where('type_ca', 'dns')->first();
            if (!$ca) {
                // Create a new CA transaction
                $ca = new CATransaction();

                // Generate new 'no_ca' code
                $currentYear = date('Y');
                $currentYearShort = date('y');
                $prefix = 'CA';
                $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                    ->orderBy('no_ca', 'desc')
                    ->first();

                $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                $newNoCa = "$prefix$currentYearShort$newNumber";

                $ca->id = (string) Str::uuid();
                $ca->no_ca = $newNoCa;
            } else {
                // Update the existing CA transaction
                $ca->no_ca = $ca->no_ca; // Keep the existing no_ca
            }

            if ($statusValue === 'Draft') {
                // Set CA status to Draft
                $caStatus = $ca->approval_status = 'Draft';
            } elseif ($statusValue === 'Pending L1') {
                // Set CA status to Pending
                $caStatus = $ca->approval_status = 'Pending';
            }

            // Assign or update values to $ca model
            $ca->type_ca = 'dns';
            $ca->no_sppd = $oldNoSppd;
            $ca->user_id = $userId;
            $ca->unit = $request->divisi;
            $ca->contribution_level_code = $request->bb_perusahaan;
            $ca->destination = $request->tujuan;
            $ca->others_location = $request->others_location;
            $ca->ca_needs = $request->keperluan;
            $ca->start_date = $request->mulai;
            $ca->end_date = $request->kembali;
            $ca->date_required = $request->date_required_2;
            // $ca->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
            $ca->declare_estimate = $request->ca_decla;
            $ca->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
            $ca->total_ca = (int) str_replace('.', '', $request->totalca);
            $ca->total_real = '0';
            $ca->total_cost = (int) str_replace('.', '', $request->totalca);
            $ca->approval_status = $caStatus;
            $ca->approval_sett = $request->approval_sett ?? "";
            $ca->approval_extend = $request->approval_extend ?? "";
            $ca->created_by = $userId;

            // Initialize arrays for details
            $detail_perdiem = [];
            $detail_meals = [];
            $detail_transport = [];
            $detail_penginapan = [];
            $detail_lainnya = [];

            if ($request->has('start_bt_meals')) {
                foreach ($request->start_bt_meals as $key => $startDate) {
                    $endDate = $request->end_bt_meals[$key] ?? '';
                    $totalDays = $request->total_days_bt_meals[$key] ?? '';
                    $companyCode = $request->company_bt_meals[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                    $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                    if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                        $detail_meals[] = [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'total_days' => $totalDays,
                            'company_code' => $companyCode,
                            'nominal' => $nominal,
                            'keterangan' => $keterangan,
                        ];
                    }
                }
            }

            // Populate detail_perdiem
            if ($request->has('start_bt_perdiem')) {
                foreach ($request->start_bt_perdiem as $key => $startDate) {
                    $endDate = $request->end_bt_perdiem[$key] ?? '';
                    $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                    $location = $request->location_bt_perdiem[$key] ?? '';
                    $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                    $companyCode = $request->company_bt_perdiem[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                    if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                        $detail_perdiem[] = [
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
            }

            // Populate detail_transport
            if ($request->has('tanggal_bt_transport')) {
                foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                    $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                    $companyCode = $request->company_bt_transport[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');


                    if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                        $detail_transport[] = [
                            'tanggal' => $tanggal,
                            'keterangan' => $keterangan,
                            'company_code' => $companyCode,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }

            // Populate detail_penginapan
            if ($request->has('start_bt_penginapan')) {
                foreach ($request->start_bt_penginapan as $key => $startDate) {
                    $endDate = $request->end_bt_penginapan[$key] ?? '';
                    $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                    $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                    $companyCode = $request->company_bt_penginapan[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');


                    if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
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
            }

            // Populate detail_lainnya
            if ($request->has('tanggal_bt_lainnya')) {
                foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                    $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                    $type = $request->type_bt_lainnya[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');

                    if (!empty($tanggal) && !empty($nominal)) {
                        $detail_lainnya[] = [
                            'tanggal' => $tanggal,
                            'keterangan' => $keterangan,
                            'type' => $type,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }
            if ($request->has('start_bt_meals')) {
                foreach ($request->start_bt_meals as $key => $startDate) {
                    $endDate = $request->end_bt_meals[$key] ?? '';
                    $totalDays = $request->total_days_bt_meals[$key] ?? '';
                    $companyCode = $request->company_bt_meals[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                    $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                    if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
                        $detail_meals[] = [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'total_days' => $totalDays,
                            'company_code' => $companyCode,
                            'nominal' => $nominal,
                            'keterangan' => $keterangan,
                        ];
                    }
                }
            }

            // Save the details
            $detail_ca = [
                'detail_perdiem' => $detail_perdiem,
                'detail_meals' => $detail_meals,
                'detail_transport' => $detail_transport,
                'detail_penginapan' => $detail_penginapan,
                'detail_lainnya' => $detail_lainnya,
            ];

            $detail_ca_ntf = $detail_ca;
            $ca->detail_ca = json_encode($detail_ca);
            $ca->declare_ca = json_encode($detail_ca);
            $ca->save();

            if ($statusValue !== 'Draft') {
                $model = $ca;

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
                    ->where('employees.designation_code', '=', $employee->designation_code)
                    ->where('dsg2.director_flag', '=', 'F')
                    ->get();

                $director_id = "";

                if ($cek_director_id->isNotEmpty()) {
                    $director_id = $cek_director_id->first()->employee_id;
                }

                $total_ca = str_replace('.', '', $request->totalca);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
                    ->whereRaw(
                        '? BETWEEN CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
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

                    if ($employee_id != null) {
                        $model_approval = new ca_approval;
                        $model_approval->ca_id = $ca->id;  // Use $ca->id instead of $request->id_ca
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = 'Pending';

                        // Simpan data ke database
                        $model_approval->save();
                    }
                    $model_approval->save();
                }
            }
        } else {
            // If CA is not selected, remove existing CA transaction for this no_sppd
            CATransaction::where('no_sppd', $oldNoSppd)->where('type_ca', 'dns')->delete();
        }

        if ($request->ent === 'Ya') {
            $businessTripStatus = $request->input('status');
            $ent = CATransaction::where('no_sppd', $oldNoSppd)->where('type_ca', 'entr')->first();
            if (!$ent) {
                $ent = new CATransaction();
                $businessTripStatus = $request->input('status');

                // Generate new 'no_ca' code
                $currentYear = date('Y');
                $currentYearShort = date('y');
                $prefix = 'CA';
                $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                    ->orderBy('no_ca', 'desc')
                    ->first();

                $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                $newNoCa = "$prefix$currentYearShort$newNumber";

                $ent_id = (string) Str::uuid();
                $ent->no_ca = $newNoCa;
            } else {
                // Update the existing CA transaction
                $ent->no_ca = $ent->no_ca; // Keep the existing no_ca
            }

            if ($statusValue === 'Draft') {
                // Set CA status to Draft
                $entStatus = $ent->approval_status = 'Draft';
            } elseif ($statusValue === 'Pending L1') {
                // Set CA status to Pending
                $entStatus = $ent->approval_status = 'Pending';
            }
            // Assign values to $ent model
            $ent->type_ca = 'entr';
            $ent->no_sppd = $oldNoSppd;
            $ent->user_id = $userId;
            $ent->unit = $request->divisi;
            $ent->contribution_level_code = $request->bb_perusahaan;
            $ent->destination = $request->tujuan;
            $ent->others_location = $request->others_location;
            $ent->ca_needs = $request->keperluan;
            $ent->start_date = $request->mulai;
            $ent->end_date = $request->kembali;
            $ent->date_required = $request->date_required_1;
            // $ent->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
            $ent->declare_estimate = $request->ca_decla;
            // dd($request->ca_decla);
            $ent->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
            $ent->total_ca = (int) str_replace('.', '', $request->total_ent_detail);
            $ent->total_real = '0';
            $ent->total_cost = (int) str_replace('.', '', $request->total_ent_detail);
            $ent->approval_status = $entStatus;
            $ent->approval_sett = $request->approval_sett ? $request->approval_sett : '';
            $ent->approval_extend = $request->approval_extend ? $request->approval_extend : '';
            $ent->created_by = $userId;

            // Initialize arrays
            $detail_e = [];
            $relation_e = [];

            if ($request->has('enter_type_e_detail')) {
                foreach ($request->enter_type_e_detail as $key => $type) {
                    $fee_detail = $request->enter_fee_e_detail[$key];
                    $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    if (!empty($type) && !empty($nominal)) {
                        $detail_e[] = [
                            'type' => $type,
                            'fee_detail' => $fee_detail,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }

            // Mengumpulkan detail relation
            if ($request->has('rname_e_relation')) {
                foreach ($request->rname_e_relation as $key => $name) {
                    $position = $request->rposition_e_relation[$key];
                    $company = $request->rcompany_e_relation[$key];
                    $purpose = $request->rpurpose_e_relation[$key];

                    // Memastikan semua data yang diperlukan untuk relation terisi
                    if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                        $relation_e[] = [
                            'name' => $name,
                            'position' => $position,
                            'company' => $company,
                            'purpose' => $purpose,
                            'relation_type' => array_filter([
                                'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                            ], fn($checked) => $checked),
                        ];
                    }
                }
            }

            // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
            $detail_ca = [
                'detail_e' => $detail_e,
                'relation_e' => $relation_e,
            ];

            $detail_ent = $detail_ca;
            // dd($detail_ca);
            $ent->detail_ca = json_encode($detail_ca);
            $ent->declare_ca = json_encode($detail_ca);
            $ent->save();

            if ($statusValue !== 'Draft') {
                $model = $ent;

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
                    ->where('employees.designation_code', '=', $employee->designation_code)
                    ->where('dsg2.director_flag', '=', 'F')
                    ->get();

                $director_id = "";

                if ($cek_director_id->isNotEmpty()) {
                    $director_id = $cek_director_id->first()->employee_id;
                }
                //cek matrix approval

                $total_ca = str_replace('.', '', $request->total_ent_detail);
                // dd($total_ca);
                // dd($employee->group_company);
                // dd($request->bb_perusahaan);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
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
                    if ($employee_id != null) {
                        $model_approval = new ca_approval;
                        $model_approval->ca_id = $ent->id;
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = 'Pending';

                        // Simpan data ke database
                        $model_approval->save();
                    }

                    // Simpan data ke database
                    $model_approval->save();
                }
                $ent->save();
            }
        } else {
            // If CA is not selected, remove existing CA transaction for this no_sppd
            CATransaction::where('no_sppd', $oldNoSppd)->where('type_ca', 'entr')->delete();
        }

        if ($statusValue !== 'Draft') {
            // Get manager email
            $managerEmail = Employee::where('employee_id', $managerL1)->pluck('email')->first();
            // $managerEmail = "erzie.aldrian02@gmail.com";
            $managerName = Employee::where('employee_id', $managerL1)->pluck('fullname')->first();
            $group_company = Employee::where('id', $employee->id)->pluck('group_company')->first();

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $employeeName = Employee::where('id', $n->user_id)->pluck('fullname')->first();
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Business Trip and waiting for your approval with the following details :";
            $isEnt = $request->ent === 'Ya';
            $isCa = $request->ca === 'Ya';

            if ($managerEmail) {
                $detail_ca_ntf = isset($detail_ca_ntf) ? $detail_ca_ntf : [];
                $detail_ent = isset($detail_ent) ? $detail_ent : [];
                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca_ntf['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca_ntf['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca_ntf['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => count($detail_ca_ntf['detail_meals'] ?? []),
                    'total_amount_meals' => array_sum(array_column($detail_ca_ntf['detail_meals'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_ent' => array_sum(array_column($detail_ent['detail_e'] ?? [], 'nominal')),
                ];
                // Fetch ticket and hotel details with proper conditions
                $ticketDetails = Tiket::where('no_sppd', $n->no_sppd)
                    ->where(function ($query) {
                        $query->where('tkt_only', '!=', 'Y')
                            ->orWhereNull('tkt_only'); // This handles the case where tkt_only is null
                    })
                    ->get();

                $hotelDetails = Hotel::where('no_sppd', $n->no_sppd)
                    ->where(function ($query) {
                        $query->where('hotel_only', '!=', 'Y')
                            ->orWhereNull('hotel_only'); // This handles the case where hotel_only is null
                    })
                    ->get();

                $messDetails = Mess::where('no_sppd', $n->no_sppd)
                    ->where(function ($query) {
                        $query->where('mess_only', '!=', 'Y')
                            ->orWhereNull('mess_only'); // This handles the case where hotel_only is null
                    })
                    ->get();

                $taksiDetails = Taksi::where('no_sppd', $n->no_sppd)->first();
                $approvalLink = route('approve.business.trip', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Pending L2'
                ]);

                $revisionLink = route('revision.link', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Request Revision',
                ]);

                $rejectionLink = route('reject.link', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Rejected'
                ]);

                // Send an email with the detailed business trip information
                try {
                    Mail::to($managerEmail)->send(new BusinessTripNotification(
                        $n,
                        $hotelDetails,  // Pass hotel details
                        $ticketDetails,
                        $taksiDetails,
                        $caDetails,
                        $managerName,
                        $approvalLink,
                        $revisionLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                        $isEnt,
                        $isCa,
                        $entDetails,
                        $group_company,
                        $messDetails,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Create Business Trip tidak terkirim: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
                    Log::info('messDetails: ' . json_encode($messDetails));
                }
            }
        }

        return redirect('/businessTrip')->with('success', 'Business trip updated successfully');
    }

    public function deklarasi($id)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $employee_data = Employee::where('id', $userId)->first();
        if ($employee_data->group_company == 'Plantations' || $employee_data->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }

        $job_level = Employee::where('id', $userId)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);
        $group_company = Employee::where('id', $employee_data->id)->pluck('group_company')->first();

        $ca = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $date = CATransaction::where('no_sppd', $n->no_sppd)->first();
        $dns = $ca->where('type_ca', 'dns')->first();
        $entr = $ca->where('type_ca', 'entr')->first();

        $entrTab = $entr ? true : false;
        $dnsTab = $dns ? true : false;

        $entrData = null;
        $dnsData = null;

        foreach ($ca as $item) {
            if ($item->type_ca == 'entr' && !$entrData) {
                $entrData = $item; // Ambil data entr hanya jika belum ada
            } elseif ($item->type_ca == 'dns' && !$dnsData) {
                $dnsData = $item; // Ambil data dns hanya jika belum ada
            }

            // Jika sudah mendapatkan kedua tipe, keluar dari loop
            if ($entrData && $dnsData) {
                break;
            }
        }

        // Initialize caDetail with an empty array if it's null
        $caDetail = [];
        $declareCa = [];
        foreach ($ca as $cas) {
            $currentDetail = json_decode($cas->detail_ca, true);
            $currentDeclare = json_decode($cas->declare_ca, true);
            if (is_array($currentDetail)) {
                $caDetail = array_merge($caDetail, $currentDetail);
                $declareCa = array_merge($declareCa, $currentDeclare);
            }
        }

        // Safely access nominalPerdiem with default '0' if caDetail is empty
        $nominalPerdiem = isset($caDetail['detail_perdiem'][0]['nominal']) ? $caDetail['detail_perdiem'][0]['nominal'] : '0';
        $nominalPerdiemDeclare = isset($declareCa['detail_perdiem'][0]['nominal']) ? $declareCa['detail_perdiem'][0]['nominal'] : '0';

        $hasCaData = $ca !== null;
        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();

        // Retrieve all hotels for the specific BusinessTrip
        $hotels = Hotel::where('no_sppd', $n->no_sppd)->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)
            ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')->first();

        $parentLink = 'Business Trip';
        $link = 'Declaration Business Trip';

        // Prepare hotel data for the view
        $hotelData = [];
        foreach ($hotels as $index => $hotel) {
            $hotelData[] = [
                'nama_htl' => $hotel->nama_htl,
                'lokasi_htl' => $hotel->lokasi_htl,
                'jmlkmr_htl' => $hotel->jmlkmr_htl,
                'bed_htl' => $hotel->bed_htl,
                'tgl_masuk_htl' => $hotel->tgl_masuk_htl,
                'tgl_keluar_htl' => $hotel->tgl_keluar_htl,
                'total_hari' => $hotel->total_hari,
                'more_htl' => ($index < count($hotels) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve all tickets for the specific BusinessTrip
        $tickets = Tiket::where('no_sppd', $n->no_sppd)->get();

        // Prepare ticket data for the view
        $ticketData = [];
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'noktp_tkt' => $ticket->noktp_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < count($tickets) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve locations and companies data for the dropdowns
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();

        $revisiInfo = null;
        if ($n->status === 'Request Revision') {
            $revisiInfo = BTApproval::where('bt_id', $n->id)
                ->where('approval_status', 'Request Revision')
                ->orderBy('created_at', 'desc') // Mengurutkan dari terbaru
                ->pluck('reject_info')
                ->first();
        }
        if ($n->status === 'Declaration Revision') {
            $revisiInfo = BTApproval::where('bt_id', $n->id)
                ->where('approval_status', 'Declaration Revision')
                ->orderBy('created_at', 'desc') // Mengurutkan dari terbaru
                ->pluck('reject_info')
                ->first();
        }

        return view('hcis.reimbursements.businessTrip.deklarasi', [
            'n' => $n,
            'group_company' => $group_company,
            'allowance' => $allowance,
            'hotelData' => $hotelData,
            'taksiData' => $taksi, // Pass the taxi data
            'ticketData' => $ticketData,
            'employee_data' => $employee_data,
            'companies' => $companies,
            'locations' => $locations,
            'caDetail' => $caDetail,
            'declareCa' => $declareCa,
            'entrData' => $entrData,
            'dnsData' => $dnsData,
            'entrTab' => $entrTab,
            'dnsTab' => $dnsTab,
            'date' => $date,
            'ca' => $ca,
            'nominalPerdiem' => $nominalPerdiem,
            'nominalPerdiemDeclare' => $nominalPerdiemDeclare,
            'hasCaData' => $hasCaData,
            'job_level_number' => $job_level_number,
            'perdiem' => $perdiem,
            'parentLink' => $parentLink,
            'link' => $link,
            'revisiInfo' => $revisiInfo,
        ]);
    }
    public function deklarasiCreate(Request $request, $id)
    {
        // Fetch the business trip record to update
        $n = BusinessTrip::find($id);
        if ($request->has('action_draft')) {
            $statusValue = 'Declaration Draft';  // When "Save as Draft" is clicked
        } elseif ($request->has('action_submit')) {
            $statusValue = 'Declaration L1';  // When "Submit" is clicked
        }
        // dd($statusValue);

        // Store old SPPD number for later use
        $oldNoSppd = $n->no_sppd;
        $userId = Auth::id();
        $employee = Employee::where('id', $userId)->first();
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
        $deptHeadManager = findDepartmentHead($employee);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        // Handle "CA Transaction" update
        $caRecords = CATransaction::where('no_sppd', $oldNoSppd)->get();
        $dnsRecord = $caRecords->where('type_ca', 'dns')->first();
        $entrRecord = $caRecords->where('type_ca', 'entr')->first();
        // dd($caRecords->isEmpty());

        // Cek apakah ada $ent dan jalankan kode jika ada
        $entrTab = $entrRecord ? true : false;
        $dnsTab = $dnsRecord ? true : false;

        $employee_data = Employee::where('id', $userId)->first();

        if ($request->totalca_ca_deklarasi == 0 && $request->totalca == 0) {
            return redirect()->back()->with('error', 'CA Real cannot be zero.')->withInput();
        }

        if ($request->has('removed_prove_declare')) {
            $removedFiles = json_decode($request->removed_prove_declare, true);
            $existingFiles = $request->existing_prove_declare ? json_decode($request->existing_prove_declare, true) : [];

            // Hapus file yang ada di server
            foreach ($removedFiles as $fileToRemove) {
                // Cek jika file yang akan dihapus ada dalam array existingFiles
                if (in_array($fileToRemove, $existingFiles)) {
                    $filePath = public_path($fileToRemove); // Path file
                    if (file_exists($filePath)) {
                        unlink($filePath); // Menghapus file
                    }
                    // Menghapus file dari gambaran existingFiles
                    $existingFiles = array_filter($existingFiles, fn($file) => $file !== $fileToRemove);
                }
                // dd($existingFiles);
            }
        } else {
            $existingFiles = $request->existing_prove_declare ? json_decode($request->existing_prove_declare, true) : [];
            // dd($existingFiles);
        }

        // Proses file baru
        if ($request->hasFile('prove_declare')) {
            $request->validate([
                'prove_declare.*' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            ]);
            // dd($existingFiles);
            // $existingFiles = [];
            foreach ($request->file('prove_declare') as $file) {
                if (!$file->isValid()) {
                    dd("error");
                    // return back()->with('error', 'One of the uploaded files is invalid.');
                }

                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $upload_path = 'uploads/proofs/' . $employee_data->employee_id;
                $full_path = public_path($upload_path);

                if (!is_dir($full_path)) {
                    mkdir($full_path, 0777, true);
                }

                $file->move($full_path, $filename);
                $existingFiles[] = $upload_path . '/' . $filename;
            }
        }

        if ($caRecords->isEmpty()) {
            if ($entrTab == false && $request->totalca > 0) {
                // Create a new CA transaction if it doesn't exist
                $ent = new CATransaction();
                $entrTab = true;

                // Generate new 'no_ca' code
                $currentYear = date('Y');
                $currentYearShort = date('y');
                $prefix = 'CA';
                $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                    ->orderBy('no_ca', 'desc')
                    ->first();

                $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                $newNoCa = "$prefix$currentYearShort$newNumber";

                $entId = $ent->id = (string) Str::uuid();
                $ent->no_ca = $newNoCa;
                $ent->no_sppd = $oldNoSppd;
                $ent->unit = $request->divisi;
                $ent->contribution_level_code = $request->bb_perusahaan;
                $ent->user_id = $userId;
                $ent->destination = $request->tujuan;
                $ent->start_date = $request->mulai;
                $ent->end_date = $request->kembali;
                $ent->ca_needs = $request->keperluan;
                $ent->type_ca = 'entr';
                $ent->date_required = null;
                $ent->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
                $ent->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
                $ent->total_ca = '0';
                $ent->total_real = (int) str_replace('.', '', $request->totalca);
                $ent->total_cost = -1 * (int) str_replace('.', '', $ent->total_real);

                // dd($ent->total_real, $ent->total_cost);

                if ($statusValue === 'Declaration Draft') {
                    // Set CA status to Draft
                    // dd($statusValue);
                    $entStatus = $ent->approval_sett = 'Draft';
                    // dd($entStatus);

                } elseif ($statusValue === 'Declaration L1') {
                    // Set CA status to Pending
                    $entStatus = $ent->approval_sett = 'Pending';
                }

                $ent->approval_status = 'Approved';
                $ent->approval_sett = $request->approval_sett;
                $ent->approval_extend = $request->approval_extend;
                $ent->created_by = $userId;


                if ($statusValue === 'Declaration L1') {
                    $ent->approval_sett = 'Pending';
                } elseif ($statusValue === 'Declaration Draft') {
                    $ent->approval_sett = 'Draft';
                } else {
                    $ent->approval_sett = $statusValue;
                }

                $ent->declaration_at = Carbon::now();
                $total_real = (int) str_replace('.', '', $request->totalca);
                // $total_ca = $ent->total_ca;

                if ($total_real === 0) {
                    // Redirect back with a SweetAlert message
                    return redirect()->back()->with('error', 'CA Real cannot be zero.')->withInput();
                }

                // Assign total_real and calculate total_cost
                // $ent->total_real = $total_real;
                // $ent->total_cost = $total_ca - $total_real;

                // Initialize arrays for details
                $detail_e = [];
                $relation_e = [];

                if ($request->has('enter_type_e_detail')) {
                    foreach ($request->enter_type_e_detail as $key => $type) {
                        $fee_detail = $request->enter_fee_e_detail[$key];
                        $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                        if (!empty($type) && !empty($nominal)) {
                            $detail_e[] = [
                                'type' => $type,
                                'fee_detail' => $fee_detail,
                                'nominal' => $nominal,
                            ];
                        }
                    }
                }

                // Mengumpulkan detail relation
                if ($request->has('rname_e_relation')) {
                    foreach ($request->rname_e_relation as $key => $name) {
                        $position = $request->rposition_e_relation[$key];
                        $company = $request->rcompany_e_relation[$key];
                        $purpose = $request->rpurpose_e_relation[$key];

                        // Memastikan semua data yang diperlukan untuk relation terisi
                        if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                            $relation_e[] = [
                                'name' => $name,
                                'position' => $position,
                                'company' => $company,
                                'purpose' => $purpose,
                                'relation_type' => array_filter([
                                    'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                    'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                    'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                    'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                    'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                                ], fn($checked) => $checked),
                            ];
                        }
                    }
                }

                // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
                $declare_ca = [
                    'detail_e' => $detail_e,
                    'relation_e' => $relation_e,
                ];
                $declare_ent_ntf = $declare_ca;
                $ent->prove_declare = json_encode(array_values($existingFiles));

                $ent->detail_ca = '[{"detail_e":[],"relation_e":[]}]';
                $ent->declare_ca = json_encode($declare_ca);
                $model = $ent;

                $model->sett_id = $managerL1;
                // dd($ca);
            }

            if ($dnsTab == false && $request->totalca_ca_deklarasi > 0) {
                // Create a new CA transaction if it doesn't exist
                $ca = new CATransaction();
                $dnsTab = true;

                // Generate new 'no_ca' code
                $currentYear = date('Y');
                $currentYearShort = date('y');
                $prefix = 'CA';
                $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                    ->orderBy('no_ca', 'desc')
                    ->first();

                $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                $newNoCa = "$prefix$currentYearShort$newNumber";

                $caId = $ca->id = (string) Str::uuid();
                $ca->no_ca = $newNoCa;
                $ca->no_sppd = $oldNoSppd;
                $ca->unit = $request->divisi;
                $ca->contribution_level_code = $request->bb_perusahaan;
                $ca->user_id = $userId;
                $ca->destination = $request->tujuan;
                $ca->start_date = $request->mulai;
                $ca->end_date = $request->kembali;
                $ca->ca_needs = $request->keperluan;
                $ca->type_ca = 'dns';
                $ca->date_required = null;
                $ca->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
                $ca->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
                $ca->total_ca = '0';
                $ca->total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);

                // dd($ca->total_real, $ca->total_cost);

                if ($statusValue === 'Declaration Draft') {
                    // Set CA status to Draft
                    // dd($statusValue);
                    $caStatus = $ca->approval_sett = 'Draft';
                    // dd($caStatus);

                } elseif ($statusValue === 'Declaration L1') {
                    // Set CA status to Pending
                    $caStatus = $ca->approval_sett = 'Pending';
                }

                $ca->approval_status = 'Approved';
                $ca->approval_sett = $request->approval_sett;
                $ca->approval_extend = $request->approval_extend;
                $ca->created_by = $userId;


                if ($statusValue === 'Declaration L1') {
                    $ca->approval_sett = 'Pending';
                } elseif ($statusValue === 'Declaration Draft') {
                    $ca->approval_sett = 'Draft';
                } else {
                    $ca->approval_sett = $statusValue;
                }

                $ca->declaration_at = Carbon::now();
                $total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                // $total_ca = $ca->total_ca;

                if ($total_real === 0) {
                    // Redirect back with a SweetAlert message
                    return redirect()->back()->with('error', 'CA Real cannot be zero.')->withInput();
                }

                // Assign total_real and calculate total_cost
                // $ca->total_real = $total_real;
                // $ca->total_cost = $total_ca - $total_real;

                $detail_perdiem = [];
                $detail_meals = [];
                $detail_transport = [];
                $detail_penginapan = [];
                $detail_lainnya = [];

                if ($request->has('start_bt_meals')) {
                    foreach ($request->start_bt_meals as $key => $startDate) {
                        $endDate = $request->end_bt_meals[$key] ?? '';
                        $totalDays = $request->total_days_bt_meals[$key] ?? '';
                        $companyCode = $request->company_bt_meals[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                        $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                        if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                            $detail_meals[] = [
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'total_days' => $totalDays,
                                'company_code' => $companyCode,
                                'nominal' => $nominal,
                                'keterangan' => $keterangan,
                            ];
                        }
                    }
                }

                // Populate detail_perdiem
                if ($request->has('start_bt_perdiem')) {
                    foreach ($request->start_bt_perdiem as $key => $startDate) {
                        $endDate = $request->end_bt_perdiem[$key] ?? '';
                        $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                        $location = $request->location_bt_perdiem[$key] ?? '';
                        $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                        $companyCode = $request->company_bt_perdiem[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                        if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                            $detail_perdiem[] = [
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
                }

                // Populate detail_transport
                if ($request->has('tanggal_bt_transport')) {
                    foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                        $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                        $companyCode = $request->company_bt_transport[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');


                        if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                            $detail_transport[] = [
                                'tanggal' => $tanggal,
                                'keterangan' => $keterangan,
                                'company_code' => $companyCode,
                                'nominal' => $nominal,
                            ];
                        }
                    }
                }

                // Populate detail_penginapan
                if ($request->has('start_bt_penginapan')) {
                    foreach ($request->start_bt_penginapan as $key => $startDate) {
                        $endDate = $request->end_bt_penginapan[$key] ?? '';
                        $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                        $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                        $companyCode = $request->company_bt_penginapan[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');


                        if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
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
                }

                // Populate detail_lainnya
                if ($request->has('tanggal_bt_lainnya')) {
                    foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                        $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                        $type = $request->type_bt_lainnya[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');

                        if (!empty($tanggal) && !empty($nominal)) {
                            $detail_lainnya[] = [
                                'tanggal' => $tanggal,
                                'keterangan' => $keterangan,
                                'type' => $type,
                                'nominal' => $nominal,
                            ];
                        }
                    }
                }
                if ($request->has('start_bt_meals')) {
                    foreach ($request->start_bt_meals as $key => $startDate) {
                        $endDate = $request->end_bt_meals[$key] ?? '';
                        $totalDays = $request->total_days_bt_meals[$key] ?? '';
                        $companyCode = $request->company_bt_meals[$key] ?? '';
                        $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                        $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                        if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
                            $detail_meals[] = [
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'total_days' => $totalDays,
                                'company_code' => $companyCode,
                                'nominal' => $nominal,
                                'keterangan' => $keterangan,
                            ];
                        }
                    }
                }

                // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
                $declare_ca = [
                    'detail_perdiem' => $detail_perdiem,
                    'detail_meals' => $detail_meals,
                    'detail_transport' => $detail_transport,
                    'detail_penginapan' => $detail_penginapan,
                    'detail_lainnya' => $detail_lainnya,
                ];
                $declare_ca_ntf = $declare_ca;
                $ca->prove_declare = json_encode(array_values($existingFiles));

                $ca->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                $ca->declare_ca = json_encode($declare_ca);
                $model = $ca;

                $model->sett_id = $managerL1;

            }
            if ($ent->isDirty()) { 
                $ent->save();
            }
            $ca->save();
        }
        if ($caRecords) {
            foreach ($caRecords as $ca) {
                // Assign or update values to $ca model
                if ($ca->type_ca == "dns") {
                    $ca->user_id = $userId;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->user_id = $userId;
                    $caId = $ca->id;

                    // Update approval_status based on the status value from the request
                    if ($statusValue === 'Declaration L1') {
                        $ca->approval_sett = 'Pending';
                        $caStatus = $ca->approval_sett = 'Pending';
                    } elseif ($statusValue === 'Declaration Draft') {
                        $ca->approval_sett = 'Draft';
                        $caStatus = $ca->approval_sett = 'Draft';
                    } else {
                        $ca->approval_sett = $statusValue;
                    }

                    $ca->declaration_at = Carbon::now();

                    $total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                    $total_ca = $ca->total_ca;
                    if ($ca->detail_ca === null) {
                        $ca->total_ca = '0';
                        $ca->total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                        $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);
                    } else {
                        $ca->total_real = $total_real;
                        $ca->total_cost = $total_ca - $total_real;
                    }

                    // Initialize arrays for details
                    $detail_perdiem = [];
                    $detail_meals = [];
                    $detail_transport = [];
                    $detail_penginapan = [];
                    $detail_lainnya = [];

                    if ($request->has('start_bt_meals')) {
                        foreach ($request->start_bt_meals as $key => $startDate) {
                            $endDate = $request->end_bt_meals[$key] ?? '';
                            $totalDays = $request->total_days_bt_meals[$key] ?? '';
                            $companyCode = $request->company_bt_meals[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                            $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                                $detail_meals[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'keterangan' => $keterangan,
                                ];
                            }
                        }
                    }
                    // dd($request->has('start_bt_meals'));

                    // Populate detail_perdiem
                    if ($request->has('start_bt_perdiem')) {
                        foreach ($request->start_bt_perdiem as $key => $startDate) {
                            $endDate = $request->end_bt_perdiem[$key] ?? '';
                            $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                            $location = $request->location_bt_perdiem[$key] ?? '';
                            $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                            $companyCode = $request->company_bt_perdiem[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                            if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                                $detail_perdiem[] = [
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
                    }
                    // dd($detail_perdiem);

                    // Populate detail_transport
                    if ($request->has('tanggal_bt_transport')) {
                        foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                            $companyCode = $request->company_bt_transport[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');

                            if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                                $detail_transport[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }
                    // dd($detail_transport);

                    // Populate detail_penginapan
                    if ($request->has('start_bt_penginapan')) {
                        foreach ($request->start_bt_penginapan as $key => $startDate) {
                            $endDate = $request->end_bt_penginapan[$key] ?? '';
                            $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                            $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                            $companyCode = $request->company_bt_penginapan[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');
                            $totalPenginapan = str_replace('.', '', $request->total_bt_penginapan[$key] ?? '0');

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
                                $detail_penginapan[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'hotel_name' => $hotelName,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'totalPenginapan' => $totalPenginapan,
                                ];
                            }
                        }
                    }

                    // Populate detail_lainnya
                    if ($request->has('tanggal_bt_lainnya')) {
                        foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                            $type = $request->type_bt_lainnya[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');
                            $totalLainnya = str_replace('.', '', $request->total_bt_lainnya[$key] ?? '0');

                            if (!empty($tanggal) && !empty($nominal)) {
                                $detail_lainnya[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'type' => $type,
                                    'nominal' => $nominal,
                                    'totalLainnya' => $totalLainnya,
                                ];
                            }
                        }
                    }

                    // Save the details
                    $declare_ca = [
                        'detail_perdiem' => $detail_perdiem,
                        'detail_meals' => $detail_meals,
                        'detail_transport' => $detail_transport,
                        'detail_penginapan' => $detail_penginapan,
                        'detail_lainnya' => $detail_lainnya,
                    ];

                    $declare_ca_ntf = $declare_ca;
                    // Simpan semua file yang tersisa ke database
                    $ca->prove_declare = json_encode(array_values($existingFiles));

                    $ca->declare_ca = json_encode($declare_ca);
                    $model = $ca;

                    $model->sett_id = $managerL1;
                } elseif ($ca->type_ca == "entr") {
                    $ca->user_id = $userId;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->user_id = $userId;
                    $caId = $ca->id;

                    // Update approval_status based on the status value from the request
                    if ($statusValue === 'Declaration L1') {
                        $ca->approval_sett = 'Pending';
                        $caStatus = $ca->approval_sett = 'Pending';
                    } elseif ($statusValue === 'Declaration Draft') {
                        $ca->approval_sett = 'Draft';
                        $caStatus = $ca->approval_sett = 'Draft';
                    } else {
                        $ca->approval_sett = $statusValue;
                    }

                    $ca->declaration_at = Carbon::now();

                    $total_real = (int) str_replace('.', '', $request->totalca);
                    // dd($total_real);
                    $total_ca = $ca->total_ca;
                    if ($ca->detail_ca === null) {
                        $ca->total_ca = '0';
                        $ca->total_real = (int) str_replace('.', '', $request->totalca);
                        $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);
                    } else {
                        $ca->total_real = $total_real;
                        $ca->total_cost = $total_ca - $total_real;
                    }

                    // Ini AWAL
                    $detail_e = [];
                    $relation_e = [];

                    if ($request->has('enter_type_e_detail')) {
                        foreach ($request->enter_type_e_detail as $key => $type) {
                            $fee_detail = $request->enter_fee_e_detail[$key];
                            $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                            if (!empty($type) && !empty($nominal)) {
                                $detail_e[] = [
                                    'type' => $type,
                                    'fee_detail' => $fee_detail,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }

                    // Mengumpulkan detail relation
                    if ($request->has('rname_e_relation')) {
                        foreach ($request->rname_e_relation as $key => $name) {
                            $position = $request->rposition_e_relation[$key];
                            $company = $request->rcompany_e_relation[$key];
                            $purpose = $request->rpurpose_e_relation[$key];

                            // Memastikan semua data yang diperlukan untuk relation terisi
                            if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                                $relation_e[] = [
                                    'name' => $name,
                                    'position' => $position,
                                    'company' => $company,
                                    'purpose' => $purpose,
                                    'relation_type' => array_filter([
                                        'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                        'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                        'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                        'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                        'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                                    ], fn($checked) => $checked),
                                ];
                            }
                            // dd($relation_e);
                        }
                    }

                    // Save the details
                    $declare_ca = [
                        'detail_e' => $detail_e,
                        'relation_e' => $relation_e,
                    ];
                    // Ini Akihit

                    // Simpan semua file yang tersisa ke database
                    $declare_ent_ntf = $declare_ca;
                    $ca->prove_declare = json_encode(array_values($existingFiles));

                    $ca->declare_ca = json_encode($declare_ca);
                    $model = $ca;

                    $model->sett_id = $managerL1;
                }

                if ($entrTab == false && $request->totalca > 0) {
                    // Create a new CA transaction if it doesn't exist
                    $ca = new CATransaction();
                    $entrTab = true;

                    // Generate new 'no_ca' code
                    $currentYear = date('Y');
                    $currentYearShort = date('y');
                    $prefix = 'CA';
                    $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                        ->orderBy('no_ca', 'desc')
                        ->first();

                    $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                    $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                    $newNoCa = "$prefix$currentYearShort$newNumber";

                    $caId = $ca->id = (string) Str::uuid();
                    $ca->no_ca = $newNoCa;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->unit = $request->divisi;
                    $ca->contribution_level_code = $request->bb_perusahaan;
                    $ca->user_id = $userId;
                    $ca->destination = $request->tujuan;
                    $ca->start_date = $request->mulai;
                    $ca->end_date = $request->kembali;
                    $ca->ca_needs = $request->keperluan;
                    $ca->type_ca = 'entr';
                    $ca->date_required = $dnsRecord->date_required;
                    $ca->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
                    $ca->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
                    $ca->total_ca = '0';
                    $ca->total_real = (int) str_replace('.', '', $request->totalca);
                    $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);

                    // dd($ca->total_real, $ca->total_cost);

                    if ($statusValue === 'Declaration Draft') {
                        // Set CA status to Draft
                        // dd($statusValue);
                        $caStatus = $ca->approval_sett = 'Draft';
                        // dd($caStatus);

                    } elseif ($statusValue === 'Declaration L1') {
                        // Set CA status to Pending
                        $caStatus = $ca->approval_sett = 'Pending';
                    }

                    $ca->approval_status = 'Approved';
                    $ca->approval_sett = $request->approval_sett;
                    $ca->approval_extend = $request->approval_extend;
                    $ca->created_by = $userId;


                    if ($statusValue === 'Declaration L1') {
                        $ca->approval_sett = 'Pending';
                    } elseif ($statusValue === 'Declaration Draft') {
                        $ca->approval_sett = 'Draft';
                    } else {
                        $ca->approval_sett = $statusValue;
                    }

                    $ca->declaration_at = Carbon::now();
                    $total_real = (int) str_replace('.', '', $request->totalca);
                    // $total_ca = $ca->total_ca;

                    if ($total_real === 0) {
                        // Redirect back with a SweetAlert message
                        return redirect()->back()->with('error', 'CA Real cannot be zero.')->withInput();
                    }

                    // Assign total_real and calculate total_cost
                    // $ca->total_real = $total_real;
                    // $ca->total_cost = $total_ca - $total_real;

                    // Initialize arrays for details
                    $detail_e = [];
                    $relation_e = [];

                    if ($request->has('enter_type_e_detail')) {
                        foreach ($request->enter_type_e_detail as $key => $type) {
                            $fee_detail = $request->enter_fee_e_detail[$key];
                            $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                            if (!empty($type) && !empty($nominal)) {
                                $detail_e[] = [
                                    'type' => $type,
                                    'fee_detail' => $fee_detail,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }

                    // Mengumpulkan detail relation
                    if ($request->has('rname_e_relation')) {
                        foreach ($request->rname_e_relation as $key => $name) {
                            $position = $request->rposition_e_relation[$key];
                            $company = $request->rcompany_e_relation[$key];
                            $purpose = $request->rpurpose_e_relation[$key];

                            // Memastikan semua data yang diperlukan untuk relation terisi
                            if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                                $relation_e[] = [
                                    'name' => $name,
                                    'position' => $position,
                                    'company' => $company,
                                    'purpose' => $purpose,
                                    'relation_type' => array_filter([
                                        'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                        'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                        'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                        'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                        'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                                    ], fn($checked) => $checked),
                                ];
                            }
                        }
                    }

                    // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
                    $declare_ca = [
                        'detail_e' => $detail_e,
                        'relation_e' => $relation_e,
                    ];
                    $declare_ent_ntf = $declare_ca;
                    $ca->prove_declare = json_encode(array_values($existingFiles));

                    $ca->detail_ca = '[{"detail_e":[],"relation_e":[]}]';
                    $ca->declare_ca = json_encode($declare_ca);
                    $model = $ca;

                    $model->sett_id = $managerL1;
                    // dd($ca);
                }

                if ($dnsTab == false && $request->totalca_ca_deklarasi > 0) {
                    // Create a new CA transaction if it doesn't exist
                    $ca = new CATransaction();
                    $dnsTab = true;

                    // Generate new 'no_ca' code
                    $currentYear = date('Y');
                    $currentYearShort = date('y');
                    $prefix = 'CA';
                    $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                        ->orderBy('no_ca', 'desc')
                        ->first();

                    $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
                    $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                    $newNoCa = "$prefix$currentYearShort$newNumber";

                    $caId = $ca->id = (string) Str::uuid();
                    $ca->no_ca = $newNoCa;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->unit = $request->divisi;
                    $ca->contribution_level_code = $request->bb_perusahaan;
                    $ca->user_id = $userId;
                    $ca->destination = $request->tujuan;
                    $ca->start_date = $request->mulai;
                    $ca->end_date = $request->kembali;
                    $ca->ca_needs = $request->keperluan;
                    $ca->type_ca = 'dns';
                    $ca->date_required = $entrRecord->date_required;
                    $ca->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
                    $ca->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
                    $ca->total_ca = '0';
                    $ca->total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                    $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);

                    // dd($ca->total_real, $ca->total_cost);

                    if ($statusValue === 'Declaration Draft') {
                        // Set CA status to Draft
                        // dd($statusValue);
                        $caStatus = $ca->approval_sett = 'Draft';
                        // dd($caStatus);

                    } elseif ($statusValue === 'Declaration L1') {
                        // Set CA status to Pending
                        $caStatus = $ca->approval_sett = 'Pending';
                    }

                    $ca->approval_status = 'Approved';
                    $ca->approval_sett = $request->approval_sett;
                    $ca->approval_extend = $request->approval_extend;
                    $ca->created_by = $userId;


                    if ($statusValue === 'Declaration L1') {
                        $ca->approval_sett = 'Pending';
                    } elseif ($statusValue === 'Declaration Draft') {
                        $ca->approval_sett = 'Draft';
                    } else {
                        $ca->approval_sett = $statusValue;
                    }

                    $ca->declaration_at = Carbon::now();
                    $total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                    // $total_ca = $ca->total_ca;

                    if ($total_real === 0) {
                        // Redirect back with a SweetAlert message
                        return redirect()->back()->with('error', 'CA Real cannot be zero.')->withInput();
                    }

                    // Assign total_real and calculate total_cost
                    // $ca->total_real = $total_real;
                    // $ca->total_cost = $total_ca - $total_real;

                    $detail_perdiem = [];
                    $detail_meals = [];
                    $detail_transport = [];
                    $detail_penginapan = [];
                    $detail_lainnya = [];

                    if ($request->has('start_bt_meals')) {
                        foreach ($request->start_bt_meals as $key => $startDate) {
                            $endDate = $request->end_bt_meals[$key] ?? '';
                            $totalDays = $request->total_days_bt_meals[$key] ?? '';
                            $companyCode = $request->company_bt_meals[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                            $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                                $detail_meals[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'keterangan' => $keterangan,
                                ];
                            }
                        }
                    }

                    // Populate detail_perdiem
                    if ($request->has('start_bt_perdiem')) {
                        foreach ($request->start_bt_perdiem as $key => $startDate) {
                            $endDate = $request->end_bt_perdiem[$key] ?? '';
                            $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                            $location = $request->location_bt_perdiem[$key] ?? '';
                            $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                            $companyCode = $request->company_bt_perdiem[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                            if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                                $detail_perdiem[] = [
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
                    }

                    // Populate detail_transport
                    if ($request->has('tanggal_bt_transport')) {
                        foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                            $companyCode = $request->company_bt_transport[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');


                            if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                                $detail_transport[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }

                    // Populate detail_penginapan
                    if ($request->has('start_bt_penginapan')) {
                        foreach ($request->start_bt_penginapan as $key => $startDate) {
                            $endDate = $request->end_bt_penginapan[$key] ?? '';
                            $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                            $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                            $companyCode = $request->company_bt_penginapan[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');


                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
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
                    }

                    // Populate detail_lainnya
                    if ($request->has('tanggal_bt_lainnya')) {
                        foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                            $type = $request->type_bt_lainnya[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');

                            if (!empty($tanggal) && !empty($nominal)) {
                                $detail_lainnya[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'type' => $type,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }
                    if ($request->has('start_bt_meals')) {
                        foreach ($request->start_bt_meals as $key => $startDate) {
                            $endDate = $request->end_bt_meals[$key] ?? '';
                            $totalDays = $request->total_days_bt_meals[$key] ?? '';
                            $companyCode = $request->company_bt_meals[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                            $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
                                $detail_meals[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'keterangan' => $keterangan,
                                ];
                            }
                        }
                    }

                    // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
                    $declare_ca = [
                        'detail_perdiem' => $detail_perdiem,
                        'detail_meals' => $detail_meals,
                        'detail_transport' => $detail_transport,
                        'detail_penginapan' => $detail_penginapan,
                        'detail_lainnya' => $detail_lainnya,
                    ];
                    $declare_ca_ntf = $declare_ca;
                    $ca->prove_declare = json_encode(array_values($existingFiles));

                    $ca->detail_ca = '[{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}]';
                    $ca->declare_ca = json_encode($declare_ca);
                    $model = $ca;

                    $model->sett_id = $managerL1;

                }
                $ca->save();
            }
        }
        // Update the status field in the BusinessTrip record
        $n->update([
            'status' => $statusValue,
        ]);
        // Only proceed with approval process if not 'Declaration Draft'
        if ($statusValue !== 'Declaration Draft') {
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
                ->where('employees.designation_code', '=', $employee->designation_code)
                ->where('dsg2.director_flag', '=', 'T')
                ->get();

            $director_id = "";

            if ($cek_director_id->isNotEmpty()) {
                $director_id = $cek_director_id->first()->employee_id;
            }

            if ($dnsTab) {
                $total_ca = str_replace('.', '', $request->totalca_ca_deklarasi);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
                    ->whereRaw(
                        '? BETWEEN CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
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

                    if ($employee_id != null) {
                        $model_approval = new ca_sett_approval;
                        $model_approval->ca_id = $dnsRecord->id ?? $ca->id;
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = $caStatus;

                        // Simpan data ke database
                        $model_approval->save();
                    }
                    $model_approval->save();
                }
            }

            if ($entrTab) {
                $total_ca = str_replace('.', '', $request->totalca);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
                    ->whereRaw(
                        '? BETWEEN CAST(SUBSTRING_INDEX(condt, "-", 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX(condt, "-", -1) AS UNSIGNED)',
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

                    if ($employee_id != null) {
                        $model_approval = new ca_sett_approval;
                        $model_approval->ca_id = $entrRecord->id ?? $ent->id ?? $ca->id;
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = $caStatus ?? $entStatus;

                        // Simpan data ke database
                        $model_approval->save();
                    }
                    $model_approval->save();
                }
            }
            $managerEmail = Employee::where('employee_id', $managerL1)->pluck('email')->first();
            // $managerEmail = "erzie.aldrian02@gmail.com";
            $managerName = Employee::where('employee_id', $managerL1)->pluck('fullname')->first();
            $group_company = Employee::where('id', $employee_data->id)->pluck('group_company')->first();

            if ($managerEmail) {
                $approvalLink = route('approve.business.trip.declare', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Declaration L2'
                ]);

                $revisionLink = route('revision.link.declaration', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Declaration Revision',
                ]);

                $rejectionLink = route('reject.link.declaration', [
                    'id' => urlencode($n->id),
                    'manager_id' => $n->manager_l1_id,
                    'status' => 'Declaration Rejected'
                ]);
                $caTrans = CATransaction::where('no_sppd', $n->no_sppd)
                    ->where(function ($query) {
                        $query->where('caonly', '!=', 'Y')
                            ->orWhereNull('caonly');
                    })
                    ->get();
                $dnsNtfRe = $caTrans->where('type_ca', 'dns')->first();
                $entrNtfRe = $caTrans->where('type_ca', 'entr')->first();
                $isCa = $dnsNtfRe ? true : false;
                $isEnt = $entrNtfRe ? true : false;
                $detail_ca_req = isset($dnsNtfRe) && isset($dnsNtfRe->detail_ca) ? json_decode($dnsNtfRe->detail_ca, true) : [];
                $detail_ent_req = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->detail_ca, true) : [];

                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $employeeName = Employee::where('id', $n->user_id)->pluck('fullname')->first();
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                $textNotification = "requesting a Declaration Business Trip and waiting for your approval with the following details :";
                // dd( $detail_ca, $caTrans);

                // dd($caTrans, $n->no_sppd);
                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca_req['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca_req['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca_req['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca_req['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'total_days')),
                    'total_amount_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_ent' => array_sum(array_column($detail_ent_req['detail_e'] ?? [], 'nominal')),
                ];

                // dd($caDetails,   $detail_ca );

                $declare_ca_ntf = isset($declare_ca_ntf) ? $declare_ca_ntf : [];
                $declare_ent_ntf = isset($declare_ent_ntf) ? $declare_ent_ntf : [];
                $caDeclare = [
                    'total_days_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($declare_ca_ntf['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($declare_ca_ntf['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($declare_ca_ntf['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($declare_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'total_days')),
                    'total_amount_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'nominal')),
                ];
                // dd($caDeclare);
                $entDeclare = [
                    'total_amount_ent' => array_sum(array_column($declare_ent_ntf['detail_e'] ?? [], 'nominal')),
                ];
                // Send email to the manager
                try {
                    Mail::to($managerEmail)->send(new DeclarationNotification(
                        $n,
                        $caDetails,
                        $caDeclare,
                        $entDetails,
                        $entDeclare,
                        $managerName,
                        $approvalLink,
                        $revisionLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                        $isEnt,
                        $isCa,
                        $group_company,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Deklarasi Create Business Trip tidak terkirim: ' . $e->getMessage());
                }
            }
        }


        return redirect('/businessTrip')->with('success', 'Declaration created successfully');
    }



    public function filterDate(Request $request)
    {
        $user = Auth::user();
        $query = BusinessTrip::where('user_id', $user->id)->orderBy('created_at', 'desc');
        // $sppd = BusinessTrip::where('user_id', $user->id);
        $filter = $request->input('filter', 'all');

        if ($filter === 'request') {
            // Show all data where the date is < today and status is in ['Pending L1', 'Pending L2', 'Draft']
            $query->where(function ($query) {
                $query->whereDate('kembali', '<', now())
                    ->whereIn('status', ['Pending L1', 'Pending L2']);
            });
        } elseif ($filter === 'declaration') {
            // Show data with Approved, Declaration L1, Declaration L2, Draft Declaration
            $query->where(function ($query) {
                $query->whereIn('status', ['Approved', 'Declaration L1', 'Declaration L2', 'Declaration Approved']);
            });
        } elseif ($filter === 'rejected') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Rejected', 'Declaration Rejected']);
            });
        } elseif ($filter === 'done') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Return/Refund', 'Doc Accepted', 'Verified']);
            });
        } elseif ($filter === 'draft') {
            // Show data with Rejected, Refund, Doc Accepted, Verified
            $query->where(function ($query) {
                $query->whereIn('status', ['Draft', 'Declaration Draft']);
            });
        }

        // If 'all' is selected or no filter is applied, just get all data
        if ($filter === 'all') {
            // No additional where clauses needed for 'all'
        }

        $sppd = $query->get();
        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();

        $btApprovals = $btApprovals->keyBy('bt_id');

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');
        // Fetch related data
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        // $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');
        // $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        // $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        // $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');

        if ($startDate && $endDate) {
            $sppd = BusinessTrip::where('user_id', $user->id) // Filter by the user's ID
                ->whereBetween('mulai', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(); // Adjust the pagination as needed
        } else {
            $sppd = BusinessTrip::where('user_id', $user->id) // Filter by the user's ID
                ->orderBy('created_at', 'desc')
                ->get();
        }
        $parentLink = 'Reimbursement';
        $link = 'Business Trip';

        return view('hcis.reimbursements.businessTrip.businessTrip', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'managerL1Names', 'managerL2Names', 'filter', 'btApprovals', 'employeeName'));
    }


    public function pdfDownload($id)
    {
        $sppd = BusinessTrip::findOrFail($id);
        $response = ['sppd' => $sppd];

        $types = [
            'ca' => ca_transaction::class,
            'tiket' => Tiket::class,
            'hotel' => Hotel::class,
            'mess' => Mess::class,
            'taksi' => Taksi::class,
            'deklarasi' => ca_transaction::class,
        ];

        foreach ($types as $type => $model) {
            if (in_array($type, ['tiket', 'hotel', 'mess'])) {
                $data = $model::where('no_sppd', $sppd->no_sppd)->get();
            } else {
                $data = $model::where('no_sppd', $sppd->no_sppd)->first();
            }

            if ($data) {
                $response[$type] = $data;
            }
        }

        return response()->json($response);
    }

    public function export($id, $types = null)
    {
        try {
            $user = Auth::user();
            $sppd = BusinessTrip::where('user_id', $user->id)->where('id', $id)->firstOrFail();

            if (!$types) {
                $types = ['sppd', 'ca', 'tiket', 'hotel', 'taksi', 'mess'];
            } else {
                $types = explode(',', $types);
            }

            if (!in_array($sppd->status, ['Approved', 'Pending L1', 'Pending L2'])) {
                $types[] = 'deklarasi';
            }

            $zip = new ZipArchive();
            $zipFileName = 'Business Trip.zip';
            $zipFilePath = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($types as $type) {
                    $pdfContent = null;
                    $pdfName = '';

                    switch ($type) {
                        case 'sppd':
                            $pdfName = 'SPPD.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.sppd_pdf';
                            $data = ['sppd' => $sppd];
                            break;
                        case 'ca':
                            $ca = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->first();
                            $allCa = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->get();

                            if ($allCa->isEmpty()) {
                                Log::info('Skipping CA download: No CA records found');
                                continue 2;
                            }

                            $pdfFiles = [];

                            $dnsCA = $allCa->where('type_ca', 'dns')->where('approval_status', '!=', 'Rejected')->first();
                            if ($dnsCA) {
                                $employee_data = Employee::where('id', $user->id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $dnsCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $dnsCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];


                                $pdfFiles[] = [
                                    'name' => 'CA.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.ca_pdf',
                                    'data' => $data
                                ];
                            }
                            $entrCA = $allCa->where('type_ca', 'entr')->where('approval_status', '!=', 'Rejected')->first();
                            if ($entrCA) {
                                $employee_data = Employee::where('id', $user->id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $entrCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();
                                // if ($approval->isNotEmpty()) {
                                $data = [
                                    'link' => 'Cash Advanced Entertainment',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $entrCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];


                                $pdfFiles[] = [
                                    'name' => 'CA Entertain.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.caEntr_pdf',
                                    'data' => $data
                                ];
                                // }
                            }
                            foreach ($pdfFiles as $pdfFile) {
                                $pdf = PDF::loadView($pdfFile['viewPath'], $pdfFile['data']);
                                $pdfContent = $pdf->output();
                                $zip->addFromString($pdfFile['name'], $pdfContent);
                            }

                            break;
                        case 'tiket':
                            $tickets = Tiket::where('no_sppd', $sppd->no_sppd)->get();
                            if ($tickets->isEmpty()) {
                                continue 2;
                            }
                            $pdfName = 'Ticket.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.tiket_pdf';
                            $data = [
                                'ticket' => $tickets->first(),
                                'passengers' => $tickets->map(function ($ticket) {
                                    return (object) [
                                        'np_tkt' => $ticket->np_tkt,
                                        'tlp_tkt' => $ticket->tlp_tkt,
                                        'jk_tkt' => $ticket->jk_tkt,
                                        'dari_tkt' => $ticket->dari_tkt,
                                        'ke_tkt' => $ticket->ke_tkt,
                                        'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                                        'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                                        'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                                        'jam_plg_tkt' => $ticket->jam_plg_tkt,
                                        'type_tkt' => $ticket->type_tkt,
                                        'jenis_tkt' => $ticket->jenis_tkt,
                                        'ket_tkt' => $ticket->ket_tkt,
                                        'company_name' => $ticket->checkcompany->contribution_level ?? $ticket->checkcompanybt->checkCompany->contribution_level,
                                        'cost_center' => $ticket->cost_center,
                                        'manager1_fullname' => $ticket->manager1_fullname, // Accessor attribute
                                        'manager2_fullname' => $ticket->manager2_fullname,
                                    ];
                                })
                            ];
                            break;
                        case 'hotel':
                            $hotels = Hotel::where('no_sppd', $sppd->no_sppd)->get(); // Fetch all hotels with the given sppd
                            if ($hotels->isEmpty()) {
                                continue 2; // Skip if no hotels found
                            }
                            $pdfName = 'Hotel.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.hotel_pdf';
                            $data = [
                                'hotel' => $hotels->first(), // Use the first hotel for general details
                                'hotels' => $hotels // Pass all hotels for detailed view
                            ];
                            break;

                        case 'mess':
                            $messes = Mess::where('no_sppd', $sppd->no_sppd)->get();
                            // dd($messes)
                            if ($messes->isEmpty()) {
                                continue 2; // Skip if no hotels found
                            }
                            $pdfName = 'Mess.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.mess_pdf';
                            $data = [
                                'mess' => $messes->first(),
                                'messes' => $messes,
                            ];
                            break;

                        case 'taksi':
                            $taksi = Taksi::where('no_sppd', $sppd->no_sppd)->first();
                            if (!$taksi)
                                continue 2;
                            $pdfName = 'Taxi.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.taksi_pdf';
                            $data = ['taksi' => $taksi];
                            break;
                        case 'deklarasi':
                            $ca = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')->first();
                            $allCa = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->get();

                            if ($allCa->isEmpty() || in_array($sppd->status, ['Approved', 'Pending L1', 'Pending L2', 'Rejected', 'Declaration Draft'])) {
                                continue 2;
                            }


                            $pdfFiles = [];

                            $dnsCA = $allCa->where('type_ca', 'dns')->first();
                            if ($dnsCA) {
                                $employee_data = Employee::where('id', $user->id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $dnsCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $dnsCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'Deklarasi.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.deklarasi_pdf',
                                    'data' => $data
                                ];
                            }
                            $entrCA = $allCa->where('type_ca', 'entr')->first();
                            if ($entrCA) {
                                $employee_data = Employee::where('id', $user->id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $entrCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced Entertainment',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $entrCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'Deklarasi Entertain.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.deklarasiEntr_pdf',
                                    'data' => $data
                                ];
                            }
                            foreach ($pdfFiles as $pdfFile) {
                                $pdf = PDF::loadView($pdfFile['viewPath'], $pdfFile['data']);
                                $pdfContent = $pdf->output();
                                $zip->addFromString($pdfFile['name'], $pdfContent);
                            }
                            break;
                        default:
                            continue 2;
                    }
                    // $pdfContent = PDF::loadView($viewPath, $data)->output();
                    // $zip->addFromString($pdfName, $pdfContent);
                    try {
                        // $pdfContent = PDF::loadView($viewPath, $data)->output();
                        // $zip->addFromString($pdfName, $pdfContent);
                        $pdf = PDF::loadView($viewPath, $data);

                        if ($type === 'ca') {
                            // Add the special footer for CA PDF using a callback
                            $pdf->output();
                            $canvas = $pdf->getCanvas();
                            $canvas->page_script('
                                if ($PAGE_COUNT > 2) {
                                    $font = $fontMetrics->getFont("Helvetica", "normal");
                                    $size = 8;
                                    $color = array(0, 0, 0);
                                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT . " Cash Advanced No. ' . $ca->no_ca . '";
                                    $pdf->text(400, 810, $text, $font, $size, $color);
                                }
                            ');
                        }
                        if ($type === 'deklarasi') {
                            // Add the special footer for CA PDF using a callback
                            $pdf->output();
                            $canvas = $pdf->getCanvas();
                            $canvas->page_script('
                                if ($PAGE_COUNT > 2) {
                                    $font = $fontMetrics->getFont("Helvetica", "normal");
                                    $size = 8;
                                    $color = array(0, 0, 0);
                                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT . " Cash Advanced No. ' . $ca->no_ca . '";
                                    $pdf->text(400, 810, $text, $font, $size, $color);
                                }
                            ');
                        }

                        $pdfContent = $pdf->output();
                        $zip->addFromString($pdfName, $pdfContent);
                    } catch (\Exception $e) {
                        Log::error("Error generating PDF for {$type}: " . $e->getMessage());
                        continue; // Skip to the next iteration if there's an error
                    }
                }
                $zip->close();
            }

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error("Error in export function: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
    public function pdfDownloadAdmin($id)
    {
        $sppd = BusinessTrip::findOrFail($id);
        $response = ['sppd' => $sppd];

        $types = [
            'ca' => ca_transaction::class,
            'tiket' => Tiket::class,
            'hotel' => Hotel::class,
            'mess' => Mess::class,
            'taksi' => Taksi::class
        ];

        foreach ($types as $type => $model) {
            if (in_array($type, ['tiket', 'hotel'])) {
                $data = $model::where('no_sppd', $sppd->no_sppd)->get();
            } else {
                $data = $model::where('no_sppd', $sppd->no_sppd)->first();
            }

            if ($data) {
                $response[$type] = $data;
            }
        }

        return response()->json($response);
    }

    public function exportAdmin($id, $types = null)
    {
        try {
            $user = Auth::user();
            $sppd = BusinessTrip::findOrFail($id);

            if (!$types) {
                $types = ['sppd', 'ca', 'tiket', 'hotel', 'taksi', 'mess'];
            } else {
                $types = explode(',', $types);
            }
            if (!in_array($sppd->status, ['Approved', 'Pending L1', 'Pending L2'])) {
                $types[] = 'deklarasi';
            }

            $zip = new ZipArchive();
            $zipFileName = 'Business Trip.zip';
            $zipFilePath = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($types as $type) {
                    $pdfContent = null;
                    $pdfName = '';

                    switch ($type) {
                        case 'sppd':
                            $pdfName = 'SPPD.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.sppd_pdf';
                            $data = ['sppd' => $sppd];
                            break;
                        case 'ca':
                            $ca = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->first();
                            $allCa = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->get();

                            if ($allCa->isEmpty()) {
                                Log::info('Skipping CA download: No CA records found');
                                continue 2;
                            }

                            $pdfFiles = [];

                            $dnsCA = $allCa->where('type_ca', 'dns')->where('approval_status', '!=', 'Rejected')->first();
                            if ($dnsCA) {
                                $employee_data = Employee::where('id', $sppd->user_id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $dnsCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $dnsCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'CA.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.ca_pdf',
                                    'data' => $data
                                ];
                            }
                            $entrCA = $allCa->where('type_ca', 'entr')->where('approval_status', '!=', 'Rejected')->first();
                            if ($entrCA) {
                                $employee_data = Employee::where('id', $sppd->user_id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $entrCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced Entertainment',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $entrCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'CA Entertain.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.caEntr_pdf',
                                    'data' => $data
                                ];
                            }
                            foreach ($pdfFiles as $pdfFile) {
                                $pdf = PDF::loadView($pdfFile['viewPath'], $pdfFile['data']);
                                $pdfContent = $pdf->output();
                                $zip->addFromString($pdfFile['name'], $pdfContent);
                            }

                            break;
                        case 'tiket':
                            $tickets = Tiket::where('no_sppd', $sppd->no_sppd)->get();
                            if ($tickets->isEmpty()) {
                                continue 2;
                            }
                            $pdfName = 'Ticket.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.tiket_pdf';
                            $data = [
                                'ticket' => $tickets->first(),
                                'passengers' => $tickets->map(function ($ticket) {
                                    return (object) [
                                        'np_tkt' => $ticket->np_tkt,
                                        'tlp_tkt' => $ticket->tlp_tkt,
                                        'jk_tkt' => $ticket->jk_tkt,
                                        'dari_tkt' => $ticket->dari_tkt,
                                        'ke_tkt' => $ticket->ke_tkt,
                                        'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                                        'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                                        'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                                        'jam_plg_tkt' => $ticket->jam_plg_tkt,
                                        'type_tkt' => $ticket->type_tkt,
                                        'jenis_tkt' => $ticket->jenis_tkt,
                                        'ket_tkt' => $ticket->ket_tkt,
                                        'company_name' => $ticket->checkcompany->contribution_level ?? $ticket->checkcompanybt->checkCompany->contribution_level,
                                        'cost_center' => $ticket->cost_center,
                                        'manager1_fullname' => $ticket->manager1_fullname, // Accessor attribute
                                        'manager2_fullname' => $ticket->manager2_fullname,
                                    ];
                                })
                            ];
                            break;
                        case 'hotel':
                            $hotels = Hotel::where('no_sppd', $sppd->no_sppd)->get(); // Fetch all hotels with the given sppd
                            if ($hotels->isEmpty()) {
                                continue 2; // Skip if no hotels found
                            }
                            $pdfName = 'Hotel.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.hotel_pdf';
                            $data = [
                                'hotel' => $hotels->first(), // Use the first hotel for general details
                                'hotels' => $hotels
                            ];
                            break;

                        case 'mess':
                            $messes = Mess::where('no_sppd', $sppd->no_sppd)->get();
                            // dd($messes)
                            if ($messes->isEmpty()) {
                                continue 2; // Skip if no hotels found
                            }
                            $pdfName = 'Mess.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.mess_pdf';
                            $data = [
                                'mess' => $messes->first(),
                                'messes' => $messes,
                            ];
                            break;

                        case 'taksi':
                            $taksi = Taksi::where('no_sppd', $sppd->no_sppd)->first();
                            if (!$taksi)
                                continue 2;
                            $pdfName = 'Taxi.pdf';
                            $viewPath = 'hcis.reimbursements.businessTrip.taksi_pdf';
                            $data = ['taksi' => $taksi];
                            break;
                        case 'deklarasi':
                            $ca = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->first();
                            $allCa = CATransaction::where('no_sppd', $sppd->no_sppd)->where('approval_status', '!=', 'Rejected')
                                ->get();

                            if ($allCa->isEmpty() || in_array($sppd->status, ['Approved', 'Pending L1', 'Pending L2', 'Rejected', 'Declaration Draft'])) {
                                continue 2;
                            }


                            $pdfFiles = [];

                            $dnsCA = $allCa->where('type_ca', 'dns')->first();
                            if ($dnsCA) {
                                $employee_data = Employee::where('id', $sppd->user_id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $dnsCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $dnsCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'Deklarasi.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.deklarasi_pdf',
                                    'data' => $data
                                ];
                            }
                            $entrCA = $allCa->where('type_ca', 'entr')->first();
                            if ($entrCA) {
                                $employee_data = Employee::where('id', $sppd->user_id)->first();
                                $allowance = in_array($employee_data->group_company, ['Plantations', 'KPN Plantations'])
                                    ? "Perdiem"
                                    : "Allowance";

                                $approval = ca_approval::with('employee')
                                    ->where('ca_id', $entrCA->id)
                                    ->where('approval_status', '!=', 'Rejected')
                                    ->orderBy('layer', 'asc')
                                    ->get();

                                $data = [
                                    'link' => 'Cash Advanced Entertainment',
                                    'parentLink' => 'Reimbursement',
                                    'userId' => $user->id,
                                    'companies' => Company::orderBy('contribution_level')->get(),
                                    'locations' => Location::orderBy('area')->get(),
                                    'employee_data' => $employee_data,
                                    'perdiem' => ListPerdiem::where('grade', $employee_data->job_level)
                                        ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')
                                        ->first(),
                                    'no_sppds' => CATransaction::where('user_id', $user->id)
                                        ->where('approval_sett', '!=', 'Done')
                                        ->get(),
                                    'transactions' => $entrCA,
                                    'approval' => $approval,
                                    'allowance' => $allowance,
                                ];

                                $pdfFiles[] = [
                                    'name' => 'Deklarasi Entertain.pdf',
                                    'viewPath' => 'hcis.reimbursements.businessTrip.deklarasiEntr_pdf',
                                    'data' => $data
                                ];
                            }
                            foreach ($pdfFiles as $pdfFile) {
                                $pdf = PDF::loadView($pdfFile['viewPath'], $pdfFile['data']);
                                $pdfContent = $pdf->output();
                                $zip->addFromString($pdfFile['name'], $pdfContent);
                            }
                            break;
                        default:
                            continue 2;
                    }

                    // $pdfContent = PDF::loadView($viewPath, $data)->output();
                    // $zip->addFromString($pdfName, $pdfContent);
                    try {
                        // $pdfContent = PDF::loadView($viewPath, $data)->output();
                        // $zip->addFromString($pdfName, $pdfContent);
                        $pdf = PDF::loadView($viewPath, $data);

                        if ($type === 'ca') {
                            // Add the special footer for CA PDF using a callback
                            $pdf->output();
                            $canvas = $pdf->getCanvas();
                            $canvas->page_script('
                                if ($PAGE_COUNT > 2) {
                                    $font = $fontMetrics->getFont("Helvetica", "normal");
                                    $size = 8;
                                    $color = array(0, 0, 0);
                                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT . " Cash Advanced No. ' . $ca->no_ca . '";
                                    $pdf->text(400, 810, $text, $font, $size, $color);
                                }
                            ');
                        }
                        if ($type === 'deklarasi') {
                            // Add the special footer for CA PDF using a callback
                            $pdf->output();
                            $canvas = $pdf->getCanvas();
                            $canvas->page_script('
                                if ($PAGE_COUNT > 2) {
                                    $font = $fontMetrics->getFont("Helvetica", "normal");
                                    $size = 8;
                                    $color = array(0, 0, 0);
                                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT . " Cash Advanced No. ' . $ca->no_ca . '";
                                    $pdf->text(400, 810, $text, $font, $size, $color);
                                }
                            ');
                        }

                        $pdfContent = $pdf->output();
                        $zip->addFromString($pdfName, $pdfContent);
                    } catch (\Exception $e) {
                        Log::error("Error generating PDF for {$type}: " . $e->getMessage());
                        continue; // Skip to the next iteration if there's an error
                    }
                }
                $zip->close();
            }

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error("Error in export function: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }

    public function businessTripformAdd()
    {
        $userId = Auth::id();
        $employee_data = Employee::where('id', $userId)->first();
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();
        $employees = Employee::orderBy('ktp')->get();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $bt_sppd = BusinessTrip::where('status', '!=', 'Verified')->where('status', '!=', 'Rejected')->where('status', '!=', 'Draft')->orderBy('no_sppd', 'desc')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)
            ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')->first();

        $isApproved = CATransaction::where('user_id', $userId)->where('approval_status', '!=', 'Done')->where('approval_status', '!=', 'Rejected')->get();

        $job_level = Employee::where('id', $userId)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);

        $isDisabled = $isApproved->count() >= 1;

        // dd($employee_data, $companies, $perdiem);

        if ($employee_data->group_company == 'Plantations' || $employee_data->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }
        $group_company = Employee::where('id', $employee_data->id)->pluck('group_company')->first();
        // dd($group_company, $job_level_number);
        // dd($group_company);
        if ($job_level) {
            // Extract numeric part of the job level
            $numericPart = intval(preg_replace('/[^0-9]/', '', $job_level));
            $isAllowed = $numericPart >= 8;
        }

        $parentLink = 'Business Trip';
        $link = 'Business Trip Request';
        return view(
            'hcis.reimbursements.businessTrip.formBusinessTrip',
            [
                'employee_data' => $employee_data,
                'employees' => $employees,
                'companies' => $companies,
                'locations' => $locations,
                'no_sppds' => $no_sppds,
                'bt_sppd' => $bt_sppd,
                'perdiem' => $perdiem,
                'parentLink' => $parentLink,
                'link' => $link,
                'isAllowed' => $isAllowed,
                'allowance' => $allowance,
                'job_level_number' => $job_level_number,
                'group_company' => $employee_data->group_company,
                'isDisabled' => $isDisabled,
            ]
        );
    }

    public function businessTripCreate(Request $request)
    {
        $bt = new BusinessTrip();

        $bt->id = (string) Str::uuid();

        // Check if "Others" is selected in the "tujuan" dropdown
        if ($request->tujuan === 'Others' && !empty($request->others_location)) {
            $tujuan = $request->others_location;  // Use the value from the text box
        } else {
            $tujuan = $request->tujuan;  // Use the selected dropdown value
        }

        if ($request->has('action_draft')) {
            $statusValue = 'Draft';  // When "Save as Draft" is clicked
        } elseif ($request->has('action_submit')) {
            $statusValue = 'Pending L1';  // When "Submit" is clicked
        }

        $noSppd = $this->generateNoSppd();
        // $noSppdCa = $this->generateNoSppdCa();
        $noSppdTkt = $this->generateNoSppdTkt();
        $noSppdHtl = $this->generateNoSppdHtl();
        $noSppdMess = $this->generateNoSppdMess();
        $userId = Auth::id();
        $employee = Employee::where('id', $userId)->first();

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
        $deptHeadManager = findDepartmentHead($employee);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        $isJobLevel = MatrixApproval::where('modul', 'businesstrip')
            ->where('group_company', 'like', '%' . $employee->group_company . '%')
            ->where('job_level', 'like', '%' . $employee->job_level . '%')
            ->get();

        if ($request->jns_dinas == 'dalam kota') {
            $tktDalam = $request->tiket_dalam_kota;
            $htlDalam = $request->hotel_dalam_kota;
            $vtDalam = $request->taksi_dalam_kota;
            $messDalam = $request->mess_dalam_kota;
        } else {
            $tktDalam = $request->tiket;
            $htlDalam = $request->hotel;
            $vtDalam = $request->taksi;
            $messDalam = $request->mess;
        }

        $businessTrip = BusinessTrip::create([
            'id' => $bt->id,
            'user_id' => $userId,
            'jns_dinas' => $request->jns_dinas,
            'nama' => $request->nama,
            'divisi' => $request->divisi,
            'unit_1' => $request->unit_1,
            'atasan_1' => $request->atasan_1,
            'email_1' => $request->email_1,
            'unit_2' => $request->unit_2,
            'atasan_2' => $request->atasan_2,
            'email_2' => $request->email_2,
            'no_sppd' => $noSppd,
            'mulai' => $request->mulai,
            'kembali' => $request->kembali,
            'tujuan' => $tujuan,
            'keperluan' => $request->keperluan,
            'bb_perusahaan' => $request->bb_perusahaan,
            'norek_krywn' => $request->norek_krywn,
            'nama_pemilik_rek' => $request->nama_pemilik_rek,
            'nama_bank' => $request->nama_bank,
            'ca' => $request->ca === 'Tidak' ? $request->ent : $request->ca,
            'tiket' => $tktDalam,
            'hotel' => $htlDalam,
            'mess' => $messDalam,
            'taksi' => $vtDalam,
            'status' => $statusValue,
            // dd($statusValue),
            'manager_l1_id' => $managerL1,
            'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
            'approval_status' => $request->status,

        ]);
        if ($vtDalam === 'Ya') {
            $taksi = new Taksi();
            $taksi->id = (string) Str::uuid();
            if ($request->jns_dinas === 'dalam kota') {
                $noVt = $request->input('no_vt_dalam_kota');
                $vtDetail = $request->input('vt_detail_dalam_kota');
            } else if ($request->jns_dinas === 'luar kota') {
                $noVt = $request->input('no_vt');
                $vtDetail = $request->input('vt_detail');
            }
            // dd($noVt, $vtDetail);
            $taksi->no_vt = $noVt;
            $taksi->vt_detail = $vtDetail;
            $taksi->no_sppd = $noSppd;
            $taksi->user_id = $userId;
            $taksi->unit = $request->divisi;
            // $taksi->nominal_vt = (int) str_replace('.', '', $request->nominal_vt);
            $taksi->approval_status = $statusValue;

            $taksi->save();
        }

        if ($htlDalam === 'Ya') {
            if ($request->jns_dinas === 'dalam kota') {
                $hotelData = [
                    'nama_htl' => $request->nama_htl_dalam_kota,
                    'lokasi_htl' => $request->lokasi_htl_dalam_kota,
                    'jmlkmr_htl' => $request->jmlkmr_htl_dalam_kota,
                    'bed_htl' => $request->bed_htl_dalam_kota,
                    'tgl_masuk_htl' => $request->tgl_masuk_htl_dalam_kota,
                    'tgl_keluar_htl' => $request->tgl_keluar_htl_dalam_kota,
                    'total_hari' => $request->total_hari_dalam_kota,
                    'no_sppd_htl' => $request->no_sppd_dalam_kota,
                    'approval_status' => $statusValue,
                ];
            } else {
                $hotelData = [
                    'nama_htl' => $request->nama_htl,
                    'lokasi_htl' => $request->lokasi_htl,
                    'jmlkmr_htl' => $request->jmlkmr_htl,
                    'bed_htl' => $request->bed_htl,
                    'tgl_masuk_htl' => $request->tgl_masuk_htl,
                    'tgl_keluar_htl' => $request->tgl_keluar_htl,
                    'total_hari' => $request->total_hari,
                    'no_sppd_htl' => $request->no_sppd,
                    'approval_status' => $statusValue,
                ];
            }

            foreach ($hotelData['nama_htl'] as $key => $value) {
                if (!empty($value)) {
                    $hotel = new Hotel();
                    $hotel->id = (string) Str::uuid();
                    $hotel->no_htl = $noSppdHtl;
                    $hotel->no_sppd = $noSppd;
                    $hotel->user_id = $userId;
                    $hotel->unit = $request->divisi;
                    $hotel->nama_htl = $value;
                    $hotel->lokasi_htl = $hotelData['lokasi_htl'][$key];
                    $hotel->jmlkmr_htl = $hotelData['jmlkmr_htl'][$key];
                    $hotel->bed_htl = $hotelData['bed_htl'][$key];
                    $hotel->tgl_masuk_htl = $hotelData['tgl_masuk_htl'][$key];
                    $hotel->tgl_keluar_htl = $hotelData['tgl_keluar_htl'][$key];
                    $hotel->total_hari = $hotelData['total_hari'][$key];
                    $hotel->no_sppd_htl = $hotelData['no_sppd_htl'][$key];
                    $hotel->approval_status = $statusValue;
                    $hotel->contribution_level_code = $request->bb_perusahaan;
                    $hotel->manager_l1_id = $managerL1;
                    $hotel->manager_l2_id = ($isJobLevel->count() == 1) ? '-' : $managerL2;

                    $hotel->save();
                }
            }
        }
        if ($messDalam === 'Ya') {
            if ($request->jns_dinas === 'dalam kota') {
                $messData = [
                    'lokasi_mess' => $request->lokasi_mess_dalam_kota,
                    'jmlkmr_mess' => $request->jmlkmr_mess_dalam_kota,
                    'tgl_masuk_mess' => $request->tgl_masuk_mess_dalam_kota,
                    'tgl_keluar_mess' => $request->tgl_keluar_mess_dalam_kota,
                    'total_hari_mess' => $request->total_hari_mess_dalam_kota,
                    'approval_status' => $statusValue,
                ];
            } else {
                $messData = [
                    'lokasi_mess' => $request->lokasi_mess,
                    'jmlkmr_mess' => $request->jmlkmr_mess,
                    'tgl_masuk_mess' => $request->tgl_masuk_mess,
                    'tgl_keluar_mess' => $request->tgl_keluar_mess,
                    'total_hari_mess' => $request->total_hari_mess,
                    'approval_status' => $statusValue,
                ];
            }

            foreach ($messData['lokasi_mess'] as $key => $value) {
                if (!empty($value)) {
                    $mess = new Mess();
                    $mess->id = (string) Str::uuid();
                    $mess->no_mess = $noSppdMess;
                    $mess->no_sppd = $noSppd;
                    $mess->user_id = $userId;
                    $mess->unit = $request->divisi;
                    $mess->lokasi_mess = $value;
                    $mess->jmlkmr_mess = $messData['jmlkmr_mess'][$key];
                    $mess->tgl_masuk_mess = $messData['tgl_masuk_mess'][$key];
                    $mess->tgl_keluar_mess = $messData['tgl_keluar_mess'][$key];
                    $mess->total_hari_mess = $messData['total_hari_mess'][$key];
                    $mess->approval_status = $statusValue;
                    $mess->contribution_level_code = $request->bb_perusahaan;
                    $mess->manager_l1_id = $managerL1;
                    $mess->manager_l2_id = ($isJobLevel->count() == 1) ? '-' : $managerL2;

                    $mess->save();
                }
            }
        }

        // dd($messData);

        if ($tktDalam === 'Ya') {
            if ($request->jns_dinas === 'dalam kota') {
                $ticketData = [
                    'noktp_tkt' => $request->noktp_tkt_dalam_kota,
                    'dari_tkt' => $request->dari_tkt_dalam_kota,
                    'ke_tkt' => $request->ke_tkt_dalam_kota,
                    'tgl_brkt_tkt' => $request->tgl_brkt_tkt_dalam_kota,
                    'tgl_plg_tkt' => $request->tgl_plg_tkt_dalam_kota,
                    'jam_brkt_tkt' => $request->jam_brkt_tkt_dalam_kota,
                    'jam_plg_tkt' => $request->jam_plg_tkt_dalam_kota,
                    'jenis_tkt' => $request->jenis_tkt_dalam_kota,
                    'type_tkt' => $request->type_tkt_dalam_kota,
                    'ket_tkt' => $request->ket_tkt_dalam_kota,
                    'approval_status' => $statusValue,
                ];
            } else {
                $ticketData = [
                    'noktp_tkt' => $request->noktp_tkt,
                    'dari_tkt' => $request->dari_tkt,
                    'ke_tkt' => $request->ke_tkt,
                    'tgl_brkt_tkt' => $request->tgl_brkt_tkt,
                    'tgl_plg_tkt' => $request->tgl_plg_tkt,
                    'jam_brkt_tkt' => $request->jam_brkt_tkt,
                    'jam_plg_tkt' => $request->jam_plg_tkt,
                    'jenis_tkt' => $request->jenis_tkt,
                    'type_tkt' => $request->type_tkt,
                    'ket_tkt' => $request->ket_tkt,
                    'approval_status' => $statusValue,
                    'jns_dinas_tkt' => "Dinas",
                ];
            }

            foreach ($ticketData['dari_tkt'] as $key => $value) {
                if (!empty($value)) {
                    // $employee_data = Employee::where('ktp', $value)->first();

                    $tiket = new Tiket();
                    $tiket->id = (string) Str::uuid();
                    $tiket->no_tkt = $noSppdTkt;
                    $tiket->no_sppd = $noSppd;
                    $tiket->user_id = $userId;
                    $tiket->unit = $request->divisi;
                    $tiket->jk_tkt = $employee ? $employee->gender : null;
                    $tiket->np_tkt = $employee ? $employee->fullname : null;
                    $tiket->noktp_tkt = $ticketData['noktp_tkt'][$key] ?? null;
                    $tiket->tlp_tkt = $employee ? $employee->personal_mobile_number : null;

                    // Handle each field using the index from $key
                    $tiket->dari_tkt = $ticketData['dari_tkt'][$key] ?? null;
                    $tiket->ke_tkt = $ticketData['ke_tkt'][$key] ?? null;
                    $tiket->tgl_brkt_tkt = $ticketData['tgl_brkt_tkt'][$key] ?? null;
                    $tiket->tgl_plg_tkt = $ticketData['tgl_plg_tkt'][$key] ?? null;
                    $tiket->jam_brkt_tkt = $ticketData['jam_brkt_tkt'][$key] ?? null;
                    $tiket->jam_plg_tkt = $ticketData['jam_plg_tkt'][$key] ?? null;
                    $tiket->jenis_tkt = $ticketData['jenis_tkt'][$key] ?? null;
                    $tiket->type_tkt = $ticketData['type_tkt'][$key] ?? null;
                    $tiket->ket_tkt = $ticketData['ket_tkt'][$key] ?? null;
                    $tiket->approval_status = $statusValue;
                    $tiket->contribution_level_code = $request->bb_perusahaan;
                    $tiket->manager_l1_id = $managerL1;
                    $tiket->manager_l2_id = ($isJobLevel->count() == 1) ? '-' : $managerL2;
                    $tiket->jns_dinas_tkt = 'Dinas';

                    $tiket->save();
                }
            }
        }

        // dd($request->all());
        if ($request->ca === 'Ya') {
            $ca = new CATransaction();
            $businessTripStatus = $request->input('status');

            // Generate new 'no_ca' code
            $currentYear = date('Y');
            $currentYearShort = date('y');
            $prefix = 'CA';
            $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                ->orderBy('no_ca', 'desc')
                ->first();

            $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            $newNoCa = "$prefix$currentYearShort$newNumber";

            if ($statusValue === 'Draft') {
                // Set CA status to Draft
                $caStatus = $ca->approval_status = 'Draft';
            } elseif ($statusValue === 'Pending L1') {
                // Set CA status to Pending
                $caStatus = $ca->approval_status = 'Pending';
            }
            $ca_id = (string) Str::uuid();
            // Assign values to $ca model
            $ca->id = $ca_id;
            $ca->type_ca = 'dns';
            $ca->no_ca = $newNoCa;
            $ca->no_sppd = $noSppd;
            $ca->user_id = $userId;
            $ca->unit = $request->divisi;
            $ca->contribution_level_code = $request->bb_perusahaan;
            $ca->destination = $request->tujuan;
            $ca->others_location = $request->others_location;
            $ca->ca_needs = $request->keperluan;
            $ca->start_date = $request->mulai;
            $ca->end_date = $request->kembali;
            $ca->date_required = $request->date_required;
            // $ca->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
            $ca->declare_estimate = $request->ca_decla;
            // dd($request->ca_decla);
            $ca->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
            $ca->total_ca = (int) str_replace('.', '', $request->totalca);
            $ca->total_real = '0';
            $ca->total_cost = (int) str_replace('.', '', $request->totalca);
            $ca->approval_status = $caStatus;
            $ca->approval_sett = $request->approval_sett ? $request->approval_sett : '';
            $ca->approval_extend = $request->approval_extend ? $request->approval_extend : '';
            $ca->created_by = $userId;

            // Initialize arrays
            $detail_meals = [];
            $detail_perdiem = [];
            $detail_transport = [];
            $detail_penginapan = [];
            $detail_lainnya = [];

            if ($request->has('start_bt_meals')) {
                foreach ($request->start_bt_meals as $key => $startDate) {
                    $endDate = $request->end_bt_meals[$key] ?? '';
                    $totalDays = $request->total_days_bt_meals[$key] ?? '';
                    $companyCode = $request->company_bt_meals[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                    $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                    if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                        $detail_meals[] = [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'total_days' => $totalDays,
                            'company_code' => $companyCode,
                            'nominal' => $nominal,
                            'keterangan' => $keterangan,
                        ];
                    }
                }
            }
            // Populate detail_perdiem
            if ($request->has('start_bt_perdiem')) {
                foreach ($request->start_bt_perdiem as $key => $startDate) {
                    $endDate = $request->end_bt_perdiem[$key] ?? '';
                    $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                    $location = $request->location_bt_perdiem[$key] ?? '';
                    $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                    $companyCode = $request->company_bt_perdiem[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                    if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                        $detail_perdiem[] = [
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
            }

            // Populate detail_transport
            if ($request->has('tanggal_bt_transport')) {
                foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                    $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                    $companyCode = $request->company_bt_transport[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');

                    if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                        $detail_transport[] = [
                            'tanggal' => $tanggal,
                            'keterangan' => $keterangan,
                            'company_code' => $companyCode,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }

            // Populate detail_penginapan
            if ($request->has('start_bt_penginapan')) {
                foreach ($request->start_bt_penginapan as $key => $startDate) {
                    $endDate = $request->end_bt_penginapan[$key] ?? '';
                    $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                    $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                    $companyCode = $request->company_bt_penginapan[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');

                    if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
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
            }

            // Populate detail_lainnya
            if ($request->has('tanggal_bt_lainnya')) {
                foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                    $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                    $type = $request->type_bt_lainnya[$key] ?? '';
                    $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');

                    if (!empty($tanggal) && !empty($nominal)) {
                        $detail_lainnya[] = [
                            'tanggal' => $tanggal,
                            'keterangan' => $keterangan,
                            'type' => $type,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }

            // Save the details
            $detail_ca = [
                'detail_perdiem' => $detail_perdiem,
                'detail_meals' => $detail_meals,
                'detail_transport' => $detail_transport,
                'detail_penginapan' => $detail_penginapan,
                'detail_lainnya' => $detail_lainnya,
            ];

            $detail_ca_ntf = $detail_ca;
            $ca->detail_ca = json_encode($detail_ca);
            $ca->declare_ca = json_encode($detail_ca);
            $ca->save();

            if ($statusValue !== 'Draft') {

                $model = $ca;

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
                    ->where('employees.designation_code', '=', $employee->designation_code)
                    ->where('dsg2.director_flag', '=', 'T')
                    ->get();

                $director_id = "";

                if ($cek_director_id->isNotEmpty()) {
                    $director_id = $cek_director_id->first()->employee_id;
                }
                //cek matrix approval

                $total_ca = str_replace('.', '', $request->totalca);
                // dd($total_ca);
                // dd($employee->group_company);
                // dd($request->bb_perusahaan);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
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
                    if ($employee_id != null) {
                        $model_approval = new ca_approval;
                        $model_approval->ca_id = $ca_id;
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = 'Pending';

                        // Simpan data ke database
                        $model_approval->save();
                    }

                    // Simpan data ke database
                    $model_approval->save();
                }
                $ca->save();
            }
        }

        if ($request->ent === 'Ya') {
            $ent = new CATransaction();
            $businessTripStatus = $request->input('status');

            // Generate new 'no_ca' code
            $currentYear = date('Y');
            $currentYearShort = date('y');
            $prefix = 'CA';
            $lastTransaction = CATransaction::whereYear('created_at', $currentYear)
                ->orderBy('no_ca', 'desc')
                ->first();

            $lastNumber = $lastTransaction && preg_match('/CA' . $currentYearShort . '(\d{6})/', $lastTransaction->no_ca, $matches) ? intval($matches[1]) : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            $newNoCa = "$prefix$currentYearShort$newNumber";

            if ($statusValue === 'Draft') {
                // Set CA status to Draft
                $entStatus = $ent->approval_status = 'Draft';
            } elseif ($statusValue === 'Pending L1') {
                // Set CA status to Pending
                $entStatus = $ent->approval_status = 'Pending';
            }
            $ent_id = (string) Str::uuid();
            // Assign values to $ent model
            $ent->id = $ent_id;
            $ent->type_ca = 'entr';
            $ent->no_ca = $newNoCa;
            $ent->no_sppd = $noSppd;
            $ent->user_id = $userId;
            $ent->unit = $request->divisi;
            $ent->contribution_level_code = $request->bb_perusahaan;
            $ent->destination = $request->tujuan;
            $ent->others_location = $request->others_location;
            $ent->ca_needs = $request->keperluan;
            $ent->start_date = $request->mulai;
            $ent->end_date = $request->kembali;
            $ent->date_required = $request->date_required;
            // $ent->declare_estimate = Carbon::parse($request->kembali)->addDays(3);
            $ent->declare_estimate = $request->ca_decla;
            // dd($request->ca_decla);
            $ent->total_days = Carbon::parse($request->mulai)->diffInDays(Carbon::parse($request->kembali));
            $ent->total_ca = (int) str_replace('.', '', $request->total_ent_detail);
            $ent->total_real = '0';
            $ent->total_cost = (int) str_replace('.', '', $request->total_ent_detail);
            $ent->approval_status = $entStatus;
            $ent->approval_sett = $request->approval_sett ? $request->approval_sett : '';
            $ent->approval_extend = $request->approval_extend ? $request->approval_extend : '';
            $ent->created_by = $userId;

            // Initialize arrays
            $detail_e = [];
            $relation_e = [];

            if ($request->has('enter_type_e_detail')) {
                foreach ($request->enter_type_e_detail as $key => $type) {
                    $fee_detail = $request->enter_fee_e_detail[$key];
                    $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                    if (!empty($type) && !empty($nominal)) {
                        $detail_e[] = [
                            'type' => $type,
                            'fee_detail' => $fee_detail,
                            'nominal' => $nominal,
                        ];
                    }
                }
            }

            // Mengumpulkan detail relation
            if ($request->has('rname_e_relation')) {
                foreach ($request->rname_e_relation as $key => $name) {
                    $position = $request->rposition_e_relation[$key];
                    $company = $request->rcompany_e_relation[$key];
                    $purpose = $request->rpurpose_e_relation[$key];

                    // Memastikan semua data yang diperlukan untuk relation terisi
                    if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                        $relation_e[] = [
                            'name' => $name,
                            'position' => $position,
                            'company' => $company,
                            'purpose' => $purpose,
                            'relation_type' => array_filter([
                                'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                            ], fn($checked) => $checked),
                        ];
                    }
                }
            }

            // Gabungkan detail entertain dan relation, lalu masukkan ke detail_ca
            $detail_ca = [
                'detail_e' => $detail_e,
                'relation_e' => $relation_e,
            ];

            $detail_ent = $detail_ca;
            // dd($detail_ca);
            $ent->detail_ca = json_encode($detail_ca);
            $ent->declare_ca = json_encode($detail_ca);
            $ent->save();

            if ($statusValue !== 'Draft') {

                $model = $ent;

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
                    ->where('employees.designation_code', '=', $employee->designation_code)
                    ->where('dsg2.director_flag', '=', 'F')
                    ->get();

                $director_id = "";

                if ($cek_director_id->isNotEmpty()) {
                    $director_id = $cek_director_id->first()->employee_id;
                }
                //cek matrix approval

                $total_ca = str_replace('.', '', $request->total_ent_detail);
                // dd($total_ca);
                // dd($employee->group_company);
                // dd($request->bb_perusahaan);
                $data_matrix_approvals = MatrixApproval::where('modul', 'dns')
                    ->where('group_company', 'like', '%' . $employee->group_company . '%')
                    ->where('contribution_level_code', 'like', '%' . $request->bb_perusahaan . '%')
                    ->where('job_level', 'like', '%' . $employee->job_level . '%')
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
                    if ($employee_id != null) {
                        $model_approval = new ca_approval;
                        $model_approval->ca_id = $ent_id;
                        $model_approval->role_name = $data_matrix_approval->desc;
                        $model_approval->employee_id = $employee_id;
                        $model_approval->layer = $data_matrix_approval->layer;
                        $model_approval->approval_status = 'Pending';

                        // Simpan data ke database
                        $model_approval->save();
                    }

                    // Simpan data ke database
                    $model_approval->save();
                }
                $ent->save();
            }
        }

        if ($statusValue !== 'Draft') {
            // Get manager email
            $managerEmail = Employee::where('employee_id', $managerL1)->pluck('email')->first();
            // $managerEmail = "eriton.dewa@kpn-corp.com";
            $group_company = Employee::where('id', $employee->id)->pluck('group_company')->first();

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Business Trip and waiting for your approval with the following details :";
            $managerName = Employee::where('employee_id', $managerL1)->pluck('fullname')->first();
            $isEnt = $request->ent === 'Ya';
            $isCa = $request->ca === 'Ya';

            if ($managerEmail) {
                $detail_ca_ntf = isset($detail_ca_ntf) ? $detail_ca_ntf : [];
                $detail_ent = isset($detail_ent) ? $detail_ent : [];
                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca_ntf['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca_ntf['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca_ntf['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => count($detail_ca_ntf['detail_meals'] ?? []),
                    'total_amount_meals' => array_sum(array_column($detail_ca_ntf['detail_meals'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_ent' => array_sum(array_column($detail_ent['detail_e'] ?? [], 'nominal')),
                ];
                // Fetch ticket and hotel details with proper conditions
                $ticketDetails = Tiket::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('tkt_only', '!=', 'Y')
                            ->orWhereNull('tkt_only'); // This handles the case where tkt_only is null
                    })
                    ->get();

                $hotelDetails = Hotel::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('hotel_only', '!=', 'Y')
                            ->orWhereNull('hotel_only'); // This handles the case where hotel_only is null
                    })
                    ->get();

                $messDetails = Mess::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('mess_only', '!=', 'Y')
                            ->orWhereNull('mess_only');
                    })
                    ->get();

                $taksiDetails = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();

                $approvalLink = route('approve.business.trip', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l1_id,
                    'status' => 'Pending L2'
                ]);

                $revisionLink = route('revision.link', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l1_id,
                    'status' => 'Request Revision',
                ]);

                $rejectionLink = route('reject.link', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l1_id,
                    'status' => 'Rejected'
                ]);


                // Send an email with the detailed business trip information
                try {
                    Mail::to($managerEmail)->send(new BusinessTripNotification(
                        $businessTrip,
                        $hotelDetails,
                        $ticketDetails,
                        $taksiDetails,
                        $caDetails,
                        $managerName,
                        $approvalLink,
                        $revisionLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                        $isEnt,
                        $isCa,
                        $entDetails,
                        $group_company,
                        $messDetails,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Create Business Trip tidak terkirim: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
                    Log::info('messDetails: ' . json_encode($messDetails));
                }
            }
        }
        return redirect()->route('businessTrip')->with('success', 'Request Successfully Added');
    }

    public function adminDivision(Request $request)
    {
        $user = Auth::user();

        $query = BusinessTrip::whereNotIn('status', ['Draft', 'Declaration Draft'])
            ->orderBy('created_at', 'desc');

        $filter = $request->input('filter', 'all');
        $division = $request->input('division');

        if ($division) {
            $query->whereHas('employee', function ($q) use ($division) {
                $q->where('divisi', $division);
            });
        }

        if ($filter === 'request') {
            $query->whereIn('status', ['Pending L1', 'Pending L2', 'Approved']);
        } elseif ($filter === 'declaration') {
            $query->whereIn('status', ['Declaration Approved', 'Declaration L1', 'Declaration L2', 'Approved']);
        } elseif ($filter === 'done') {
            $query->whereIn('status', ['Doc Accepted', 'Verified']);
        } elseif ($filter === 'return_refund') {
            $query->whereIn('status', ['Return/Refund']);
        } elseif ($filter === 'rejected') {
            $query->whereIn('status', ['Rejected', 'Declaration Rejected']);
        }

        $sppd = $query->get();

        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');
        $departments = Designation::select('department_name')->distinct()->get();

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();
        // Log::info('Ticket Approvals:', $btApprovals->toArray());

        $btApprovals = $btApprovals->keyBy('bt_id');
        // dd($btApprovals);
        // Log::info('BT Approvals:', $btApprovals->toArray());

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        // Related data
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');
        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $parentLink = 'Reimbursement';
        $link = 'Business Trip (Admin)';

        return view('hcis.reimbursements.businessTrip.btAdminDivison', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'managerL1Names', 'managerL2Names', 'filter', 'btApprovals', 'employeeName', 'departments', 'division'));
    }

    public function filterDivision(Request $request)
    {
        $user = Auth::user();
        $division = $request->input('division');

        $query = BusinessTrip::whereNotIn('status', ['Draft', 'Declaration Draft'])
            ->orderBy('created_at', 'desc');

        if ($division) {
            $query->where('divisi', 'LIKE', '%' . $division . '%');
        }

        $sppd = $query->get();

        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');
        $departments = Designation::select('department_name')->distinct()->get();
        // $departments = BusinessTrip::select('divisi')->distinct()->get();

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get()
            ->keyBy('bt_id');

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        // Related data
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');
        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $parentLink = 'Reimbursement';
        $link = 'Business Trip (Admin)';

        return view('hcis.reimbursements.businessTrip.btAdminDivison', compact(
            'sppd',
            'parentLink',
            'link',
            'caTransactions',
            'tickets',
            'hotel',
            'taksi',
            'managerL1Names',
            'managerL2Names',
            'btApprovals',
            'employeeName',
            'departments',
            'division'
        ));
    }

    public function exportExcelDivision(Request $request)
    {
        // Retrieve query parameters
        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');
        $division = $request->input('division'); // Get the division input

        // Initialize query builders
        $query = BusinessTrip::query();
        $queryCA = CATransaction::query();

        // Apply filters if both dates are present
        if ($startDate && $endDate) {
            $query->whereBetween('mulai', [$startDate, $endDate]);
        }

        // Apply division filter if it is selected
        if ($division) {
            $query->where('divisi', 'LIKE', '%' . $division . '%');
        }
        // Exclude drafts
        $query->where(function ($subQuery) {
            $subQuery->where('status', '<>', 'draft')
                ->where('status', '<>', 'declaration draft'); // Adjust if 'declaration draft' is the exact status name
        });
        $queryCA->where('approval_status', '<>', 'draft'); // Adjust 'status' and 'draft' as needed

        // Fetch the filtered BusinessTrip data
        $businessTrips = $query->get();

        // Extract the no_sppd values from the filtered BusinessTrip records
        $noSppds = $businessTrips->pluck('no_sppd')->unique();

        // Fetch CA data where no_sppd matches the filtered BusinessTrip records
        $caData = $queryCA->whereIn('no_sppd', $noSppds)->get();

        // Pass the filtered data to the export class
        return Excel::download(new BusinessTripExport($businessTrips, $caData), 'Data_Perjalanan_Dinas.xlsx');
    }

    public function exportPdfDivision(Request $request)
    {
        // Retrieve query parameters
        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');
        $division = $request->input('division'); // Get the division input

        // Initialize query builders
        $query = BusinessTrip::query();

        // Apply filters if both dates are present
        if ($startDate && $endDate) {
            $query->whereBetween('mulai', [$startDate, $endDate]);
        }

        // Apply division filter if it is selected
        if ($division) {
            $query->where('divisi', 'LIKE', '%' . $division . '%');
        }

        // Exclude drafts and specifically 'Declaration Draft'
        $query->where(function ($subQuery) {
            $subQuery->where('status', '<>', 'draft')
                ->where('status', '<>', 'declaration draft');
        });

        // Fetch the filtered BusinessTrip data
        $businessTrips = $query->get();

        // Generate PDF
        $pdf = PDF::loadView('hcis.reimbursements.businessTrip.division-pdf', ['businessTrips' => $businessTrips]);

        // Return PDF as a download
        return $pdf->stream('Data_Perjalanan_Dinas.pdf');
    }

    public function admin(Request $request)
    {
        $user = Auth::user();

        $query = BusinessTrip::whereNotIn('status', ['Draft', 'Declaration Draft'])
            ->orderBy('created_at', 'desc');

        $filter = $request->input('filter', 'all');

        if ($filter === 'request') {
            $query->whereIn('status', ['Pending L1', 'Pending L2', 'Approved']);
        } elseif ($filter === 'declaration') {
            $query->whereIn('status', ['Declaration Approved', 'Declaration L1', 'Declaration L2', 'Approved']);
        } elseif ($filter === 'done') {
            $query->whereIn('status', ['Doc Accepted', 'Verified']);
        } elseif ($filter === 'return_refund') {
            $query->whereIn('status', ['Return/Refund']);
        } elseif ($filter === 'rejected') {
            $query->whereIn('status', ['Rejected', 'Declaration Rejected']);
        }

        $permissionLocations = $this->permissionLocations;
        $permissionCompanies = $this->permissionCompanies;
        $permissionGroupCompanies = $this->permissionGroupCompanies;

        if (!empty($permissionLocations)) {
            $query->whereHas('employee', function ($query) use ($permissionLocations) {
                $query->whereIn('work_area_code', $permissionLocations);
            });
        }

        if (!empty($permissionCompanies)) {
            $query->whereIn('bb_perusahaan', $permissionCompanies);
        }

        if (!empty($permissionGroupCompanies)) {
            $query->whereHas('employee', function ($query) use ($permissionGroupCompanies) {
                $query->whereIn('group_company', $permissionGroupCompanies);
            });
        }

        $sppd = $query->get();

        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();

        $btApprovals = $btApprovals->keyBy('bt_id');
        // dd($btApprovals);
        // Log::info('BT Approvals:', $btApprovals->toArray());

        $btApproved = BTApproval::whereIn('bt_id', $btIds)->get();

        // dd($btIds, $btApproved);

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        // Related data
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $mess = Mess::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');
        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $parentLink = 'Reimbursement';
        $link = 'Business Trip (Admin)';

        return view('hcis.reimbursements.businessTrip.btAdmin', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'managerL1Names', 'managerL2Names', 'filter', 'btApprovals', 'employeeName', 'btApproved', 'mess'));
    }
    public function filterDateAdmin(Request $request)
    {

        $query = BusinessTrip::whereNotIn('status', ['Draft', 'Declaration Draft'])
            ->orderBy('created_at', 'desc');

        $filter = $request->input('filter', 'all');
        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');


        if ($filter === 'request') {
            $query->whereIn('status', ['Pending L1', 'Pending L2', 'Approved']);
        } elseif ($filter === 'declaration') {
            $query->whereIn('status', ['Declaration Approved', 'Declaration L1', 'Declaration L2']);
        } elseif ($filter === 'done') {
            $query->whereIn('status', ['Doc Accepted', 'Verified']);
        } elseif ($filter === 'return_refund') {
            $query->whereIn('status', ['Return/Refund']);
        } elseif ($filter === 'rejected') {
            $query->whereIn('status', ['Rejected', 'Declaration Rejected']);
        }

        $permissionLocations = $this->permissionLocations;
        $permissionCompanies = $this->permissionCompanies;
        $permissionGroupCompanies = $this->permissionGroupCompanies;

        if (!empty($permissionLocations)) {
            $query->whereHas('employee', function ($query) use ($permissionLocations) {
                $query->whereIn('work_area_code', $permissionLocations);
            });
        }

        if (!empty($permissionCompanies)) {
            $query->whereIn('contribution_level_code', $permissionCompanies);
        }

        if (!empty($permissionGroupCompanies)) {
            $query->whereHas('employee', function ($query) use ($permissionGroupCompanies) {
                $query->whereIn('group_company', $permissionGroupCompanies);
            });
        }

        $sppd = $query->get();

        $sppdNos = $sppd->pluck('no_sppd');
        $btIds = $sppd->pluck('id');
        // Retrieve the start and end dates from the request
        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();

        $employeeIds = $sppd->pluck('user_id')->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        // Fetch related data based on the filtered SPPD numbers
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $mess = Mess::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        $managerL1Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l1_id'))->pluck('fullname', 'employee_id');
        $managerL2Names = Employee::whereIn('employee_id', $sppd->pluck('manager_l2_id'))->pluck('fullname', 'employee_id');

        $btApprovals = BTApproval::whereIn('bt_id', $btIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected')
                    ->orWhere('approval_status', 'Declaration Rejected');
            })
            ->get();
        // Log::info('Ticket Approvals:', $btApprovals->toArray());

        $btApprovals = $btApprovals->keyBy('bt_id');
        $btApproved = BTApproval::whereIn('bt_id', $btIds)->get();

        if ($startDate && $endDate) {
            $query->whereBetween('mulai', [$startDate, $endDate]);
        }
        // dd($startDate, $endDate);
        $sppd = $query->orderBy('created_at', 'desc')->get();

        $parentLink = 'Reimbursement';
        $link = 'Business Trip (Admin)';

        return view('hcis.reimbursements.businessTrip.btAdmin', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'managerL1Names', 'managerL2Names', 'filter', 'btApprovals', 'employeeName', 'btApproved'));
    }
    public function deklarasiAdmin($id)
    {
        $n = BusinessTrip::find($id);
        $userId = $n->user_id;
        $employee_data = Employee::where('id', $n->user_id)->first();

        if ($employee_data->group_company == 'Plantations' || $employee_data->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }
        $group_company = Employee::where('id', $employee_data->id)->pluck('group_company')->first();
        $ca = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $date = CATransaction::where('no_sppd', $n->no_sppd)->first();
        $dns = $ca->where('type_ca', 'dns')->first();
        $entr = $ca->where('type_ca', 'entr')->first();

        $job_level = Employee::where('id', $userId)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);

        $entrTab = $entr ? true : false;
        $dnsTab = $dns ? true : false;

        $entrData = null;
        $dnsData = null;

        foreach ($ca as $item) {
            if ($item->type_ca == 'entr' && !$entrData) {
                $entrData = $item; // Ambil data entr hanya jika belum ada
            } elseif ($item->type_ca == 'dns' && !$dnsData) {
                $dnsData = $item; // Ambil data dns hanya jika belum ada
            }

            // Jika sudah mendapatkan kedua tipe, keluar dari loop
            if ($entrData && $dnsData) {
                break;
            }
        }

        // Initialize caDetail with an empty array if it's null
        $caDetail = [];
        $declareCa = [];
        foreach ($ca as $cas) {
            $currentDetail = json_decode($cas->detail_ca, true);
            $currentDeclare = json_decode($cas->declare_ca, true);
            if (is_array($currentDetail)) {
                $caDetail = array_merge($caDetail, $currentDetail);
                $declareCa = array_merge($declareCa, $currentDeclare);
            }
        }

        // Safely access nominalPerdiem with default '0' if caDetail is empty
        $nominalPerdiem = isset($caDetail['detail_perdiem'][0]['nominal']) ? $caDetail['detail_perdiem'][0]['nominal'] : '0';
        $nominalPerdiemDeclare = isset($declareCa['detail_perdiem'][0]['nominal']) ? $declareCa['detail_perdiem'][0]['nominal'] : '0';

        $hasCaData = $ca !== null;
        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();

        // Retrieve all hotels for the specific BusinessTrip
        $hotels = Hotel::where('no_sppd', $n->no_sppd)->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)
            ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')->first();

        // Prepare hotel data for the view
        $hotelData = [];
        foreach ($hotels as $index => $hotel) {
            $hotelData[] = [
                'nama_htl' => $hotel->nama_htl,
                'lokasi_htl' => $hotel->lokasi_htl,
                'jmlkmr_htl' => $hotel->jmlkmr_htl,
                'bed_htl' => $hotel->bed_htl,
                'tgl_masuk_htl' => $hotel->tgl_masuk_htl,
                'tgl_keluar_htl' => $hotel->tgl_keluar_htl,
                'total_hari' => $hotel->total_hari,
                'more_htl' => ($index < count($hotels) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve all tickets for the specific BusinessTrip
        $tickets = Tiket::where('no_sppd', $n->no_sppd)->get();

        // Prepare ticket data for the view
        $ticketData = [];
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'noktp_tkt' => $ticket->noktp_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < count($tickets) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve locations and companies data for the dropdowns
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();

        $parentLink = 'Business Trip Admin';
        $link = 'Declaration Business Trip (Admin)';

        return view('hcis.reimbursements.businessTrip.deklarasiAdmin', [
            'n' => $n,
            'allowance' => $allowance,
            'hotelData' => $hotelData,
            'taksiData' => $taksi, // Pass the taxi data
            'ticketData' => $ticketData,
            'employee_data' => $employee_data,
            'companies' => $companies,
            'locations' => $locations,
            'caDetail' => $caDetail,
            'declareCa' => $declareCa,
            'entrData' => $entrData,
            'dnsData' => $dnsData,
            'entrTab' => $entrTab,
            'dnsTab' => $dnsTab,
            'date' => $date,
            'ca' => $ca,
            'nominalPerdiem' => $nominalPerdiem,
            'nominalPerdiemDeclare' => $nominalPerdiemDeclare,
            'hasCaData' => $hasCaData,
            'perdiem' => $perdiem,
            'group_company' => $group_company,
            'job_level_number' => $job_level_number,
            'parentLink' => $parentLink,
            'link' => $link,
        ]);
    }
    public function deklarasiStatusAdmin(Request $request, $id)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $oldNoSppd = $n->no_sppd;
        $companies = Company::orderBy('contribution_level')->get();
        $caRecords = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $dnsRecord = $caRecords->where('type_ca', 'dns')->first();
        $entrRecord = $caRecords->where('type_ca', 'entr')->first();

        $entrTab = $entrRecord ? true : false;
        $dnsTab = $dnsRecord ? true : false;

        $accNum = Company::where('contribution_level_code', $n->bb_perusahaan)->pluck('account_number')->first();
        $ca_note = $request->ca_note;
        $employeeEmail = null;

        // Initialize default values

        if ($caRecords) {
            foreach ($caRecords as $ca) {
                // Assign or update values to $ca model
                if ($ca->type_ca == "dns") {
                    $ca->user_id = $userId;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->ca_note = $ca_note;
                    $ca->user_id = $userId;

                    $ca->declaration_at = Carbon::now();

                    $total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                    $total_ca = $ca->total_ca;
                    if ($ca->detail_ca === null) {
                        $ca->total_ca = '0';
                        $ca->total_real = (int) str_replace('.', '', $request->totalca_ca_deklarasi);
                        $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);
                    } else {
                        $ca->total_real = $total_real;
                        $ca->total_cost = $total_ca - $total_real;
                    }

                    // Initialize arrays for details
                    $detail_perdiem = [];
                    $detail_meals = [];
                    $detail_transport = [];
                    $detail_penginapan = [];
                    $detail_lainnya = [];

                    if ($request->has('start_bt_meals')) {
                        foreach ($request->start_bt_meals as $key => $startDate) {
                            $endDate = $request->end_bt_meals[$key] ?? '';
                            $totalDays = $request->total_days_bt_meals[$key] ?? '';
                            $companyCode = $request->company_bt_meals[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_meals[$key] ?? '0');
                            $keterangan = $request->keterangan_bt_meals[$key] ?? '';

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($companyCode) && !empty($nominal)) {
                                $detail_meals[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'keterangan' => $keterangan,
                                ];
                            }
                        }
                    }
                    // dd($request->has('start_bt_meals'));

                    // Populate detail_perdiem
                    if ($request->has('start_bt_perdiem')) {
                        foreach ($request->start_bt_perdiem as $key => $startDate) {
                            $endDate = $request->end_bt_perdiem[$key] ?? '';
                            $totalDays = $request->total_days_bt_perdiem[$key] ?? '';
                            $location = $request->location_bt_perdiem[$key] ?? '';
                            $other_location = $request->other_location_bt_perdiem[$key] ?? '';
                            $companyCode = $request->company_bt_perdiem[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_perdiem[$key] ?? '0');

                            if (!empty($startDate) && !empty($endDate) && !empty($companyCode) && !empty($nominal)) {
                                $detail_perdiem[] = [
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
                    }
                    // dd($detail_perdiem);

                    // Populate detail_transport
                    if ($request->has('tanggal_bt_transport')) {
                        foreach ($request->tanggal_bt_transport as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_transport[$key] ?? '';
                            $companyCode = $request->company_bt_transport[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_transport[$key] ?? '0');

                            if (!empty($tanggal) && !empty($companyCode) && !empty($nominal)) {
                                $detail_transport[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }
                    // dd($detail_transport);

                    // Populate detail_penginapan
                    if ($request->has('start_bt_penginapan')) {
                        foreach ($request->start_bt_penginapan as $key => $startDate) {
                            $endDate = $request->end_bt_penginapan[$key] ?? '';
                            $totalDays = $request->total_days_bt_penginapan[$key] ?? '';
                            $hotelName = $request->hotel_name_bt_penginapan[$key] ?? '';
                            $companyCode = $request->company_bt_penginapan[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_penginapan[$key] ?? '0');
                            $totalPenginapan = str_replace('.', '', $request->total_bt_penginapan[$key] ?? '0');

                            if (!empty($startDate) && !empty($endDate) && !empty($totalDays) && !empty($hotelName) && !empty($companyCode) && !empty($nominal)) {
                                $detail_penginapan[] = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                    'total_days' => $totalDays,
                                    'hotel_name' => $hotelName,
                                    'company_code' => $companyCode,
                                    'nominal' => $nominal,
                                    'totalPenginapan' => $totalPenginapan,
                                ];
                            }
                        }
                    }

                    // Populate detail_lainnya
                    if ($request->has('tanggal_bt_lainnya')) {
                        foreach ($request->tanggal_bt_lainnya as $key => $tanggal) {
                            $keterangan = $request->keterangan_bt_lainnya[$key] ?? '';
                            $nominal = str_replace('.', '', $request->nominal_bt_lainnya[$key] ?? '0');
                            $totalLainnya = str_replace('.', '', $request->total_bt_lainnya[$key] ?? '0');

                            if (!empty($tanggal) && !empty($nominal)) {
                                $detail_lainnya[] = [
                                    'tanggal' => $tanggal,
                                    'keterangan' => $keterangan,
                                    'nominal' => $nominal,
                                    'totalLainnya' => $totalLainnya,
                                ];
                            }
                        }
                    }

                    // Save the details
                    $declare_ca = [
                        'detail_perdiem' => $detail_perdiem,
                        'detail_meals' => $detail_meals,
                        'detail_transport' => $detail_transport,
                        'detail_penginapan' => $detail_penginapan,
                        'detail_lainnya' => $detail_lainnya,
                    ];

                    $ca->declare_ca = json_encode($declare_ca);

                    if ($ca->total_cost <= 0 && $request->input('accept_status') === 'Return/Refund') {
                        return redirect()->back()->with('error', 'Cannot set status to Return/Refund when the Total Cost is negative.');
                    } elseif ($ca->total_cost > 0 && $request->input('accept_status') === 'Return/Refund') {
                        $employeeEmail = Employee::where('id', $n->user_id)->pluck('email')->first();
                        // $employeeEmail = "erzie.aldrian02@gmail.com";
                        $employeeName = Employee::where('id', $n->user_id)->pluck('fullname')->first();
                    }

                } elseif ($ca->type_ca == "entr") {
                    $ca->user_id = $userId;
                    $ca->no_sppd = $oldNoSppd;
                    $ca->user_id = $userId;
                    $ca->ca_note = $ca_note;

                    $ca->declaration_at = Carbon::now();

                    $total_real = (int) str_replace('.', '', $request->totalca);
                    // dd($total_real);
                    $total_ca = $ca->total_ca;
                    if ($ca->detail_ca === null) {
                        $ca->total_ca = '0';
                        $ca->total_real = (int) str_replace('.', '', $request->totalca);
                        $ca->total_cost = -1 * (int) str_replace('.', '', $ca->total_real);
                    } else {
                        $ca->total_real = $total_real;
                        $ca->total_cost = $total_ca - $total_real;
                    }

                    // Ini AWAL
                    $detail_e = [];
                    $relation_e = [];

                    if ($request->has('enter_type_e_detail')) {
                        foreach ($request->enter_type_e_detail as $key => $type) {
                            $fee_detail = $request->enter_fee_e_detail[$key];
                            $nominal = str_replace('.', '', $request->nominal_e_detail[$key]); // Menghapus titik dari nominal sebelum menyimpannya

                            if (!empty($type) && !empty($nominal)) {
                                $detail_e[] = [
                                    'type' => $type,
                                    'fee_detail' => $fee_detail,
                                    'nominal' => $nominal,
                                ];
                            }
                        }
                    }

                    // Mengumpulkan detail relation
                    if ($request->has('rname_e_relation')) {
                        foreach ($request->rname_e_relation as $key => $name) {
                            $position = $request->rposition_e_relation[$key];
                            $company = $request->rcompany_e_relation[$key];
                            $purpose = $request->rpurpose_e_relation[$key];

                            // Memastikan semua data yang diperlukan untuk relation terisi
                            if (!empty($name) && !empty($position) && !empty($company) && !empty($purpose)) {
                                $relation_e[] = [
                                    'name' => $name,
                                    'position' => $position,
                                    'company' => $company,
                                    'purpose' => $purpose,
                                    'relation_type' => array_filter([
                                        'Food' => !empty($request->food_e_relation[$key]) && $request->food_e_relation[$key] === 'food',
                                        'Transport' => !empty($request->transport_e_relation[$key]) && $request->transport_e_relation[$key] === 'transport',
                                        'Accommodation' => !empty($request->accommodation_e_relation[$key]) && $request->accommodation_e_relation[$key] === 'accommodation',
                                        'Gift' => !empty($request->gift_e_relation[$key]) && $request->gift_e_relation[$key] === 'gift',
                                        'Fund' => !empty($request->fund_e_relation[$key]) && $request->fund_e_relation[$key] === 'fund',
                                    ], fn($checked) => $checked),
                                ];
                            }
                            // dd($relation_e);
                        }
                    }

                    // Save the details
                    $declare_ca = [
                        'detail_e' => $detail_e,
                        'relation_e' => $relation_e,
                    ];
                    // Ini Akihit

                    $ca->declare_ca = json_encode($declare_ca);

                    if ($ca->total_cost <= 0 && $request->input('accept_status') === 'Return/Refund') {
                        return redirect()->back()->with('error', 'Cannot set status to Return/Refund when the Total Cost is negative.');
                    }
                    if ($ca->total_cost > 0 && $request->input('accept_status') === 'Return/Refund') {
                        $employeeEmail = Employee::where('id', $n->user_id)->pluck('email')->first();
                        // $employeeEmail = "erzie.aldrian02@gmail.com";
                        $employeeName = Employee::where('id', $n->user_id)->pluck('fullname')->first();
                    }
                }
                $ca->save();
            }

            if ($employeeEmail) {
                $caTrans = CATransaction::where('no_sppd', $n->no_sppd)
                    ->where(function ($query) {
                        $query->where('caonly', '!=', 'Y')
                            ->orWhereNull('caonly');
                    })
                    ->get();

                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                $dnsNtfRe = $caTrans->where('type_ca', 'dns')->first();
                $entrNtfRe = $caTrans->where('type_ca', 'entr')->first();
                $isCa = $dnsNtfRe ? true : false;
                $isEnt = $entrNtfRe ? true : false;
                // dd($caTrans);
                $detail_ca_req = isset($dnsNtfRe) && isset($dnsNtfRe->detail_ca) ? json_decode($dnsNtfRe->detail_ca, true) : [];
                $detail_ent_req = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->detail_ca, true) : [];

                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca_req['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca_req['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca_req['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca_req['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'total_days')),
                    'total_amount_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_detailent' => array_sum(array_column($detail_ent_req['detail_e'] ?? [], 'nominal')),
                ];
                // dd($caDetails,   $detail_ca );

                $declare_ca_ntf = isset($dnsNtfRe) && isset($dnsNtfRe->detail_ca) ? json_decode($dnsNtfRe->declare_ca, true) : [];
                $declare_ent_ntf = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->declare_ca, true) : [];
                $caDeclare = [
                    'total_days_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($declare_ca_ntf['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($declare_ca_ntf['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($declare_ca_ntf['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($declare_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'total_days')),
                    'total_amount_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'nominal')),
                ];
                // dd($caDeclare);
                $entDeclare = [
                    'total_amount_ent' => array_sum(array_column($declare_ent_ntf['detail_e'] ?? [], 'nominal')),
                ];

                $selisihCa = array_sum($caDetails) - array_sum($caDeclare);
                $selisihEnt = array_sum($entDetails) - array_sum($entDeclare);
                // dd($newDeclareCa, $selisih);

                // Send email to the manager
                try {
                    Mail::to($employeeEmail)->send(new RefundNotification(
                        $n,
                        $caDetails,
                        $caDeclare,
                        $entDetails,
                        $entDeclare,
                        $employeeName,
                        $accNum,
                        $selisihCa,
                        $selisihEnt,
                        $isCa,
                        $isEnt,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Deklarasi Status Admin Business Trip tidak terkirim: ' . $e->getMessage());
                }
            }
        }

        $n->status = $request->input('accept_status');
        $n->save();


        return redirect('/businessTrip/admin')->with('success', 'Status updated successfully');
    }


    public function exportExcel(Request $request)
    {
        // Retrieve query parameters
        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');

        // Initialize query builders
        $query = BusinessTrip::query();
        $queryCA = CATransaction::query();

        // Apply filters if both dates are present
        if ($startDate && $endDate) {
            $query->whereBetween('mulai', [$startDate, $endDate]);
        }

        // Exclude drafts
        $query->where(function ($subQuery) {
            $subQuery->where('status', '<>', 'draft')
                ->where('status', '<>', 'declaration draft');
        });
        $queryCA->where('approval_status', '<>', 'draft'); // Adjust 'status' and 'draft' as needed

        // Fetch the filtered BusinessTrip data
        $businessTrips = $query->get();

        // Extract the no_sppd values from the filtered BusinessTrip records
        $noSppds = $businessTrips->pluck('no_sppd')->unique();

        // Fetch CA data where no_sppd matches the filtered BusinessTrip records
        $caData = $queryCA->whereIn('no_sppd', $noSppds)->get();

        // Pass the filtered data to the export class
        return Excel::download(new BusinessTripExport($businessTrips, $caData), 'Data_Perjalanan_Dinas.xlsx');
    }

    public function approval(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();
        $employeeId = auth()->user()->employee_id;
        $employee = Employee::where('id', $userId)->first();  // Authenticated user's employee record

        $bt_all = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->whereIn('status', ['Pending L1', 'Declaration L1']);
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->whereIn('status', ['Pending L2', 'Declaration L2']);
            });
        })->orderBy('created_at', 'desc')
            ->get();
        // $sppd_all = BusinessTrip::orderBy('created_at', 'desc')->get();

        $bt_request = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->where('status', 'Pending L1');
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->where('status', 'Pending L2');
            });
        })->orderBy('created_at', 'desc')
            ->get();

        $bt_declaration = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->where('status', 'Declaration L1');
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->where('status', 'Declaration L2');
            });
        })->orderBy('created_at', 'desc')
            ->get();

        // Count only "Request" status (Pending L1 and L2)
        $requestCount = $bt_request->count();
        $declarationCount = $bt_declaration->count();
        $totalBTCount = $requestCount + $declarationCount;
        $totalPendingCount = CATransaction::where(function ($query) use ($employeeId) {
            $query->where('status_id', $employeeId)->where('approval_status', 'Pending')
                ->orWhere('sett_id', $employeeId)->where('approval_sett', 'Pending')
                ->orWhere('extend_id', $employeeId)->where('approval_extend', 'Pending');
        })->count();
        $ticketNumbers = Tiket::where('tkt_only', 'Y')
            ->where('approval_status', '!=', 'Draft')
            ->pluck('no_tkt')->unique();
        $transactions_tkt = Tiket::whereIn('no_tkt', $ticketNumbers)
            ->with('businessTrip')
            ->orderBy('created_at', 'desc')
            ->get();
        $totalTKTCount = $transactions_tkt->filter(function ($ticket) use ($employee) {
            $ticketOwnerEmployee = Employee::where('id', $ticket->user_id)->first();
            return ($ticket->approval_status == 'Pending L1' && $ticketOwnerEmployee->manager_l1_id == $employee->employee_id) ||
                ($ticket->approval_status == 'Pending L2' && $ticketOwnerEmployee->manager_l2_id == $employee->employee_id);
        })->count();

        $hotelNumbers = Hotel::where('hotel_only', 'Y')
            ->where('approval_status', '!=', 'Draft')
            ->pluck('no_htl')->unique();

        // Fetch all tickets using the latestTicketIds
        $transactions_htl = Hotel::whereIn('no_htl', $hotelNumbers)
            ->with('businessTrip')
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter tickets based on manager and approval status
        $hotels = $transactions_htl->filter(function ($hotel) use ($employee) {
            // Get the employee who owns the ticket
            $ticketOwnerEmployee = Employee::where('id', $hotel->user_id)->first();

            if ($hotel->approval_status == 'Pending L1' && $ticketOwnerEmployee->manager_l1_id == $employee->employee_id) {
                return true;
            } elseif ($hotel->approval_status == 'Pending L2' && $ticketOwnerEmployee->manager_l2_id == $employee->employee_id) {
                return true;
            }
            return false;
        });

        $totalHTLCount = $hotels->count();

        // Check if the user has approval rights
        $hasApprovalRights = DB::table('master_bisnisunits')
            ->where('approval_medical', $employee->employee_id)
            ->where('nama_bisnis', $employee->group_company)
            ->exists();

        if ($hasApprovalRights) {
            $medicalGroup = HealthCoverage::select(
                'no_medic',
                'date',
                'period',
                'hospital_name',
                'patient_name',
                'disease',
                DB::raw('SUM(CASE WHEN medical_type = "Maternity" THEN balance_verif ELSE 0 END) as maternity_balance_verif'),
                DB::raw('SUM(CASE WHEN medical_type = "Inpatient" THEN balance_verif ELSE 0 END) as inpatient_balance_verif'),
                DB::raw('SUM(CASE WHEN medical_type = "Outpatient" THEN balance_verif ELSE 0 END) as outpatient_balance_verif'),
                DB::raw('SUM(CASE WHEN medical_type = "Glasses" THEN balance_verif ELSE 0 END) as glasses_balance_verif'),
                'status'
            )
                ->whereNotNull('verif_by')   // Only include records where verif_by is not null
                ->whereNotNull('balance_verif')
                ->where('status', 'Pending')
                ->groupBy('no_medic', 'date', 'period', 'hospital_name', 'patient_name', 'disease', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            // Add usage_id for each medical record without filtering by employee_id
            $medical = $medicalGroup->map(function ($item) {
                // Fetch the usage_id based on no_medic (for any employee)
                $usageId = HealthCoverage::where('no_medic', $item->no_medic)->value('usage_id');
                $item->usage_id = $usageId;

                // Calculate total per no_medic
                $item->total_per_no_medic = $item->maternity_balance_verif + $item->inpatient_balance_verif + $item->outpatient_balance_verif + $item->glasses_balance_verif;

                return $item;
            });
        } else {
            $medical = collect(); // Empty collection if user doesn't have approval rights
        }

        $totalMDCCount = $medical->count();

        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $bt_all->pluck('no_sppd');

        // Retrieve related data based on the collected SPPD numbers
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $mess = Mess::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        $parentLink = 'Approval';
        $link = 'Business Trip';

        return view('hcis.reimbursements.businessTrip.btApproval', compact('bt_all', 'bt_request', 'bt_declaration', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'requestCount', 'declarationCount', 'totalBTCount', 'totalPendingCount', 'totalTKTCount', 'totalHTLCount', 'totalMDCCount', 'mess'));
    }
    public function approvalDetail($id)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $employee_data = Employee::where('id', $n->user_id)->first();
        $employees = Employee::orderBy('ktp')->get();
        $group_company = Employee::where('id', $employee_data->id)->pluck('group_company')->first();
        $bt_sppd = BusinessTrip::where('status', '!=', 'Done')->where('status', '!=', 'Rejected')->where('status', '!=', 'Draft')->orderBy('no_sppd', 'desc')->get();
        $job_level = Employee::where('id', $employee_data)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);

        if ($employee_data->group_company == 'Plantations' || $employee_data->group_company == 'KPN Plantations') {
            $allowance = "Perdiem";
        } else {
            $allowance = "Allowance";
        }

        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();
        $taksiLuarKota = null;
        $taksiDalamKota = null;

        if ($n->jns_dinas === 'luar kota') {
            $taksiLuarKota = $taksi;
        } else if ($n->jns_dinas === 'dalam kota') {
            $taksiDalamKota = $taksi;
        }

        $ca = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $date = CATransaction::where('no_sppd', $n->no_sppd)->first();
        // Initialize caDetail with an empty array if it's null
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)
            ->where('bisnis_unit', 'like', '%' . $employee_data->group_company . '%')->first();

        // Retrieve all hotels for the specific BusinessTrip
        $hotels = Hotel::where('no_sppd', $n->no_sppd)->get();

        $caDetail = [];
        foreach ($ca as $cas) {
            $currentDetail = json_decode($cas->detail_ca, true);
            if (is_array($currentDetail)) {
                $caDetail = array_merge($caDetail, $currentDetail);
            }
        }
        // Safely access nominalPerdiem with default '0' if caDetail is empty
        $nominalPerdiem = isset($caDetail['detail_perdiem'][0]['nominal']) ? $caDetail['detail_perdiem'][0]['nominal'] : '0';

        // Prepare hotel data for the view
        $hotelData = [];
        foreach ($hotels as $index => $hotel) {
            $hotelData[] = [
                'nama_htl' => $hotel->nama_htl,
                'lokasi_htl' => $hotel->lokasi_htl,
                'jmlkmr_htl' => $hotel->jmlkmr_htl,
                'bed_htl' => $hotel->bed_htl,
                'tgl_masuk_htl' => $hotel->tgl_masuk_htl,
                'tgl_keluar_htl' => $hotel->tgl_keluar_htl,
                'total_hari' => $hotel->total_hari,
                'no_sppd_htl' => $hotel->no_sppd_htl,
                'more_htl' => ($index < count($hotels) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve all tickets for the specific BusinessTrip
        $tickets = Tiket::where('no_sppd', $n->no_sppd)->get();

        // Prepare ticket data for the view
        $ticketData = [];
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'noktp_tkt' => $ticket->noktp_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < count($tickets) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        $messes = Mess::where('no_sppd', $n->no_sppd)->get();
        $messData = [];
        foreach ($messes as $index => $mess) {
            $messData[] = [
                'lokasi_mess' => $mess->lokasi_mess,
                'jmlkmr_mess' => $mess->jmlkmr_mess,
                'tgl_masuk_mess' => $mess->tgl_masuk_mess,
                'tgl_keluar_mess' => $mess->tgl_keluar_mess,
                'total_hari_mess' => $mess->total_hari_mess,
            ];
        }

        // Retrieve locations and companies data for the dropdowns
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();
        // dd($taksi->toArray());

        $parentLink = 'Business Trip Approval';
        $link = 'Approval Details';

        return view('hcis.reimbursements.businessTrip.btApprovalDetail', [
            'n' => $n,
            'group_company' => $group_company,
            'allowance' => $allowance,
            'hotelData' => $hotelData,
            'taksiData' => $taksi, // Pass the taxi data
            'ticketData' => $ticketData,
            'employee_data' => $employee_data,
            'companies' => $companies,
            'locations' => $locations,
            'caDetail' => $caDetail,
            'ca' => $ca,
            'date' => $date,
            'nominalPerdiem' => $nominalPerdiem,
            'employees' => $employees,
            'parentLink' => $parentLink,
            'link' => $link,
            'perdiem' => $perdiem,
            'group_company' => $group_company,
            'taksiLuarKota' => $taksiLuarKota,
            'taksiDalamKota' => $taksiDalamKota,
            'bt_sppd' => $bt_sppd,
            'job_level_number' => $job_level_number,
            'messData' => $messData,
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);

        // Determine the new status and layer based on the action and manager's role
        $action = $request->input('status_approval');
        $revisiInfo = $request->input('revisi_info');
        $rejectInfo = $request->input('reject_info');
        if ($action == 'Request Revision') {
            $statusValue = 'Request Revision';
            if ($employeeId == $businessTrip->manager_l1_id) {
                $layer = 1;
            } elseif ($employeeId == $businessTrip->manager_l2_id) {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y') {
                        // Update CA approval status for L1 or L2 as Rejected
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Rejected', 'approved_at' => now(), 'reject_info' => $revisiInfo] // Save rejection info
                        );

                        $caTransactions->update(['approval_status' => 'Revision']);
                    }
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->reject_info = $revisiInfo;
                        $approval_tkt->save();
                    }
                }
            }
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->reject_info = $revisiInfo;
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->reject_info = $revisiInfo;
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->reject_info = $revisiInfo;
                    $approval_vt->save();
                }
            }
        } elseif ($action == 'Rejected') {
            $statusValue = 'Rejected';
            if ($employeeId == $businessTrip->manager_l1_id) {
                $layer = 1;
            } elseif ($employeeId == $businessTrip->manager_l2_id) {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y') {
                        // Update CA approval status for L1 or L2 as Rejected
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => $statusValue, 'approved_at' => now(), 'reject_info' => $rejectInfo] // Save rejection info
                        );

                        $caTransactions->update(['approval_status' => 'Rejected']);
                    }
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->reject_info = $rejectInfo;
                        $approval_tkt->save();
                    }
                }
            }
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->reject_info = $rejectInfo;
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->reject_info = $rejectInfo;
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->reject_info = $rejectInfo;
                    $approval_vt->save();
                }
            }
        } elseif ($businessTrip->manager_l2_id == '-') {
            $statusValue = 'Approved';
            $layer = 1;
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->save();
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $layer; // Determine layer based on status
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->save();
                    }
                }
            }
            // Handle CA approval for L2
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y') {
                        // Update CA approval status for L2
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => now()]
                        );

                        // Find the next approver (Layer 3) explicitly
                        $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->status_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_status' => 'Approved']);
                        }
                    }
                }
            }
        } elseif ($employeeId == $businessTrip->manager_l1_id) {
            $statusValue = 'Pending L2';
            $layer = 1;
            $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
            // $managerL2 = "erzie.aldrian02@gmail.com";
            $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();
            $group_company = Employee::where('id', $businessTrip->user_id)->pluck('group_company')->first();

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $employeeName = Employee::where('id', $businessTrip->user_id)->pluck('fullname')->first();
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Business Trip and waiting for your approval with the following details :";
            $isEnt = $request->ent === 'Ya';
            $isCa = $request->ca === 'Ya';

            // dd($managerL2);
            if ($managerL2) {
                $ca = CATransaction::where('no_sppd', $businessTrip->no_sppd)->orWhere('caonly', '!=', 'Y')->first();
                $detail_ca = $ca ? json_decode($ca->detail_ca, true) : [];
                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca['detail_lainnya'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_ent' => array_sum(array_column($detail_ent['detail_e'] ?? [], 'nominal')),
                ];
                // Fetch ticket and hotel details with proper conditions
                $ticketDetails = Tiket::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('tkt_only', '!=', 'Y')
                            ->orWhereNull('tkt_only'); // This handles the case where tkt_only is null
                    })
                    ->get();

                $hotelDetails = Hotel::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('hotel_only', '!=', 'Y')
                            ->orWhereNull('hotel_only'); // This handles the case where hotel_only is null
                    })
                    ->get();
                $messDetails = Mess::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('mess_only', '!=', 'Y')
                            ->orWhereNull('mess_only'); // This handles the case where hotel_only is null
                    })
                    ->get();

                $taksiDetails = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                // dd($taksiDetails);
                $approvalLink = route('approve.business.trip', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Approved',
                ]);

                $revisionLink = route('revision.link', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Request Revision',
                ]);

                $rejectionLink = route('reject.link', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Rejected',
                ]);

                // Send an email with the detailed business trip information
                try {
                    Mail::to($managerL2)->send(new BusinessTripNotification(
                        $businessTrip,
                        $hotelDetails,
                        $ticketDetails,
                        $taksiDetails,
                        $caDetails,
                        $managerName,
                        $approvalLink,
                        $revisionLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                        $isEnt,
                        $isCa,
                        $entDetails,
                        $group_company,
                        $messDetails,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Update Status Business Trip tidak terkirim: ' . $e->getMessage());
                }
            }

            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->save();
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->save();
                    }
                }
            }

            // Handle CA approval for L1
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y') {
                        // Update CA approval status for L1
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => Carbon::now()]
                        );

                        // Find the next approver (Layer 2) from ca_approval
                        $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->status_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_status' => 'Approved']);
                        }
                    }
                }
            }
        } elseif ($employeeId == $businessTrip->manager_l2_id) {
            $statusValue = 'Approved';
            $layer = 2;
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->save();
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->save();
                    }
                }
            }
            // Handle CA approval for L2
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y') {
                        // Update CA approval status for L2
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => now()]
                        );

                        // Find the next approver (Layer 3) explicitly
                        $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->status_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_status' => 'Approved']);
                        }
                    }
                }
            }
        } else {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Update the status in the BusinessTrip table
        $businessTrip->update(['status' => $statusValue]);

        // Record the approval or rejection in the BTApproval table
        $approval->bt_id = $businessTrip->id;
        $approval->layer = $layer;
        $approval->approval_status = $statusValue;
        $approval->approved_at = now();
        if ($action == 'Request Revision' || $action == 'Declaration Revision') {
            $approval->reject_info = $revisiInfo;
        } elseif ($action == 'Rejected') {
            $approval->reject_info = $rejectInfo;
        }
        $approval->employee_id = $employeeId;

        // Save the approval record
        $approval->save();

        $message = ($approval->approval_status == 'Approved')
            ? 'The request has been successfully Approved.'
            : 'The request has been successfully moved to Pending L2.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect('/businessTrip/approval')->with('success', 'Request updated successfully');
    }
    public function adminApprove(Request $request, $id)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);
        // dd($businessTrip);

        if ($businessTrip->status == 'Pending L1' || $businessTrip->status == 'Pending L2') {
            if ($businessTrip->manager_l2_id == '-') {
                $statusValue = 'Approved';
                $layer = 1;
                if ($businessTrip->hotel == 'Ya') {
                    $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($hotels as $hotel) {
                        if ($hotel->hotel_only != 'Y') {
                            $hotel->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_htl = new HotelApproval();
                            $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_htl->htl_id = $hotel->id;
                            $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_htl->approval_status = $statusValue;
                            $approval_htl->by_admin = 'T';
                            $approval_htl->approved_at = now();
                            $approval_htl->save();
                        }
                    }
                }
                if ($businessTrip->mess == 'Ya') {
                    $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($messes as $mess) {
                        if ($mess->mess_only != 'Y') {
                            $mess->update([
                                'approval_status' => $statusValue,
                            ]);

                            // Record the rejection in MessApproval
                            $approval_mess = new MessApproval();
                            $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_mess->mess_id = $mess->id;
                            $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_mess->approval_status = $statusValue;
                            $approval_mess->approved_at = now();
                            $approval_mess->by_admin = 'T';
                            $approval_mess->save();
                        }
                    }
                }
                if ($businessTrip->taksi == 'Ya') {
                    $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                    if ($taksi) {
                        // Update the existing hotel record with the new approval status
                        $taksi->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_vt = new TaksiApproval();
                        $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_vt->vt_id = $taksi->id;
                        $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_vt->layer = $layer;
                        $approval_vt->approval_status = $statusValue;
                        $approval_vt->by_admin = 'T';
                        $approval_vt->approved_at = now();
                        $approval_vt->save();
                    }
                }
                if ($businessTrip->tiket == 'Ya') {
                    $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($tikets as $tiket) {
                        if ($tiket->tkt_only != 'Y') {
                            $tiket->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_tkt = new TiketApproval();
                            $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_tkt->tkt_id = $tiket->id;
                            $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                            $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                            $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                            $approval_tkt->layer = $layer;
                            $approval_tkt->approval_status = $statusValue;
                            $approval_tkt->by_admin = 'T';
                            $approval_tkt->approved_at = now();
                            $approval_tkt->save();
                        }
                    }
                }
                if ($businessTrip->ca == 'Ya') {
                    $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($caTransaction as $caTransactions) {
                        if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                            // Update CA approval status for L2
                            $caApproval = ca_approval::where([
                                'ca_id' => $caTransactions->id,
                                'layer' => $layer
                            ])->first();

                            if ($caApproval) {
                                // Only update if the record exists
                                $caApproval->update([
                                    'approval_status' => 'Approved',
                                    'approved_at' => now(),
                                    'by_admin' => 'T',
                                    'admin_id' => $employeeId
                                ]);
                            }

                            // Find the next approver (Layer 3) explicitly
                            $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                                ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                                ->first();

                            if ($nextApproval) {
                                $updateCa = caTransaction::where('id', $caTransactions->id)->first();
                                $updateCa->status_id = $nextApproval->employee_id;
                                $updateCa->save();
                            } else {
                                // No next layer, so mark as Approved
                                $caTransactions->update(['approval_status' => 'Approved']);
                            }
                        }
                    }
                }
            } elseif ($businessTrip->status == 'Pending L1') {
                $statusValue = 'Pending L2';
                $layer = 1;

                $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
                // $managerL2 = "erzie.aldrian02@gmail.com";
                $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();
                $group_company = Employee::where('id', $businessTrip->id)->pluck('group_company')->first();

                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $employeeName = Employee::where('id', $businessTrip->user_id)->pluck('fullname')->first();
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                $textNotification = "requesting a Business Trip and waiting for your approval with the following details :";
                $isEnt = CATransaction::where('type_ca', 'entr')->first();
                $isCa = CATransaction::where('type_ca', 'dns')->first();

                // dd($managerL2);
                if ($managerL2) {
                    $detail_ca = $isCa ? json_decode($isCa->detail_ca, true) : [];
                    $detail_ent = $isEnt ? json_decode($isEnt->detail_ca, true) : [];
                    $caDetails = [
                        'total_days_perdiem' => array_sum(array_column($detail_ca['detail_perdiem'] ?? [], 'total_days')),
                        'total_amount_perdiem' => array_sum(array_column($detail_ca['detail_perdiem'] ?? [], 'nominal')),

                        'total_days_transport' => count($detail_ca['detail_transport'] ?? []),
                        'total_amount_transport' => array_sum(array_column($detail_ca['detail_transport'] ?? [], 'nominal')),

                        'total_days_accommodation' => array_sum(array_column($detail_ca['detail_penginapan'] ?? [], 'total_days')),
                        'total_amount_accommodation' => array_sum(array_column($detail_ca['detail_penginapan'] ?? [], 'nominal')),

                        'total_days_others' => count($detail_ca['detail_lainnya'] ?? []),
                        'total_amount_others' => array_sum(array_column($detail_ca['detail_lainnya'] ?? [], 'nominal')),
                    ];
                    $entDetails = [
                        'total_amount_ent' => array_sum(array_column($detail_ent['detail_e'] ?? [], 'nominal')),
                    ];
                    // Fetch ticket and hotel details with proper conditions
                    $ticketDetails = Tiket::where('no_sppd', $businessTrip->no_sppd)
                        ->where(function ($query) {
                            $query->where('tkt_only', '!=', 'Y')
                                ->orWhereNull('tkt_only'); // This handles the case where tkt_only is null
                        })
                        ->get();

                    $hotelDetails = Hotel::where('no_sppd', $businessTrip->no_sppd)
                        ->where(function ($query) {
                            $query->where('hotel_only', '!=', 'Y')
                                ->orWhereNull('hotel_only'); // This handles the case where hotel_only is null
                        })
                        ->get();
                    $messDetails = Mess::where('no_sppd', $businessTrip->no_sppd)
                        ->where(function ($query) {
                            $query->where('mess_only', '!=', 'Y')
                                ->orWhereNull('mess_only');
                        })
                        ->get();

                    $taksiDetails = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                    // dd($taksiDetails);
                    $approvalLink = route('approve.business.trip', [
                        'id' => urlencode($businessTrip->id),
                        'manager_id' => $businessTrip->manager_l2_id,
                        'status' => 'Approved',
                    ]);

                    $revisionLink = route('revision.link', [
                        'id' => urlencode($businessTrip->id),
                        'manager_id' => $businessTrip->manager_l2_id,
                        'status' => 'Request Revision',
                    ]);

                    $rejectionLink = route('reject.link', [
                        'id' => urlencode($businessTrip->id),
                        'manager_id' => $businessTrip->manager_l2_id,
                        'status' => 'Rejected',
                    ]);

                    // Send an email with the detailed business trip information
                    try {
                        Mail::to($managerL2)->send(new BusinessTripNotification(
                            $businessTrip,
                            $hotelDetails,
                            $ticketDetails,
                            $taksiDetails,
                            $caDetails,
                            $managerName,
                            $approvalLink,
                            $revisionLink,
                            $rejectionLink,
                            $employeeName,
                            $base64Image,
                            $textNotification,
                            $isEnt,
                            $isCa,
                            $entDetails,
                            $group_company,
                            $messDetails,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email Update Status Business Trip tidak terkirim: ' . $e->getMessage());
                    }
                }

                if ($businessTrip->hotel == 'Ya') {
                    $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($hotels as $hotel) {
                        if ($hotel->hotel_only != 'Y') {
                            $hotel->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_htl = new HotelApproval();
                            $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_htl->htl_id = $hotel->id;
                            $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_htl->approval_status = $statusValue;
                            $approval_htl->by_admin = 'T';
                            $approval_htl->approved_at = now();
                            $approval_htl->save();
                        }
                    }
                }
                if ($businessTrip->mess == 'Ya') {
                    $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($messes as $mess) {
                        if ($mess->mess_only != 'Y') {
                            $mess->update([
                                'approval_status' => $statusValue,
                            ]);

                            // Record the rejection in MessApproval
                            $approval_mess = new MessApproval();
                            $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_mess->mess_id = $mess->id;
                            $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_mess->approval_status = $statusValue;
                            $approval_mess->approved_at = now();
                            $approval_mess->by_admin = 'T';
                            $approval_mess->save();
                        }
                    }
                }
                if ($businessTrip->taksi == 'Ya') {
                    $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                    if ($taksi) {
                        // Update the existing hotel record with the new approval status
                        $taksi->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_vt = new TaksiApproval();
                        $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_vt->vt_id = $taksi->id;
                        $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_vt->approval_status = $statusValue;
                        $approval_vt->by_admin = 'T';
                        $approval_vt->approved_at = now();
                        $approval_vt->save();
                    }
                }
                if ($businessTrip->tiket == 'Ya') {
                    $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($tikets as $tiket) {
                        if ($tiket->tkt_only != 'Y') {
                            $tiket->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_tkt = new TiketApproval();
                            $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_tkt->tkt_id = $tiket->id;
                            $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                            $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                            $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                            $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                            $approval_tkt->approval_status = $statusValue;
                            $approval_tkt->by_admin = 'T';
                            $approval_tkt->approved_at = now();
                            $approval_tkt->save();
                        }
                    }
                }
                // Handle CA approval for L1
                if ($businessTrip->ca == 'Ya') {
                    $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($caTransaction as $caTransactions) {
                        if ($caTransactions && $caTransactions->caonly != 'Y' && $caTransactions->caonly == null) {
                            // Update CA approval status for L1
                            $caApproval = ca_approval::where([
                                'ca_id' => $caTransactions->id,
                                'layer' => $layer
                            ])->first();

                            if ($caApproval) {
                                // Only update if the record exists
                                $caApproval->update([
                                    'approval_status' => 'Approved',
                                    'approved_at' => now(),
                                    'by_admin' => 'T',
                                    'admin_id' => $employeeId
                                ]);
                            }

                            // Find the next approver (Layer 2) from ca_approval
                            $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                                ->where('layer', $layer + 1)
                                ->first();

                            if ($nextApproval) {
                                $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                                $updateCa->status_id = $nextApproval->employee_id;
                                $updateCa->save();
                            } else {
                                // No next layer, so mark as Approved
                                $caTransactions->update(['approval_status' => 'Approved']);
                            }
                        }
                    }
                }
            } elseif ($businessTrip->status == 'Pending L2') {
                // dd($businessTrip);
                $statusValue = 'Approved';
                $layer = 2;
                if ($businessTrip->hotel == 'Ya') {
                    $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($hotels as $hotel) {
                        if ($hotel->hotel_only != 'Y') {
                            $hotel->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_htl = new HotelApproval();
                            $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_htl->htl_id = $hotel->id;
                            $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_htl->approval_status = $statusValue;
                            $approval_htl->by_admin = 'T';
                            $approval_htl->approved_at = now();
                            $approval_htl->save();
                        }
                    }
                }
                if ($businessTrip->mess == 'Ya') {
                    $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($messes as $mess) {
                        if ($mess->mess_only != 'Y') {
                            $mess->update([
                                'approval_status' => $statusValue,
                            ]);

                            // Record the rejection in MessApproval
                            $approval_mess = new MessApproval();
                            $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_mess->mess_id = $mess->id;
                            $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                            $approval_mess->role_id = $user->role_id; // Assuming role_id is in the user data
                            $approval_mess->role_name = $user->role_name; // Assuming role_name is in the user data
                            $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                            $approval_mess->approval_status = $statusValue;
                            $approval_mess->approved_at = now();
                            $approval_mess->by_admin = 'T';
                            $approval_mess->save();
                        }
                    }
                }
                if ($businessTrip->taksi == 'Ya') {
                    $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                    if ($taksi) {
                        // Update the existing hotel record with the new approval status
                        $taksi->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_vt = new TaksiApproval();
                        $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_vt->vt_id = $taksi->id;
                        $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_vt->approval_status = $statusValue;
                        $approval_vt->by_admin = 'T';
                        $approval_vt->approved_at = now();
                        $approval_vt->save();
                    }
                }
                if ($businessTrip->tiket == 'Ya') {
                    $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($tikets as $tiket) {
                        if ($tiket->tkt_only != 'Y') {
                            $tiket->update([
                                'approval_status' => $statusValue,
                            ]);
                            $approval_tkt = new TiketApproval();
                            $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                            $approval_tkt->tkt_id = $tiket->id;
                            $approval_tkt->employee_id = Auth::user()->employee_id; // Assuming the logged-in user's employee ID is needed
                            $approval_tkt->role_id = Auth::user()->role_id; // Assuming role_id is in the user data
                            $approval_tkt->role_name = Auth::user()->role_name; // Assuming role_name is in the user data
                            $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                            $approval_tkt->approval_status = $statusValue;
                            $approval_tkt->by_admin = 'T';
                            $approval_tkt->approved_at = now();
                            $approval_tkt->save();
                        }
                    }
                }
                // Handle CA approval for L2
                if ($businessTrip->ca == 'Ya') {
                    $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                    foreach ($caTransaction as $caTransactions) {
                        if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                            // Update CA approval status for L2
                            $caApproval = ca_approval::where([
                                'ca_id' => $caTransactions->id,
                                'layer' => $layer
                            ])->first();

                            if ($caApproval) {
                                // Only update if the record exists
                                $caApproval->update([
                                    'approval_status' => 'Approved',
                                    'approved_at' => now(),
                                    'by_admin' => 'T',
                                    'admin_id' => $employeeId
                                ]);
                            }

                            // Find the next approver (Layer 3) explicitly
                            $nextApproval = ca_approval::where('ca_id', $caTransactions->id)
                                ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                                ->first();

                            if ($nextApproval) {
                                $updateCa = caTransaction::where('id', $caTransactions->id)->first();
                                $updateCa->status_id = $nextApproval->employee_id;
                                $updateCa->save();
                            } else {
                                // No next layer, so mark as Approved
                                $caTransactions->update(['approval_status' => 'Approved']);
                            }
                        }
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            // Update the status in the BusinessTrip table
            $businessTrip->update(['status' => $statusValue]);

            $approval->bt_id = $businessTrip->id;
            $approval->layer = $layer;
            $approval->approval_status = $statusValue;
            $approval->approved_at = now();
            $approval->employee_id = $employeeId;
            $approval->by_admin = 'T';

            // Save the approval record
            $approval->save();
        }

        if ($businessTrip->status == 'Declaration L1' || $businessTrip->status == 'Declaration L2') {
            if ($businessTrip->manager_l2_id == '-') {
                $statusValue = 'Declaration Approved';
                $layer = 1;
                // if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                        // Update CA approval status for L1
                        $caApproval = ca_sett_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->where('approval_status', '!=', 'Rejected')
                            ->first();

                        if ($caApproval) {
                            // Only update if the record exists
                            $caApproval->update([
                                'approval_status' => 'Approved',
                                'approved_at' => now(),
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                        }

                        // Find the next approver (Layer 3) explicitly
                        $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->sett_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_sett' => 'Approved']);
                        }
                    }
                }
                // }

                $businessTrip->update(['status' => $statusValue]);
                $approval->bt_id = $businessTrip->id;
                $approval->layer = $layer;
                $approval->approval_status = $statusValue;
                $approval->approved_at = now();
                $approval->employee_id = $employeeId;
                $approval->by_admin = 'T';

                // Save the approval record
                $approval->save();
            } elseif ($businessTrip->status == 'Declaration L1') {
                $statusValue = 'Declaration L2';
                $layer = 1;
                // Handle CA approval for L1

                $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
                $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();

                $approvalLink = route('approve.business.trip.declare', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Declaration Approved'
                ]);

                $revisionLink = route('revision.link.declaration', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l1_id,
                    'status' => 'Declaration Revision',
                ]);

                $rejectionLink = route('reject.link.declaration', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Declaration Rejected'
                ]);

                $caTrans = CATransaction::where('no_sppd', $businessTrip->no_sppd)
                    ->where(function ($query) {
                        $query->where('caonly', '!=', 'Y')
                            ->orWhereNull('caonly');
                    })
                    ->get();
                $dnsNtfRe = $caTrans->where('type_ca', 'dns')->first();
                $entrNtfRe = $caTrans->where('type_ca', 'entr')->first();
                $isEnt = $dnsNtfRe ? true : false;
                $isCa = $entrNtfRe ? true : false;
                $detail_ca_req = isset($dnsNtfRe) && isset($dnsNtfRe->detail_ca) ? json_decode($dnsNtfRe->detail_ca, true) : [];
                $detail_ent_req = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->detail_ca, true) : [];

                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $employeeName = Employee::where('employee_id', $employeeId)->pluck('fullname')->first();
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                $textNotification = "requesting a Declaration Business Trip and waiting for your approval with the following details :";

                // dd($caTrans, $n->no_sppd);
                $caDetails = [
                    'total_days_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($detail_ca_req['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($detail_ca_req['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($detail_ca_req['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($detail_ca_req['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => count($detail_ca_req['detail_meals'] ?? []),
                    'total_amount_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'nominal')),
                ];
                $entDetails = [
                    'total_amount_ent' => array_sum(array_column($detail_ent_req['detail_e'] ?? [], 'nominal')),
                ];
                // dd($caDetails,   $detail_ca );

                $declare_ca_ntf = isset($dnsNtfRe) && isset($dnsNtfRe->declare_ca) ? json_decode($dnsNtfRe->declare_ca, true) : [];
                $declare_ent_ntf = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->declare_ca, true) : [];
                $caDeclare = [
                    'total_days_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($declare_ca_ntf['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($declare_ca_ntf['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($declare_ca_ntf['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($declare_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                    'total_days_meals' => count($declare_ca_ntf['detail_meals'] ?? []),
                    'total_amount_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'nominal')),
                ];
                $entDeclare = [
                    'total_amount_ent' => array_sum(array_column($declare_ent_ntf['detail_e'] ?? [], 'nominal')),
                ];
                // dd($managerL2);
                if ($managerL2) {
                    // Send email to L2
                    try {
                        Mail::to($managerL2)->send(new DeclarationNotification(
                            $businessTrip,
                            $caDetails,
                            $caDeclare,
                            $entDetails,
                            $entDeclare,
                            $managerName,
                            $approvalLink,
                            $revisionLink,
                            $rejectionLink,
                            $employeeName,
                            $base64Image,
                            $textNotification,
                            $isEnt,
                            $isCa,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email Update Status Deklarasi Business Trip tidak terkirim: ' . $e->getMessage());
                    }
                }
                // if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                        // Update CA approval status for L1
                        $caApproval = ca_sett_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->where('approval_status', '!=', 'Rejected')
                            ->first();

                        if ($caApproval) {
                            // Only update if the record exists
                            $caApproval->update([
                                'approval_status' => 'Approved',
                                'approved_at' => now(),
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                        }
                        // Find the next approver (Layer 2) from ca_approval
                        $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->sett_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_sett' => 'Approved']);
                        }
                    }
                }
                // }
                $businessTrip->update(['status' => $statusValue]);

                $approval->bt_id = $businessTrip->id;
                $approval->layer = $layer;
                $approval->approval_status = $statusValue;
                $approval->approved_at = now();
                $approval->employee_id = $employeeId;
                $approval->by_admin = 'T';

                // Save the approval record
                $approval->save();

            } elseif ($businessTrip->status == 'Declaration L2') {
                $statusValue = 'Declaration Approved';
                $layer = 2;

                // Handle CA approval for L2
                // if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                        // Update CA approval status for L1
                        $caApproval = ca_sett_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->where('approval_status', '!=', 'Rejected')
                            ->first();

                        if ($caApproval) {
                            // Only update if the record exists
                            $caApproval->update([
                                'approval_status' => 'Approved',
                                'approved_at' => now(),
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                        }

                        // Find the next approver (Layer 3) explicitly
                        $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                            ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                            $updateCa->sett_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransactions->update(['approval_sett' => 'Approved']);
                        }
                    }
                }
                // }

                $businessTrip->update(['status' => $statusValue]);
                $approval->bt_id = $businessTrip->id;
                $approval->layer = $layer;
                $approval->approval_status = $statusValue;
                $approval->approved_at = now();
                $approval->employee_id = $employeeId;
                $approval->by_admin = 'T';

                // Save the approval record
                $approval->save();
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
        }


        return redirect('/businessTrip/admin')->with('success', 'Request updated successfully');
    }

    public function adminRevisi(Request $request, $id)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);

        // Determine the new status and layer based on the action and manager's role
        // dd($businessTrip);
        $revisiInfo = $request->input('revisi_info');

        if ($businessTrip->status == 'Pending L1' || $businessTrip->status == 'Pending L2') {
            $statusValue = 'Request Revision';
            if ($businessTrip->status == 'Pending L1') {
                $layer = 1;
            } elseif ($businessTrip->status == 'Pending L2') {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                // dd($caTransaction->caonly != 'Y' && $caTransaction->caonly== null);
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' && $caTransactions->caonly == null) {
                        $caApproval = ca_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->first();
                        // dd($caApproval);

                        if ($caApproval) {
                            // Only update if the record exists
                            $caApproval->update([
                                'approved_at' => now(),
                                'reject_info' => $revisiInfo,
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                            ca_approval::where('ca_id', $caTransactions->id)
                                ->update(['approval_status' => 'Rejected']);

                            $caTransactions->update(['approval_status' => 'Revision']);
                        }
                    }
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $layer;
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->reject_info = $revisiInfo;
                        $approval_tkt->by_admin = 'T';
                        $approval_tkt->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->reject_info = $request->revision_info;
                        $approval_mess->by_admin = 'T';
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->reject_info = $revisiInfo;
                        $approval_htl->by_admin = 'T';
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->reject_info = $revisiInfo;
                    $approval_vt->by_admin = 'T';
                    $approval_vt->save();
                }
            }
            // Update the status in the BusinessTrip table
            $businessTrip->update(['status' => $statusValue]);
            // Record the approval or rejection in the BTApproval table
            $approval->bt_id = $businessTrip->id;
            $approval->layer = $layer;
            $approval->approval_status = $statusValue;
            $approval->approved_at = now();
            $approval->reject_info = $revisiInfo;
            $approval->employee_id = $employeeId;
            $approval->by_admin = 'T';

            // Save the approval record
            $approval->save();
        }

        if ($businessTrip->status == 'Declaration L1' || $businessTrip->status == 'Declaration L2') {
            $statusValue = 'Declaration Revision';
            if ($businessTrip->status == 'Declaration L1') {
                $layer = 1;
            } elseif ($businessTrip->status == 'Declaration L2') {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            // dd($revisiInfo, $statusValue, $layer);
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' && $caTransactions->caonly == null) {
                        $caApproval = ca_sett_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->first();
                        if ($caApproval) {
                            $caApproval->update([
                                'approved_at' => now(),
                                'reject_info' => $revisiInfo,
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                            ca_sett_approval::where('ca_id', $caTransactions->id)
                                ->update(['approval_status' => 'Rejected']);

                            $caTransactions->update(['approval_sett' => 'Revision']);
                        }
                    }
                }
            }
            // Update the status in the BusinessTrip table
            $businessTrip->update(['status' => $statusValue]);
            // Record the approval or rejection in the BTApproval table
            $approval->bt_id = $businessTrip->id;
            $approval->layer = $layer;
            $approval->approval_status = $statusValue;
            $approval->approved_at = now();
            $approval->reject_info = $revisiInfo;
            $approval->employee_id = $employeeId;
            $approval->by_admin = 'T';

            // Save the approval record
            $approval->save();
        }

        return redirect('/businessTrip/admin')->with('success', 'Status updated successfully');
    }

    public function adminReject(Request $request, $id)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);

        // Determine the new status and layer based on the action and manager's role
        // dd($businessTrip);
        $rejectInfo = $request->input('reject_info');

        if ($businessTrip->status == 'Pending L1' || $businessTrip->status == 'Pending L2') {
            $statusValue = 'Rejected';
            if ($businessTrip->status == 'Pending L1') {
                $layer = 1;
            } elseif ($businessTrip->status == 'Pending L2') {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                // dd($caTransaction->caonly != 'Y' && $caTransaction->caonly== null);
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' && $caTransactions->caonly == null) {
                        $caApproval = ca_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->first();
                        // dd($caApproval);

                        if ($caApproval) {
                            // Only update if the record exists
                            $caApproval->update([
                                'approved_at' => now(),
                                'reject_info' => $rejectInfo,
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                            ca_approval::where('ca_id', $caTransactions->id)
                                ->update(['approval_status' => 'Rejected']);

                            $caTransactions->update(['approval_status' => 'Rejected']);
                        }
                    }
                }
            }
            if ($businessTrip->tiket == 'Ya') {
                $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($tikets as $tiket) {
                    if ($tiket->tkt_only != 'Y') {
                        $tiket->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_tkt = new TiketApproval();
                        $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_tkt->tkt_id = $tiket->id;
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_tkt->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_tkt->layer = $layer;
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->reject_info = $rejectInfo;
                        $approval_tkt->by_admin = 'T';
                        $approval_tkt->save();
                    }
                }
            }
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in TiketApproval
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->role_id = $user->role_id; // Assuming role_id is in the user data
                        $approval_htl->role_name = $user->role_name; // Assuming role_name is in the user data
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
                        $approval_htl->reject_info = $rejectInfo;
                        $approval_htl->by_admin = 'T';
                        $approval_htl->save();
                    }
                }
            }
            if ($businessTrip->mess == 'Ya') {
                $messes = Mess::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($messes as $mess) {
                    if ($mess->mess_only != 'Y') {
                        $mess->update([
                            'approval_status' => $statusValue,
                        ]);

                        // Record the rejection in MessApproval
                        $approval_mess = new MessApproval();
                        $approval_mess->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_mess->mess_id = $mess->id;
                        $approval_mess->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_mess->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_mess->approval_status = $statusValue;
                        $approval_mess->approved_at = now();
                        $approval_mess->reject_info = $request->reject_info;
                        $approval_mess->by_admin = 'T';
                        $approval_mess->save();
                    }
                }
            }
            if ($businessTrip->taksi == 'Ya') {
                $taksi = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($taksi) {
                    // Update the existing hotel record with the new approval status
                    $taksi->update([
                        'approval_status' => $statusValue,
                    ]);
                    $approval_vt = new TaksiApproval();
                    $approval_vt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_vt->vt_id = $taksi->id;
                    $approval_vt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                    $approval_vt->role_id = $user->role_id; // Assuming role_id is in the user data
                    $approval_vt->role_name = $user->role_name; // Assuming role_name is in the user data
                    $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                    $approval_vt->approval_status = $statusValue;
                    $approval_vt->approved_at = now();
                    $approval_vt->reject_info = $rejectInfo;
                    $approval_vt->by_admin = 'T';
                    $approval_vt->save();
                }
            }
            // Update the status in the BusinessTrip table
            $businessTrip->update(['status' => $statusValue]);
            // Record the approval or rejection in the BTApproval table
            $approval->bt_id = $businessTrip->id;
            $approval->layer = $layer;
            $approval->approval_status = $statusValue;
            $approval->approved_at = now();
            $approval->reject_info = $rejectInfo;
            $approval->employee_id = $employeeId;
            $approval->by_admin = 'T';

            // Save the approval record
            $approval->save();
        }

        if ($businessTrip->status == 'Declaration L1' || $businessTrip->status == 'Declaration L2') {
            $statusValue = 'Declaration Rejected';
            if ($businessTrip->status == 'Declaration L1') {
                $layer = 1;
            } elseif ($businessTrip->status == 'Declaration L2') {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            // dd($rejectInfo, $statusValue, $layer);
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($caTransaction as $caTransactions) {
                    if ($caTransactions && $caTransactions->caonly != 'Y' && $caTransactions->caonly == null) {
                        $caApproval = ca_sett_approval::where([
                            'ca_id' => $caTransactions->id,
                            'layer' => $layer
                        ])->first();
                        if ($caApproval) {
                            $caApproval->update([
                                'approved_at' => now(),
                                'reject_info' => $rejectInfo,
                                'by_admin' => 'T',
                                'admin_id' => $employeeId
                            ]);
                            ca_sett_approval::where('ca_id', $caTransactions->id)
                                ->update(['approval_status' => 'Rejected']);

                            $caTransactions->update(['approval_sett' => 'Rejected']);
                        }
                    }
                }
            }
            // Update the status in the BusinessTrip table
            $businessTrip->update(['status' => $statusValue]);
            // Record the approval or rejection in the BTApproval table
            $approval->bt_id = $businessTrip->id;
            $approval->layer = $layer;
            $approval->approval_status = $statusValue;
            $approval->approved_at = now();
            $approval->reject_info = $rejectInfo;
            $approval->employee_id = $employeeId;
            $approval->by_admin = 'T';

            // Save the approval record
            $approval->save();
        }

        return redirect('/businessTrip/admin')->with('success', 'Status updated successfully');
    }


    public function updateStatusDeklarasi($id, Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        // $roleName = Employee::where('employee_id' ,$employeeId)->pluck('role_name')->first();
        // dd($roleName);
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);
        $rejectInfo = $request->input('reject_info');
        $revisiInfo = $request->input('revisi_info');
        // Determine the new status and layer based on the action and manager's role
        $action = $request->input('status_approval');
        if ($action == 'Declaration Revision') {
            $statusValue = 'Declaration Revision';
            if ($employeeId == $businessTrip->manager_l1_id) {
                $layer = 1;
            } elseif ($employeeId == $businessTrip->manager_l2_id) {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($caTransaction as $caTransactions) {
                if ($caTransactions && $caTransactions->caonly != 'Y') {
                    // Update rejection info for the current layer
                    ca_sett_approval::updateOrCreate(
                        ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                        ['approval_status' => 'Rejected', 'approved_at' => now(), 'reject_info' => $revisiInfo]
                    );

                    // Update all records with the same ca_id to 'Rejected' status
                    ca_sett_approval::where('ca_id', $caTransactions->id)
                        ->update(['approval_status' => 'Rejected']);

                    // Update the main CA transaction approval status
                    $caTransactions->update(['approval_sett' => 'Revision']);
                }
            }
        } elseif ($action == 'Declaration Rejected') {
            $statusValue = 'Declaration Rejected';
            if ($employeeId == $businessTrip->manager_l1_id) {
                $layer = 1;
            } elseif ($employeeId == $businessTrip->manager_l2_id) {
                $layer = 2;
            } else {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($caTransaction as $caTransactions) {
                if ($caTransactions && $caTransactions->caonly != 'Y') {
                    // Update rejection info for the current layer
                    ca_sett_approval::updateOrCreate(
                        ['ca_id' => $caTransactions->id, 'employee_id' => $employeeId, 'layer' => $layer],
                        ['approval_status' => 'Rejected', 'approved_at' => now(), 'reject_info' => $rejectInfo]
                    );

                    // Update all records with the same ca_id to 'Rejected' status
                    ca_sett_approval::where('ca_id', $caTransactions->id)
                        ->update(['approval_status' => 'Rejected']);

                    // Update the main CA transaction approval status
                    $caTransactions->update(['approval_sett' => 'Rejected']);
                }
            }
        } elseif ($businessTrip->manager_l2_id == '-') {
            $statusValue = 'Declaration Approved';
            $layer = 1;

            // Handle CA approval for L2
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($caTransaction as $caTransactions) {
                if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                    // Update CA approval status for L1
                    $caApproval = ca_sett_approval::where([
                        'ca_id' => $caTransactions->id,
                        'layer' => $layer
                    ])->where('approval_status', '!=', 'Rejected')
                        ->first();

                    if ($caApproval) {
                        // Only update if the record exists
                        $caApproval->update([
                            'approval_status' => 'Approved',
                            'approved_at' => now(),
                        ]);
                    }
                    // Find the next approver (Layer 2) from ca_approval
                    $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                        ->where('layer', $layer + 1)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                        $updateCa->sett_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransactions->update(['approval_sett' => 'Approved']);
                    }
                }
            }
        } elseif ($employeeId == $businessTrip->manager_l1_id) {
            $statusValue = 'Declaration L2';
            $layer = 1;
            $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
            // $managerL2 = "erzie.aldrian02@gmail.com";
            $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();

            $approvalLink = route('approve.business.trip.declare', [
                'id' => urlencode($businessTrip->id),
                'manager_id' => $businessTrip->manager_l2_id,
                'status' => 'Declaration Approved'
            ]);

            $revisionLink = route('revision.link.declaration', [
                'id' => urlencode($businessTrip->id),
                'manager_id' => $businessTrip->manager_l1_id,
                'status' => 'Declaration Revision',
            ]);

            $rejectionLink = route('reject.link.declaration', [
                'id' => urlencode($businessTrip->id),
                'manager_id' => $businessTrip->manager_l2_id,
                'status' => 'Declaration Rejected'
            ]);

            $caTrans = CATransaction::where('no_sppd', $businessTrip->no_sppd)
                ->where(function ($query) {
                    $query->where('caonly', '!=', 'Y')
                        ->orWhereNull('caonly');
                })
                ->get();
            $dnsNtfRe = $caTrans->where('type_ca', 'dns')->first();
            $entrNtfRe = $caTrans->where('type_ca', 'entr')->first();
            $isEnt = $dnsNtfRe ? true : false;
            $isCa = $entrNtfRe ? true : false;
            $detail_ca_req = isset($dnsNtfRe) && isset($dnsNtfRe->detail_ca) ? json_decode($dnsNtfRe->detail_ca, true) : [];
            $detail_ent_req = isset($entrNtfRe) && isset($entrNtfRe->detail_ca) ? json_decode($entrNtfRe->detail_ca, true) : [];

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $employeeName = Employee::where('employee_id', $employeeId)->pluck('fullname')->first();
            $group_company = Employee::where('employee_id', $employeeId)->pluck('group_company')->first();
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Declaration Business Trip and waiting for your approval with the following details :";
            // dd( $detail_ca, $caTrans);

            // dd($caTrans, $n->no_sppd);
            $caDetails = [
                'total_days_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'total_days')),
                'total_amount_perdiem' => array_sum(array_column($detail_ca_req['detail_perdiem'] ?? [], 'nominal')),

                'total_days_transport' => count($detail_ca_req['detail_transport'] ?? []),
                'total_amount_transport' => array_sum(array_column($detail_ca_req['detail_transport'] ?? [], 'nominal')),

                'total_days_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'total_days')),
                'total_amount_accommodation' => array_sum(array_column($detail_ca_req['detail_penginapan'] ?? [], 'nominal')),

                'total_days_others' => count($detail_ca_req['detail_lainnya'] ?? []),
                'total_amount_others' => array_sum(array_column($detail_ca_req['detail_lainnya'] ?? [], 'nominal')),

                'total_days_meals' => count($detail_ca_req['detail_meals'] ?? []),
                'total_amount_meals' => array_sum(array_column($detail_ca_req['detail_meals'] ?? [], 'nominal')),
            ];
            $entDetails = [
                'total_amount_ent' => array_sum(array_column($detail_ent_req['detail_e'] ?? [], 'nominal')),
            ];
            // dd($caDetails,   $detail_ca );

            $declare_ca_ntf = isset($declare_ca_ntf) ? $declare_ca_ntf : [];
            $declare_ent_ntf = isset($declare_ent_ntf) ? $declare_ent_ntf : [];
            $caDeclare = [
                'total_days_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'total_days')),
                'total_amount_perdiem' => array_sum(array_column($declare_ca_ntf['detail_perdiem'] ?? [], 'nominal')),

                'total_days_transport' => count($declare_ca_ntf['detail_transport'] ?? []),
                'total_amount_transport' => array_sum(array_column($declare_ca_ntf['detail_transport'] ?? [], 'nominal')),

                'total_days_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'total_days')),
                'total_amount_accommodation' => array_sum(array_column($declare_ca_ntf['detail_penginapan'] ?? [], 'nominal')),

                'total_days_others' => count($declare_ca_ntf['detail_lainnya'] ?? []),
                'total_amount_others' => array_sum(array_column($declare_ca_ntf['detail_lainnya'] ?? [], 'nominal')),

                'total_days_meals' => count($declare_ca_ntf['detail_meals'] ?? []),
                'total_amount_meals' => array_sum(array_column($declare_ca_ntf['detail_meals'] ?? [], 'nominal')),
            ];
            $entDeclare = [
                'total_amount_ent' => array_sum(array_column($declare_ent_ntf['detail_e'] ?? [], 'nominal')),
            ];
            // dd($managerL2);
            if ($managerL2) {
                // Send email to L2
                try {
                    Mail::to($managerL2)->send(new DeclarationNotification(
                        $businessTrip,
                        $caDetails,
                        $caDeclare,
                        $entDetails,
                        $entDeclare,
                        $managerName,
                        $approvalLink,
                        $revisionLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                        $isEnt,
                        $isCa,
                        $group_company,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Update Status Deklarasi Business Trip tidak terkirim: ' . $e->getMessage());
                }
            }
            // Handle CA approval for L1
            // if ($businessTrip->ca == 'Ya') {
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
            // dd($caTransaction);
            foreach ($caTransaction as $caTransactions) {
                if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                    // Update CA approval status for L1
                    $caApproval = ca_sett_approval::where([
                        'ca_id' => $caTransactions->id,
                        'layer' => $layer
                    ])->where('approval_status', '!=', 'Rejected')
                        ->first();

                    if ($caApproval) {
                        // Only update if the record exists
                        $caApproval->update([
                            'approval_status' => 'Approved',
                            'approved_at' => now(),
                        ]);
                    }
                    // Find the next approver (Layer 2) from ca_approval
                    $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                        ->where('layer', $layer + 1)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                        $updateCa->sett_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransactions->update(['approval_sett' => 'Approved']);
                    }
                }
            }
            // }
        } elseif ($employeeId == $businessTrip->manager_l2_id) {
            $statusValue = 'Declaration Approved';
            $layer = 2;

            // Handle CA approval for L2
            // if ($businessTrip->ca == 'Ya') {
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($caTransaction as $caTransactions) {
                if ($caTransactions && $caTransactions->caonly != 'Y' || $caTransactions->caonly == null) {
                    // Update CA approval status for L1
                    $caApproval = ca_sett_approval::where([
                        'ca_id' => $caTransactions->id,
                        'layer' => $layer,
                        'approval_status' => 'Pending'
                    ])->first();

                    if ($caApproval) {
                        // Only update if the record exists
                        $caApproval->update([
                            'approval_status' => 'Approved',
                            'approved_at' => now(),
                        ]);
                    }
                    // Find the next approver (Layer 2) from ca_approval
                    $nextApproval = ca_sett_approval::where('ca_id', $caTransactions->id)
                        ->where('layer', $layer + 1)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransactions->id)->first();
                        $updateCa->sett_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransactions->update(['approval_sett' => 'Approved']);
                    }
                }
            }
            // }
        } else {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Update the status in the BusinessTrip table
        $businessTrip->update(['status' => $statusValue]);

        // Record the approval or rejection in the BTApproval table
        $approval->bt_id = $businessTrip->id;
        $approval->layer = $layer;
        $approval->approval_status = $statusValue;
        $approval->approved_at = now();
        if ($action == 'Declaration Revision') {
            $approval->reject_info = $revisiInfo;
        } elseif ($action == 'Declaration Rejected') {
            $approval->reject_info = $rejectInfo;
        }
        $approval->employee_id = $employeeId;

        // Save the approval record
        $approval->save();

        // Redirect back to the previous page with a success message
        return redirect('/businessTrip/approval')->with('success', 'Request updated successfully');
    }


    public function ApprovalDeklarasi($id)
    {
        $n = BusinessTrip::find($id);
        $userId = $n->user_id;
        $employee_data = Employee::where('id', $n->user_id)->first();
        $group_company = $employee_data->group_company;
        // dd($group_company);
        $ca = CATransaction::where('no_sppd', $n->no_sppd)->get();
        $dns = $ca->where('type_ca', 'dns')->first();
        $entr = $ca->where('type_ca', 'entr')->first();

        $job_level = Employee::where('id', $userId)->pluck('job_level')->first();
        $job_level_number = (int) preg_replace('/[^0-9]/', '', $job_level);
        // Cek apakah ada $ent dan jalankan kode jika ada
        $entrTab = $entr ? true : false;
        $dnsTab = $dns ? true : false;

        $caDetail = [];
        $declareCa = [];
        foreach ($ca as $cas) {
            $currentDetail = json_decode($cas->detail_ca, true);
            $currentDeclare = json_decode($cas->declare_ca, true);
            if (is_array($currentDetail) || is_array($currentDeclare)) {
                $caDetail = array_merge($caDetail, $currentDetail);
                $declareCa = array_merge($declareCa, $currentDeclare);
            }
        }
        // dd($caDetail);

        // // Initialize caDetail with an empty array if it's null
        // $caDetail = $ca ? json_decode($ca->detail_ca, true) : [];
        // $declareCa = $ca ? json_decode($ca->declare_ca, true) : [];

        // Safely access nominalPerdiem with default '0' if caDetail is empty
        $nominalPerdiem = isset($caDetail['detail_perdiem'][0]['nominal']) ? $caDetail['detail_perdiem'][0]['nominal'] : '0';
        $nominalPerdiemDeclare = isset($declareCa['detail_perdiem'][0]['nominal']) ? $declareCa['detail_perdiem'][0]['nominal'] : '0';

        $hasCaData = $ca !== null;
        // Retrieve the taxi data for the specific BusinessTrip
        $taksi = Taksi::where('no_sppd', $n->no_sppd)->first();

        // Retrieve all hotels for the specific BusinessTrip
        $hotels = Hotel::where('no_sppd', $n->no_sppd)->get();

        // Prepare hotel data for the view
        $hotelData = [];
        foreach ($hotels as $index => $hotel) {
            $hotelData[] = [
                'nama_htl' => $hotel->nama_htl,
                'lokasi_htl' => $hotel->lokasi_htl,
                'jmlkmr_htl' => $hotel->jmlkmr_htl,
                'bed_htl' => $hotel->bed_htl,
                'tgl_masuk_htl' => $hotel->tgl_masuk_htl,
                'tgl_keluar_htl' => $hotel->tgl_keluar_htl,
                'total_hari' => $hotel->total_hari,
                'more_htl' => ($index < count($hotels) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve all tickets for the specific BusinessTrip
        $tickets = Tiket::where('no_sppd', $n->no_sppd)->get();

        // Prepare ticket data for the view
        $ticketData = [];
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'noktp_tkt' => $ticket->noktp_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < count($tickets) - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // Retrieve locations and companies data for the dropdowns
        $locations = Location::orderBy('id')->get();
        $companies = Company::orderBy('contribution_level')->get();

        $parentLink = 'Business Trip Approval';
        $link = 'Approval Details';

        return view('hcis.reimbursements.businessTrip.btApprovalDeklarasi', [
            'n' => $n,
            'hotelData' => $hotelData,
            'taksiData' => $taksi, // Pass the taxi data
            'ticketData' => $ticketData,
            'employee_data' => $employee_data,
            'companies' => $companies,
            'locations' => $locations,
            'caDetail' => $caDetail,
            'declareCa' => $declareCa,
            'ca' => $ca,
            'dns' => $dns,
            'entr' => $entr,
            'entrTab' => $entrTab,
            'dnsTab' => $dnsTab,
            'group_company' => $group_company,
            'nominalPerdiem' => $nominalPerdiem,
            'nominalPerdiemDeclare' => $nominalPerdiemDeclare,
            'hasCaData' => $hasCaData,
            'job_level_number' => $job_level_number,
            'parentLink' => $parentLink,
            'link' => $link,
        ]);
    }


    public function filterDateApproval(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->query('start-date');
        $endDate = $request->query('end-date');
        $filter = $request->input('filter', 'all');

        // Base query for filtering by user's role and status
        $query = BusinessTrip::query()
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('manager_l1_id', $user->employee_id)
                        ->whereIn('status', ['Pending L1', 'Declaration L1']);
                })->orWhere(function ($q) use ($user) {
                    $q->where('manager_l2_id', $user->employee_id)
                        ->whereIn('status', ['Pending L2', 'Declaration L2']);
                });
            });

        // Apply date filtering if both startDate and endDate are provided
        if ($startDate && $endDate) {
            $query->whereBetween('mulai', [$startDate, $endDate]);
        }

        // Apply status filter based on the 'filter' parameter
        if ($filter === 'request') {
            $query->where(function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->where('manager_l1_id', $user->employee_id)
                        ->where('status', 'Pending L1');
                })->orWhere(function ($subQ) use ($user) {
                    $subQ->where('manager_l2_id', $user->employee_id)
                        ->where('status', 'Pending L2');
                });
            });
        } elseif ($filter === 'declaration') {
            $query->where(function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->where('manager_l1_id', $user->employee_id)
                        ->where('status', 'Declaration L1');
                })->orWhere(function ($subQ) use ($user) {
                    $subQ->where('manager_l2_id', $user->employee_id)
                        ->where('status', 'Declaration L2');
                });
            });
        }

        // Order and retrieve the filtered results
        $sppd = $query->orderBy('created_at', 'desc')->get();

        $requestCount = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->where('status', 'Pending L1');
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->where('status', 'Pending L2');
            });
        })->count();

        // Count only "Declaration" status (Declaration L1 and L2)
        $declarationCount = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->where('status', 'Declaration L1');
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->where('status', 'Declaration L2');
            });
        })->count();


        // Collect all SPPD numbers from the BusinessTrip instances
        $sppdNos = $sppd->pluck('no_sppd');

        // Retrieve related data based on the collected SPPD numbers
        $caTransactions = ca_transaction::whereIn('no_sppd', $sppdNos)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('no_sppd');
        $tickets = Tiket::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $hotel = Hotel::whereIn('no_sppd', $sppdNos)->get()->groupBy('no_sppd');
        $taksi = Taksi::whereIn('no_sppd', $sppdNos)->get()->keyBy('no_sppd');

        $parentLink = 'Reimbursement';
        $link = 'Business Trip';
        $showData = true;

        return view('hcis.reimbursements.businessTrip.btApproval', compact('sppd', 'parentLink', 'link', 'caTransactions', 'tickets', 'hotel', 'taksi', 'showData', 'filter', 'requestCount', 'declarationCount'));
    }


    private function generateNoSppd()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Get the last transaction for the current year, including deleted ones
        $lastTransaction = BusinessTrip::whereYear('created_at', $currentYear)
            ->orderBy('no_sppd', 'desc')
            ->withTrashed()
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/SPPD-HC\/([IVX]+)\/\d{4}/', $lastTransaction->no_sppd, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }
        // dd($lastNumber);

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/SPPD-HC/$romanMonth/$currentYear";
        // dd($newNoSppd);

        return $newNoSppd;
    }

    private function generateNoSppdHtl()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Get the last transaction for the current year, including deleted ones
        $lastTransaction = Hotel::whereYear('created_at', $currentYear)
            ->orderBy('no_htl', 'desc')
            ->withTrashed()
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/HTLD-HRD\/([IVX]+)\/\d{4}/', $lastTransaction->no_htl, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/HTLD-HRD/$romanMonth/$currentYear";
        // dd($newNoSppd);

        return $newNoSppd;
    }
    private function generateNoSppdMess()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Get the last transaction for the current year, including deleted ones
        $lastTransaction = Mess::whereYear('created_at', $currentYear)
            ->orderBy('no_mess', 'desc')
            ->withTrashed()
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/MSD-HRD\/([IVX]+)\/\d{4}/', $lastTransaction->no_mess, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/MSD-HRD/$romanMonth/$currentYear";
        // dd($newNoSppd);

        return $newNoSppd;
    }
    private function generateNoSppdTkt()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Get the last transaction for the current year, including deleted ones
        $lastTransaction = Tiket::whereYear('created_at', $currentYear)
            ->where('no_tkt', 'like', '%TKTD-HRD%')  // Keep the filter for 'TKTD-HRD'
            ->orderBy('no_tkt', 'desc')
            ->withTrashed()
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/TKTD-HRD\/([IVX]+)\/\d{4}/', $lastTransaction->no_tkt, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/TKTD-HRD/$romanMonth/$currentYear";

        // dd($newNoSppd);

        return $newNoSppd;
    }
    private function generateNoSppdCa()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Assuming you want to generate no_sppd similarly to no_ca
        $lastTransaction = ca_transaction::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('no_sppd', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/SPPD-CA\/' . $romanMonth . '\/\d{4}/', $lastTransaction->no_sppd, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/SPPD-CA/$romanMonth/$currentYear";

        return $newNoSppd;
    }


    private function getRomanMonth($month)
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
}
