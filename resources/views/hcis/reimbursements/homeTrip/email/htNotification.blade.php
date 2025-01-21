<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Trip Request Notification</title>
</head>

<body>
    <h2>Home Trip Request Notification</h2>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>

    <p><strong>Approval Status:</strong> {{ $approvalStatus }}</p>

    <h3>Home Trip Ticket Details</h3>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">#</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Ticket Number</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Passenger Name</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Departure</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Ticket Type</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Departure Date & Time</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Return Date & Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($noTktList as $index => $noTkt)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $noTkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $namaPenumpang[$index] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $dariTkt[$index] }} - {{ $keTkt[$index] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $tipeTkt[$index] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">
                        {{ \Carbon\Carbon::parse($tglBrktTkt[$index])->format('d M Y') }} at
                        {{ \Carbon\Carbon::parse($jamBrktTkt[$index])->format('H:i') }} WIB
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px;">
                        @if ($tipeTkt[$index] == 'Round Trip')
                            {{ \Carbon\Carbon::parse($tglPlgTkt[$index])->format('d M Y') }} at
                            {{ \Carbon\Carbon::parse($jamPlgTkt[$index])->format('H:i') }} WIB
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <hr>
    <p>For approval or rejection of the Home Trip Ticket, you can choose the following links:</p>
    <p><a href="{{ $approvalLink }}">Approve</a> / <a href="{{ $rejectionLink }}">Reject</a></p>

    <p>Thank you,</p>
    <p>HC System</p>
</body>

</html>
