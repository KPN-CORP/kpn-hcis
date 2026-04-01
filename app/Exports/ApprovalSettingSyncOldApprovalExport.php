<?php

namespace App\Exports;

use App\Http\Controller\Admin\ApprovalSettingController;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApprovalSettingSyncOldApprovalExport implements WithMultipleSheets, FromCollection, WithMapping, WithHeadings
{
    protected $bt, $ca, $sett, $type;

    public function __construct($bt = null, $ca = null, $sett = null, $type = null)
    {
        $this->bt = $bt;
        $this->ca = $ca;
        $this->sett = $sett;
        $this->type = $type;
    }

    public function sheets(): array
    {
        return [
            'BT Approval' => new self($this->bt, null, null, 'bt'),
            'CA Approval' => new self(null, $this->ca, null, 'ca'),
            'CA Sett'     => new self(null, null, $this->sett, 'sett'),
        ];
    }

    public function collection()
    {
        if ($this->type === 'bt') {
            return $this->bt;
        }

        if ($this->type === 'ca') {
            return $this->ca;
        }

        if ($this->type === 'sett') {
            return $this->sett;
        }

        return collect();
    }

    public function map($row): array
    {
        return match ($this->type) {
            'bt' => [
                $row->id,
                $row->role_name,
                // $row->businessTrip->status ?? null,
                $row->approved_at,
            ],

            'ca' => [
                $row->id,
                $row->role_name,
                // $row->caTransaction->no_ca ?? null,
                // $row->caTransaction->approval_status ?? null,
                $row->approved_at,
            ],

            'sett' => [
                $row->id,
                $row->role_name,
                // $row->caTransaction->no_ca ?? null,
                // $row->caTransaction->approval_sett ?? null,
                $row->approved_at,
            ],

            default => [],
        };
    }

    public function headings(): array
    {
        return match ($this->type) {

            'bt' => [
                'ID',
                'Role',
                // 'Status BT',
                'Approved At',
            ],

            'ca' => [
                'ID',
                'Role',
                // 'No CA',
                // 'Approval Status',
                'Approved At',
            ],

            'sett' => [
                'ID',
                'Role',
                // 'No CA',
                // 'Approval Settlement',
                'Approved At',
            ],

            default => [],
        };
    }
}
