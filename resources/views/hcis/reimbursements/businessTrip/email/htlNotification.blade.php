<!DOCTYPE html>
<html>

<head>
    <title>Hotel Request Notification</title>
</head>

<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $base64Image }}" 
             alt="Kop Surat" 
             style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>  
    <h2>New Hotel Request Notification</h2>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>

    <p><b>{{ $employeeName }}</b> {{ $textNotification }}</p>
    <br>
    <table>
        <tr>
            <td><b>No SPPD</b></td>
            <td>:</td>
            <td>{{ $noSppd }}</td>
        </tr>
        <tr>
            <td><b>Approval Status</b></td>
            <td>:</td>
            <td>{{ $approvalStatus }}</td>
        </tr>
    </table>

    <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
        <tr>
            <th colspan="7" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                <b>Detail Hotel :</b>
            </th>
        </tr>
        <tr style="font-weight: bold; background-color: #f5f5f5;">
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No HTL</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Hotel Name</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Location</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Check-in Date</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Check-out Date</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Nights</td>
        </tr>
        @foreach ($noHtlList as $index => $noHtl)
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $noHtl }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $namaHtl[$index] }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $lokasiHtl[$index] }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    {{ \Carbon\Carbon::parse($tglMasukHtl[$index])->format('d M Y') }}
                </td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    {{ \Carbon\Carbon::parse($tglKeluarHtl[$index])->format('d M Y') }}
                </td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    {{ $totalHari[$index] }}
                </td>
            </tr>
        @endforeach
    </table>

    <hr>
    <p>For approval or rejection of the Hotels, you can choose the following links:</p>
    <p>
        <a href="{{ $approvalLink }}" style="font-size: 20px;">Approve</a>    /     
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
