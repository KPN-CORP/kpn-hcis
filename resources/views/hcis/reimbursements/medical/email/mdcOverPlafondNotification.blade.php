@php
    $totalPlafond = $healthPlafond->balance ?? 0;
    $sisaPlafond = $healthPlan->balance ?? 0;
    $totalOverPlafond = $totalUsage - $healthPlan->balance;
@endphp
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Informasi Penggunaan Benefit Medical Melebihi Plafon</title>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">
        Informasi Penggunaan Benefit Medical Melebihi Plafon
    </h5>
    <p style="margin: 4px 0; padding: 2px;">
        Dear : Bapak/Ibu <strong>{{ $employee->fullname ?? "-" }}</strong>
    </p>
    <p style="margin: 4px 0; padding: 2px;">
        Kami informasikan bahwa penggunaan benefit kesehatan Bapak/Ibu saat ini telah <strong>melebihi plafon benefit yang tersedia</strong>.
    </p>
    <div style="overflow-x: auto; max-width: 100%;">
        <p style="margin: 4px 0; padding: 2px;">
            <b>Detail plafon:</b>
        </p>
        <ul>
            <li>
                <strong>Jenis Benefit:</strong> {{ $healthPlan->medical_type ?? "-" }}
            </li>
            <li>
                <strong>Total Plafon:</strong> Rp {{ number_format($totalPlafond, 0, ',', '.') }}
            </li>
            <li>
                <strong>Total Penggunaan:</strong> Rp {{ number_format($totalUsage ?? 0, 0, ',', '.') }}
            </li>
            <li>
                <strong>Sisa Plafon:</strong> Rp {{ number_format($sisaPlafond, 0, ',', '.') }}
            </li>
            <li>
                <strong>Kelebihan Penggunaan:</strong> Rp {{ number_format($totalOverPlafond ?? 0, 0, ',', '.') }}
            </li>
        </ul>
        <p>
            Dengan demikian, terdapat kelebihan penggunaan benefit sebesar <strong>Rp {{ number_format($totalOverPlafond ?? 0, 0, ',', '.') }}</strong> yang berada di luar plafon benefit kesehatan yang tersedia.
        </p>
        <p>
            Untuk informasi lebih lanjut terkait perhitungan benefit dan ketentuan atas kelebihan penggunaan tersebut, silakan menghubungi HCO terkait.
        </p>
        <p>
            Terima kasih.
        </p>
        <br>
        <p>
            Best Regards,
            <br>
            HC System
        </p>
    </div>
  </body>
</html>
