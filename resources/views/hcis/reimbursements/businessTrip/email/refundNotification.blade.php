<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Refund Notification</title>
</head>

<body>
    <h1>Refund Notification</h1>
    <p>Dear Sir/Madam: <b>{{ $employeeName }}</b></p><br>
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
        <p><strong><u>Refund Details:</u></strong></p>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Description</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Total CA Request Row -->
                @if (isset($caDetails) && array_sum($caDetails) > 0)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">Total CA Request</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                            {{ number_format(array_sum($caDetails), 0, ',', '.') }}
                        </td>
                    </tr>
                @endif

                <!-- Total CA Declaration Row -->
                @if (isset($newDeclareCa) && array_sum($newDeclareCa) > 0)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">Total CA Declaration</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                            {{ number_format(array_sum($newDeclareCa), 0, ',', '.') }}
                        </td>
                    </tr>
                @endif

                <!-- Selisih Row (Difference) -->
                @if (isset($selisih) && $selisih !== 0)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">Difference (Selisih)</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                            {{ number_format($selisih, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <p>Kindly transfer the difference of <b>Rp. {{ number_format($selisih, 0, ',', '.') }}</b> to the following
            account number: <b>{{ $accNum }}</b></p>
    @endif

    <hr>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
