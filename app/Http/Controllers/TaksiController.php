<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Taksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaksiController extends Controller
{
    public function taksi()
    {
        $perPage = request()->query('per_page', 10);
        $vt = Taksi::orderBy('id_vt', 'asc')->paginate($perPage);

        $parentLink = 'Reimbursement';
        $link = 'Voucher Taksi';

        return view('hcis.reimbursements.taksi.taksi', compact('vt', 'parentLink', 'link'));
    }
    public function taksiFormAdd()
    {
        $userId = Auth::id();
        $employee_data = Employee::where('id', $userId)->first();
        $sppd_bt = BusinessTrip::orderBy('no_sppd')->get();
        $companies = Company::orderBy('contribution_level')->get();
        return view(
            'hcis.reimbursements.taksi.formAddTaksi',
            [
                'employee_data' => $employee_data,
                'companies' => $companies,
                'sppd_bt' => $sppd_bt,
            ]
        );
    }

    public function taksiCreate(Request $request)
    {
        $noSppd = $this->generateNoSppd();
        $no_vt = $this->generateNoVt();
        Taksi::create([
            'id_vt ' => $request->id_vt,
            'nama' => $request->nama,
            'no_vt' => $no_vt,
            'no_sppd' => $noSppd,
            'user_id' => $request->user_id,
            'unit' => $request->unit,
            'sppd_bt' => $request->sppd_bt,
            'nom_vt' => $request->nom_vt,
            'keeper_vt' => $request->keeper_vt,
        ]);
        return redirect('/taksi');
    }
    private function generateNoSppd()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Assuming you want to generate no_sppd similarly to no_ca
        $lastTransaction = Taksi::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('no_sppd', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/VT_ACC\/' . $romanMonth . '\/\d{4}/', $lastTransaction->no_sppd, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/VT_ACC/$romanMonth/$currentYear";

        return $newNoSppd;
    }
    private function generateNoVt()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Assuming you want to generate no_sppd similarly to no_ca
        $lastTransaction = Taksi::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('no_vt', 'desc')
            ->first();

        if ($lastTransaction && preg_match('/(\d{3})\/VT\/' . $romanMonth . '\/\d{4}/', $lastTransaction->no_sppd, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $newNoSppd = "$newNumber/VT/$romanMonth/$currentYear";

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
