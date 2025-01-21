<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Business Trip Notification</title>
</head>

<body>
    <h1>New Business Trip Request</h1>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>
    <p><strong>No. SPPD:</strong> {{ $businessTrip->no_sppd }}</p>
    <p><strong>Employee Name:</strong> {{ $businessTrip->nama }}</p>
    <p><strong>Start Date:</strong> {{ $businessTrip->mulai }}</p>
    <p><strong>End Date:</strong> {{ $businessTrip->kembali }}</p>
    <p><strong>Type of Service:</strong> {{ ucwords(strtolower($businessTrip->jns_dinas)) }}</p>
    <p><strong>Location:</strong> {{ $businessTrip->tujuan }}</p>
    <p><strong>Trip Purpose:</strong> {{ $businessTrip->keperluan }}</p>
    <p><strong>PT :</strong> {{ $businessTrip->bb_perusahaan }}</p>
    <hr>
    @if ($businessTrip->ca === 'Ya')
        <p><strong><u>CA Details:</u></strong></p>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Category</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Total Days</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Perdiem Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Perdiem</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_perdiem'] ?? 0 }} Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_perdiem'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Transport Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Transport</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_transport'] ?? 0 }}
                        Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_transport'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Accommodation Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Accommodation</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_accommodation'] ?? 0 }}
                        Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_accommodation'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Others Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Others</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_others'] ?? 0 }} Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_others'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif
    <p><strong><u>CA Declaration:</u></strong></p>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Category</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Total Days</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Perdiem Row -->
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">Perdiem</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDeclare['total_days_perdiem'] ?? 0 }} Days
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    {{ number_format($caDeclare['total_amount_perdiem'] ?? 0, 0, ',', '.') }}</td>
            </tr>

            <!-- Transport Row -->
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">Transport</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDeclare['total_days_transport'] ?? 0 }}
                    Days
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    {{ number_format($caDeclare['total_amount_transport'] ?? 0, 0, ',', '.') }}</td>
            </tr>

            <!-- Accommodation Row -->
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">Accommodation</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDeclare['total_days_accommodation'] ?? 0 }}
                    Days
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    {{ number_format($caDeclare['total_amount_accommodation'] ?? 0, 0, ',', '.') }}</td>
            </tr>

            <!-- Others Row -->
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">Others</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDeclare['total_days_others'] ?? 0 }} Days
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    {{ number_format($caDeclare['total_amount_others'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <hr>
    <p>For approval or rejection of the Business Trip, you can choose the following links:</p>
    <p><a href="{{ $approvalLink }}">Approve</a> / <a href="{{ $rejectionLink }}">Reject</a></p>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
