<?php

namespace App\Imports;

use App\Models\ApprovalLayer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ApprovalLayerImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $invalidEmployees = [];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if ($row['layer'] > 6) {
            // Simpan ID karyawan ke dalam array invalidEmployees
            $this->invalidEmployees[] = $row['employee_id'];
            return null; // Jangan masukkan data ini ke database
        }

        return new ApprovalLayer([
            'employee_id' => $row['employee_id'],
            'approver_id' => $row['approver_id'],
            'layer' => $row['layer'],
            'updated_by' => $this->userId,
        ]);
    }

    public function getInvalidEmployees()
    {
        return $this->invalidEmployees;
    }
}
