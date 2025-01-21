<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SPPD</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('/public/images/favicon.ico') }}">
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
            padding: 20px;
        }

        h5 {
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        h4 {
            padding-left: 2px;
            padding-top: 4px;
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
            padding: 2px;
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

        .bottom-table {
            margin-top: 10px;
        }

        .bottom-table th,
        .bottom-table td {
            padding: 10px;
            text-align: left !important;
            width: 70px;
        }

        .bottom-table th {
            background-color: #dddddd;
        }

        .bottom-table tr:nth-child(even) {
            background-color: #d3d3d3;
        }

        .bottom-table tr:nth-child(odd) {
            background-color: #ededed;
        }

        table .ttd {
            height: 64px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/kop.jpg') }}" alt="Kop Surat">
    </div>
    <h5 class="center">SURAT PERINTAH PERJALANAN DINAS</h5>
    <h5 class="center">No. {{ $sppd->no_sppd }}</h5>

    <table>
        <tr>
            <td colspan="3"><b>Assigned to:</b></td>
        </tr>
        <tr>
            <td class="label">Name</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->employee->fullname }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->employee->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->employee->email }}</td>
        </tr>
        <tr>
            <td class="label">Division</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->divisi }}</td>
        </tr>
        {{-- <tr>
            <td class="label">PT</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->employee->company_name }}</td>
        </tr> --}}
        <tr>
            <td class="label">Cost Center</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->cost_center ?? '0' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>To Make a Business Trip to:</b></td>
        </tr>
        <tr>
            <td class="label">Destination</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->tujuan }}</td>
        </tr>
        <tr>
            <td class="label">Company</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->bb_perusahaan }}</td>
        </tr>
        <tr>
            <td class="label">Needs</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->keperluan }}</td>
        </tr>

        @php
            use Carbon\Carbon;
            Carbon::setLocale('id');
        @endphp
        <tr>
            <td class="label">From Date</td>
            <td class="colon">:</td>
            <td class="value">{{ Carbon::parse($sppd->mulai)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">To Date</td>
            <td class="colon">:</td>
            <td class="value">{{ Carbon::parse($sppd->kembali)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="colon">:</td>
            <td class="value"><b>{{ $sppd->status }}</b></td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>Approved By :</b></td>
        </tr>
        <tr>
            <td class="label">Manager Name 1</td>
            <td class="colon">:</td>
            <td class="value"> {{ $sppd->manager1->fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value"> {{ $sppd->latestApprovalL1->approved_at ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Manager Name 2</td>
            <td class="colon">:</td>
            <td class="value">{{ $sppd->manager2->fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value"> {{ $sppd->latestApprovalL2->approved_at ?? '-' }}</td>
        </tr>
    </table>
    <p><b><i>Note: This agreement has been agreed electronically and does not require a signature.</b></i></p>

    <h4>To be filled in by an official at the destination</h4>
    <table class="bottom-table">
        <tr>
            <td>Arrival Date</td>
            <td>1.</td>
            <td>2.</td>
            <td>3.</td>
            <td>4.</td>
        </tr>
        <tr>
            <td>Return Date</td>
            <td>1.</td>
            <td>2.</td>
            <td>3.</td>
            <td>4.</td>
        </tr>
        <tr>
            <td class="ttd">Signature & Stamp</td>
            <td>1.</td>
            <td>2.</td>
            <td>3.</td>
            <td>4.</td>
        </tr>
    </table>
</body>

</html>
