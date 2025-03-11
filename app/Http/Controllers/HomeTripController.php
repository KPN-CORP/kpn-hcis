<?php

namespace App\Http\Controllers;

use App\Mail\HomeTripNotification;
use App\Mail\TicketNotification;
use App\Models\BusinessTrip;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HomeTrip;
use App\Models\Location;
use App\Models\Tiket;
use App\Models\TiketApproval;
use App\Models\Designation;
use Exception;
use Illuminate\Http\Request;
use App\Models\Dependents;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\MasterBusinessUnit;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\date_interval_create_from_string;
use Illuminate\Support\Facades\Log;
use App\Models\MatrixApproval;

class HomeTripController extends Controller
{
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

    public function homeTrip(Request $request)
    {
        // Get the filtered tickets
        $parentLink = 'Reimbursement';
        $link = 'Home Trip';
        $userId = Auth::user()->id;

        $employee_id = Auth::user()->employee_id;
        $fullname = Employee::where('employee_id', $employee_id)->pluck('fullname')->first();
        $user_id = Auth::user()->id;
        $family = Dependents::orderBy('date_of_birth', 'asc')->where('employee_id', $employee_id)->get();

        $yearNow = Carbon::now()->year; // Current year (e.g., 2024)
        $yearStart = $yearNow - 2; // Start year (e.g., 2022)

        // Fetch data filtered by the last 3 years
        $plafonds = HomeTrip::where('employee_id', $employee_id)
            ->whereBetween('period', [$yearStart, $yearNow]) // Filter for 2022 to 2024
            ->orderBy('name') // Order by name
            ->orderBy('period') // Then by period
            ->get();

        // Group data by period
        $plafonds = $plafonds->groupBy('period');

        $latestTicketIds = Tiket::selectRaw('MAX(id) as id')
            ->where('user_id', $user_id)
            ->groupBy('no_tkt')
            ->pluck('id');

        $transactions = Tiket::whereIn('id', $latestTicketIds)
            ->where('jns_dinas_tkt', '=', 'Cuti')
            // ->where('approval_status', $statusFilter)
            ->orderBy('created_at', 'desc')
            ->select('id', 'no_tkt', 'np_tkt', 'type_tkt', 'jenis_tkt', 'dari_tkt', 'ke_tkt', 'approval_status', 'jns_dinas_tkt', 'user_id', 'no_sppd')
            ->get();

        // Get all tickets for user
        $tickets = Tiket::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();
        $tiketIds = $tickets->pluck('id');

        $ticketApprovals = TiketApproval::whereIn('tkt_id', $tiketIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected');
            })
            ->get();

        $ticketApprovals = $ticketApprovals->keyBy('tkt_id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        $employeeIds = $tickets->pluck('user_id')->unique();

        $managerL1Names = 'Unknown';
        $managerL2Names = 'Unknown';

        foreach ($transactions as $transaction) {
            // Fetch the employee for the current transaction
            $employee = Employee::find($transaction->user_id);
            // dd($employee);
            // If the employee exists, fetch their manager names
            if ($employee) {
                $managerL1Id = $employee->manager_l1_id;
                $managerL2Id = $employee->manager_l2_id;

                $managerL1 = Employee::where('employee_id', $managerL1Id)->first();
                $managerL2 = Employee::where('employee_id', $managerL2Id)->first();

                $managerL1Names = $managerL1 ? $managerL1->fullname : 'Unknown';
                $managerL2Names = $managerL2 ? $managerL2->fullname : 'Unknown';
            }
        }

        $ticket = $tickets->groupBy('no_tkt');

        $ticketCounts = $tickets->groupBy('no_tkt')->mapWithKeys(function ($group, $key) {
            return [$key => ['total' => $group->count()]];
        });

        $dependents_fam = Dependents::where('employee_id', $employee_id)->get();
        $periods = HomeTrip::where('employee_id', $employee_id)->orderBy('period', 'desc')->pluck('period')->unique();

        $formatted_data = $periods->mapWithKeys(function ($period) use ($employee_id, $dependents_fam) {
            $data = ['employee' => HomeTrip::where('employee_id', $employee_id)->where('period', $period)->where('relation_type', 'Employee')->value('quota') ?? '-'];

            foreach ($dependents_fam as $dependent) {
                $data[$dependent->name] = HomeTrip::where('employee_id', $employee_id)
                    ->where('period', $period)
                    ->where('name', $dependent->name)
                    ->value('quota') ?? '-';
            }

            return [$period => $data];
        })->toArray();

        $currentYear = date('Y');
        $employees_cast = Employee::where('employee_id', $employee_id)->where('homebase', '!=', '')->get();

        foreach ($employees_cast as $employee) {
            $joiningDate = date_create($employee->date_of_joining);
            $monthDay = $joiningDate->format('m-d');
            $eligibleYear = (int) $joiningDate->format('Y') + 1;

            if ($eligibleYear <= $currentYear) {
                $dependents = Dependents::where('employee_id', $employee->employee_id)->get();
                $totalFamilyMembers = $dependents->count() + 1;

                $quota = $totalFamilyMembers * 2;

                $existingHomeTrip = HomeTrip::where('employee_id', $employee->employee_id)
                    ->where('period', $currentYear)
                    ->where('relation_type', 'Employee')
                    ->first();

                if (!$existingHomeTrip) {
                    $homeTrip = new HomeTrip();
                    $homeTrip->id = Str::uuid();
                    $homeTrip->employee_id = $employee->employee_id;
                    $homeTrip->name = $employee->fullname;
                    $homeTrip->relation_type = 'Employee';
                    $homeTrip->quota = $quota;
                    $homeTrip->last_generate = $currentYear . '-' . $monthDay;
                    $homeTrip->period = $currentYear;
                    $homeTrip->created_by = $userId;
                    $homeTrip->save();

                    session()->flash('refresh', true);
                }
            }
        }

        return view('hcis.reimbursements.homeTrip.homeTrip', compact(
            'parentLink',
            'link',
            'family',
            'transactions',
            'tickets',
            'ticket',
            'ticketApprovals',
            'employeeName',
            'ticketCounts',
            'managerL1Names',
            'managerL2Names',
            'dependents_fam',
            'employee_id',
            'formatted_data',
            'fullname',
            'plafonds',
        ));
    }
    public function homeTripForm()
    {
        $employee_id = Auth::user()->employee_id;
        $userId = Auth::user()->id;
        $currentYear = now()->year;

        $families = Dependents::orderBy('date_of_birth', 'desc')->where('employee_id', $employee_id)->get();

        $parentLink = 'Home Trip';
        $link = 'Home Trip Request';

        $employee_data = Employee::where('employee_id', $employee_id)->first();
        $employee_name = Employee::select('fullname')
            ->where('employee_id', $employee_id)
            ->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $employees = Employee::orderBy('ktp')->get();

        $familyMembers = Dependents::where('employee_id', $employee_id)
            ->where('relation_type', '!=', 'employee')
            ->get();

        $employeeInHomeTrip = HomeTrip::where('employee_id', $employee_id)
            ->where('period', $currentYear)
            ->where('quota', '>', 0)
            ->where('relation_type', '=', 'employee')
            ->first();

        // dd($familyMembers);
        return view('hcis.reimbursements.homeTrip.form.formHt', [
            'link' => $link,
            'parentLink' => $parentLink,
            'families' => $families,
            'familyMembers' => $familyMembers,
            'employeeInHomeTrip' => $employeeInHomeTrip,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'employees' => $employees,
            'employee_name' => $employee_name,
        ]);
    }
    public function homeTripCreate(Request $request)
    {
        $userId = Auth::id();
        $currentYear = now()->year;
        $employee_id = Auth::user()->employee_id;
        $employee = Employee::where('employee_id', $employee_id)->first();

        $employeeName = $employee->fullname;
        $employee_data = Employee::where('id', $userId)->first();
        $contribution_level_code = Employee::where('id', $userId)->pluck('contribution_level_code')->first();

        $deptHeadManager = $this->findDepartmentHead($employee_data);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        $isJobLevel = MatrixApproval::where('modul', 'businesstrip')
            ->where('group_company', 'like', '%' . $employee->group_company . '%')
            ->where('job_level', 'like', '%' . $employee->job_level . '%')
            ->get();

        $npTkt = array_values($request->np_tkt);
        $selectedName = $npTkt[0] ?? null;
        // dd($request->np_tkt, $selectedName);

        $passengerRequests = [];
        foreach ($request->np_tkt as $key => $selectedName) {
            if (!isset($passengerRequests[$selectedName])) {
                $passengerRequests[$selectedName] = [
                    'tickets' => [],
                    'quota' => HomeTrip::where('employee_id', $employee_id)
                        ->where('period', $currentYear)
                        ->pluck('quota')
                        ->sum() // Mengambil total kuota untuk employee_id
                ];
            }
            $passengerRequests[$selectedName]['tickets'][] = $request->type_tkt[$key];
        }

        // Validasi total kuota berdasarkan employee_id
        $totalQuotaUsed = 0;
        foreach ($passengerRequests as $passengerName => $data) {
            $quota = $data['quota'];
            $tickets = $data['tickets'];
            $quotaNeeded = 0;

            // Hitung total kuota yang dibutuhkan
            foreach ($tickets as $ticketType) {
                $quotaNeeded += ($ticketType == 'Round Trip') ? 2 : 1;
            }
            $totalQuotaUsed += $quotaNeeded;

            // Validasi apakah kuota cukup
            if ($totalQuotaUsed > $quota) {
                return redirect()->back()
                    ->with('error', "The trip requested for {$passengerName} exceeds the allocated quota")
                    ->withInput();
            }

            // If multiple tickets, prevent round trips
            // if (count($tickets) > 1) {
            //     foreach ($tickets as $ticketType) {
            //         if ($ticketType == 'Round Trip') {
            //             return redirect()->back()
            //                 ->with('error', "Round trip not allowed for {$passengerName} when requesting multiple tickets")
            //                 ->withInput();
            //         }
            //     }
            // }
        }

        if ($request->has('action_draft')) {
            $statusValue = 'Draft';  // When "Save as Draft" is clicked
        } elseif ($request->has('action_submit')) {
            $statusValue = 'Pending L1';  // When "Submit" is clicked
        }

        // Prepare the ticket data arrays
        $ticketData = [
            'noktp_tkt' => $request->noktp_tkt,
            'tlp_tkt' => $request->tlp_tkt,
            'jk_tkt' => $request->jk_tkt,
            'np_tkt' => $request->np_tkt,
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
            'tkt_only' => 'Y',
        ];
        // dd($ticketData);

        $noKtp = [];
        $npTkt = [];
        $dariTkt = [];
        $keTkt = [];
        $tglBrktTkt = [];
        $jamBrktTkt = [];
        $noTktList = [];
        $tglPlgTkt = [];
        $jamPlgTkt = [];
        $tipeTkt = [];

        $generatedNoTkt = $this->generateTicketNumber();

        foreach ($ticketData['np_tkt'] as $key => $selectedName) {
            // Check if the selected name is the employee
            if ($selectedName === $employeeName) {
                $gender = $employee->gender;
                $noTelp = $employee->personal_mobile_number;
            } else {
                // Handle dependents
                $dependent = Dependents::where('name', $selectedName)->first();
                if ($dependent) {
                    $gender = $dependent->gender;
                    $noTelp = $dependent->phone;
                } else {
                    // Handle the case where the name doesn't match employee or dependents
                    $gender = null;
                    $noTelp = null;
                }
            }
            if (!empty($selectedName)) {
                $tiket = new Tiket();
                $tiket->id = (string) Str::uuid();
                // Use the pre-generated ticket number
                $tiket->no_tkt = $generatedNoTkt;

                $userId = Auth::id();
                $tiket->no_sppd = $request->bisnis_numb;
                $tiket->user_id = $userId;
                $tiket->manager_l1_id = $managerL1;
                $tiket->manager_l2_id = ($isJobLevel->count() == 1) ? '-' : $managerL2;
                $tiket->contribution_level_code = $contribution_level_code;
                $tiket->unit = $request->unit;
                $tiket->jk_tkt = $gender;
                $tiket->np_tkt = $ticketData['np_tkt'][$key];
                $tiket->noktp_tkt = $ticketData['noktp_tkt'][$key];
                $tiket->tlp_tkt = $noTelp;
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
                $tiket->jns_dinas_tkt = 'Cuti';
                $tiket->tkt_only = 'Y';
                $tiket->no_sppd = '-';
                // dd($request->all());
                $tiket->save();

                $noKtp[] = $ticketData['noktp_tkt'][$key];
                $npTkt[] = $ticketData['np_tkt'][$key];
                $dariTkt[] = $ticketData['dari_tkt'][$key];
                $keTkt[] = $ticketData['ke_tkt'][$key];
                $tipeTkt[] = $ticketData['type_tkt'][$key];
                $tglBrktTkt[] = $ticketData['tgl_brkt_tkt'][$key];
                $jamBrktTkt[] = $ticketData['jam_brkt_tkt'][$key];
                $noTktList[] = $tiket->no_tkt;
                $tglPlgTkt[] = $ticketData['tgl_plg_tkt'][$key];
                $jamPlgTkt[] = $ticketData['jam_plg_tkt'][$key];
            }
        }

        if ($statusValue !== 'Draft') {
            $managerId = Employee::where('id', $userId)->pluck('manager_l1_id')->first();
            $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
            $managerEmail = Employee::where('employee_id', $managerId)->pluck('email')->first();
            // $managerEmail = "eriton.dewa@kpn-corp.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $managerName = Employee::where('employee_id', $managerId)->pluck('fullname')->first();
            $textNotification = "requesting a Home Trip and waiting for your Approval with the following details :";
            $approvalLink = route('approve.ticket', [
                'id' => urlencode($tiket->id),
                'manager_id' => $managerId,
                'status' => 'Pending L2'
            ]);

            $rejectionLink = route('reject.ticket.link', [
                'id' => urlencode($tiket->id),
                'manager_id' => $managerId,
                'status' => 'Rejected'
            ]);
            // // dd($managerEmail);
            if ($managerEmail) {
                // Send email to the manager
                try {
                    Mail::to($managerEmail)->send(new HomeTripNotification([
                        'noTkt' => $noTktList,
                        'namaPenumpang' => $npTkt,
                        'dariTkt' => $dariTkt,
                        'keTkt' => $keTkt,
                        'tglBrktTkt' => $tglBrktTkt,
                        'jamBrktTkt' => $jamBrktTkt,
                        'approvalStatus' => $statusValue,
                        'tipeTkt' => $tipeTkt,
                        'tglPlgTkt' => $tglPlgTkt,
                        'jamPlgTkt' => $jamPlgTkt,
                        'managerName' => $managerName,
                        'approvalLink' => $approvalLink,
                        'rejectionLink' => $rejectionLink,
                        'textNotification' => $textNotification,
                        'employeeName' => $employeeName,
                        'base64Image' => $base64Image,
                    ]));
                } catch (\Exception $e) {
                    Log::error('Email Create Home Trip tidak terkirim: ' . $e->getMessage());
                }
            }
        }
        return redirect()->route('home-trip')->with('success', 'The ticket request has been input successfully.');
    }

    public function homeTripFormUpdate($id)
    {
        $parentLink = 'Home Trip';
        $link = 'Edit Home Trip';

        $employee_id = Auth::user()->employee_id;
        $userId = Auth::user()->id;
        $currentYear = now()->year;

        $ticket = Tiket::findByRouteKey($id);
        $families = Dependents::orderBy('date_of_birth', 'desc')->where('employee_id', $employee_id)->get();

        $employee_data = Employee::where('employee_id', $employee_id)->first();
        $employee_name = Employee::select('fullname')
            ->where('employee_id', $employee_id)
            ->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $employees = Employee::orderBy('ktp')->get();

        $familyMembers = HomeTrip::where('employee_id', $employee_id)
            ->where('period', $currentYear)
            ->where('relation_type', '!=', 'employee')
            ->where('quota', '>', 0) // Only include family members with a quota > 0
            ->get();

        $employeeInHomeTrip = HomeTrip::where('employee_id', $employee_id)
            ->where('period', $currentYear)
            ->where('quota', '>', 0)
            ->where('relation_type', '=', 'employee')
            ->first();


        if (!$ticket) {
            return redirect()->route('ticket')->with('error', 'Ticket not found');
        }

        // Fetch all tickets associated with the same no_sppd for reference
        $tickets = Tiket::where('no_tkt', $ticket->no_tkt)->get();

        $transactions = $tickets;

        $ticketData = [];
        $ticketCount = $tickets->count();
        foreach ($tickets as $index => $ticket) {
            $ticketData[] = [
                'id' => $ticket->id,
                'noktp_tkt' => $ticket->noktp_tkt,
                'tlp_tkt' => $ticket->tlp_tkt,
                'jk_tkt' => $ticket->jk_tkt,
                'np_tkt' => $ticket->np_tkt,
                'dari_tkt' => $ticket->dari_tkt,
                'ke_tkt' => $ticket->ke_tkt,
                'tgl_brkt_tkt' => $ticket->tgl_brkt_tkt,
                'jam_brkt_tkt' => $ticket->jam_brkt_tkt,
                'jenis_tkt' => $ticket->jenis_tkt,
                'type_tkt' => $ticket->type_tkt,
                'tgl_plg_tkt' => $ticket->tgl_plg_tkt,
                'jam_plg_tkt' => $ticket->jam_plg_tkt,
                'ket_tkt' => $ticket->ket_tkt,
                'more_tkt' => ($index < $ticketCount - 1) ? 'Ya' : 'Tidak'
            ];
        }

        // dd($familyMembers);
        return view('hcis.reimbursements.homeTrip.form.editFormHt', [
            'link' => $link,
            'parentLink' => $parentLink,
            'families' => $families,
            'familyMembers' => $familyMembers,
            'employeeInHomeTrip' => $employeeInHomeTrip,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'employees' => $employees,
            'employee_name' => $employee_name,
            'transactions' => $transactions,
            'ticketData' => $ticketData,
            'ticket' => $ticket,
        ]);
    }

    public function homeTripUpdate(Request $request, $id)
    {
        $userId = Auth::id();
        $employee_id = Auth::user()->employee_id;
        $employee = Employee::where('employee_id', $employee_id)->first();
        $employeeName = $employee->fullname;
        $currentYear = now()->year;

        $employee_data = Employee::where('id', $userId)->first();
        $contribution_level_code = Employee::where('id', $userId)->pluck('contribution_level_code')->first();

        $deptHeadManager = $this->findDepartmentHead($employee_data);

        $managerL1 = $deptHeadManager->employee_id;
        $managerL2 = $deptHeadManager->manager_l1_id;

        $isJobLevel = MatrixApproval::where('modul', 'businesstrip')
            ->where('group_company', 'like', '%' . $employee->group_company . '%')
            ->where('job_level', 'like', '%' . $employee->job_level . '%')
            ->get();

        $npTkt = array_values($request->np_tkt);
        $selectedName = $npTkt[0] ?? null;
        // dd($request->np_tkt, $selectedName);

        $existingTickets = Tiket::where('id', $id)->get()->keyBy('id');
        $passengerRequests = [];
        foreach ($request->np_tkt as $key => $selectedName) {
            if (!isset($passengerRequests[$selectedName])) {
                $passengerRequests[$selectedName] = [
                    'tickets' => [],
                    'quota' => HomeTrip::where('employee_id', $employee_id)
                        ->where('period', $currentYear)
                        ->where('name', $selectedName)
                        ->pluck('quota')
                        ->first()
                ];
            }
            $passengerRequests[$selectedName]['tickets'][] = $request->type_tkt[$key];
        }

        // Validate each passenger's total ticket requests against their quota
        foreach ($passengerRequests as $passengerName => $data) {
            $quota = $data['quota'];
            $tickets = $data['tickets'];
            $quotaNeeded = 0;

            // Calculate total quota needed
            foreach ($tickets as $ticketType) {
                $quotaNeeded += ($ticketType == 'Round Trip') ? 2 : 1;
            }

            // Validation checks
            if ($quotaNeeded > $quota) {
                return redirect()->back()
                    ->with('error', "TThe trip requested for {$passengerName} exceeds the allocated quota")
                    ->withInput();
            }

            // If multiple tickets, prevent round trips
            if (count($tickets) > 1) {
                foreach ($tickets as $ticketType) {
                    if ($ticketType == 'Round Trip') {
                        return redirect()->back()
                            ->with('error', "Round trip not allowed for {$passengerName} when requesting multiple tickets")
                            ->withInput();
                    }
                }
            }
        }

        if ($request->has('action_draft')) {
            $statusValue = 'Draft';  // When "Save as Draft" is clicked
        } elseif ($request->has('action_submit')) {
            $statusValue = 'Pending L1';  // When "Submit" is clicked
        }

        $existingNoTkt = $existingTickets->first()->no_tkt ?? null;

        $noTktList = [];
        $npTkt = [];
        $dariTkt = [];
        $keTkt = [];
        $tglBrktTkt = [];
        $jamBrktTkt = [];
        $tglPlgTkt = [];
        $jamPlgTkt = [];
        $tipeTkt = [];

        foreach ($request->np_tkt as $key => $selectedName) {
            if (!empty($selectedName)) {
                // Check if the selected name is the employee
                if ($selectedName === $employeeName) {
                    $gender = $employee->gender;
                    $noTelp = $employee->personal_mobile_number;
                } else {
                    // Handle dependents
                    $dependent = Dependents::where('name', $selectedName)->first();
                    if ($dependent) {
                        $gender = $dependent->gender;
                        $noTelp = $dependent->phone;
                    } else {
                        // Handle the case where the name doesn't match employee or dependents
                        $gender = null;
                        $noTelp = null;
                    }
                }

                $ticketData = [
                    'user_id' => Auth::id(),
                    'manager_l1_id' => $managerL1,
                    'manager_l2_id' => ($isJobLevel->count() == 1) ? '-' : $managerL2,
                    'contribution_level_code' => $contribution_level_code,
                    'unit' => $request->unit,
                    'noktp_tkt' => $request->noktp_tkt[$key] ?? null,
                    'dari_tkt' => $request->dari_tkt[$key] ?? null,
                    'ke_tkt' => $request->ke_tkt[$key] ?? null,
                    'tgl_brkt_tkt' => $request->tgl_brkt_tkt[$key] ?? null,
                    'jam_brkt_tkt' => $request->jam_brkt_tkt[$key] ?? null,
                    'jenis_tkt' => $request->jenis_tkt[$key] ?? null,
                    'type_tkt' => $request->type_tkt[$key] ?? null,
                    'tgl_plg_tkt' => $request->tgl_plg_tkt[$key] ?? null,
                    'jam_plg_tkt' => $request->jam_plg_tkt[$key] ?? null,
                    'ket_tkt' => $request->ket_tkt[$key] ?? null,
                    'jk_tkt' => $gender,
                    'np_tkt' => $request->np_tkt[$key] ?? null,
                    'tlp_tkt' => $noTelp,
                    'approval_status' => $statusValue,
                    'jns_dinas_tkt' => 'Cuti',
                    'tkt_only' => 'Y',
                    'no_sppd' => '-',
                ];


                if (isset($existingTickets[$selectedName])) {
                    $existingTicket = $existingTickets[$selectedName];
                    $ticketData['no_tkt'] = $existingTicket->no_tkt; // Use the same no_tkt
                    $existingTicket->update($ticketData);
                    $processedTicketIds[] = $existingTicket->id;
                } else {
                    // If no existing ticket, use the existing no_tkt from the first ticket
                    $existingTicket = $existingTickets->first();
                    $ticketData['no_tkt'] = $existingTicket->no_tkt;
                    // dd($ticketData['no_tkt']);
                    $newTiket = Tiket::create(array_merge($ticketData, [
                        'id' => (string) Str::uuid(),
                        // 'noktp_tkt' => $request->noktp_tkt[$key] ?? null,
                        'tkt_only' => 'Y',
                    ]));
                    $processedTicketIds[] = $newTiket->id;
                }

                // Collect ticket data for email
                $noTktList[] = $ticketData['no_tkt'];
                $npTkt[] = $ticketData['np_tkt'];
                $dariTkt[] = $ticketData['dari_tkt'];
                $keTkt[] = $ticketData['ke_tkt'];
                $tipeTkt[] = $ticketData['type_tkt'];
                $tglBrktTkt[] = $ticketData['tgl_brkt_tkt'];
                $jamBrktTkt[] = $ticketData['jam_brkt_tkt'];
                $tglPlgTkt[] = $ticketData['tgl_plg_tkt'];
                $jamPlgTkt[] = $ticketData['jam_plg_tkt'];
            }
        }

        // Soft delete tickets that are no longer in the request
        Tiket::where('no_tkt', $existingNoTkt)
            ->whereNotIn('id', $processedTicketIds)
            ->delete();

        $ticketIdToUse = null;

        // Always use the first valid ID from the processed tickets, ensuring it's a ticket that exists in the final state.
        if (!empty($processedTicketIds)) {
            $ticketIdToUse = $processedTicketIds[0];  // Use the first updated/created ticket ID
        } elseif (!empty($existingTickets)) {
            // As a fallback, use the first existing ticket ID if no processed IDs were created
            $ticketIdToUse = $existingTickets->first()->id;
        }

        if ($statusValue !== 'Draft') {
            $managerId = Employee::where('id', Auth::id())->pluck('manager_l1_id')->first();
            // $managerEmail = Employee::where('employee_id', $managerId)->pluck('email')->first();
            $managerEmail = "eriton.dewa@kpn-corp.com";
            $managerName = Employee::where('employee_id', $managerId)->pluck('fullname')->first();
            $approvalLink = route('approve.ticket', [
                'id' => urlencode($ticketIdToUse),
                'manager_id' => $managerId,
                'status' => 'Pending L2'
            ]);

            $rejectionLink = route('reject.ticket.link', [
                'id' => urlencode($ticketIdToUse),
                'manager_id' => $managerId,
                'status' => 'Rejected'
            ]);

            if ($managerEmail) {
                // Send email to the manager with all ticket details
                try {
                    Mail::to($managerEmail)->send(new HomeTripNotification([
                        'noTkt' => $noTktList,  // all ticket numbers
                        'namaPenumpang' => $npTkt,  // all passengers
                        'dariTkt' => $dariTkt,  // all departure locations
                        'keTkt' => $keTkt,
                        'tipeTkt' => $tipeTkt,
                        'tglBrktTkt' => $tglBrktTkt,
                        'jamBrktTkt' => $jamBrktTkt,
                        'tglPlgTkt' => $tglPlgTkt,
                        'jamPlgTkt' => $jamPlgTkt,
                        'approvalStatus' => $statusValue,
                        'managerName' => $managerName,
                        'approvalLink' => $approvalLink,
                        'rejectionLink' => $rejectionLink,
                    ]));
                } catch (\Exception $e) {
                    Log::error('Email Update Home Trip tidak terkirim: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('home-trip')->with('success', 'The ticket request has been updated successfully.');

    }

    public function homeTripDelete($id)
    {
        $ticket = Tiket::findByRouteKey($id);
        // dd($ticket);
        Tiket::where('no_tkt', $ticket->no_tkt)->delete();

        // Redirect to the ticket page with a success message
        return redirect()->route('home-trip')->with('success', 'Home Trip has been deleted');
    }

    private function generateTicketNumber()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Get the last transaction for the current year, including deleted ones
        $lastTransaction = Tiket::whereYear('created_at', $currentYear)
            ->where('no_tkt', 'like', '%TKTC-HRD%')  // Keep the filter for 'TKTD-HRD'
            ->orderBy('no_tkt', 'desc')
            ->withTrashed()
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/TKTC-HRD\/([IVX]+)\/\d{4}/', $lastTransaction->no_tkt, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/TKTC-HRD/$romanMonth/$currentYear";

        // dd($newNoSppd);

        return $newNoSppd;
    }

    public function getRomanMonth($month)
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

    public function homeTripAdmin(Request $request)
    {
        $parentLink = 'Reimbursement';
        $link = 'Home Trip';
        $employee_id = Auth::user()->employee_id;
        $userId = Auth::user()->id;

        $userRole = auth()->user()->roles->first();
        $roleRestriction = json_decode($userRole->restriction, true);
        $restrictedWorkAreas = $roleRestriction['work_area_code'] ?? [];
        $restrictedGroupCompanies = $roleRestriction['group_company'] ?? [];

        if (!empty($restrictedWorkAreas)) {
            $locations = Location::orderBy('area')->whereIn('work_area', $restrictedWorkAreas)->get();
        } else {
            $locations = Location::orderBy('area')->get();
        }

        $currentYear = date('Y');

        $query = Employee::with(['employee', 'statusReqEmployee', 'statusSettEmployee']);

        $ht_employee = collect();
        $hasFilter = false;

        if (request()->get('stat') == '') {
        } else {
            if ($request->has('stat') && $request->input('stat') !== '') {
                $status = $request->input('stat');
                $query->where('work_area_code', $status);
                $hasFilter = true;
            }
        }

        if (request()->get('customsearch') == '') {
        } else {
            if ($request->has('customsearch') && $request->input('customsearch') !== '') {
                $customsearch = $request->input('customsearch');
                if (!empty($restrictedWorkAreas)) {
                    $query->where('fullname', 'LIKE', '%' . $customsearch . '%')
                        ->where('work_area_code', $restrictedWorkAreas);
                } else {
                    $query->where('fullname', 'LIKE', '%' . $customsearch . '%');
                }
                $hasFilter = true;
            }
        }

        if ($hasFilter) {
            $ht_employee = $query->orderBy('created_at', 'desc')->where('homebase', '!=', '')->get();
            foreach ($ht_employee as $ht_employees) {
                // Get family count
                $ht_employees->family_count = Dependents::where('employee_id', $ht_employees->employee_id)->count() + 1;

                // Get total quota from HomeTrip table for this employee
                $ht_employees->ticket_count = HomeTrip::where('employee_id', $ht_employees->employee_id)
                    ->where('period', $currentYear)
                    ->sum('quota');

                $latestPeriod = HomeTrip::where('employee_id', $ht_employees->employee_id)
                    ->orderBy('period', 'desc')
                    ->value('period');
                $ht_employees->latest_period = $latestPeriod ? $latestPeriod : '-';
            }
        }

        // // Uncomment This for Generate all ht_plan from admin Auto Input ht_plan Start
        // $currentYear = date('Y');
        // $employees_cast = Employee::where('homebase', '!=', '')->get();

        // foreach ($employees_cast as $employee) {
        //     // Get joining date
        //     $joiningDate = date_create($employee->date_of_joining);

        //     // Calculate the first January after 1 year of employment
        //     $oneYearAfterJoining = date_create($employee->date_of_joining);
        //     $oneYearAfterJoining->modify('+1 year');
        //     $eligibleYear = (int)$oneYearAfterJoining->format('Y') + 1;

        //     // If eligible year is current year or past year and no record exists, create records
        //     if ($eligibleYear <= $currentYear) {
        //         // Get all dependents for this employee
        //         $dependents = Dependents::where('employee_id', $employee->employee_id)->get();

        //         // First create record for the employee
        //         $existingHomeTrip = HomeTrip::where('employee_id', $employee->employee_id)
        //             ->where('period', $currentYear)
        //             ->where('relation_type', 'Employee')
        //             ->first();

        //         if (!$existingHomeTrip) {
        //             $homeTrip = new HomeTrip();
        //             $homeTrip->id = Str::uuid();
        //             $homeTrip->employee_id = $employee->employee_id;
        //             $homeTrip->name = $employee->fullname;
        //             $homeTrip->relation_type = 'Employee';
        //             $homeTrip->quota = '2';
        //             $homeTrip->period = $currentYear;
        //             $homeTrip->created_by = $userId;
        //             $homeTrip->save();
        //         }

        //         // Then create records for each dependent
        //         foreach ($dependents as $dependent) {
        //             $existingDependentTrip = HomeTrip::where('employee_id', $employee->employee_id)
        //                 ->where('period', $currentYear)
        //                 ->where('name', $dependent->name)
        //                 ->where('relation_type', $dependent->relation_type)
        //                 ->first();

        //             if (!$existingDependentTrip) {
        //                 $dependentTrip = new HomeTrip();
        //                 $dependentTrip->id = Str::uuid();
        //                 $dependentTrip->employee_id = $employee->employee_id;
        //                 $dependentTrip->name = $dependent->name;
        //                 $dependentTrip->relation_type = $dependent->relation_type;
        //                 $dependentTrip->quota = '2';
        //                 $dependentTrip->period = $currentYear;
        //                 $dependentTrip->created_by = $userId;
        //                 $dependentTrip->save();
        //             }
        //         }
        //     }
        // }
        // // Auto Input ht_plan End

        return view('hcis.reimbursements.homeTrip.admin.homeTripAdmin', compact(
            'link',
            'parentLink',
            'ht_employee',
            'locations',
            // 'family',
            // 'familyCount',
        ));
    }

    public function homeTripAdminDetail(Request $request, $key)
    {
        $parentLink = 'Reimbursement';
        $link = 'Home Trip';
        $userId = Auth::user()->id;

        $employee_id = decrypt($key);
        $fullname = Employee::where('employee_id', $employee_id)->pluck('fullname')->first();
        $user_id = Employee::where('employee_id', $employee_id)->pluck('id')->first();
        $family = Dependents::orderBy('date_of_birth', 'asc')->where('employee_id', $employee_id)->get();

        $query = Tiket::where('user_id', $user_id)->orderBy('created_at', 'desc');

        $yearNow = Carbon::now()->year; // Current year (e.g., 2024)
        $yearStart = $yearNow - 2; // Start year (e.g., 2022)

        // Fetch data filtered by the last 3 years
        $plafonds = HomeTrip::where('employee_id', $employee_id)
            ->whereBetween('period', [$yearStart, $yearNow]) // Filter for 2022 to 2024
            ->orderBy('name') // Order by name
            ->orderBy('period') // Then by period
            ->get();

        // Group data by period
        $plafonds = $plafonds->groupBy('period');

        $latestTicketIds = Tiket::selectRaw('MAX(id) as id')
            ->where('user_id', $user_id)
            ->groupBy('no_tkt')
            ->pluck('id');

        $transactions = Tiket::whereIn('id', $latestTicketIds)
            ->where('jns_dinas_tkt', '=', 'Cuti')
            // ->where('approval_status', $statusFilter)
            ->orderBy('created_at', 'desc')
            ->select('id', 'no_tkt', 'np_tkt', 'type_tkt', 'jenis_tkt', 'dari_tkt', 'ke_tkt', 'approval_status', 'jns_dinas_tkt', 'user_id', 'no_sppd')
            ->get();

        // Get all tickets for user
        $tickets = Tiket::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();
        $tiketIds = $tickets->pluck('id');

        $ticketApprovals = TiketApproval::whereIn('tkt_id', $tiketIds)
            ->where(function ($query) {
                $query->where('approval_status', 'Rejected');
            })
            ->get();

        $ticketApprovals = $ticketApprovals->keyBy('tkt_id');
        $employeeName = Employee::pluck('fullname', 'employee_id');

        $managerL1Names = 'Unknown';
        $managerL2Names = 'Unknown';

        $employeeIds = $tickets->pluck('user_id')->unique();
        foreach ($transactions as $transaction) {
            // Fetch the employee for the current transaction
            $employee = Employee::find($transaction->user_id);
            // dd($employee);
            // If the employee exists, fetch their manager names
            if ($employee) {
                $managerL1Id = $employee->manager_l1_id;
                $managerL2Id = $employee->manager_l2_id;

                $managerL1 = Employee::where('employee_id', $managerL1Id)->first();
                $managerL2 = Employee::where('employee_id', $managerL2Id)->first();

                $managerL1Names = $managerL1 ? $managerL1->fullname : 'Unknown';
                $managerL2Names = $managerL2 ? $managerL2->fullname : 'Unknown';
            }
        }

        $ticket = $tickets->groupBy('no_tkt');

        $ticketCounts = $tickets->groupBy('no_tkt')->mapWithKeys(function ($group, $key) {
            return [$key => ['total' => $group->count()]];
        });

        $dependents_fam = Dependents::where('employee_id', $employee_id)->get();
        $periods = HomeTrip::where('employee_id', $employee_id)->orderBy('period', 'desc')->pluck('period')->unique();

        $formatted_data = $periods->mapWithKeys(function ($period) use ($employee_id, $dependents_fam) {
            $data = ['employee' => HomeTrip::where('employee_id', $employee_id)->where('period', $period)->where('relation_type', 'Employee')->value('quota') ?? '-'];

            foreach ($dependents_fam as $dependent) {
                $data[$dependent->name] = HomeTrip::where('employee_id', $employee_id)
                    ->where('period', $period)
                    ->where('name', $dependent->name)
                    ->value('quota') ?? '-';
            }

            return [$period => $data];
        })->toArray();

        $currentYear = date('Y');
        $employees_cast = Employee::where('employee_id', $employee_id)->where('homebase', '!=', '')->get();

        foreach ($employees_cast as $employee) {
            $joiningDate = date_create($employee->date_of_joining);
            $monthDay = $joiningDate->format('m-d');
            $eligibleYear = (int) $joiningDate->format('Y') + 1;

            if ($eligibleYear <= $currentYear) {
                $dependents = Dependents::where('employee_id', $employee->employee_id)->get();
                $totalFamilyMembers = $dependents->count() + 1;

                $quota = $totalFamilyMembers * 2;

                $existingHomeTrip = HomeTrip::where('employee_id', $employee->employee_id)
                    ->where('period', $currentYear)
                    ->where('relation_type', 'Employee')
                    ->first();

                if (!$existingHomeTrip) {
                    $homeTrip = new HomeTrip();
                    $homeTrip->id = Str::uuid();
                    $homeTrip->employee_id = $employee->employee_id;
                    $homeTrip->name = $employee->fullname;
                    $homeTrip->relation_type = 'Employee';
                    $homeTrip->quota = $quota;
                    $homeTrip->last_generate = $currentYear . '-' . $monthDay;
                    $homeTrip->period = $currentYear;
                    $homeTrip->created_by = $userId;
                    $homeTrip->save();

                    session()->flash('refresh', true);
                }
            }
        }

        return view('hcis.reimbursements.homeTrip.admin.homeTripDetailAdmin', compact(
            'parentLink',
            'link',
            'family',
            'transactions',
            'tickets',
            'ticket',
            'ticketApprovals',
            'employeeName',
            'ticketCounts',
            'managerL1Names',
            'managerL2Names',
            'dependents_fam',
            'employee_id',
            'formatted_data',
            'fullname',
            'plafonds',
        ));
    }
}
