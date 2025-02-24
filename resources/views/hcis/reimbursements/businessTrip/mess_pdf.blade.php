<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mess Form</title>
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
    <h5 class="center">MESS BOOKING FORM</h5>
    <h5 class="center">No. {{ $mess->no_mess }}</h5>

    <table>
        <tr>
            <td colspan="3"><b>Identity:</b></td>
        </tr>
        <tr>
            <td class="label">Name</td>
            <td class="colon">:</td>
            <td class="value">{{ $mess->employee->fullname }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td class="colon">:</td>
            <td class="value">{{ $mess->employee->designation }}</td>
        </tr>
        <tr>
            <td class="label">Division</td>
            <td class="colon">:</td>
            <td class="value">{{ $mess->unit }}</td>
        </tr>
    </table>

    @foreach ($messes as $index => $mess)
        <table>
            <tr>
                <td colspan="3"><b>Detail Mess {{ $index + 1 }}:</b></td>
            </tr>
            <tr>
                <td class="label">Mess Location</td>
                <td class="colon">:</td>
                <td class="value">{{ $mess->lokasi_mess }}</td>
            </tr>
            <tr>
                <td class="label">Total Room</td>
                <td class="colon">:</td>
                <td class="value">{{ $mess->jmlkmr_mess }} Kamar</td>
            </tr>
            <tr>
                <td class="label">Check In Date</td>
                <td class="colon">:</td>
                <td class="value">
                    @php
                        $formattedCheckInDate = \Carbon\Carbon::parse($mess->tgl_masuk_mess)->format('d M Y');
                    @endphp
                    {{ $formattedCheckInDate }}
                </td>
            </tr>
            <tr>
                <td class="label">Check Out Date</td>
                <td class="colon">:</td>
                <td class="value">
                    @php
                        $formattedCheckOutDate = \Carbon\Carbon::parse($mess->tgl_keluar_mess)->format('d M Y');
                    @endphp
                    {{ $formattedCheckOutDate }}
                </td>
            </tr>
            <tr>
                <td class="label">Mess Status</td>
                <td class="colon">:</td>
                <td class="value"><b>{{ $mess->approval_status ?? '-' }}</b></td>
            </tr>
        </table>
    @endforeach

    <table>
        <tr>
            <td colspan="3"><b>Others :</b></td>
        </tr>
        @if ($mess->no_sppd !== null && $mess->no_sppd !== '-')
            <tr>
                <td class="label">No SPPD</td>
                <td class="colon">:</td>
                <td class="value">{{ $mess->no_sppd }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">PT</td>
            <td class="colon">:</td>
            <td class="value">
                @if ($mess->no_sppd !== null && $mess->no_sppd !== '-')
                    {{ $mess->businessTrip->checkCompany->contribution_level }}
                @elseif($mess->contribution_level_code !== null && $mess->contribution_level_code !== '-')
                    {{ $mess->contribution_level_code }}
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Cost Center</td>
            <td class="colon">:</td>
            <td class="value">{{ $mess->cost_center ?? '0' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>Approved By :</b></td>
        </tr>
        <tr>
            <td class="label">Manager Name 1</td>
            <td class="colon">:</td>
            <td class="value"> {{ $mess->manager1_fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value"> {{ $mess->latestApprovalL1->approved_at ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Manager Name 2</td>
            <td class="colon">:</td>
            <td class="value">{{ $mess->manager2_fullname ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="colon">:</td>
            <td class="value"> {{ $mess->latestApprovalL2->approved_at ?? '-' }}</td>
        </tr>
    </table>
</body>

</html>
