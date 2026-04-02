<?php

namespace App\Exports;

use App\Http\Controller\Admin\ApprovalSettingController;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApprovalSettingSyncOldApprovalExport implements WithMultipleSheets, FromCollection, WithMapping, WithHeadings
{
    protected $bt_approvals, $ca_approvals, $sett_approvals, $type;

    public function __construct($bt_approvals = null, $ca_approvals = null, $sett_approvals = null, $type = null)
    {
        $this->bt_approvals = $bt_approvals;
        $this->ca_approvals = $ca_approvals;
        $this->sett_approvals = $sett_approvals;
        $this->type = $type;
    }

    public function sheets(): array
    {
        return [
            'BT Approval' => new self($this->bt_approvals, null, null, 'bt'),
            'CA Approval' => new self(null, $this->ca_approvals, null, 'ca'),
            'CA Sett'     => new self(null, null, $this->sett_approvals, 'sett'),
        ];
    }

    public function collection()
    {
        if ($this->type === 'bt') {
            return $this->bt_approvals;
        }

        if ($this->type === 'ca') {
            return $this->ca_approvals;
        }

        if ($this->type === 'sett') {
            return $this->sett_approvals;
        }

        return collect();
    }

    public function map($row): array
    {
        return match ($this->type) {

            'bt' => [
                $row->businessTrip ? ($row->businessTrip->no_sppd ?? "-") : "-",
                $row->businessTrip ? ($row->businessTrip->employee ? ($row->businessTrip->employee->employee_id ?? "-") : "-") : "-",
                $row->businessTrip ? ($row->businessTrip->employee ? ($row->businessTrip->employee->fullname ?? "-") : "-") : "-",
                $row->businessTrip ? ($row->businessTrip->employee ? ($row->businessTrip->employee->group_company ?? "-") : "-") : "-",
                $row->businessTrip ? ($row->businessTrip->divisi ?? "-") : "-",
                $row->businessTrip ? ($row->businessTrip->mulai ?? "-") : "-",
                $row->businessTrip ? ($row->businessTrip->kembali ?? "-") : "-",
                $row->businessTrip ? ($row->businessTrip->tujuan ?? "-") : "-",
                $row->bt_tujuan_area ?? "-",
                $row->businessTrip ? ($row->businessTrip->bb_perusahaan ?? "-") : "-",
                $row->businessTrip ? ($row->businessTrip->status ?? "-") : "-",
                $row->layer,
                $row->role_name ?? "-",
                $row->employee_id,
                $row->employee ? ($row->employee->fullname ?? "-") : "-",
                $row->new_employee_id ?? "-",
                $row->new_employee_fullname ?? "-",
                $row->approval_status,
                $row->approved_at ?? "-",
                $row->approval_setting_name ?? "-",
                $row->approval_setting_company_names ?? "-",
                $row->approval_setting_contibution_level_codes ?? "-",
                $row->approval_setting_work_areas ?? "-",
            ],

            'ca' => [
                $row->caTransaction ? ($row->caTransaction->no_ca ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->no_sppd ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->employee_id ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->fullname ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->group_company ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->unit ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->contribution_level_code ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->destination ?? "-") : "-",
                $row->ca_destination_area ?? "-",
                $row->caTransaction ? ($row->caTransaction->others_location ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->approval_status ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->ca_status ?? "-") : "-",
                $row->layer,
                $row->role_name ?? "-",
                $row->employee_id,
                $row->employee ? ($row->employee->fullname ?? "-") : "-",
                $row->new_employee_id ?? "-",
                $row->new_employee_fullname ?? "-",
                $row->approval_status,
                $row->approved_at ?? "-",
                $row->approval_setting_name ?? "-",
                $row->approval_setting_company_names ?? "-",
                $row->approval_setting_contibution_level_codes ?? "-",
                $row->approval_setting_work_areas ?? "-",
            ],

            'sett' => [
                $row->caTransaction ? ($row->caTransaction->no_ca ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->no_sppd ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->employee_id ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->fullname ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->employee ? ($row->caTransaction->employee->group_company ?? "-") : "-") : "-",
                $row->caTransaction ? ($row->caTransaction->unit ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->contribution_level_code ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->destination ?? "-") : "-",
                $row->ca_destination_area ?? "-",
                $row->caTransaction ? ($row->caTransaction->others_location ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->approval_sett ?? "-") : "-",
                $row->caTransaction ? ($row->caTransaction->ca_status ?? "-") : "-",
                $row->layer,
                $row->role_name ?? "-",
                $row->employee_id,
                $row->employee ? ($row->employee->fullname ?? "-") : "-",
                $row->new_employee_id ?? "-",
                $row->new_employee_fullname ?? "-",
                $row->approval_status,
                $row->approved_at ?? "-",
                $row->approval_setting_name ?? "-",
                $row->approval_setting_company_names ?? "-",
                $row->approval_setting_contibution_level_codes ?? "-",
                $row->approval_setting_work_areas ?? "-",
            ],

            default => [],
        };
    }

    public function headings(): array
    {
        return match ($this->type) {

            'bt' => [
                'BT No SPPD',
                'BT Employee ID',
                'BT Employee Name',
                'BT Employee Group Company',
                'BT Divisi',
                'BT Mulai',
                'BT Kembali',
                'BT Tujuan',
                'BT Tujuan Area',
                'BT BB Perusahaan',
                'BT Status',

                'Approval Layer',
                'Approval Role Name',
                'Approval Old Employee ID',
                'Approval Old Employee Name',
                'Approval New Employee ID',
                'Approval New Employee Name',
                'Approval Status',
                'Approved At',

                'Setting Name',
                'Setting Company Names',
                'Setting Contribution Level Codes',
                'Setting Work Areas',
            ],

            'ca' => [
                'CA No',
                'CA No SPPD',
                'CA Employee ID',
                'CA Employee Name',
                'CA Employee Group Company',
                'CA Unit',
                'CA Contribution Level Code',
                'CA Destination',
                'CA Destination Area',
                'CA Others Location',
                'CA Approval Status',
                'CA Status',

                'Approval Layer',
                'Approval Role Name',
                'Approval Old Employee ID',
                'Approval Old Employee Name',
                'Approval New Employee ID',
                'Approval New Employee Name',
                'Approval Status',
                'Approved At',

                'Setting Name',
                'Setting Company Names',
                'Setting Contribution Level Codes',
                'Setting Work Areas',
            ],

            'sett' => [
                'CA No',
                'CA No SPPD',
                'CA Employee ID',
                'CA Employee Name',
                'CA Employee Group Company',
                'CA Unit',
                'CA Contribution Level Code',
                'CA Destination',
                'CA Destination Area',
                'CA Others Location',
                'CA Approval Sett Status',
                'CA Status',

                'Approval Layer',
                'Approval Role Name',
                'Approval Old Employee ID',
                'Approval Old Employee Name',
                'Approval New Employee ID',
                'Approval New Employee Name',
                'Approval Status',
                'Approved At',

                'Setting Name',
                'Setting Company Names',
                'Setting Contribution Level Codes',
                'Setting Work Areas',
            ],

            default => [],
        };
    }
}
