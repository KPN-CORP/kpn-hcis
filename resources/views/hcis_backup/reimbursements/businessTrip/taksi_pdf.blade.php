<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Taxi Voucher Form</title>
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
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/kop.jpg') }}" alt="Kop Surat">
    </div>
    <h5 class="center">TAXI VOUCHER FORM</h5>
    <h5 class="center">No. {{ $taksi->no_sppd }}</h5>

    <table>
        <tr>
            <td colspan="3"><b>Identitas Pengaju:</b></td>
        </tr>
        <tr>
            <td class="label">Name</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->employee->fullname }}</td>
        </tr>
        <tr>
            <td class="label">Position</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->employee->designation }}</td>
        </tr>
        <tr>
            <td class="label">Unit</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->unit }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>Detail Voucher:</b></td>
        </tr>
        <tr>
            <td class="label">Jumlah Voucher</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->no_vt }}</td>
        </tr>
        <tr>
            <td class="label">Nominal Voucher</td>
            <td class="colon">:</td>
            <td class="value">Rp {{ number_format($taksi->nominal_vt, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Needs</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->businessTrip->keperluan }}</td>
        </tr>
        <tr>
            <td class="label">PT</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->businessTrip->checkCompany->contribution_level}}</td>
        </tr>
        <tr>
            <td class="label">Cost Center</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->cost_center ?? '0' }}</td>
        </tr>
        <tr>
            <td class="label">Voucher Status</td>
            <td class="colon">:</td>
            <td class="value"><b>{{ $taksi->approval_status ?? '-'}}</b></td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>Approved By :</b></td>
        </tr>
        <tr>
            <td class="label">Manager Name 1</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->manager1_fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->latestApprovalL1->approved_at ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="label">Manager Name 2</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->manager2_fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value">{{ $taksi->latestApprovalL2->approved_at ?? '-' }}</td>
        </tr>
    </table>
</body>

</html>
