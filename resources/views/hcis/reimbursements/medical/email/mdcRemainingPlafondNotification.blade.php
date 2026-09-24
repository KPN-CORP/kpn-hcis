@php
    $totalPlafond = $healthPlafond->balance ?? 0;
    $sisaPlafond = $healthPlan->balance ?? 0;
    $persentaseSisa = $totalPlafond > 0 ? ($sisaPlafond / $totalPlafond) * 100 : 0;
@endphp
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Sisa Plafon Medical Anda di Bawah 20%</title>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">
        Sisa Plafon Medical Anda di Bawah 20%
    </h5>
    <p style="margin: 4px 0; padding: 2px;">
        Dear : Bapak/Ibu <strong>{{ $employee->fullname ?? "-" }}</strong>
    </p>
    <p style="margin: 4px 0; padding: 2px;">
        Kami informasikan bahwa sisa plafon benefit kesehatan Anda saat ini telah berada di bawah 20% dari total plafon yang tersedia.
    </p>
    <div style="overflow-x: auto; max-width: 100%;">
        <p style="margin: 4px 0; padding: 2px;">
            <b>Detail plafon:</b>
        </p>
        <ul>
            <li>
                Jenis Benefit: {{ $healthPlan->medical_type ?? "-" }}
            </li>
            <li>
                Total Plafon: Rp {{ number_format($totalPlafond, 0, ',', '.') }}
            </li>
            <li>
                Sisa Plafon: Rp {{ number_format($sisaPlafond, 0, ',', '.') }}
            </li>
            <li>
                Persentase Sisa Plafon: {{ number_format($persentaseSisa, 2) }}%
            </li>
        </ul>
        <p>
            Email ini merupakan pengingat agar Bapak/Ibu dapat mengetahui posisi sisa plafon medical yang tersedia.
        </p>
        <p>
            Untuk informasi lebih lanjut terkait benefit kesehatan Anda, silakan menghubungi HCO terkait.
        </p>
        <p>
            Terima kasih.
        </p>
        <br>
        <br>
        <p>
            Best Regards,
            <br>
            HC System
        </p>
    </div>
  </body>
</html>
