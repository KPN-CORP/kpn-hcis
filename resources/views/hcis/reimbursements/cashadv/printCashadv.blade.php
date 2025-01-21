<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CA Transaction</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            width: 100%;
            height: auto;
        }

        .header img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .content {
            padding: 0px;
        }

        h5 {
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        p {
            margin-top: 4px;
            padding: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td {
            padding: 1px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .label {
            width: 30%;
        }

        .colon {
            width: 20px;
            text-align: center;
        }

        .value {
            width: 70%;
        }

        .section-title {
            margin-top: 20px;
        }

        .table-approve {
            border-collapse: collapse;
            width: 100%;
        }

        .table-approve th,
        .table-approve td {
            border: 1px solid black;
            padding: 1px;
            text-align: center;
        }

        .table-approve .head-row {
            font-weight: bold;
        }

        .table-approve th {
            background-color: #d4d4d4;
        }

        .table-approve .total-row {
            text-align: left;
        }
        .approved {
            color: green;
        }
        .pending {
            color: yellow;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            text-align: right;
            line-height: 1.5cm;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/kop.jpg') }}" alt="Kop Surat">
    </div>
    <h5 class="center">Form Cash Advanced</h5>
    <h5 class="center">No. {{ $transactions->no_ca }}</h5>

    <table>
        <tr>
            <td colspan="3"><b>Employee Data:</b></td>
        </tr>
        <tr>
            <td class="label">Name</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->fullname }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->email }}</td>
        </tr>
        <tr>
            <td class="label">Account Details</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->bank_name }} - {{ $transactions->employee->bank_account_number }} - {{ $transactions->employee->bank_account_name }}</td>
        </tr>
        <tr>
            <td class="label">Dept</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->unit }}</td>
        </tr>
        <tr>
            <td class="label">PT/Location</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->contribution_level_code }} / {{ $transactions->employee->office_area }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>CA Submission Details:</b></td>
        </tr>
        <tr>
            <td class="label">Costing Company</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $transactions->companies->contribution_level }} ({{ $transactions->contribution_level_code }})
            </td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $transactions->destination == 'Others' ? $transactions->others_location : $transactions->destination }}
            </td>
        </tr>
        <tr>
            <td class="label">Start Date</td>
            <td class="colon">:</td>
            <td class="value">{{ \Carbon\Carbon::parse($transactions->start_date)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($transactions->end_date)->format('d-M-y') }} ({{ $transactions->total_days }} days)</td>
        </tr>
        <tr>
            <td class="label">Date CA Required</td>
            <td class="colon">:</td>
            <td class="value">{{ \Carbon\Carbon::parse($transactions->date_required)->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="label">Purpose</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->ca_needs }}</td>
        </tr>
        {{-- <tr>
            <td class="label">Status</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->approval_status }}</td>
        </tr> --}}
    </table>

    @php
        $detailCA = json_decode($transactions->detail_ca, true);
    @endphp

    @if ( $transactions->type_ca == 'dns' )
        @if (count($detailCA['detail_perdiem']) > 0 && !empty($detailCA['detail_perdiem'][0]['company_code']))
            <table class="table-approve">
                <tr>
                    <th colspan="5"><b>Perdiem Plan :</b></th>
                </tr>
                <tr class="head-row">
                    <td>Start Date</td>
                    <td>End Date</td>
                    <td>Office Location</td>
                    <td>Company Code</td>
                    <td>Total Days</td>
                </tr>

                @foreach($detailCA['detail_perdiem'] as $perdiem)
                <tr style="text-align: center">
                    <td>{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                    <td>
                        @if ($perdiem['location'] == 'Others')
                            Other ({{$perdiem['other_location']}})
                        @else
                            {{$perdiem['location']}}
                        @endif
                    </td>
                    <td>{{ $perdiem['company_code'] }}</td>
                    <td>{{ $perdiem['total_days'] }} Hari</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="head-row">Total</td>
                    <td>
                        {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Hari
                    </td>
                </tr>
            </table>
        @endif
        <table class="table-approve" style="width: 70%;">
            <tr>
                <th colspan="3"><b>Detail Cash Advanced :</b></th>
            </tr>
            <tr class="head-row">
                <td rowspan="2" style="text-align: center;">Types of Cash Advanced</td>
                <td colspan="2">Estimate</td>
            </tr>
            <tr class="head-row">
                <td>Total Days</td>
                <td>Amount</td>
            </tr>
            <tr>
                <td class="label">Perdiem</td>
                <td>
                    @if (array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) <= 0)
                        -
                    @else
                        {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Days
                    @endif
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td>Transport</td>
                <td>
                    -
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_transport'], 'nominal')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td>Accomodation</td>
                <td>
                    @if (array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) <= 0)
                        -
                    @else
                        {{ array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) }} Night
                    @endif
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td>Others</td>
                <td>
                    -
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td colspan="2">Total</td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format($transactions->total_ca, 0, ',', '.') }}</span>  
                </td>
            </tr>
        </table>
    @elseif ( $transactions->type_ca == 'ndns' )
        <table class="table-approve" style="width: 70%;">
            <tr>
                <th colspan="3"><b>Detail Cash Advanced :</b></th>
            </tr>
            <tr class="head-row">
                <td rowspan="2" style="text-align: center;">Types of Cash Advanced</td>
                <td colspan="2">Estimate</td>
            </tr>
            <tr class="head-row">
                <td>Total Days</td>
                <td>Amount</td>
            </tr>
            <tr>
                <td class="label">Non Bussiness Trip</td>
                <td>
                    {{ $transactions->total_days }} Days
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td colspan="2">Total</td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format($transactions->total_ca, 0, ',', '.') }}</span>  
                </td>
            </tr>
        </table>
    @elseif ( $transactions->type_ca == 'entr' )
        <table class="table-approve" style="width: 70%;">
            <tr>
                <th colspan="3"><b>Detail Cash Advanced :</b></th>
            </tr>
            <tr class="head-row">
                <td rowspan="2" style="text-align: center;">Types of Cash Advanced</td>
                <td colspan="2">Estimate</td>
            </tr>
            <tr class="head-row">
                <td>Total Days</td>
                <td>Amount</td>
            </tr>
            <tr>
                <td class="label">Detail Entertain</td>
                <td>
                    {{ $transactions->total_days }} Days
                </td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}</span>  
                </td>
            </tr>
            <tr>
                <td colspan="2">Total</td>
                <td>  
                    <span style="float: left; margin-left:4px">Rp.</span>  
                    <span style="float: right;">{{ number_format($transactions->total_ca, 0, ',', '.') }}</span>  
                </td>
            </tr>
        </table>
    @endif

    <div style="page-break-after:always;">
        <table border=0 style="width: 20%; font-size: 11px;">
            <tr>
                <td style=" vertical-align: top;">
                    <table class="table-approve" style="width: 100%; text-align: center; display: inline-table;">
                        <tr>
                            <th>Submitted By</th>
                        </tr>
                        <tr>
                            <td>User</td>
                        </tr>
                        <tr>
                            <td><br><br><br><br><br></td>
                        </tr>
                        <tr>
                            <td>{{ $transactions->employee->fullname }}</td>
                        </tr>
                        <tr>
                            <td>{{ $transactions->created_at }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table border=0 style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 100%;">
                    <table class="table-approve" style="text-align:center;">
                        <tr>
                            <th colspan="{{ count($approval) }}">Approval</th>
                        </tr>
                        <tr>
                            @foreach ($approval as $role)
                                <td style="width: 20%;">
                                    @if ($role->role_name == 'Dept Head')
                                        Approval 1
                                    @elseif ($role->role_name == 'Div Head')
                                        Approval 2
                                    @elseif ($role->role_name == 'Director')
                                        Approval 3
                                    @else
                                        {{ $role->role_name }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($approval as $role)
                                <td>
                                    @if($role->approval_status =='Approved')
                                        {{-- <br><img src="{{ public_path('images/approved_64.png')}}" alt="logo"> --}}
                                        <br><img src="{{ asset('images/approved_64.png')}}" alt="logo">
                                    @else
                                        <br><br><br><br><br>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($approval as $role)
                                <td>{{ $role->employee ? $role->employee->fullname : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($approval as $role)
                                <td style="text-align:center;">
                                    {{ $role->approved_at ? \Carbon\Carbon::parse($role->approved_at) : 'Date : ' }}
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div>
        <h2 style="text-align: center">Cash Advanced Attachment</h2>
        @if ( $transactions->type_ca == 'dns' )
            @if (count($detailCA['detail_perdiem']) > 0 && !empty($detailCA['detail_perdiem'][0]['company_code']))
                <table class="table-approve">
                    <tr>
                        <th colspan="6"><b>Perdiem Plan :</b></th>
                    </tr>
                    <tr class="head-row">
                        <td style="width:12%">Start Date</td>
                        <td style="width:12%">End Date</td>
                        <td>Office Location</td>
                        <td>Company Code</td>
                        <td>Total Days</td>
                        <td style="width:20%">Amount</td>
                    </tr>

                    @foreach($detailCA['detail_perdiem'] as $perdiem)
                    <tr style="text-align: center">
                        <td>{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                        <td>
                            @if ($perdiem['location'] == 'Others')
                                Other ({{$perdiem['other_location']}})
                            @else
                                {{$perdiem['location']}}
                            @endif
                        </td>
                        <td>{{ $perdiem['company_code'] }}</td>
                        <td>{{ $perdiem['total_days'] }} Hari</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format($perdiem['nominal'], 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" class="head-row">Total</td>
                        <td>
                            {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Hari
                        </td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                </table>
            @endif

            @if (count($detailCA['detail_transport']) > 0 && !empty($detailCA['detail_transport'][0]['tanggal']))
                <table class="table-approve">
                    <tr>
                        <th colspan="4"><b>Transport Plan :</b></th>
                    </tr>
                    <tr class="head-row">
                        <td style="width:12%">Date</td>
                        <td>Information</td>
                        <td style="width:16%">Company Code</td>
                        <td style="width:20%">Amount</td>
                    </tr>

                    @foreach($detailCA['detail_transport'] as $transport)
                    @if (!empty($transport['company_code']))
                    <tr style="text-align: center">
                        <td>{{ \Carbon\Carbon::parse($transport['tanggal'])->format('d-M-y') }}</td>
                        <td style="text-align: left">{{ $transport['keterangan'] }}</td>
                        <td>{{ $transport['company_code'] }}</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format($transport['nominal'], 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3" class="head-row">Total</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_transport'], 'nominal')), 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                </table>
            @endif

            @if (count($detailCA['detail_penginapan']) > 0 && !empty($detailCA['detail_penginapan'][0]['company_code']))
                <table class="table-approve">
                    <tr>
                        <th colspan="6"><b>Accomodation Plan :</b></th>
                    </tr>
                    <tr class="head-row">
                        <td style="width:12%">Start Date</td>
                        <td style="width:12%">End Date</td>
                        <td>Hotel Name</td>
                        <td>Company Code</td>
                        <td>Total Nights</td>
                        <td style="width:20%">Amount</td>
                    </tr>

                    @foreach($detailCA['detail_penginapan'] as $perdiem)
                        <tr style="text-align: center">
                            <td>{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                            <td>{{ $perdiem['hotel_name'] }}</td>
                            <td>{{ $perdiem['company_code'] }}</td>
                            <td>{{ $perdiem['total_days'] }} Hari</td>
                            <td>  
                                <span style="float: left; margin-left:4px">Rp.</span>  
                                <span style="float: right;">{{ number_format($perdiem['nominal'], 0, ',', '.') }}</span>  
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" class="head-row">Total</td>
                        <td>
                            {{ array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) }} Night
                        </td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                </table>
            @endif

            @if (count($detailCA['detail_lainnya']) > 0 && !empty($detailCA['detail_lainnya'][0]['keterangan']))
                <table class="table-approve">
                    <tr>
                        <th colspan="3"><b>Others Plan :</b></th>
                    </tr>
                    <tr class="head-row">
                        <td style="width:12%">Date</td>
                        <td>Information</td>
                        <td style="width:20%">Amount</td>
                    </tr>

                    @foreach($detailCA['detail_lainnya'] as $perdiem)
                    <tr style="text-align: center">
                        <td>{{ \Carbon\Carbon::parse($perdiem['tanggal'])->format('d-M-y') }}</td>
                        <td style="text-align: left">{{ $perdiem['keterangan'] }}</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format($perdiem['nominal'], 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2" class="head-row">Total</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                </table>
            @endif

        @elseif ( $transactions->type_ca == 'ndns' )
            <table class="table-approve">
                <tr>
                    <th colspan="3"><b>Detail Non Bussiness Trip :</b></th>
                </tr>
                <tr class="head-row">
                    <td style="width:12%">Date</td>
                    <td>Information</td>
                    <td style="width:20%">Amount</td>
                </tr>

                @foreach($detailCA as $item)
                <tr style="text-align: center">
                    <td>{{ \Carbon\Carbon::parse($item['tanggal_nbt'])->format('d-M-y') }}</td>
                    <td style="text-align: left">{{ $item['keterangan_nbt'] }}</td>
                    <td>  
                        <span style="float: left; margin-left:4px">Rp.</span>  
                        <span style="float: right;">{{ number_format($item['nominal_nbt'], 0, ',', '.') }}</span>  
                    </td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" class="head-row">Total</td>
                    <td>  
                        <span style="float: left; margin-left:4px">Rp.</span>  
                        <span style="float: right;">{{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}</span>  
                    </td>
                </tr>
            </table>
        @elseif ( $transactions->type_ca == 'entr' )
            @if (count($detailCA['detail_e']) > 0 && !empty($detailCA['detail_e'][0]['type']))
                <table class="table-approve">
                    <tr>
                        <th colspan="3"><b>Detail Entertain :</b></th>
                    </tr>
                    <tr class="head-row">
                        <td style="width:23%">Type</td>
                        <td>Information</td>
                        <td style="width:20%">Amount</td>
                    </tr>

                    @foreach($detailCA['detail_e'] as $detail)
                    <tr style="text-align: center">
                        @php
                            $typeMap = [
                                'food' => 'Food/Beverages/Souvenir',
                                'transport' => 'Transport',
                                'accommodation' => 'Accommodation',
                                'gift' => 'Gift',
                                'fund' => 'Fund',
                            ];
                        @endphp
                        <td>{{ $typeMap[$detail['type']] ?? $detail['type'] }}</td>
                        <td style="text-align: left">{{ $detail['fee_detail'] }}</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format($detail['nominal'], 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2" class="head-row">Total</td>
                        <td>  
                            <span style="float: left; margin-left:4px">Rp.</span>  
                            <span style="float: right;">{{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}</span>  
                        </td>
                    </tr>
                </table>
            @endif

            @if (count($detailCA['relation_e']) > 0 && !empty($detailCA['relation_e'][0]['name']))
                <table class="table-approve">
                    <tr>
                        <th colspan="5"><b>Relation Entertain:</b></th>
                    </tr>
                    <tr class="head-row">
                        <td>Name</td>
                        <td>Position</td>
                        <td>Company</td>
                        <td>Purpose</td>
                        <td>Relation Type</td>
                    </tr>

                    @foreach($detailCA['relation_e'] as $relation)
                    <tr style="text-align: center">
                        <td>{{ $relation['name'] }}</td>
                        <td>{{ $relation['position'] }}</td>
                        <td>{{ $relation['company'] }}</td>
                        <td>{{ $relation['purpose'] }}</td>
                        <td>
                            @php
                                $relationTypes = [];
                                $typeMap = [
                                    'Food' => 'Food/Beverages/Souvenir',
                                    'Gift' => 'Gift',
                                    'Transport' => 'Transport',
                                    'Accommodation' => 'Accommodation',
                                    'Fund' => 'Fund',
                                ];

                                // Mengumpulkan semua tipe relasi yang berstatus true
                                foreach($relation['relation_type'] as $type => $status) {
                                    if ($status && isset($typeMap[$type])) {
                                        $relationTypes[] = $typeMap[$type]; // Menggunakan pemetaan untuk mendapatkan deskripsi
                                    }
                                }
                            @endphp

                            {{ implode(', ', $relationTypes) }} {{-- Menggabungkan tipe relasi yang relevan menjadi string --}}
                        </td>
                    </tr>
                    @endforeach
                </table>
            @endif
        @endif

        {{-- <table>
            <tr>
                <td class="label"><b>Total Plan Cash Advanced</b></td>
                <td class="colon">:</td>
                <td class="value">Rp. {{ number_format($transactions->total_ca, 0, ',', '.' )}}</td>
            </tr>
            <tr>
                <td class="label"><b>Balance</b></td>
                <td class="colon">:</td>
                <td class="value">Rp. {{ number_format($transactions->total_cost, 0, ',', '.' )}}</td>
            </tr>
        </table> --}}
    </div>

    <footer>
        <script type="text/php">
            if (isset($pdf)) {
                $x = 400;
                $y = 810;
                $text = "Page {PAGE_NUM} of {PAGE_COUNT} Cash Advanced No. {{ $transactions->no_ca }}";
                $font = null;
                $size = 8;
                $color = array(0, 0, 0);
                $word_space = 0.0;
                $char_space = 0.0;
                $angle = 0.0;

                // Tambahkan pengecekan PAGE_COUNT
                if ($pdf->get_page_count() > 1) {
                    $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
                }
            }
        </script>
    </footer>
</body>
</html>
