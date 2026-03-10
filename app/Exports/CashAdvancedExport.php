<?php

namespace App\Exports;

use App\Models\CATransaction;
use App\Models\Employee;
use App\Models\ca_approval;
use App\Helpers\CalculateDays as CalculateDaysHelper;
use App\Helpers\GetCAEstimate as GetCAEstimateHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class CashAdvancedExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $fromDate;
    protected $untilDate;
    protected $stat;
    protected $ca_status;
    protected $permissionCompanies;
    protected $permissionGroupCompanies;
    protected $roles;

    public function __construct(
        $startDate,
        $endDate,
        $fromDate,
        $untilDate,
        $stat,
        $ca_status,
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->fromDate = $fromDate;
        $this->untilDate = $untilDate;
        $this->stat = $stat;
        $this->ca_status = $ca_status;

        $this->roles = Auth()->user()->roles;

        $restrictionData = [];
        if (!is_null($this->roles) && $this->roles->isNotEmpty()) {
            $restrictionData = json_decode(
                $this->roles->first()->restriction,
                true,
            );
        }

        $this->permissionGroupCompanies =
            $restrictionData["group_company"] ?? [];
        $this->permissionCompanies =
            $restrictionData["contribution_level_code"] ?? [];
    }

    public function collection()
    {
        // Definisikan kategori dengan nomor romawi
        $categories = [
            "dns" => ["I", "Dinas"],
            "ndns" => ["II", "Non Dinas"],
            "entr" => ["III", "Entertain"],
        ];
        $data = collect();
        $grandTotalCA = 0;
        $grandTotalReal = 0;
        $grandTotalBalance = 0;

        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $fromDate = $this->fromDate;
        $untilDate = $this->untilDate;
        $stat = $this->stat;
        $ca_status = $this->ca_status;

        foreach ($categories as $key => [$categoryNumber, $categoryName]) {
            // Menggunakan eager loading untuk mengoptimalkan pengambilan data dari relasi
            $permissionGroupCompanies = $this->permissionGroupCompanies;
            $permissionCompanies = $this->permissionCompanies;

            $categoryData = CATransaction::query()
                ->leftJoin(
                    "bt_transaction as bt",
                    "bt.no_sppd",
                    "=",
                    "ca_transactions.no_sppd",
                )
                ->leftJoin(
                    "employees as emp",
                    "emp.id",
                    "=",
                    "ca_transactions.user_id",
                )
                ->leftJoin(
                    DB::raw("(SELECT ca_id, employee_id
                            FROM ca_approvals
                            WHERE role_name = 'Dept Head'
                            GROUP BY ca_id, role_name, employee_id) as cp"),
                    "cp.ca_id",
                    "=",
                    "ca_transactions.id",
                )
                ->leftJoin(
                    "employees as dph",
                    "dph.employee_id",
                    "=",
                    "cp.employee_id",
                )
                ->leftJoin(
                    DB::raw("(SELECT ca_id, employee_id
                            FROM ca_approvals
                            WHERE role_name = 'Div Head'
                            GROUP BY ca_id, role_name, employee_id) as cp2"),
                    "cp2.ca_id",
                    "=",
                    "ca_transactions.id",
                )
                ->leftJoin(
                    "employees as dvh",
                    "dvh.employee_id",
                    "=",
                    "cp2.employee_id",
                )
                ->leftJoin(
                    DB::raw("(SELECT ca_id, employee_id
                            FROM ca_sett_approvals
                            WHERE role_name = 'Dept Head'
                            GROUP BY ca_id, role_name, employee_id) as cp_st"),
                    "cp_st.ca_id",
                    "=",
                    "ca_transactions.id",
                )
                ->leftJoin(
                    "employees as dph_st",
                    "dph_st.employee_id",
                    "=",
                    "cp_st.employee_id",
                )
                ->leftJoin(
                    DB::raw("(SELECT ca_id, employee_id
                            FROM ca_sett_approvals
                            WHERE role_name = 'Div Head'
                            GROUP BY ca_id, role_name, employee_id) as cp2_st"),
                    "cp2_st.ca_id",
                    "=",
                    "ca_transactions.id",
                )
                ->leftJoin(
                    "employees as dvh_st",
                    "dvh_st.employee_id",
                    "=",
                    "cp2_st.employee_id",
                )
                ->select(
                    DB::raw("'$categoryNumber' AS type_ca_number"),
                    DB::raw("'$categoryName' AS type_ca_name"),
                    "ca_transactions.*",
                    "emp.employee_id",
                    "emp.fullname",
                    "emp.group_company",
                    "bt.status as travel_status",
                    "dph.fullname as approval1",
                    "dvh.fullname as approval2",
                    "dph_st.fullname as approval_sett1",
                    "dvh_st.fullname as approval_sett2",
                    DB::raw("CASE
                        WHEN ca_transactions.total_cost = '' OR ca_transactions.total_cost IS NULL THEN 0
                        ELSE ca_transactions.total_cost
                    END AS ca_transactions_total_cost"),
                    DB::raw(
                        "DATE_FORMAT(ca_transactions.created_at, '%d-%M-%Y') as formatted_created_at",
                    ),
                    DB::raw(
                        "DATE_FORMAT(ca_transactions.ca_paid_date, '%d-%M-%Y') as formatted_date_required",
                    ),
                    DB::raw(
                        "DATE_FORMAT(ca_transactions.start_date, '%d-%M-%Y') as formatted_start_date",
                    ),
                    DB::raw(
                        "DATE_FORMAT(ca_transactions.end_date, '%d-%M-%Y') as formatted_end_date",
                    ),
                    DB::raw(
                        "DATE_FORMAT(ca_transactions.declare_estimate, '%d-%M-%Y') as formatted_declare_estimate",
                    ),

                    // OLD LOGIC
                    DB::raw(
                        "DATEDIFF(CURDATE(), ca_transactions.declare_estimate) as days_difference",
                    ),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) > 0 THEN 'Overdue'
                        ELSE 'Not Overdue'
                    END as overdue_status"),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) < 0 THEN ca_transactions.total_ca
                        ELSE 0
                    END as total_ca_adjusted"),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 0 AND 6 THEN ca_transactions.total_ca
                        ELSE 0
                    END as total_ca_within_6_days"),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 7 AND 14 THEN ca_transactions.total_ca
                        ELSE 0
                    END as total_ca_within_14_days"),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 15 AND 30 THEN ca_transactions.total_ca
                        ELSE 0
                    END as total_ca_within_30_days"),
                    DB::raw("CASE
                        WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 30 AND 999 THEN ca_transactions.total_ca
                        ELSE 0
                    END as total_ca_within_99_days"),

                    // // NEW LOGIC
                    // // WE COMMEND THIS BECAUSE WE NEED TO CALCULATE THE DIFFERENT DAYS MANUALLY (HOLIDAY EXCLUDED)
                    // DB::raw(
                    //     "DATEDIFF(CURDATE(), ca_transactions.declare_estimate) as days_difference",
                    // ),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) > 0 THEN 'Overdue'
                    //     ELSE 'Not Overdue'
                    // END as overdue_status"),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) < 0 THEN ca_transactions.total_ca
                    //     ELSE 0
                    // END as total_ca_adjusted"),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 0 AND 6 THEN ca_transactions.total_ca
                    //     ELSE 0
                    // END as total_ca_within_6_days"),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 7 AND 14 THEN ca_transactions.total_ca
                    //     ELSE 0
                    // END as total_ca_within_14_days"),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 15 AND 30 THEN ca_transactions.total_ca
                    //     ELSE 0
                    // END as total_ca_within_30_days"),
                    // DB::raw("CASE
                    //     WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) BETWEEN 30 AND 999 THEN ca_transactions.total_ca
                    //     ELSE 0
                    // END as total_ca_within_99_days"),
                )
                ->whereNull("ca_transactions.deleted_at")
                ->where("ca_transactions.type_ca", $key);

            // // FOR TRACING
            // // Trace & Log Raw Query
            // $sql = $categoryData->toRawSql();
            // header("Content-Type: text/plain");
            // die($sql);

            // Tambahkan kondisi permission jika ada data di $permissionCompanies
            if (!empty($permissionCompanies)) {
                $categoryData->whereIn(
                    "ca_transactions.contribution_level_code",
                    $permissionCompanies,
                );
            }
            if (!empty($permissionGroupCompanies)) {
                $categoryData->whereIn(
                    "emp.group_company",
                    $permissionGroupCompanies,
                );
            }

            if (!empty($startDate) && !empty($endDate)) {
                $categoryData->whereBetween("ca_transactions.start_date", [
                    $startDate,
                    $endDate,
                ]);
            }

            if (!empty($fromDate) && !empty($untilDate)) {
                $categoryData->whereBetween("ca_transactions.created_at", [
                    $fromDate,
                    $untilDate,
                ]);
            }

            if (!empty($stat)) {
                if ($stat === "On Progress") {
                    $categoryData->where(
                        "ca_transactions.ca_status",
                        "!=",
                        "Done",
                    );
                } elseif ($stat === "Done") {
                    $categoryData->where("ca_transactions.ca_status", "Done");
                }
            }

            if (!empty($ca_status)) {
                if ($ca_status == "nonca") {
                    $categoryData->where("ca_transactions.total_ca", 0);
                } else {
                    $categoryData->where("ca_transactions.total_ca", ">", 0);
                }
            }

            $categoryData = $categoryData->get();

            // Hitung total per kategori
            $totalCA = $categoryData->sum("total_ca");
            $totalReal = $categoryData->sum("total_real");
            $totalBalance = $categoryData->sum("ca_transactions_total_cost");

            // Tambahkan ke grand total
            $grandTotalCA += $totalCA;
            $grandTotalReal += $totalReal;
            $grandTotalBalance += $totalBalance;

            // Tambahkan baris header untuk kategori (misalnya "I - Dinas")
            $data->push([
                "Type_CA" => $categoryNumber,
                "Unit" => $categoryName,
                "Company" => "",
                "Total CA" => "",
                "Total Settlement" => "",
                "Balance" => "",
            ]);

            // OLD LOGIC
            // Tambahkan data kategori dengan nomor urut
            $categoryData->each(function ($row) use ($data) {
                if (strtoupper($row->ca_status) == "DONE") {
                    $data->push([
                        "Type_CA" => "", // Nomor urut
                        "Employee ID" => $row->employee_id,
                        "Employee Name" => $row->fullname,
                        "Dept Head" => $row->approval1,
                        "Div Head" => $row->approval2,
                        "Unit" => $row->unit,
                        "Level Code" => $row->contribution_level_code,
                        "No CA" => $row->no_ca,
                        "CA Status" => $row->ca_status,
                        "No SPPD" => $row->no_sppd,
                        "Travel Status" => $row->travel_status,
                        "Total CA" => (string) ($row->total_ca ?? 0),
                        "Date Required" => $row->formatted_date_required,
                        "Created At" => $row->formatted_created_at,
                        "Start Date" => $row->formatted_start_date,
                        "End Date" => $row->formatted_end_date,
                        "Declare Estimate" => $row->formatted_declare_estimate,
                        "Total Settlement" => (string) ($row->total_real ?? 0),
                        "Balance" =>
                            (string) ($row->ca_transactions_total_cost ?? 0),
                        "Approval Stat" => $row->approval_status,
                        "Approval Sett" => $row->approval_sett,
                        "Approval Ext" => $row->approval_extend,
                        "Days" => "",
                        "Overdue" => "",
                        "CA Adjust" => "",
                        "CA 6Days" => "",
                        "CA 14Days" => "",
                        "CA 30Days" => "",
                        "CA 99Days" => "",
                    ]);
                } else {
                    $data->push([
                        "Type_CA" => "", // Nomor urut
                        "Employee ID" => $row->employee_id,
                        "Employee Name" => $row->fullname,
                        "Dept Head" => $row->approval1,
                        "Div Head" => $row->approval2,
                        "Unit" => $row->unit,
                        "Level Code" => $row->contribution_level_code,
                        "No CA" => $row->no_ca,
                        "CA Status" => $row->ca_status,
                        "No SPPD" => $row->no_sppd,
                        "Travel Status" => $row->travel_status,
                        "Total CA" => (string) ($row->total_ca ?? 0),
                        "Date Required" => $row->formatted_date_required,
                        "Created At" => $row->formatted_created_at,
                        "Start Date" => $row->formatted_start_date,
                        "End Date" => $row->formatted_end_date,
                        "Declare Estimate" => $row->formatted_declare_estimate,
                        "Total Settlement" => (string) ($row->total_real ?? 0),
                        "Balance" =>
                            (string) ($row->ca_transactions_total_cost ?? 0),
                        "Approval Stat" => $row->approval_status,
                        "Approval Sett" => $row->approval_sett,
                        "Approval Ext" => $row->approval_extend,
                        "Days" => $row->days_difference,
                        "Overdue" => $row->overdue_status,
                        "CA Adjust" => $row->total_ca_adjusted,
                        "CA 6Days" => $row->total_ca_within_6_days,
                        "CA 14Days" => $row->total_ca_within_14_days,
                        "CA 30Days" => $row->total_ca_within_30_days,
                        "CA 99Days" => $row->total_ca_within_99_days,
                    ]);
                }
            });

            // // NEW LOGIC
            // // Tambahkan data kategori dengan nomor urut
            // $categoryData->each(function ($row) use ($data) {
            //     $declare_estimate_days_overdue = CalculateDaysHelper::different_days_exclude_holiday(
            //         $row->declare_estimate,
            //         now(),
            //     );

            //     $total_ca = $row->total_ca ?? 0;

            //     $total_ca_adjusted = 0;
            //     $overdue_status = "Overdue";

            //     if ($declare_estimate_days_overdue < 1) {
            //         $total_ca_adjusted = $total_ca;
            //         $overdue_status = "Not Overdue";
            //     }

            //     $total_ca_within_6_days = 0;
            //     $total_ca_within_14_days = 0;
            //     $total_ca_within_30_days = 0;
            //     $total_ca_within_99_days = 0;

            //     if (
            //         $declare_estimate_days_overdue >= 0 &&
            //         $declare_estimate_days_overdue <= 6
            //     ) {
            //         $total_ca_within_6_days = $total_ca;
            //     }

            //     if (
            //         $declare_estimate_days_overdue >= 7 &&
            //         $declare_estimate_days_overdue <= 14
            //     ) {
            //         $total_ca_within_14_days = $total_ca;
            //     }

            //     if (
            //         $declare_estimate_days_overdue >= 15 &&
            //         $declare_estimate_days_overdue <= 30
            //     ) {
            //         $total_ca_within_30_days = $total_ca;
            //     }

            //     if (
            //         $declare_estimate_days_overdue >= 30 &&
            //         $declare_estimate_days_overdue <= 999
            //     ) {
            //         $total_ca_within_99_days = $total_ca;
            //     }

            //     if (strtoupper($row->ca_status) == "DONE") {
            //         $data->push([
            //             "Type_CA" => "", // Nomor urut
            //             "Employee ID" => $row->employee_id,
            //             "Employee Name" => $row->fullname,
            //             "Dept Head" => $row->approval1,
            //             "Div Head" => $row->approval2,
            //             "Unit" => $row->unit,
            //             "Level Code" => $row->contribution_level_code,
            //             "No CA" => $row->no_ca,
            //             "CA Status" => $row->ca_status,
            //             "No SPPD" => $row->no_sppd,
            //             "Travel Status" => $row->travel_status,
            //             "Total CA" => (string) $total_ca,
            //             "Date Required" => $row->formatted_date_required,
            //             "Created At" => $row->formatted_created_at,
            //             "Start Date" => $row->formatted_start_date,
            //             "End Date" => $row->formatted_end_date,
            //             "Declare Estimate" => $row->formatted_declare_estimate,
            //             "Total Settlement" => (string) ($row->total_real ?? 0),
            //             "Balance" =>
            //                 (string) ($row->ca_transactions_total_cost ?? 0),
            //             "Approval Stat" => $row->approval_status,
            //             "Approval Sett" => $row->approval_sett,
            //             "Approval Ext" => $row->approval_extend,
            //             "Days" => "",
            //             "Overdue" => "",
            //             "CA Adjust" => "",
            //             "CA 6Days" => "",
            //             "CA 14Days" => "",
            //             "CA 30Days" => "",
            //             "CA 99Days" => "",
            //         ]);
            //     } else {
            //         $data->push([
            //             "Type_CA" => "", // Nomor urut
            //             "Employee ID" => $row->employee_id,
            //             "Employee Name" => $row->fullname,
            //             "Dept Head" => $row->approval1,
            //             "Div Head" => $row->approval2,
            //             "Unit" => $row->unit,
            //             "Level Code" => $row->contribution_level_code,
            //             "No CA" => $row->no_ca,
            //             "CA Status" => $row->ca_status,
            //             "No SPPD" => $row->no_sppd,
            //             "Travel Status" => $row->travel_status,
            //             "Total CA" => (string) $total_ca,
            //             "Date Required" => $row->formatted_date_required,
            //             "Created At" => $row->formatted_created_at,
            //             "Start Date" => $row->formatted_start_date,
            //             "End Date" => $row->formatted_end_date,
            //             "Declare Estimate" => $row->formatted_declare_estimate,
            //             "Total Settlement" => (string) ($row->total_real ?? 0),
            //             "Balance" =>
            //                 (string) ($row->ca_transactions_total_cost ?? 0),
            //             "Approval Stat" => $row->approval_status,
            //             "Approval Sett" => $row->approval_sett,
            //             "Approval Ext" => $row->approval_extend,
            //             "Days" => $declare_estimate_days_overdue,
            //             "Overdue" => $overdue_status,
            //             "CA Adjust" => $total_ca_adjusted,
            //             "CA 6Days" => $total_ca_within_6_days,
            //             "CA 14Days" => $total_ca_within_14_days,
            //             "CA 30Days" => $total_ca_within_30_days,
            //             "CA 99Days" => $total_ca_within_99_days,
            //         ]);
            //     }
            // });

            // Tambahkan baris subtotal setelah data kategori
            $data->push([
                "Type_CA" => "Total $categoryName",
                "Employee ID" => "",
                "Employee Name" => "",
                "Dept Head" => "",
                "Div Head" => "",
                "Unit" => "",
                "Level Code" => "",
                "No CA" => "",
                "CA Status" => "",
                "No SPPD" => "",
                "Travel Status" => "",
                "Total CA" => (string) ($totalCA ?? 0),
                "Date Required" => "",
                "Created At" => "",
                "Start Date" => "",
                "End Date" => "",
                "Declare Estimate" => "",
                "Total Settlement" => (string) ($totalReal ?? 0),
                "Balance" => (string) ($totalBalance ?? 0),
            ]);
        }

        // Tambahkan baris total keseluruhan setelah semua kategori
        $data->push([
            "Type_CA" => "Total Employee Advanced",
            "Employee ID" => "",
            "Employee Name" => "",
            "Dept Head" => "",
            "Div Head" => "",
            "Unit" => "",
            "Level Code" => "",
            "No CA" => "",
            "CA Status" => "",
            "No SPPD" => "",
            "Travel Status" => "",
            "Total CA" => (string) ($grandTotalCA ?? 0),
            "Date Required" => "",
            "Created At" => "",
            "Start Date" => "",
            "End Date" => "",
            "Declare Estimate" => "",
            "Total Settlement" => (string) ($grandTotalReal ?? 0),
            "Balance" => (string) ($grandTotalBalance ?? 0),
        ]);

        return $data;
    }

    public function headings(): array
    {
        // Base headings
        return [
            "No",
            "Employee ID",
            "Employee Name",
            "Dept Head",
            "Div Head",
            "Unit",
            "Company",
            "Doc No",
            "CA Status",
            "Assignment",
            "Travel Status",
            "Total CA",
            "CA Paid Date",
            "Submitted Date",
            "Start Date",
            "End Date",
            "Est. Settlement Date",
            "Total Settlement",
            "Balance",
            "Request Status",
            "Settlement Status",
            "Extend Status",
            "Days",
            "Overdue",
            "Current",
            "< 7 Days",
            "7 - 14 Days",
            "15 - 30 Days",
            "> 30 Days",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                "font" => [
                    "bold" => true,
                    "color" => [
                        "argb" => "FFFFFFFF", // Warna putih
                    ],
                ],
                "fill" => [
                    "fillType" =>
                        \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    "startColor" => [
                        "argb" => "228B22", // Warna hijau
                    ],
                ],
                "alignment" => [
                    "horizontal" =>
                        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center horizontal
                    "vertical" =>
                        \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Center vertical
                ],
                "borders" => [
                    "allBorders" => [
                        "borderStyle" =>
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getColumnDimension("A")->setWidth(7.45);
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow(); // Get highest row number
                $highestColumn = $sheet->getHighestColumn(); // Get highest column letter

                // Apply border to the entire data range
                $sheet
                    ->getStyle("A1:" . $highestColumn . $highestRow)
                    ->applyFromArray([
                        "borders" => [
                            "allBorders" => [
                                "borderStyle" =>
                                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                // Adjust column widths automatically
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    $highestColumn,
                ); // Get highest column index
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                        $col,
                    ); // Convert to letter
                    $sheet
                        ->getColumnDimension($columnLetter)
                        ->setAutoSize(true);
                }

                $sheet
                    ->getStyle("B1:B" . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(
                        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
                    );
            },
        ];
    }
}
