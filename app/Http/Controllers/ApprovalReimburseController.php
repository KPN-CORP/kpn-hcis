<?php

namespace App\Http\Controllers;

use App\Models\ca_approval;
use App\Models\ca_extend;
use App\Models\HomeTrip;
use Illuminate\Support\Facades\Auth;
use App\Models\CATransaction;
use App\Models\BusinessTrip;
use App\Models\Tiket;
use App\Models\Hotel;
use App\Models\HealthCoverage;
use App\Models\Company;
use App\Models\Designation;
use App\Models\htl_transaction;
use App\Models\Employee;
use App\Models\Location;
use App\Models\ca_transaction;
use App\Models\ListPerdiem;
use App\Models\ca_sett_approval;
use App\Models\MatrixApproval;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use App\Mail\CashAdvancedNotification;
use App\Mail\HotelNotification;
use App\Mail\TicketNotification;
use App\Mail\HomeTripNotification;
use App\Models\TiketApproval;
use App\Models\HotelApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ApprovalReimburseController extends Controller
{
    public function reimbursementsApproval()
    {
        $userId = Auth::id();

        return view('hcis.reimbursements.approval.approval', [
            'userId' => $userId,
        ]);
    }
    public function cashadvancedApproval()
    {
        $userId = Auth::id();
        $user = Auth::user();
        $parentLink = 'Approval';
        $link = 'Cash Advanced Approval';
        $employeeId = auth()->user()->employee_id;
        $employee = Employee::where('id', $userId)->first();  // Authenticated user's employee record

        $ca_transactions = CATransaction::with('employee')->where('status_id', $employeeId)->where('approval_status', 'Pending')->get();
        $ca_transactions_dec = CATransaction::with('employee')->where('sett_id', $employeeId)->where('approval_sett', 'Pending')->get();
        $ca_transactions_ext = CATransaction::with('employee')->where('extend_id', $employeeId)->where('approval_extend', 'Pending')->get();

        $fullnames_req = ca_approval::whereIn('ca_id', $ca_transactions->pluck('id'))
            ->whereNotIn('approval_status', ['Verified', 'Rejected'])
            ->orderBy('created_at', 'desc') // Urutkan dari tanggal terbaru ke lama
            ->orderBy('layer', 'asc') // Urutkan layer dari kecil ke besar dalam tanggal yang sama
            ->get()
            ->groupBy('ca_id')
            ->map(function ($approvals) {
                return $approvals->map(function ($approval) {
                    return [
                        'role_name' => $approval->role_name,
                        'employee_id' => $approval->employee->fullname,
                        'approval_status' => $approval->approval_status,
                    ];
                })->values()->toArray();
            });

        $fullnames_dec = ca_sett_approval::whereIn('ca_id', collect($ca_transactions_dec)->pluck('id'))
            ->whereNotIn('approval_status', ['Verified', 'Rejected'])    
            ->orderBy('created_at', 'desc') 
            ->orderBy('layer', 'asc') 
            ->get()
            ->groupBy('ca_id')
            ->map(function ($approvals) {
                return $approvals->map(function ($approval) {
                    $employee = Employee::where('employee_id', $approval->employee_id)->first();
                    return [
                        'role_name' => $approval->role_name,
                        'employee_id' => optional($employee)->fullname ?? 'Unknown',
                        'approval_status' => $approval->approval_status,
                    ];
                })->values()->toArray();
            });
        
        $fullnames_ext = ca_extend::whereIn('ca_id', $ca_transactions_ext->pluck('id'))  
            ->whereNotIn('approval_status', ['Verified', 'Rejected']) // Menggunakan whereNotIn()    
            ->orderBy('created_at', 'desc') // Urutkan dari tanggal terbaru ke lama
            ->orderBy('layer', 'asc') // Urutkan layer dari kecil ke besar dalam tanggal yang sama
            ->get()
            ->groupBy('ca_id')
            ->map(function ($approvals) {
                return $approvals->map(function ($approval) {
                    return [
                        'role_name' => $approval->role_name,
                        'employee_id' => $approval->employee->fullname,
                        'approval_status' => $approval->approval_status,
                    ];
                })->values()->toArray();
            });
        
        $extendData = ca_extend::whereIn('ca_id', $ca_transactions_ext->pluck('id'))
            ->get(['ca_id', 'ext_end_date', 'ext_total_days', 'reason_extend']);

        $extendTime = $extendData->keyBy('ca_id')->map(function ($item) {
            return [
                'ext_end_date' => $item->ext_end_date,
                'ext_total_days' => $item->ext_total_days,
                'reason_extend' => $item->reason_extend,
            ];
        });

        // Hitung Pending Request, Deklarasi, Extend dan Hotel
        $pendingCACount = CATransaction::where('status_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingDECCount = CATransaction::where('sett_id', $employeeId)->where('approval_sett', 'Pending')->count();
        $pendingEXCount = CATransaction::where('extend_id', $employeeId)->where('approval_extend', 'Pending')->count();
        $totalPendingCount = $pendingCACount + $pendingDECCount + $pendingEXCount;
        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

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

        $totalBTCount = BusinessTrip::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_l1_id', $user->employee_id)
                    ->whereIn('status', ['Pending L1', 'Declaration L1']);
            })->orWhere(function ($q) use ($user) {
                $q->where('manager_l2_id', $user->employee_id)
                    ->whereIn('status', ['Pending L2', 'Declaration L2']);
            });
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

        // Memformat tanggal
        foreach ($ca_transactions as $transaction) {
            $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
            $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
        }

        return view('hcis.reimbursements.approval.approvalCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingDECCount' => $pendingDECCount,
            'pendingEXCount' => $pendingEXCount,
            'totalPendingCount' => $totalPendingCount,
            'totalBTCount' => $totalBTCount,
            'totalTKTCount' => $totalTKTCount,
            'totalHTLCount' => $totalHTLCount,
            'totalMDCCount' => $totalMDCCount,
            'fullnames_req' => $fullnames_req,
            'fullnames_dec' => $fullnames_dec,
            'fullnames_ext' => $fullnames_ext,
            'extendTime' => $extendTime,
            'extendData' => $extendData,
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
            'ca_transactions_dec' => $ca_transactions_dec,
            'ca_transactions_ext' => $ca_transactions_ext,
            'pendingHTLCount' => $pendingHTLCount,
        ]);
    }
    public function cashadvancedFormApproval($key)
    {
        $userId = Auth::id();
        $parentLink = 'Approval';
        $link = 'Cash Advanced Approval';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = CATransaction::findByRouteKey($key);

        return view('hcis.reimbursements.approval.listApproveCashadv', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    function cashadvancedActionApproval(Request $req, $ca_id)
    {
        $userId = Auth::id();
        $employeeId = auth()->user()->employee_id;
        $model = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->firstOrFail();

        // Cek apakah ini sudah di-approve atau tidak
        if ($model->approval_status == 'Approved') {
            return redirect()->route('approval.cashadvanced')->with('Warning', 'This approval has already been approved.');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            $caApprovals = ca_approval::where('ca_id', $ca_id)->get();
            if ($caApprovals->isNotEmpty()) {
                foreach ($caApprovals as $caApproval) {
                    $caApproval->approval_status = 'Rejected';
                    $caApproval->approved_at = Carbon::now();
                    $caApproval->reject_info = $req->reject_info;
                    $caApproval->save();
                }
            }
            // ->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_status = 'Rejected';
                $caTransaction->total_ca = 0;
                $caTransaction->total_cost = $caTransaction->total_ca - $caTransaction->total_real;
                if ($caTransaction->type_ca == 'entr') {
                    $caTransaction->detail_ca = '{"detail_e":[],"relation_e":[]}';
                    $caTransaction->declare_ca = '{"detail_e":[],"relation_e":[]}';
                }
                if ($caTransaction->type_ca == 'dns') {
                    $caTransaction->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                    $caTransaction->declare_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                }
                $caTransaction->save();
            }

            $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            // $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected please discuss further with your supervisor :";
                try {
                    try {
                        Mail::to($CANotificationLayer)->cc('eriton.dewa@kpn-corp.com')->send(new CashAdvancedNotification(
                            null,
                            $caTransaction,
                            $textNotification,
                            null,
                            null,
                            null,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('approval.cashadvanced')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($req->input('action_ca_approve')) {
            $nextApproval = null;


            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_status = 'Approved'; // Set ke ID user layer tertinggi
                    // $caTransaction->approval_sett = 'On Progress';
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Cash Advanced request has been Approved, please check your request again or can download your submission in the email attachment :";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                null,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now();
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->status_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} apply for Cash Advanced with details as follows:";

                    $linkApprove = route('approval.email.aproved', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);

                    // $pdfContent = $this->cashadvancedDownload (encrypt($caTransaction->id));
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            null,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('approval.cashadvanced')->with('success', 'Transaction Approved, Thanks for Approving.');
    }

    function cashadvancedActionApprovalEmail(Request $req, $ca_id, $employeeId)
    {
        // $userId = Auth::id();
        // $employeeId = auth()->user()->employee_id;
        $model = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->firstOrFail();
        // dd($ca_id);

        // Cek apakah ini sudah di-approve atau tidak
        if ($model->approval_status == 'Approved') {
            return redirect()->route('blank.pageUn')->with('success', 'This approval has already been approved.');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        $action = $req->query('action');

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            $caApprovals = ca_approval::where('ca_id', $ca_id)->get();
            if ($caApprovals->isNotEmpty()) {
                foreach ($caApprovals as $caApproval) {
                    $caApproval->approval_status = 'Rejected';
                    $caApproval->approved_at = Carbon::now();
                    $caApproval->reject_info = $req->reject_info;
                    $caApproval->save();
                }
            }
            // ->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_status = 'Rejected';
                $caTransaction->total_ca = 0;
                $caTransaction->total_cost = $caTransaction->total_ca - $caTransaction->total_real;
                if ($caTransaction->type_ca == 'entr') {
                    $caTransaction->detail_ca = '{"detail_e":[],"relation_e":[]}';
                    $caTransaction->detail_ca = '{"detail_e":[],"relation_e":[]}';
                }
                if ($caTransaction->type_ca == 'dns') {
                    $caTransaction->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                    $caTransaction->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                }
                $caTransaction->save();
            }

            $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            // $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor: ";
                try {
                    Mail::to($CANotificationLayer)->cc('eriton.dewa@kpn-corp.com')->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('blank.pageUn')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($action === 'approve') {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }
            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_status = 'Approved'; // Set ke ID user layer tertinggi
                    // $caTransaction->approval_sett = 'On Progress';
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Cash Advanced request has been Approved, please check your request again or can download your submission in the email attachment: ";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                null,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now();
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->status_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} apply for Cash Advanced with details as follows:";

                    $linkApprove = route('approval.email.aproved', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            null,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            }
        }
    }

    public function cashadvancedActionApprovalAdmin(Request $req, $ca_id)
    {
        // Ambil dataNoId dari request
        $dataNoId = $req->input('data_no_id');
        $model = ca_approval::where('ca_id', $ca_id)
            ->where('id', $dataNoId)
            ->first();

        if (!$model) {
            return redirect()->route('cashadvanced.admin')->with('error', 'Approval not found for this transaction.');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        if ($req->input('action_ca_reject')) {
            $caApprovals = ca_approval::where('ca_id', $ca_id)->get();
            if ($caApprovals->isNotEmpty()) {
                foreach ($caApprovals as $caApproval) {
                    $caApproval->approval_status = 'Rejected';
                    $caApproval->approved_at = Carbon::now();
                    $caApproval->reject_info = $req->reject_info;
                    $caApproval->by_admin = 'T';
                    $caApproval->admin_id = Auth::id();
                    $caApproval->save();
                }
            }
            // ->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_status = 'Rejected';
                $caTransaction->total_ca = 0;
                $caTransaction->total_cost = $caTransaction->total_ca - $caTransaction->total_real;
                if ($caTransaction->type_ca == 'entr') {
                    $caTransaction->detail_ca = '{"detail_e":[],"relation_e":[]}';
                    $caTransaction->detail_ca = '{"detail_e":[],"relation_e":[]}';
                }
                if ($caTransaction->type_ca == 'dns') {
                    $caTransaction->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                    $caTransaction->detail_ca = '{"detail_perdiem":[],"detail_meals":[],"detail_transport":[],"detail_penginapan":[],"detail_lainnya":[]}';
                }
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor.: ";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Transaction Rejected, Rejection will be send to the employee.')
                ->with('refresh', true);
        }

        if ($req->input('action_ca_approve')) {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update approval_status pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_status = 'Approved'; // Set ke Approved untuk transaksi
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    // dd($CANotificationLayer);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Cash Advanced request has been Approved, please check your request again or can download your submission in the email attachment :";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                null,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_approval::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->status_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                // $CANotificationLayer = "eriton.dewa@kpn-corp.com";
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);

                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} apply for Cash Advanced with details as follows:";
                    $linkApprove = route('approval.email.aproved', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            null,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->back()
            ->with('success', 'Transaction approved successfully.')
            ->with('refresh', true);
    }

    public function cashadvancedDeklarasi()
    {
        $userId = Auth::id();
        $parentLink = 'Approval';
        $link = 'Cash Advanced';
        $employeeId = auth()->user()->employee_id;

        $ca_transactions = CATransaction::with('employee')->where('sett_id', $employeeId)->where('approval_sett', 'Pending')->get();

        $fullnames = Employee::whereIn('employee_id', $ca_transactions->pluck('sett_id'))
            ->pluck('fullname', 'employee_id');

        $pendingCACount = CATransaction::where('status_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingDECCount = CATransaction::where('sett_id', $employeeId)->where('approval_sett', 'Pending')->count();
        $pendingEXCount = CATransaction::where('extend_id', $employeeId)->where('approval_extend', 'Pending')->count();
        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

        // Memformat tanggal
        foreach ($ca_transactions as $transaction) {
            $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
            $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
        }

        return view('hcis.reimbursements.approval.approvalCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingDECCount' => $pendingDECCount,
            'pendingEXCount' => $pendingEXCount,
            'link' => $link,
            'fullnames' => $fullnames,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
            'pendingHTLCount' => $pendingHTLCount,
        ]);
    }
    public function cashadvancedFormDeklarasi($key)
    {
        $userId = Auth::id();
        $parentLink = 'Approval';
        $link = 'Cash Advanced Approval';

        $employee_data = Employee::where('id', $userId)->first();
        $companies = Company::orderBy('contribution_level')->get();
        $locations = Location::orderBy('area')->get();
        $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
        $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
        $transactions = CATransaction::findByRouteKey($key);

        return view('hcis.reimbursements.approval.listApproveDeklarasiCashadv', [
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }
    function cashadvancedActionDeklarasi(Request $req, $ca_id)
    {
        $userId = Auth::id();
        $employeeId = auth()->user()->employee_id;
        $model = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->firstOrFail();

        // Cek apakah ini sudah di-approve atau tidak
        if ($model->approval_status == 'Approved') {
            Alert::warning('Warning', 'This approval has already been approved.');
            return redirect()->route('approval.cashadvanced');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_sett_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        if ($req->input('action_ca_reject')) {
            $caApprovalsSett = ca_sett_approval::where('ca_id', $ca_id)->get();
            if ($caApprovalsSett->isNotEmpty()) {
                foreach ($caApprovalsSett as $caApprovalSett) {
                    $caApprovalSett->approval_status = 'Rejected';
                    $caApprovalSett->approved_at = Carbon::now();
                    $caApprovalSett->reject_info = $req->reject_info;
                    $caApprovalSett->save();
                }
            }
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_sett = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('approval.cashadvanced')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($req->input('action_ca_approve')) {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_sett = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Declaration Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment :";
                        $declaration = "Declaration";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                $declaration,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->sett_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} filed Declaration Cash Advanced with details as follows:";
                    $declaration = "Declaration";

                    $linkApprove = route('approval.email.approveddec', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            $declaration,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('approval.cashadvanced')->with('success', 'Transaction Approved, Thanks for Approving.');
    }

    function cashadvancedActionDeklarasiEmail(Request $req, $ca_id, $employeeId)
    {
        // $userId = Auth::id();
        // $employeeId = auth()->user()->employee_id;
        $model = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->firstOrFail();

        // Cek apakah ini sudah di-approve atau tidak
        if ($model->approval_status == 'Approved') {
            if ($req->input('action_ca_reject')) {
                return redirect()->route('blank.pageUn')->with('error', 'Reject Failed, Approval already been Approved.');
            } else {
                return redirect()->route('blank.pageUn')->with('success', 'This approval has already been approved.');
            }
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_sett_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        $action = $req->query('action');

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            $caApprovalsSett = ca_sett_approval::where('ca_id', $ca_id)->get();
            if ($caApprovalsSett->isNotEmpty()) {
                foreach ($caApprovalsSett as $caApprovalSett) {
                    $caApprovalSett->approval_status = 'Rejected';
                    $caApprovalSett->approved_at = Carbon::now();
                    $caApprovalSett->reject_info = $req->reject_info;
                    $caApprovalSett->save();
                }
            }
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_sett = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('blank.pageUn')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($action === 'approve') {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }
            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_sett = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Declaration Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment :";
                        $declaration = "Declaration";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                $declaration,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->sett_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                // dd($CANotificationLayer);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} filed Declaration Cash Advanced with details as follows:";
                    $declaration = "Declaration";

                    $linkApprove = route('approval.email.approveddec', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            $declaration,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            }
        }
    }

    public function cashadvancedActionDeklarasiAdmin(Request $req, $ca_id)
    {
        // Ambil dataNoId dari request
        $dataNoId = $req->input('data_no_id');
        $model = ca_sett_approval::where('ca_id', $ca_id)
            ->where('id', $dataNoId)
            ->first();

        if (!$model) {
            return redirect()->route('cashadvanced.admin')->with('error', 'Approval not found for this transaction.');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $caApprovalsSett = ca_sett_approval::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();
        // dd($caApprovalsSett);

        if ($req->input('action_ca_reject')) {
            $caApprovalsSett = ca_sett_approval::where('ca_id', $ca_id)->get();
            if ($caApprovalsSett->isNotEmpty()) {
                foreach ($caApprovalsSett as $caApprovalSett) {
                    $caApprovalSett->approval_status = 'Rejected';
                    $caApprovalSett->approved_at = Carbon::now();
                    $caApprovalSett->reject_info = $req->reject_info;
                    $caApprovalSett->by_admin = 'T';
                    $caApprovalSett->admin_id = Auth::id();
                    $caApprovalSett->save();
                }
            }
            // ->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_sett = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Transaction Rejected, Rejection will be send to the employee.')
                ->with('refresh', true);
        }

        if ($req->input('action_ca_approve')) {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($caApprovalsSett as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update approval_sett pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_sett = 'Approved'; // Set ke Approved untuk transaksi
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Declaration Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment :";
                        $declaration = "Declaration";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                $declaration,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_sett_approval::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->sett_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }


                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} filed Declaration Cash Advanced with details as follows:";
                    $declaration = "Declaration";

                    $linkApprove = route('approval.email.approveddec', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            $declaration,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Transaction approved successfully.')
            ->with('refresh', true);
    }

    public function cashadvancedExtend()
    {
        $userId = Auth::id();
        $parentLink = 'Approval';
        $link = 'Cash Advanced';
        $employeeId = auth()->user()->employee_id;

        $ca_transactions = CATransaction::with('employee')->where('extend_id', $employeeId)->where('approval_extend', 'Pending')->get();

        $fullnames = Employee::whereIn('employee_id', $ca_transactions->pluck('extend_id'))
            ->pluck('fullname', 'employee_id');

        $extendData = ca_extend::whereIn('ca_id', $ca_transactions->pluck('id'))
            ->get(['ca_id', 'ext_end_date', 'ext_total_days', 'reason_extend']);

        // Indeks koleksi berdasarkan ca_id
        $extendTime = $extendData->keyBy('ca_id')->map(function ($item) {
            return [
                'ext_end_date' => $item->ext_end_date,
                'ext_total_days' => $item->ext_total_days,
                'reason_extend' => $item->reason_extend,
            ];
        });

        $pendingCACount = CATransaction::where('status_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingDECCount = CATransaction::where('sett_id', $employeeId)->where('approval_sett', 'Pending')->count();
        $pendingEXCount = CATransaction::where('extend_id', $employeeId)->where('approval_extend', 'Pending')->count();
        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

        return view('hcis.reimbursements.approval.approvalExtendCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingDECCount' => $pendingDECCount,
            'pendingEXCount' => $pendingEXCount,
            'link' => $link,
            'fullnames' => $fullnames,
            'extendTime' => $extendTime,
            'extendData' => $extendData,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
            'pendingHTLCount' => $pendingHTLCount,
        ]);
    }
    public function cashadvancedActionExtended(Request $req)
    {
        $id = $req->input('no_id'); // Get the ID from the no_id input
        $userId = Auth::id();
        $employeeId = auth()->user()->employee_id;
        $employee_data = Employee::where('id', $userId)->first();

        $model = ca_extend::where('ca_id', $id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->firstOrFail();

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_extend::where('ca_id', $id)
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            ca_extend::where('ca_id', $id)->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $id)->first();
            if ($caTransaction) {
                $caTransaction->approval_extend = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('approval.cashadvanced')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($req->input('action_ca_approve')) {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $model->approval_status = 'Approved';
                $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                $model->save();

                // Update status_id pada ca_transaction
                $caTransaction = CATransaction::where('id', $id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_extend = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->start_date = $req->input('ext_start_date');
                    $caTransaction->end_date = $req->input('ext_end_date');
                    $caTransaction->total_days = $req->input('ext_totaldays');
                    $caTransaction->reason_extend = $req->input('ext_reason');
                    // dd($caTransaction);
                    $caTransaction->save();
                }

                // dd($caTransaction);
                // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "Your Declaration Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment :";
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            null,
                            $caTransaction,
                            $textNotification,
                            null,
                            null,
                            null,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }

                return redirect()->route('approval.cashadvanced');
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $model->approval_status = 'Approved';
                $model->approved_at = Carbon::now();
                $model->save();
                // dd($model);

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $id)->first();
                if ($caTransaction) {
                    $caTransaction->extend_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} applied for Extend Cash Advanced with details as follows:";

                    $linkApprove = route('approval.email.aproved', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            null,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }

                return redirect()->route('approval.cashadvanced')->with('success', 'Extend Approved, Thanks for Approving.');
            }
        }
    }

    public function cashadvancedActionExtendAdmin(Request $req, $ca_id)
    {
        // Ambil dataNoId dari request
        $dataNoId = $req->input('data_no_id');
        $model = ca_extend::where('ca_id', $ca_id)
            ->where('id', $dataNoId)
            ->first();

        if (!$model) {
            return redirect()->route('cashadvanced.admin')->with('error', 'Approval not found for this transaction.');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $caApprovalsExt = ca_extend::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        if ($req->input('action_ca_reject')) {
            $caApprovalsExt = ca_extend::where('ca_id', $ca_id)->get();
            if ($caApprovalsExt->isNotEmpty()) {
                foreach ($caApprovalsExt as $caApprovalSett) {
                    $caApprovalSett->approval_status = 'Rejected';
                    $caApprovalSett->approved_at = Carbon::now();
                    $caApprovalSett->reject_info = $req->reject_info;
                    $caApprovalSett->by_admin = 'T';
                    $caApprovalSett->admin_id = Auth::id();
                    $caApprovalSett->save();
                }
            }
            // ->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_extend = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        if ($req->input('action_ca_approve')) {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($caApprovalsExt as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }

            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_extend::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update approval_sett pada ca_transaction
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_extend = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->end_date = $req->input('ext_end_date');
                    $caTransaction->total_days = $req->input('ext_totaldays');
                    $caTransaction->reason_extend = $req->input('ext_reason');
                    $caTransaction->save();


                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Extend Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment :";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                null,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_extend::where('ca_id', $ca_id)->where('employee_id', $model->employee_id)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->by_admin = 'T';
                    $model->admin_id = Auth::id();
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->extend_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }


                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} filed Declaration Cash Advanced with details as follows:";

                    $linkApprove = route('approval.email.approveddec', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            null,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Transaction approved successfully.');
    }

    function cashadvancedActionExtendEmail(Request $req, $ca_id, $employeeId)
    {
        // $userId = Auth::id();
        // $employeeId = auth()->user()->employee_id;
        $model = ca_extend::where('ca_id', $ca_id)->where('employee_id', $employeeId)->firstOrFail();

        // Cek apakah ini sudah di-approve atau tidak
        if ($model->approval_extend == 'Approved') {
            if ($req->input('action_ca_reject')) {
                return redirect()->route('blank.pageUn')->with('error', 'Reject Failed, Approval already been Approved.');
            } else {
                return redirect()->route('blank.pageUn')->with('success', 'This approval has already been approved.');
            }
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_extend::where('ca_id', $ca_id)
            ->where('approval_status', 'Pending')
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        $action = $req->query('action');

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            $caApprovalsExt = ca_extend::where('ca_id', $ca_id)->get();
            if ($caApprovalsExt->isNotEmpty()) {
                foreach ($caApprovalsExt as $caApprovalExt) {
                    $caApprovalExt->approval_status = 'Rejected';
                    $caApprovalExt->approved_at = Carbon::now();
                    $caApprovalExt->reject_info = $req->reject_info;
                    $caApprovalExt->save();
                }
            }
            $caTransaction = CATransaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_extend = 'Rejected';
                $caTransaction->save();
            }

            // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
            $CANotificationLayer = "erzie.aldrian02@outlook.com";
            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            if ($CANotificationLayer) {
                $textNotification = "Your Cash Advanced request has been rejected, please discuss further with your supervisor :";
                try {
                    Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                        null,
                        $caTransaction,
                        $textNotification,
                        null,
                        null,
                        null,
                        $base64Image,
                    ));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }

            return redirect()->route('blank.pageUn')->with('success', 'Transaction Rejected, Rejection will be send to the employee.');
        }

        // Cek jika tombol approve ditekan
        if ($action === 'approve') {
            $nextApproval = null;

            // Mencari layer berikutnya yang lebih tinggi
            foreach ($approvals as $approval) {
                if ($approval->layer > $model->layer && $approval->employee_id <> $model->employee_id) {
                    $nextApproval = $approval;
                    break;
                }
            }
            // Jika tidak ada layer yang lebih tinggi (berarti ini adalah layer tertinggi)
            if (!$nextApproval) {
                // Set status ke Approved untuk layer tertinggi
                $models = ca_extend::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction
                $extendData = ca_extend::where('ca_id', $ca_id)
                    ->where('layer', '4')
                    ->where('approval_status', 'Approved')
                    ->orderBy('created_at', 'desc') // Mengurutkan berdasarkan created_at secara menurun
                    ->first();
                // dd($extendData);
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_extend = 'Approved'; // Set ke ID user layer tertinggi
                    // $caTransaction->start_date = $extendData->start_date;
                    $caTransaction->end_date = $extendData->ext_end_date;
                    $caTransaction->total_days = $extendData->ext_total_days;
                    $caTransaction->reason_extend = $extendData->ext_reason;
                    $caTransaction->save();

                    // $CANotificationLayer = Employee::where('id', $caTransaction->user_id)->pluck('email')->first();
                    $CANotificationLayer = "erzie.aldrian02@outlook.com";
                    $imagePath = public_path('images/kop.jpg');
                    $imageContent = file_get_contents($imagePath);
                    $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                    if ($CANotificationLayer) {
                        $textNotification = "Your Extend Cash Advanced request has been approved, please check your request again or can download your submission in the email attachment:";
                        $declaration = "Extend";
                        try {
                            Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                                null,
                                $caTransaction,
                                $textNotification,
                                $declaration,
                                null,
                                null,
                                $base64Image,
                            ));
                        } catch (\Exception $e) {
                            Log::error('Email tidak terkirim: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $models = ca_extend::where('ca_id', $ca_id)->where('employee_id', $employeeId)->where('approval_status', '<>', 'Rejected')->get();
                foreach ($models as $model) {
                    $model->approval_status = 'Approved';
                    $model->approved_at = Carbon::now(); // Simpan waktu approval sekarang
                    $model->save();
                }

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = CATransaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->extend_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }

                // Mengambil email employee di layer berikutnya dan mengirimkan notifikasi
                // $CANotificationLayer = Employee::where('employee_id', $nextApproval->employee_id)->pluck('email')->first();
                $CANotificationLayer = "erzie.aldrian02@outlook.com";
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);
                // dd($CANotificationLayer);
                if ($CANotificationLayer) {
                    $textNotification = "{$caTransaction->employee->fullname} applied for Extend Cash Advanced with details as follows:";
                    $declaration = "Extend";

                    $linkApprove = route('approval.email.approvedext', [
                        'id' => $caTransaction->id,
                        'employeeId' => $nextApproval->employee_id,
                        'action' => 'approve',
                    ]);
                    $linkReject = route('blank.page', [
                        'key' => encrypt($caTransaction->id),  // Ganti 'id' dengan 'key' sesuai dengan parameter di controller
                        'userId' => $nextApproval->employee->id, // Jika perlu, masukkan ID pengguna di sini
                        'autoOpen' => 'reject'
                    ]);
                    try {
                        Mail::to($CANotificationLayer)->send(new CashAdvancedNotification(
                            $nextApproval,
                            $caTransaction,
                            $textNotification,
                            $declaration,
                            $linkApprove,
                            $linkReject,
                            $base64Image,
                        ));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
                return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
            }
        }
    }

    public function blankApproval($key = null, $userId = null)
    {
        $employee_data = null;
        $companies = collect(); // Koleksi kosong sebagai default
        $locations = collect(); // Koleksi kosong sebagai default
        $perdiem = null;
        $no_sppds = collect(); // Koleksi kosong sebagai default
        $transactions = null; // Default nilai null

        // Cek jika $userId tidak null
        if ($userId != null) {
            $employee_data = Employee::where('id', $userId)->first();
            $companies = Company::orderBy('contribution_level')->get();
            $locations = Location::orderBy('area')->get();

            if ($employee_data) { // Pastikan employee_data tidak null sebelum menggunakan
                $perdiem = ListPerdiem::where('grade', $employee_data->job_level)->first();
                $no_sppds = CATransaction::where('user_id', $userId)->where('approval_sett', '!=', 'Done')->get();
            }
        }

        // Cek jika $key tidak null
        if ($key != null) {
            $transactions = CATransaction::findByRouteKey($key);
        }

        return view('hcis.reimbursements.approval.navigation.blankPage', [
            'userId' => $userId,
            'companies' => $companies,
            'locations' => $locations,
            'employee_data' => $employee_data,
            'perdiem' => $perdiem,
            'no_sppds' => $no_sppds,
            'transactions' => $transactions,
        ]);
    }

    public function approveHotelFromLink($id, $manager_id, $status)
    {
        $employeeId = $manager_id;

        // Find the hotel by ID
        $hotel = Hotel::findOrFail($id);
        $noHtl = $hotel->no_htl;

        // Handle approval scenarios
        if ($hotel->manager_l2_id == '-') {
            Hotel::where('no_htl', $noHtl)->update(['approval_status' => 'Approved']);
        } elseif ($hotel->approval_status == 'Pending L1') {
            Hotel::where('no_htl', $noHtl)->update(['approval_status' => 'Pending L2']);

            $managerId = Employee::where('id', $hotel->user_id)->value('manager_l2_id');
            $managerEmail = Employee::where('employee_id', $managerId)->value('email');
            $managerName = Employee::where('employee_id', $managerId)->value('fullname');
            $employeeName = Employee::where('id', $hotel->user_id)->pluck('fullname')->first();

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Hotel and waiting for your Approval with the following details :";

            $approvalLink = route('approve.hotel', [
                'id' => urlencode($hotel->id),
                'manager_id' => $managerId,
                'status' => 'Pending L2'
            ]);

            $revisionLink = route('revision.hotel.link', [
                'id' => urlencode($hotel->id),
                'manager_id' => $managerId,
                'status' => 'Request Revision'
            ]);

            $rejectionLink = route('reject.hotel.link', [
                'id' => urlencode($hotel->id),
                'manager_id' => $managerId,
                'status' => 'Rejected'
            ]);

            if ($managerEmail) {
                // Initialize arrays to collect details for multiple hotels
                $noHtlList = [];
                $namaHtl = [];
                $lokasiHtl = [];
                $tglMasukHtl = [];
                $tglKeluarHtl = [];
                $totalHari = [];

                // Collect details for each hotel with the same no_htl
                $hotels = Hotel::where('no_htl', $noHtl)->get();
                foreach ($hotels as $htl) {
                    $noHtlList[] = $htl->no_htl;
                    $namaHtl[] = $htl->nama_htl;
                    $lokasiHtl[] = $htl->lokasi_htl;
                    $tglMasukHtl[] = $htl->tgl_masuk_htl;
                    $tglKeluarHtl[] = $htl->tgl_keluar_htl;
                    $totalHari[] = $htl->total_hari;
                }

                // Send email with all hotel details
                try {
                    Mail::to($managerEmail)->send(new HotelNotification([
                        'noSppd' => $hotel->no_sppd,
                        'noHtl' => $noHtlList,
                        'namaHtl' => $namaHtl,
                        'lokasiHtl' => $lokasiHtl,
                        'tglMasukHtl' => $tglMasukHtl,
                        'tglKeluarHtl' => $tglKeluarHtl,
                        'totalHari' => $totalHari,
                        'managerName' => $managerName,
                        'approvalLink' => $approvalLink,
                        'revisionLink' => $revisionLink,
                        'rejectionLink' => $rejectionLink,
                        'approvalStatus' => 'Pending L2',
                        'base64Image' => $base64Image,
                        'textNotification' => $textNotification,
                        'employeeName' => $employeeName,
                    ]));
                } catch (\Exception $e) {
                    Log::error('Email tidak terkirim: ' . $e->getMessage());
                }
            }
        } elseif ($hotel->approval_status == 'Pending L2') {
            Hotel::where('no_htl', $noHtl)->update(['approval_status' => 'Approved']);
        }
        // Log the approval into the hotel_approvals table for all hotels with the same no_htl
        $hotels = Hotel::where('no_htl', $noHtl)->get();
        foreach ($hotels as $hotel) {
            $approval = new HotelApproval();
            $approval->id = (string) Str::uuid();
            $approval->htl_id = $hotel->id;
            $approval->employee_id = $employeeId;
            if ($hotel->manager_l2_id == '-') {
                $approval->layer = 1;
            } else {
                $approval->layer = $hotel->approval_status == 'Pending L2' ? 1 : 2;
            }
            $approval->approval_status = $hotel->approval_status;
            $approval->approved_at = now();
            $approval->save();
        }
    }
    public function rejectHotelLink($id, $manager_id, $status)
    {
        $hotel = Hotel::where('id', $id)->first();
        // dd($id, $hotel);
        $userId = $hotel->user_id;

        $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
        $noHtl = $hotel->no_htl;
        $hotels = Hotel::where('no_htl', $noHtl)->first();
        $hotelsTotal = Hotel::where('no_htl', $noHtl)->count();

        return view('hcis.reimbursements.hotel.hotelReject', [
            'userId' => $userId,
            'id' => $id,
            'manager_id' => $manager_id,
            'status' => $status,
            'hotels' => $hotels,
            'employeeName' => $employeeName,
            'hotelsTotal' => $hotelsTotal,
        ]);
    }
    public function revisionHotelLink($id, $manager_id, $status)
    {
        $hotel = Hotel::where('id', $id)->first();
        // dd($id, $hotel);
        $userId = $hotel->user_id;

        $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
        $noHtl = $hotel->no_htl;
        $hotels = Hotel::where('no_htl', $noHtl)->first();
        $hotelsTotal = Hotel::where('no_htl', $noHtl)->count();

        return view('hcis.reimbursements.hotel.hotelRevision', [
            'userId' => $userId,
            'id' => $id,
            'manager_id' => $manager_id,
            'status' => $status,
            'hotels' => $hotels,
            'employeeName' => $employeeName,
            'hotelsTotal' => $hotelsTotal,
        ]);
    }
    public function rejectHotelFromLink(Request $request, $id, $manager_id, $status)
    {
        $employeeId = $manager_id;

        $rejectInfo = $request->reject_info;
        $hotel = Hotel::findOrFail($id);
        $noHtl = $hotel->no_htl;
        // Get the current approval status before updating it
        $currentApprovalStatus = $hotel->approval_status;

        Hotel::where('no_htl', $noHtl)->update(['approval_status' => 'Rejected']);

        // Log the rejection into the hotel_approvals table for all hotels with the same no_htl
        $hotels = Hotel::where('no_htl', $noHtl)->get();
        foreach ($hotels as $hotel) {
            $rejection = new HotelApproval();
            $rejection->id = (string) Str::uuid();
            $rejection->htl_id = $hotel->id;
            $rejection->employee_id = $employeeId;

            // Determine the correct layer based on the hotel's approval status BEFORE rejection
            $rejection->layer = $currentApprovalStatus == 'Pending L2' ? 2 : 1;

            $rejection->approval_status = 'Rejected';
            $rejection->approved_at = now();
            $rejection->reject_info = $rejectInfo;
            $rejection->save();
        }
    }
    public function revisionHotelFromLink(Request $request, $id, $manager_id, $status)
    {
        $employeeId = $manager_id;

        $revisionInfo = $request->revision_info;
        $hotel = Hotel::findOrFail($id);
        $noHtl = $hotel->no_htl;
        // Get the current approval status before updating it
        $currentApprovalStatus = $hotel->approval_status;

        Hotel::where('no_htl', $noHtl)->update(['approval_status' => 'Request Revision']);

        // Log the rejection into the hotel_approvals table for all hotels with the same no_htl
        $hotels = Hotel::where('no_htl', $noHtl)->get();
        foreach ($hotels as $hotel) {
            $rejection = new HotelApproval();
            $rejection->id = (string) Str::uuid();
            $rejection->htl_id = $hotel->id;
            $rejection->employee_id = $employeeId;

            // Determine the correct layer based on the hotel's approval status BEFORE rejection
            $rejection->layer = $currentApprovalStatus == 'Pending L2' ? 2 : 1;

            $rejection->approval_status = 'Request Revision';
            $rejection->approved_at = now();
            $rejection->reject_info = $revisionInfo;
            $rejection->save();
        }
    }
    public function approveTicketFromLink($id, $manager_id, $status)
    {
        $employeeId = $manager_id;
        $currentYear = now()->year;

        // Find the ticket by ID
        $ticket = Tiket::findOrFail($id);
        $ticketUserId = $ticket->user_id;
        $noTkt = $ticket->no_tkt;
        $ticketEmployeeId = Employee::where('id', $ticketUserId)->pluck('employee_id')->first();

        $ticketNpTkt = Tiket::where('no_tkt', $noTkt)->pluck('np_tkt');
        // dd($ticket->approval_status);
        // If not rejected, proceed with normal approval process
        if ($ticket->manager_l2_id == '-') {
            Tiket::where('no_tkt', $noTkt)->update(['approval_status' => 'Approved']);
            if ($ticket->jns_dinas_tkt == 'Cuti') {
                // Hitung total pengurangan kuota berdasarkan semua tiket
                $totalDecrement = 0;

                foreach ($ticketNpTkt as $name) {
                    // Dapatkan type_tkt untuk setiap tiket berdasarkan nama
                    $ticketType = Tiket::where('user_id', $ticket->user_id)
                        ->where('no_tkt', $ticket->no_tkt)
                        ->where('tkt_only', '=', 'Y')
                        ->where('np_tkt', $name) // Pastikan mengambil type_tkt untuk nama saat ini
                        ->value('type_tkt');

                    // Default ke 'One Way' jika type_tkt tidak ditemukan
                    if (!$ticketType) {
                        $ticketType = 'One Way';
                    }

                    // Tentukan nilai pengurangan berdasarkan type_tkt
                    $decrementValue = ($ticketType == 'One Way') ? 1 : 2;

                    // Tambahkan nilai pengurangan ke total
                    $totalDecrement += $decrementValue;
                }

                // Kurangi kuota total di HomeTrip berdasarkan employee_id
                HomeTrip::where('employee_id', $ticketEmployeeId)
                    ->where('period', $currentYear)
                    ->decrement('quota', $totalDecrement);
            }
        } elseif ($ticket->approval_status == 'Pending L1') {
            Tiket::where('no_tkt', $noTkt)->update(['approval_status' => 'Pending L2']);
            $managerId = Employee::where('id', $ticket->user_id)->value('manager_l2_id');
            $managerEmail = Employee::where('employee_id', $managerId)->value('email');
            $managerName = Employee::where('employee_id', $managerId)->pluck('fullname')->first();
            $employeeName = Employee::where('id', $ticket->user_id)->pluck('fullname')->first();

            $imagePath = public_path('images/kop.jpg');
            $imageContent = file_get_contents($imagePath);
            $base64Image = "data:image/png;base64," . base64_encode($imageContent);
            $textNotification = "requesting a Ticket and waiting for your Approval with the following details :";

            $approvalLink = route('approve.ticket', [
                'id' => urlencode($ticket->id),
                'manager_id' => $managerId,
                'status' => 'Pending L2'
            ]);

            $rejectionLink = route('reject.ticket.link', [
                'id' => urlencode($ticket->id),
                'manager_id' => $managerId,
                'status' => 'Rejected'
            ]);

            if ($managerEmail) {
                // Initialize arrays to collect details for multiple hotels
                $noTktList = [];
                $npTkt = [];
                $dariTkt = [];
                $keTkt = [];
                $tglBrktTkt = [];
                $jamBrktTkt = [];
                $tglPlgTkt = [];
                $jamPlgTkt = [];
                $tipeTkt = [];

                // Collect details for each hotel with the same no_htl
                $tickets = Tiket::where('no_tkt', $noTkt)->get();
                // dd($tickets);
                foreach ($tickets as $tkt) {
                    $noTktList[] = $tkt->no_tkt;
                    $npTkt[] = $tkt->np_tkt;
                    $dariTkt[] = $tkt->dari_tkt;
                    $keTkt[] = $tkt->ke_tkt;
                    $tglBrktTkt[] = $tkt->tgl_brkt_tkt;
                    $jamBrktTkt[] = $tkt->jam_brkt_tkt;
                    $tglPlgTkt[] = $tkt->tgl_plg_tkt;
                    $jamPlgTkt[] = $tkt->jam_plg_tkt;
                    $tipeTkt[] = $tkt->type_tkt;
                }

                if ($ticket->jns_dinas_tkt == 'Dinas') {
                    // Send email with all hotel details
                    try {
                        Mail::to($managerEmail)->send(new TicketNotification([
                            'noSppd' => $ticket->no_sppd,
                            'noTkt' => $noTktList,
                            'namaPenumpang' => $npTkt,
                            'dariTkt' => $dariTkt,
                            'keTkt' => $keTkt,
                            'tglBrktTkt' => $tglBrktTkt,
                            'jamBrktTkt' => $jamBrktTkt,
                            'tipeTkt' => $tipeTkt,
                            'tglPlgTkt' => $tglPlgTkt,
                            'jamPlgTkt' => $jamPlgTkt,
                            'managerName' => $managerName,
                            'approvalStatus' => 'Pending L2',
                            'approvalLink' => $approvalLink,
                            'rejectionLink' => $rejectionLink,
                            'base64Image' => $base64Image,
                            'textNotification' => $textNotification,
                            'employeeName' => $employeeName,
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                } else {
                    try {
                        Mail::to($managerEmail)->send(new HomeTripNotification([
                            'noTkt' => $noTktList,
                            'namaPenumpang' => $npTkt,
                            'dariTkt' => $dariTkt,
                            'keTkt' => $keTkt,
                            'tglBrktTkt' => $tglBrktTkt,
                            'jamBrktTkt' => $jamBrktTkt,
                            'tipeTkt' => $tipeTkt,
                            'tglPlgTkt' => $tglPlgTkt,
                            'jamPlgTkt' => $jamPlgTkt,
                            'managerName' => $managerName,
                            'approvalStatus' => 'Pending L2',
                            'approvalLink' => $approvalLink,
                            'rejectionLink' => $rejectionLink,
                            'base64Image' => $base64Image,
                            'textNotification' => $textNotification,
                            'employeeName' => $employeeName,
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Email tidak terkirim: ' . $e->getMessage());
                    }
                }
            }
        } elseif ($ticket->approval_status == 'Pending L2') {
            Tiket::where('no_tkt', $noTkt)->update(['approval_status' => 'Approved']);
            if ($ticket->jns_dinas_tkt == 'Cuti') {
                // Hitung total pengurangan kuota berdasarkan semua tiket
                $totalDecrement = 0;

                foreach ($ticketNpTkt as $name) {
                    // Dapatkan type_tkt untuk setiap tiket berdasarkan nama
                    $ticketType = Tiket::where('user_id', $ticket->user_id)
                        ->where('no_tkt', $ticket->no_tkt)
                        ->where('tkt_only', '=', 'Y')
                        ->where('np_tkt', $name) // Pastikan mengambil type_tkt untuk nama saat ini
                        ->value('type_tkt');

                    // Default ke 'One Way' jika type_tkt tidak ditemukan
                    if (!$ticketType) {
                        $ticketType = 'One Way';
                    }

                    // Tentukan nilai pengurangan berdasarkan type_tkt
                    $decrementValue = ($ticketType == 'One Way') ? 1 : 2;

                    // Tambahkan nilai pengurangan ke total
                    $totalDecrement += $decrementValue;
                }

                // Kurangi kuota total di HomeTrip berdasarkan employee_id
                HomeTrip::where('employee_id', $ticketEmployeeId)
                    ->where('period', $currentYear)
                    ->decrement('quota', $totalDecrement);
            }
        }

        // Log the approval into the tkt_approvals table for all tickets with the same no_tkt
        $tickets = Tiket::where('no_tkt', $noTkt)->get();
        foreach ($tickets as $ticket) {
            $approval = new TiketApproval();
            $approval->id = (string) Str::uuid();
            $approval->tkt_id = $ticket->id;
            $approval->employee_id = $employeeId;
            if ($ticket->manager_l2_id == '-') {
                $approval->layer = 1;
            } else {
                $approval->layer = $ticket->approval_status == 'Pending L2' ? 1 : 2;
            }
            $approval->approval_status = $ticket->approval_status;
            $approval->approved_at = now();
            $approval->save();
        }

        return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
    }

    public function rejectTicketLink($id, $manager_id, $status)
    {
        $ticket = Tiket::where('id', $id)->first();

        $userId = $ticket->user_id;
        // dd($userId);
        $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
        $noTkt = $ticket->no_tkt;
        $tickets = Tiket::where('no_tkt', $noTkt)->first();
        $ticketsTotal = Tiket::where('no_tkt', $noTkt)->count();
        // dd($tickets);

        return view('hcis.reimbursements.ticket.ticketReject', [
            'userId' => $userId,
            'id' => $id,
            'manager_id' => $manager_id,
            'status' => $status,
            'tickets' => $tickets,
            'employeeName' => $employeeName,
            'ticketsTotal' => $ticketsTotal,
        ]);
    }
    public function revisionTicketLink($id, $manager_id, $status)
    {
        $ticket = Tiket::where('id', $id)->first();

        $userId = $ticket->user_id;
        // dd($userId);
        $employeeName = Employee::where('id', $userId)->pluck('fullname')->first();
        $noTkt = $ticket->no_tkt;
        $tickets = Tiket::where('no_tkt', $noTkt)->first();
        $ticketsTotal = Tiket::where('no_tkt', $noTkt)->count();
        // dd($tickets);

        return view('hcis.reimbursements.ticket.ticketRevision', [
            'userId' => $userId,
            'id' => $id,
            'manager_id' => $manager_id,
            'status' => $status,
            'tickets' => $tickets,
            'employeeName' => $employeeName,
            'ticketsTotal' => $ticketsTotal,
        ]);
    }
    public function rejectTicketFromLink(Request $request, $id, $manager_id, $status)
    {
        $employeeId = $manager_id;

        // Find the ticket by ID
        $ticket = Tiket::findOrFail($id);
        $noTkt = $ticket->no_tkt;

        $rejectInfo = $request->reject_info;

        // Get the current approval status before updating it
        $currentApprovalStatus = $ticket->approval_status;

        // Update all tickets with the same no_tkt to 'Rejected'
        Tiket::where('no_tkt', $noTkt)->update(['approval_status' => 'Rejected']);

        // Log the rejection into the tkt_approvals table for all tickets with the same no_tkt
        $tickets = Tiket::where('no_tkt', $noTkt)->get();
        foreach ($tickets as $ticket) {
            $rejection = new TiketApproval();
            $rejection->id = (string) Str::uuid();
            $rejection->tkt_id = $ticket->id;
            $rejection->employee_id = $employeeId;

            // Determine the correct layer based on the ticket's approval status BEFORE rejection
            if ($currentApprovalStatus == 'Pending L2') {
                $rejection->layer = 2; // Layer 2 if ticket was at L2
            } else {
                $rejection->layer = 1; // Otherwise, it's Layer 1
            }

            $rejection->approval_status = 'Rejected';
            $rejection->approved_at = now();
            $rejection->reject_info = $rejectInfo;
            $rejection->save();
        }

        return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
    }
    public function revisionTicketFromLink(Request $request, $id, $manager_id, $status)
    {
        $employeeId = $manager_id;

        // Find the ticket by ID
        $ticket = Tiket::findOrFail($id);
        $noTkt = $ticket->no_tkt;

        $revisionInfo = $request->revision_info;

        // Get the current approval status before updating it
        $currentApprovalStatus = $ticket->approval_status;

        // Update all tickets with the same no_tkt to 'Rejected'
        Tiket::where('no_tkt', $noTkt)->update(['approval_status' => 'Request Revision']);

        // Log the revisionion into the tkt_approvals table for all tickets with the same no_tkt
        $tickets = Tiket::where('no_tkt', $noTkt)->get();
        foreach ($tickets as $ticket) {
            $revision = new TiketApproval();
            $revision->id = (string) Str::uuid();
            $revision->tkt_id = $ticket->id;
            $revision->employee_id = $employeeId;

            // Determine the correct layer based on the ticket's approval status BEFORE revision
            if ($currentApprovalStatus == 'Pending L2') {
                $revision->layer = 2; // Layer 2 if ticket was at L2
            } else {
                $revision->layer = 1; // Otherwise, it's Layer 1
            }

            $revision->approval_status = 'Request Revision';
            $revision->approved_at = now();
            $revision->reject_info = $revisionInfo;
            $revision->save();
        }

        return redirect()->route('blank.pageUn')->with('success', 'Transaction Approved, Thanks for Approving.');
    }
}
