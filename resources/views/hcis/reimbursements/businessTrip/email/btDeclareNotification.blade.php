<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Business Trip Notification</title>
</head>

<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $base64Image }}" alt="Kop Surat" style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>
    <h2>New Business Trip Declaration</h2>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>
    <p><b>{{ $employeeName }}</b> {{ $textNotification }}</p>
    <table>
        <tr>
            <td><b>No SPPD</b></td>
            <td>:</td>
            <td>{{ $businessTrip->no_sppd }}</td>
        </tr>
        <tr>
            <td><b>Employee Name</b></td>
            <td>:</td>
            <td>{{ $businessTrip->nama }}</td>
        </tr>
        <tr>
            <td><b>Start Date</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($businessTrip->mulai)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><b>End Date</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($businessTrip->kembali)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><b>Type of Service</b></td>
            <td>:</td>
            <td>{{ ucwords(strtolower($businessTrip->jns_dinas)) }}</td>
        </tr>
        <tr>
            <td><b>Location</b></td>
            <td>:</td>
            <td>{{ $businessTrip->tujuan }}</td>
        </tr>
        <tr>
            <td><b>Trip Purpose</b></td>
            <td>:</td>
            <td>{{ $businessTrip->keperluan }}</td>
        </tr>
        <tr>
            <td><b>PT</b></td>
            <td>:</td>
            <td>{{ $businessTrip->bb_perusahaan }}</td>
        </tr>
        <tr>
            <td><b>Cash Advance</b></td>
            <td>:</td>
            <td>{{ $businessTrip->ca === 'Ya' ? 'Yes' : ($businessTrip->ca === 'Tidak' ? 'No' : $businessTrip->ca) }}
            </td>
        </tr>
        <tr>
            <td><b>Ticket</b></td>
            <td>:</td>
            <td>{{ $businessTrip->tiket === 'Ya' ? 'Yes' : ($businessTrip->tiket === 'Tidak' ? 'No' : $businessTrip->tiket) }}
            </td>
        </tr>
        <tr>
            <td><b>Hotel</b></td>
            <td>:</td>
            <td>{{ $businessTrip->hotel === 'Ya' ? 'Yes' : ($businessTrip->hotel === 'Tidak' ? 'No' : $businessTrip->hotel) }}
            </td>
        </tr>
        <tr>
            <td><b>Voucher Taxi</b></td>
            <td>:</td>
            <td>{{ $businessTrip->taksi === 'Ya' ? 'Yes' : ($businessTrip->taksi === 'Tidak' ? 'No' : $businessTrip->taksi) }}
            </td>
        </tr>
    </table>
    @if ($businessTrip->ca === 'Ya')
        @if ($isCa == true)
            <table style="border-collapse: collapse; width: 60%; margin-top: 8px; font-size: 12px;">
                <tr>
                    <th colspan="5"
                        style="border: 1px solid #ddd; padding: 6px; background-color: #ab2f2b; color: #ffffff; font-size: 12px; font-weight: bold; text-align: center;">
                        Cash Advanced Details
                    </th>
                </tr>
                <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                    <td rowspan="2" style="border: 1px solid #ddd; padding: 6px; vertical-align: middle;">Category</td>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 6px;">Estimate Plan</td>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 6px;">Declaration</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                    <td style="border: 1px solid #ddd; padding: 6px;">Total Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Total Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Allowance</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDetails['total_days_perdiem'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDetails['total_amount_perdiem'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDeclare['total_days_perdiem'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDeclare['total_amount_perdiem'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Meals</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDetails['total_days_meals'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDetails['total_amount_meals'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDeclare['total_days_meals'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDeclare['total_amount_meals'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Transport</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">-</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDetails['total_amount_transport'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">-</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDeclare['total_amount_transport'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Accommodation</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDetails['total_days_accommodation'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDetails['total_amount_accommodation'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $caDeclare['total_days_accommodation'] ?? 0 }} Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDeclare['total_amount_accommodation'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Others</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">-</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDetails['total_amount_others'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">-</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($caDeclare['total_amount_others'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        @endif
        @if ($isEnt == true)
            <table style="border-collapse: collapse; width: 50%; margin-top: 8px; font-size: 12px;">
                <tr>
                    <th colspan="5"
                        style="border: 1px solid #ddd; padding: 6px; background-color: #ab2f2b; color: #ffffff; font-size: 12px; font-weight: bold; text-align: center;">
                        Entertain Details
                    </th>
                </tr>
                <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                    <td rowspan="2" style="border: 1px solid #ddd; padding: 6px; vertical-align: middle;">Category</td>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 6px;">Estimate Plan</td>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 6px;">Declaration</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                    <td style="border: 1px solid #ddd; padding: 6px;">Total Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Total Days</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">Entertain</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;"> - </td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($entDetails['total_amount_ent'] ?? 0, 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;"> - </td>
                    <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($entDeclare['total_amount_ent'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>        
        @endif
    @endif

    <hr>
    <p>For approval or rejection of the Business Trip, you can choose the following links:</p>
    <p>
        <a href="{{ $approvalLink }}" style="font-size: 20px;">Approve</a> /
        <a href="{{ $rejectionLink }}" style="font-size: 20px;">Reject</a>
    </p>

    <p>If you have any questions, please contact the respective business unit GA. </p>
    <br>
    <p><strong>----------------</strong></p>
    <p>Human Capital - KPN Corp</p>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
