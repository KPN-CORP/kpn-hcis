<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Request Notification</title>
</head>

<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $base64Image }}" 
             alt="Kop Surat" 
             style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>  
    <h2>Ticket Request Notification</h2>
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
                <b>Detail Ticket :</b>
            </th>
        </tr>
        <tr style="font-weight: bold; background-color: #f5f5f5;">
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No</td>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Ticket Number</th>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Passenger Name</th>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Departure</th>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Ticket Type</th>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Departure Date & Time</th>
            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Return Date & Time</th>
        </tr>
        @foreach ($noTktList as $index => $noTkt)
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $noTkt }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $namaPenumpang[$index] }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $dariTkt[$index] }} - {{ $keTkt[$index] }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $tipeTkt[$index] }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    {{ \Carbon\Carbon::parse($tglBrktTkt[$index])->format('d M Y') }} at
                    {{ \Carbon\Carbon::parse($jamBrktTkt[$index])->format('H:i') }} WIB
                </td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    @if ($tipeTkt[$index] == 'Round Trip')
                        {{ \Carbon\Carbon::parse($tglPlgTkt[$index])->format('d M Y') }} at
                        {{ \Carbon\Carbon::parse($jamPlgTkt[$index])->format('H:i') }} WIB
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <hr>
    <p>For approval or rejection of the Ticket, you can choose the following links:</p>
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
