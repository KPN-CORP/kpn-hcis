<?php

namespace App\Http\Controllers;

use App\Models\ca_approval;
use Illuminate\Support\Facades\Auth;
use App\Models\CATransaction;
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

use Illuminate\Http\Request;

class ApprovalReimburseController extends Controller
{
    public function approval()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced Approval';
        $employeeId = auth()->user()->employee_id;

        // Ambil ca_approval berdasarkan employee_id
        $ca_approval = ca_approval::with('employee')->where('employee_id', $employeeId)->where('approval_status', 'Pending')->get();

        $ca_approvals_with_transactions = $ca_approval->map(function ($approval) {
            $approval->transactions = CATransaction::where('id', $approval->ca_id)->get();
            return $approval;
        });
        $pendingCACount = ca_approval::where('employee_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

        foreach ($ca_approval as $ca_approvals) {
            foreach ($ca_approvals->transactions as $transaction) {
                $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
                $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
            }
        }

        return view('hcis.reimbursements.approval.approvalCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingHTLCount' => $pendingHTLCount,
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_approval' => $ca_approvals_with_transactions,
            // 'company' => $company,
        ]);
    }
    public function cashadvancedApproval()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';
        $employeeId = auth()->user()->employee_id;

        $ca_transactions = CATransaction::with('employee')->where('status_id', $employeeId)->where('approval_status', 'Pending')->get();

        $pendingCACount = CATransaction::where('status_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingDECCount = CATransaction::where('sett_id', $employeeId)->where('approval_sett', 'Pending')->count();

        // Memformat tanggal
        foreach ($ca_transactions as $transaction) {
            $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
            $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
        }

        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

        return view('hcis.reimbursements.approval.approvalCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingDECCount' => $pendingDECCount,
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
            'pendingHTLCount' => $pendingHTLCount,
        ]);
    }
    public function cashadvancedFormApproval($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
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
            Alert::warning('Warning', 'This approval has already been approved.');
            return redirect()->route('approval.cashadvanced');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_approval::where('ca_id', $ca_id)
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            ca_approval::where('ca_id', $ca_id)->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = ca_transaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_status = 'Rejected';
                $caTransaction->save();
            }

            Alert::success('Success', 'All approvals rejected successfully.');
            return redirect()->route('approval.cashadvanced');
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
                $caTransaction = ca_transaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_status = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->save();
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $model->approval_status = 'Approved';
                $model->approved_at = Carbon::now();
                $model->save();

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = ca_transaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->status_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }
            }
        }

        Alert::success('Success', 'Approval updated successfully.');
        return redirect()->route('approval.cashadvanced');
    }

    public function cashadvancedDeklarasi()
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
        $link = 'Cash Advanced';
        $employeeId = auth()->user()->employee_id;

        $ca_transactions = CATransaction::with('employee')->where('sett_id', $employeeId)->where('approval_sett', 'Pending')->get();

        $pendingCACount = CATransaction::where('status_id', $employeeId)->where('approval_status', 'Pending')->count();
        $pendingDECCount = CATransaction::where('sett_id', $employeeId)->where('approval_sett', 'Pending')->count();

        // Memformat tanggal
        foreach ($ca_transactions as $transaction) {
            $transaction->formatted_start_date = Carbon::parse($transaction->start_date)->format('d-m-Y');
            $transaction->formatted_end_date = Carbon::parse($transaction->end_date)->format('d-m-Y');
        }

        $pendingHTLCount = htl_transaction::where('approval_status', 'Pending')->count();

        return view('hcis.reimbursements.approval.approvalDeklarasiCashadv', [
            'pendingCACount' => $pendingCACount,
            'pendingDECCount' => $pendingDECCount,
            'link' => $link,
            'parentLink' => $parentLink,
            'userId' => $userId,
            'ca_transactions' => $ca_transactions,
            'pendingHTLCount' => $pendingHTLCount,
        ]);
    }
    public function cashadvancedFormDeklarasi($key)
    {
        $userId = Auth::id();
        $parentLink = 'Reimbursement';
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
            return redirect()->route('approval.cashadvancedDeklarasi');
        }

        // Ambil semua approval yang terkait dengan ca_id
        $approvals = ca_sett_approval::where('ca_id', $ca_id)
            ->orderBy('layer', 'asc') // Mengurutkan berdasarkan layer
            ->get();

        // Cek jika tombol reject ditekan
        if ($req->input('action_ca_reject')) {
            ca_sett_approval::where('ca_id', $ca_id)->update(['approval_status' => 'Rejected', 'approved_at' => Carbon::now()]);
            $caTransaction = ca_transaction::where('id', $ca_id)->first();
            if ($caTransaction) {
                $caTransaction->approval_sett = 'Rejected';
                $caTransaction->save();
            }

            Alert::success('Success', 'All approvals rejected successfully.');
            return redirect()->route('approval.cashadvancedDeklarasi');
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
                $caTransaction = ca_transaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->approval_sett = 'Approved'; // Set ke ID user layer tertinggi
                    $caTransaction->save();
                }
            } else {
                // Jika ada layer yang lebih tinggi, update status layer saat ini dan alihkan ke layer berikutnya
                $model->approval_status = 'Approved';
                $model->approved_at = Carbon::now();
                $model->save();

                // Update status_id pada ca_transaction ke employee_id layer berikutnya
                $caTransaction = ca_transaction::where('id', $ca_id)->first();
                if ($caTransaction) {
                    $caTransaction->sett_id = $nextApproval->employee_id;
                    $caTransaction->save();
                }
            }
        }

        Alert::success('Success', 'Approval updated successfully.');
        return redirect()->route('approval.cashadvancedDeklarasi');
    }
}
