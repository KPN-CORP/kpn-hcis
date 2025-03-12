<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Refund Notification</title>
</head>

<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $base64Image }}" alt="Kop Surat" style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>
    <h2>Refund Notification</h2>
    <p>Dear Sir/Madam: <b>{{ $employeeName }}</b></p><br>
    <br>
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
            <td><b>Status Changed At</b></td>
            <td>:</td>
            <td>{{ $businessTrip->updated_at }}</td>
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

    {{-- @if ($businessTrip->ca === 'Ya') --}}
    @if ($isCa)
        <table style="border-collapse: collapse; width: 60%; margin-top: 8px; font-size: 12px;">
            <tr>
            <tr>
                <th colspan="2"
                    style="border: 1px solid #ddd; padding: 6px; background-color: #ab2f2b; color: #ffffff; font-size: 12px; font-weight: bold; text-align: center;">
                    Refund Details Cash Advanced
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; padding: 6px;">Description</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Total CA Request</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp.
                    {{ number_format(array_sum($caDetails), 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Total CA Declaration</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp.
                    {{ number_format(array_sum($caDeclare), 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Difference (Selisih)</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($selisihCa, 0, ',', '.') }}</td>
            </tr>
            </tr>
        </table>

        @if (!$isEnt)
            <p>Kindly transfer the difference of <b>Rp. {{ number_format($selisihCa, 0, ',', '.') }}</b> to the
                following
                account number: <b>{{ $accNum }}</b></p>
        @endif
    @endif

    @if ($isEnt)
        <table style="border-collapse: collapse; width: 60%; margin-top: 8px; font-size: 12px;">
            <tr>
            <tr>
                <th colspan="2"
                    style="border: 1px solid #ddd; padding: 6px; background-color: #ab2f2b; color: #ffffff; font-size: 12px; font-weight: bold; text-align: center;">
                    Refund Details Entertain
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; padding: 6px;">Description</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Amount</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Total Entertain Request</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp.
                    {{ number_format(array_sum($entDetails), 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Total Entertain Declaration</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp.
                    {{ number_format(array_sum($entDeclare), 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <td style="border: 1px solid #ddd; font-weight:bold; padding: 6px;">Difference (Selisih)</td>
                <td style="border: 1px solid #ddd; padding: 6px;">Rp. {{ number_format($selisihEnt, 0, ',', '.') }}
                </td>
            </tr>
            </tr>
        </table>

        @if (!$isCa)
            <p>Kindly transfer the difference of <b>Rp. {{ number_format($selisihEnt, 0, ',', '.') }}</b> to the
                following
                account number: <b>{{ $accNum }}</b></p>
        @endif
    @endif

    @if ($isEnt && $isCa)
        @php
            $selisihTotal = $selisihCa + $selisihEnt;
        @endphp
        <p>Kindly transfer the difference of <b>Rp. {{ number_format($selisihTotal, 0, ',', '.') }}</b> to the
            following
            account number: <b>{{ $accNum }}</b></p>
    @endif
    {{-- @endif --}}
    <hr>

    <p>If you have any questions, please contact the respective business unit GA. </p>
    <br>
    <p><strong>----------------</strong></p>
    <p>Human Capital - KPN Corp</p>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
