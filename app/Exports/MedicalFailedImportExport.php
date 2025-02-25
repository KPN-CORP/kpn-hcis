<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MedicalFailedImportExport implements FromArray, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $failedRows;

    public function __construct(array $failedRows)
    {
        $this->failedRows = $failedRows;
    }

    public function array(): array
    {
        return $this->failedRows;
    }

    public function headings(): array
    {
        return [
            'No', 'Employee Name', 'Employee ID', 'Contribution Level Code',
            'No Invoice', 'Hospital Name', 'Patient Name', 'Disease',
            'Date', 'Coverage Detail', 'Period', 'Medical Type',
            'Amount', 'Error Message' // Kolom tambahan untuk error
        ];
    }
}
