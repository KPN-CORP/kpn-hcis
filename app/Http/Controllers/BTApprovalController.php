<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use App\Models\Tiket;
use App\Models\ca_sett_approval;
use App\Models\ListPerdiem;
use App\Models\TiketApproval;
use App\Models\HotelApproval;
use App\Models\TaksiApproval;
use App\Models\HealthCoverage;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\DB;
use App\Models\MatrixApproval;
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

class BTApprovalController extends Controller
{
    public function rejectLink(Request $request, $id, $manager_id, $status)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $employee_data = Employee::where('id', $n->user_id)->first();
        // dd($employee_data);
        $ca = CATransaction::where('no_sppd', $n->no_sppd)->first();

        // Initialize caDetail with an empty array if it's null
        $caDetail = $ca ? json_decode($ca->detail_ca, true) : [];
        $declareCa = $ca ? json_decode($ca->declare_ca, true) : [];

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
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();

        $parentLink = 'Business Trip Approval';
        $link = 'Approval Details';

        return view('hcis.reimbursements.businessTrip.btReject', [
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
            'nominalPerdiem' => $nominalPerdiem,
            'nominalPerdiemDeclare' => $nominalPerdiemDeclare,
            'hasCaData' => $hasCaData,
            'parentLink' => $parentLink,
            'link' => $link,
            'manager_id' => $manager_id,
            'status' => $status,
        ]);
    }


    public function rejectFromLink($id, $manager_id, $status, Request $request)
    {
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();
        // dd($id, $status, $manager_id);
        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);
        $employeeId = $manager_id;
        // Determine the new status and layer based on the action and manager's role
        $oldStatus = $businessTrip->status;
        // dd($oldStatus);
        // dd($request->reject_info);
        $statusValue = 'Rejected';
        if ($oldStatus == 'Pending L1') {
            $layer = 1;
        } elseif ($oldStatus == 'Pending L2') {
            $layer = 2;
        } else {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        if ($businessTrip->ca == 'Ya') {
            $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
            if ($caTransaction && $caTransaction->caonly != 'Y') {
                // Update CA approval status for L1 or L2 as Rejected
                ca_approval::updateOrCreate(
                    ['ca_id' => $caTransaction->id, 'employee_id' => $employeeId, 'layer' => $layer],
                    ['approval_status' => $statusValue, 'approved_at' => now(), 'reject_info' => $request->reject_info] // Save rejection info
                );

                $caTransaction->update(['approval_status' => 'Rejected']);
            }
        }
        if ($businessTrip->tiket == 'Ya') {
            $tikets = Tiket::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($tikets as $tiket) {
                if ($tiket->tkt_only != 'Y') {
                    // Update each tiket record with the new approval status
                    $tiket->update([
                        'approval_status' => $statusValue,
                    ]);

                    // Record the rejection in TiketApproval
                    $approval_tkt = new TiketApproval();
                    $approval_tkt->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_tkt->tkt_id = $tiket->id;
                    $approval_tkt->employee_id = $employeeId;
                    $approval_tkt->layer = $layer;
                    $approval_tkt->approval_status = $statusValue;
                    $approval_tkt->approved_at = now();
                    $approval_tkt->reject_info = $request->reject_info;
                    $approval_tkt->save();
                }
            }
        }

        if ($businessTrip->hotel == 'Ya') {
            $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
            foreach ($hotels as $hotel) {
                if ($hotel->hotel_only != 'Y') {
                    // Update each hotel record with the new approval status
                    $hotel->update([
                        'approval_status' => $statusValue,
                    ]);

                    // Record the rejection in HotelApproval
                    $approval_htl = new HotelApproval();
                    $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                    $approval_htl->htl_id = $hotel->id;
                    $approval_htl->employee_id = $employeeId;
                    $approval_htl->layer = $layer;
                    $approval_htl->approval_status = $statusValue;
                    $approval_htl->approved_at = now();
                    $approval_htl->reject_info = $request->reject_info;
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
                $approval_vt->layer = $layer; // Set layer to 2 for rejected cases
                $approval_vt->approval_status = $statusValue;
                $approval_vt->approved_at = now();
                $approval_vt->reject_info = $request->reject_info;
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
        $approval->reject_info = $request->reject_info;
        $approval->employee_id = $employeeId;

        // Save the approval record
        $approval->save();
    }

    public function approveFromLink($id, $manager_id, $status)
    {
        $user = Auth::user();
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();
        // dd($id, $status, $manager_id);
        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);
        $employeeId = $manager_id;

        // Determine the new status and layer based on the action and manager's role
        if ($status == 'Pending L2') {
            $statusValue = 'Pending L2';
            $layer = 1;
            $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
            // dd($managerL2, $status);

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $employeeName = Employee::where('id', $businessTrip->user_id)->pluck('fullname')->first();
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Bussiness Trip and waiting for your Approval with the following details :";

            $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();

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

                $taksiDetails = Taksi::where('no_sppd', $businessTrip->no_sppd)->first();
                // dd($taksiDetails);
                $approvalLink = route('approve.business.trip', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Approved',
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
                        $hotelDetails,  // Pass hotel details
                        $ticketDetails,
                        $taksiDetails,
                        $caDetails,
                        $managerName,
                        $approvalLink,
                        $rejectionLink,
                        $employeeName,
                        $base64Image,
                        $textNotification,
                    ));   
                } catch (\Exception $e) {
                	Log::error('Link Email Approval Bussines Trip tidak terkirim: ' . $e->getMessage());
                }
            }

            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        // Update the existing hotel record with the new approval status
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
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
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->save();
                    }
                }
            }

            // Handle CA approval for L1
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($caTransaction && $caTransaction->caonly != 'Y') {
                    // Update CA approval status for L1
                    ca_approval::updateOrCreate(
                        ['ca_id' => $caTransaction->id, 'employee_id' => $employeeId, 'layer' => $layer],
                        ['approval_status' => 'Approved', 'approved_at' => Carbon::now()]
                    );

                    // Find the next approver (Layer 2) from ca_approval
                    $nextApproval = ca_approval::where('ca_id', $caTransaction->id)
                        ->where('layer', $layer + 1)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransaction->id)->first();
                        $updateCa->status_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransaction->update(['approval_status' => 'Approved']);
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
            $approval->reject_info = null;
            $approval->employee_id = $employeeId;

            // Save the approval record
            $approval->save();

        } elseif ($status == 'Approved') {
            $statusValue = 'Approved';
            $layer = 2;
            if ($businessTrip->hotel == 'Ya') {
                $hotels = Hotel::where('no_sppd', $businessTrip->no_sppd)->get();
                foreach ($hotels as $hotel) {
                    if ($hotel->hotel_only != 'Y') {
                        // Update the existing hotel record with the new approval status
                        $hotel->update([
                            'approval_status' => $statusValue,
                        ]);
                        $approval_htl = new HotelApproval();
                        $approval_htl->id = (string) Str::uuid(); // Generate a UUID for the approval record
                        $approval_htl->htl_id = $hotel->id;
                        $approval_htl->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_htl->layer = $layer; // Set layer to 2 for rejected cases
                        $approval_htl->approval_status = $statusValue;
                        $approval_htl->approved_at = now();
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
                        $approval_tkt->employee_id = $employeeId; // Assuming the logged-in user's employee ID is needed
                        $approval_tkt->layer = $tiket->approval_status == 'Pending L2' ? 1 : 2; // Determine layer based on status
                        $approval_tkt->approval_status = $statusValue;
                        $approval_tkt->approved_at = now();
                        $approval_tkt->save();
                    }
                }
                // Handle CA approval for L2
                if ($businessTrip->ca == 'Ya') {
                    $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
                    if ($caTransaction && $caTransaction->caonly != 'Y') {
                        // Update CA approval status for L2
                        ca_approval::updateOrCreate(
                            ['ca_id' => $caTransaction->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => now()]
                        );

                        // Find the next approver (Layer 3) explicitly
                        $nextApproval = ca_approval::where('ca_id', $caTransaction->id)
                            ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                            ->first();

                        if ($nextApproval) {
                            $updateCa = CATransaction::where('id', $caTransaction->id)->first();
                            $updateCa->status_id = $nextApproval->employee_id;
                            $updateCa->save();
                        } else {
                            // No next layer, so mark as Approved
                            $caTransaction->update(['approval_status' => 'Approved']);
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
            $approval->reject_info = null;
            $approval->employee_id = $employeeId;

            // Save the approval record
            $approval->save();
        }
    }
    public function approveFromLinkDeklarasi($id, $manager_id, $status)
    {
        $employeeId = $manager_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);

        if ($employeeId == $businessTrip->manager_l1_id) {
            $statusValue = 'Declaration L2';
            $layer = 1;
            $managerL2 = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('email')->first();
            $managerName = Employee::where('employee_id', $businessTrip->manager_l2_id)->pluck('fullname')->first();

            // dd($managerL2);
            if ($managerL2) {
                $approvalLink = route('approve.business.trip.declare', [
                    'id' => urlencode($businessTrip->id),
                    'manager_id' => $businessTrip->manager_l2_id,
                    'status' => 'Declaration Approved'
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
                    ->first();
                $detail_ca = isset($caTrans) && isset($caTrans->detail_ca) ? json_decode($caTrans->detail_ca, true) : [];
                $declare_ca = isset($caTrans) && isset($caTrans->declare_ca) ? json_decode($caTrans->declare_ca, true) : [];
                // dd( $detail_ca, $caTrans);

                // dd($caTrans, $n->no_sppd);
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
                // dd($caDetails,   $detail_ca );

                $declare_ca = isset($declare_ca) ? $declare_ca : [];
                $caDeclare = [
                    'total_days_perdiem' => array_sum(array_column($declare_ca['detail_perdiem'] ?? [], 'total_days')),
                    'total_amount_perdiem' => array_sum(array_column($declare_ca['detail_perdiem'] ?? [], 'nominal')),

                    'total_days_transport' => count($declare_ca['detail_transport'] ?? []),
                    'total_amount_transport' => array_sum(array_column($declare_ca['detail_transport'] ?? [], 'nominal')),

                    'total_days_accommodation' => array_sum(array_column($declare_ca['detail_penginapan'] ?? [], 'total_days')),
                    'total_amount_accommodation' => array_sum(array_column($declare_ca['detail_penginapan'] ?? [], 'nominal')),

                    'total_days_others' => count($declare_ca['detail_lainnya'] ?? []),
                    'total_amount_others' => array_sum(array_column($declare_ca['detail_lainnya'] ?? [], 'nominal')),
                ];

                // Send email to the manager
                try {
                    Mail::to($managerL2)->send(new DeclarationNotification(
                        $businessTrip,
                        $caDetails,
                        $caDeclare,
                        $managerName,
                        $approvalLink,
                        $rejectionLink,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email Deklarasi Approval Bussines Trip tidak terkirim: ' . $e->getMessage());
                }
            }
            // Handle CA approval for L1
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($caTransaction) {
                    // Update CA approval status for L1
                    ca_sett_approval::where('ca_id', $caTransaction->id)
                        ->where('employee_id', $employeeId)
                        ->where('layer', $layer)
                        ->where('approval_status', '!=', 'Rejected')  // Only update if status isn't "Declaration Rejected"
                        ->updateOrCreate(
                            ['ca_id' => $caTransaction->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => now()]
                        );

                    // Find the next approver (Layer 2) from ca_approval
                    $nextApproval = ca_sett_approval::where('ca_id', $caTransaction->id)
                        ->where('layer', $layer + 1)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransaction->id)->first();
                        $updateCa->sett_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransaction->update(['approval_sett' => 'Approved']);
                    }
                }
            }
        } elseif ($employeeId == $businessTrip->manager_l2_id) {
            $statusValue = 'Declaration Approved';
            $layer = 2;

            // Handle CA approval for L2
            if ($businessTrip->ca == 'Ya') {
                $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
                if ($caTransaction) {
                    // Update CA approval status for L2
                    ca_sett_approval::where('ca_id', $caTransaction->id)
                        ->where('employee_id', $employeeId)
                        ->where('layer', $layer)
                        ->where('approval_status', '!=', 'Rejected')  // Only update if status isn't "Declaration Rejected"
                        ->updateOrCreate(
                            ['ca_id' => $caTransaction->id, 'employee_id' => $employeeId, 'layer' => $layer],
                            ['approval_status' => 'Approved', 'approved_at' => now()]
                        );

                    // Find the next approver (Layer 3) explicitly
                    $nextApproval = ca_sett_approval::where('ca_id', $caTransaction->id)
                        ->where('layer', $layer + 1) // This will ensure it gets the immediate next layer (3)
                        ->first();

                    if ($nextApproval) {
                        $updateCa = CATransaction::where('id', $caTransaction->id)->first();
                        $updateCa->sett_id = $nextApproval->employee_id;
                        $updateCa->save();
                    } else {
                        // No next layer, so mark as Approved
                        $caTransaction->update(['approval_sett' => 'Approved']);
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
        $approval->employee_id = $employeeId;

        // Save the approval record
        $approval->save();
    }

    public function rejectDeclarationLink(Request $request, $id, $manager_id, $status)
    {
        $n = BusinessTrip::find($id);
        $userId = Auth::id();
        $employee_data = Employee::where('id', $n->user_id)->first();
        // dd($employee_data);
        $ca = CATransaction::where('no_sppd', $n->no_sppd)->first();

        // Initialize caDetail with an empty array if it's null
        $caDetail = $ca ? json_decode($ca->detail_ca, true) : [];
        $declareCa = $ca ? json_decode($ca->declare_ca, true) : [];

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
        $locations = Location::orderBy('area')->get();
        $companies = Company::orderBy('contribution_level')->get();

        $parentLink = 'Business Trip Approval';
        $link = 'Approval Details';

        return view('hcis.reimbursements.businessTrip.btRejectDeclaration', [
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
            'nominalPerdiem' => $nominalPerdiem,
            'nominalPerdiemDeclare' => $nominalPerdiemDeclare,
            'hasCaData' => $hasCaData,
            'parentLink' => $parentLink,
            'manager_id' => $manager_id,
            'status' => $status,
            'link' => $link,
        ]);
    }
    public function rejectDeclarationFromLink(Request $request, $id, $manager_id, $status)
    {
        $employeeId = $manager_id;
        $approval = new BTApproval();
        $approval->id = (string) Str::uuid();

        // Find the business trip by ID
        $businessTrip = BusinessTrip::findOrFail($id);
        $rejectInfo = $request->reject_info;

        // Determine the new status and layer based on the action and manager's role
        $oldStatus = $businessTrip->status;
        $statusValue = 'Declaration Rejected';
        if ($oldStatus == 'Declaration L1') {
            $layer = 1;
        } elseif ($oldStatus == 'Declaration L2') {
            $layer = 2;
        } else {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $caTransaction = CATransaction::where('no_sppd', $businessTrip->no_sppd)->first();
        // dd($caTransaction->id);
        // dd( $caTransaction, $caTransaction->caonly != 'Y');
        if ($caTransaction && $caTransaction->caonly != 'Y') {
            // Update all records with the same ca_id to 'Rejected' status
            ca_sett_approval::where('ca_id', $caTransaction->id)
                ->update(['approval_status' => 'Rejected', 'reject_info' => $rejectInfo, 'approved_at' => now()]);

            // Update the main CA transaction approval status
            $caTransaction->update(['approval_sett' => 'Rejected']);
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
        $approval->save();
    }
}
