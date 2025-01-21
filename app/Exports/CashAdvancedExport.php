<?php

namespace App\Exports;

use App\Models\CATransaction;
use App\Models\Employee;
use App\Models\ca_approval;
use App\Models\ca_sett_approval;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class CashAdvancedExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $fromDate;
    protected $untilDate;
    protected $stat;

    public function __construct($startDate, $endDate, $fromDate, $untilDate, $stat)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->fromDate = $fromDate;
        $this->untilDate = $untilDate;
        $this->stat = $stat;
    }

    public function collection()
    {
        // Definisikan kategori dengan nomor romawi
        $categories = ['dns' => ['I', 'Dinas'], 'ndns' => ['II', 'Non Dinas'], 'entr' => ['III', 'Entertain']];
        $data = collect();
        $grandTotalCA = 0;
        $grandTotalReal = 0;
        $grandTotalBalance = 0;

        foreach ($categories as $key => [$categoryNumber, $categoryName]) {
            // Query data per kategori
            $categoryData = CATransaction::select(
                DB::raw("'$categoryNumber' AS type_ca_number"),  // Set nomor kategori (I, II, III)
                DB::raw("'$categoryName' AS type_ca_name"),       // Set nama kategori (Dinas, Non Dinas, Entertain)
                'ca_transactions.unit',
                DB::raw("DATE_FORMAT(ca_transactions.created_at, '%d-%M-%Y') as formatted_created_at"),
                DB::raw("DATE_FORMAT(ca_transactions.date_required, '%d-%M-%Y') as formatted_date_required"),
                DB::raw("DATE_FORMAT(ca_transactions.start_date, '%d-%M-%Y') as formatted_start_date"),
                DB::raw("DATE_FORMAT(ca_transactions.end_date, '%d-%M-%Y') as formatted_end_date"),
                DB::raw("DATE_FORMAT(ca_transactions.declare_estimate, '%d-%M-%Y') as formatted_declare_estimate"),
                'ca_transactions.contribution_level_code',
                'employees.employee_id',
                'employees.fullname as employee_name',
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT e1.fullname ORDER BY layer ASC)
                    FROM ca_approvals ca1
                    LEFT JOIN employees e1 ON FIND_IN_SET(e1.employee_id, (
                        SELECT GROUP_CONCAT(DISTINCT employee_id ORDER BY layer ASC)
                        FROM ca_approvals
                        WHERE ca_approvals.ca_id = ca_transactions.id
                        AND role_name = 'Dept Head'
                    )) > 0
                    WHERE ca1.ca_id = ca_transactions.id
                    AND ca1.role_name = 'Dept Head'
                ) AS manager_l1_fullnames"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT e2.fullname ORDER BY layer ASC)
                    FROM ca_approvals ca2
                    LEFT JOIN employees e2 ON FIND_IN_SET(e2.employee_id, (
                        SELECT GROUP_CONCAT(DISTINCT employee_id ORDER BY layer ASC)
                        FROM ca_approvals
                        WHERE ca_approvals.ca_id = ca_transactions.id
                        AND role_name = 'Div Head'
                    )) > 0
                    WHERE ca2.ca_id = ca_transactions.id
                    AND ca2.role_name = 'Div Head'
                ) AS manager_l2_fullnames"),
                'ca_transactions.no_ca',
                'ca_transactions.no_sppd',
                'ca_transactions.total_ca',
                'ca_transactions.total_real',
                DB::raw('ca_transactions.total_ca - ca_transactions.total_real as balance'),
                'ca_transactions.approval_status',
                'ca_transactions.approval_sett',
                'ca_transactions.approval_extend',
                DB::raw("DATEDIFF(CURDATE(), ca_transactions.declare_estimate) as days_difference"),
                DB::raw("CASE
                WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) > 0 THEN 'Overdue'
                ELSE 'Not Overdue'
            END as overdue_status"),
                DB::raw("CASE
                WHEN DATEDIFF(CURDATE(), ca_transactions.declare_estimate) > 0 THEN ca_transactions.total_ca
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
            END as total_ca_within_99_days")
            )
                ->join('employees', 'ca_transactions.user_id', '=', 'employees.id')
                ->where('ca_transactions.type_ca', $key)
                ->get();

            // Hitung total per kategori
            $totalCA = $categoryData->sum('total_ca');
            $totalReal = $categoryData->sum('total_real');
            $totalBalance = $categoryData->sum('balance');

            // Tambahkan ke grand total
            $grandTotalCA += $totalCA;
            $grandTotalReal += $totalReal;
            $grandTotalBalance += $totalBalance;

            // Tambahkan baris header untuk kategori (misalnya "I - Dinas")
            $data->push([
                'Type_CA' => $categoryNumber,
                'Unit' => $categoryName,
                'Company' => '',
                'Total CA' => '',
                'Total Settlement' => '',
                'Balance' => ''
            ]);

            // Tambahkan data kategori dengan nomor urut
            $categoryData->each(function ($row, $index) use ($data) {
                $data->push([
                    'Type_CA' => $index + 1,  // Nomor urut
                    'Unit' => $row->unit,
                    'Created At' => $row->formatted_created_at,
                    'Date Required' => $row->formatted_date_required,
                    'Start Date' => $row->formatted_start_date,
                    'End Date' => $row->formatted_end_date,
                    'Declare Estimate' => $row->formatted_declare_estimate,
                    'Level Code' => $row->contribution_level_code,
                    'Employee ID' => $row->employee_id,
                    'Employee Name' => $row->employee_name,
                    'Dept Head' => $row->manager_l1_fullnames,
                    'Div Head' => $row->manager_l2_fullnames,
                    'No CA' => $row->no_ca,
                    'No SPPD' => $row->no_sppd,
                    'Total CA' => $row->total_ca,
                    'Total Settlement' => $row->total_real,
                    'Balance' => $row->balance,
                    'Approval Stat' => $row->approval_status,
                    'Approval Sett' => $row->approval_sett,
                    'Approval Ext' => $row->approval_extend,
                    'Days' => $row->days_difference,
                    'Overdue' => $row->overdue_status,
                    'CA Adjust' => $row->total_ca_adjusted,
                    'CA 6Days' => $row->total_ca_within_6_days,
                    'CA 14Days' => $row->total_ca_within_14_days,
                    'CA 30Days' => $row->total_ca_within_30_days,
                    'CA 99Days' => $row->total_ca_within_99_days,
                ]);
            });

            // Tambahkan baris subtotal setelah data kategori
            $data->push([
                'Type_CA' => "Total $categoryName",
                'Unit' => '',
                'Created At' => '',
                'Date Required' => '',
                'Start Date' => '',
                'End Date' => '',
                'Declare Estimate' => '',
                'Level Code' => '',
                'Employee ID' => '',
                'Employee Name' => '',
                'Dept Head' => '',
                'Div Head' => '',
                'No CA' => '',
                'No SPPD' => '',
                'Total CA' => $totalCA,
                'Total Settlement' => $totalReal,
                'Balance' => $totalBalance
            ]);
        }

        // Tambahkan baris total keseluruhan setelah semua kategori
        $data->push([
            'Type_CA' => 'Total Employee Advanced',
            'Unit' => '',
            'Created At' => '',
            'Date Required' => '',
            'Start Date' => '',
            'End Date' => '',
            'Declare Estimate' => '',
            'Level Code' => '',
            'Employee ID' => '',
            'Employee Name' => '',
            'Dept Head' => '',
            'Div Head' => '',
            'No CA' => '',
            'No SPPD' => '',
            'Total CA' => $grandTotalCA,
            'Total Settlement' => $grandTotalReal,
            'Balance' => $grandTotalBalance
        ]);

        return $data;
    }

    public function headings(): array
    {
        // Base headings
        $headings = [
            'No',
            'Unit',
            'Submitted Date',
            'Paid Date',
            'Start Date',
            'End Date',
            'Est. Settlement Date',
            'Settlement Date',
            'Company',
            'Employee ID',
            'Employee Name',
            'Dept Head',
            'Div Head',
            'Doc No',
            'Assignment',
            'Total CA',
            'Total Settlement',
            'Balance',
            'Request Status',
            'Settlement Status',
            'Extend Status',
            'Days',
            'Overdue',
            'Current',
            '< 7 Days',
            '7 - 14 Days',
            '15 - 30 Days',
            '> 30 Days',
        ];

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF', // Warna putih
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => '228B22', // Warna kuning
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center horizontal
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,   // Center vertical
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow(); // Get highest row number
                $highestColumn = $sheet->getHighestColumn(); // Get highest column letter

                // Apply border to the entire data range
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Adjust column widths automatically
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // Get highest column index
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col); // Convert to letter
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $sheet->getStyle('B1:B' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
