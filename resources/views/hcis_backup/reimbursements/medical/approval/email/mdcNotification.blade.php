<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Notification</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    {{-- <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $logoBase64 }}" 
             alt="Kop Surat" 
             style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>       --}}
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">Reimburse Medical Notification</h5>
    <p style="margin: 4px 0; padding: 2px;">Dear : Bapak/Ibu <strong>{{ $healthCoverages->first()->employee->fullname }}</strong></p>

    <p style="margin: 4px 0; padding: 2px;">Your Medical Plafond Submission has been Imported by Admin <strong>{{ $healthCoverages->first()->employee_approve->fullname }}</strong> as much as {{ $healthCoverages->count() }} data.</p>
    @if ($healthCoverages->contains(fn($item) => $item->balance_uncoverage != 0))
        <p style="margin: 4px 0; padding: 2px;">Some of your applications exceed the available Plafond, so there are some expenses that are not covered by your medical Plafond.</p>
    @endif
    <p style="margin: 4px 0; padding: 2px;">With the following details : </p>

    <div style="overflow-x: auto; max-width: 100%;">
        <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
            <tr>
                <th colspan="10" style="background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center; padding: 4px;">
                    <b>Detail Reimburse Medical:</b>
                </th>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">No</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">No Invoice</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">Hospital</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">Patient</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">Disease</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">Date</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: left; font-weight: bold; background-color: #f5f5f5;">Type</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right; font-weight: bold; background-color: #f5f5f5;">Amount</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right; font-weight: bold; background-color: #f5f5f5;">Uncovered</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right; font-weight: bold; background-color: #f5f5f5;">Verified</td>
            </tr>
            @foreach($healthCoverages as $index => $healthCoverage)
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $healthCoverage->no_invoice }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $healthCoverage->hospital_name }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $healthCoverage->patient_name }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $healthCoverage->disease }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ \Carbon\Carbon::parse($healthCoverage->date)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #ddd; padding: 4px;">{{ $healthCoverage->medical_type }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;">{{ number_format($healthCoverage->balance, 0, ',', '.') }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;">{{ number_format($healthCoverage->balance_uncoverage, 0, ',', '.') }}</td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;">{{ number_format($healthCoverage->balance_verif, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="7" style="border: 1px solid #ddd; padding: 4px; text-align: center;"><strong>Total:</strong></td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;"><strong>{{ number_format($healthCoverages->sum('balance'), 0, ',', '.') }}</strong></td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;"><strong>{{ number_format($healthCoverages->sum('balance_uncoverage'), 0, ',', '.') }}</strong></td>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: right;"><strong>{{ number_format($healthCoverages->sum('balance_verif'), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
        <br><br>
        <p>If you have any questions, please contact the respective business unit GA. </p>
        <br>
        <p><strong>----------------</strong></p>
        <p>Human Capital - KPN Corp</p>
    </div>
</body>
</html>
