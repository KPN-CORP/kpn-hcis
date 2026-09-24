<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Plafon Medical Anda Mengalami Over Plafond</title>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">
        Plafon Medical Anda Mengalami Over Plafond
    </h5>
    <p style="margin: 4px 0; padding: 2px;">
        Dear : Bapak/Ibu <strong>{{ $employee->fullname ?? "-" }}</strong>
    </p>
    <p style="margin: 4px 0; padding: 2px;">
        Kami informasikan bahwa plafon benefit kesehatan Anda untuk <strong>{{ $healthPlan->medical_type ?? "-" }}</strong> saat ini telah mengalami over plafond.
    </p>
    <div style="overflow-x: auto; max-width: 100%;">
        <p>
            Email ini merupakan pengingat agar Bapak/Ibu dapat mengetahui kondisi plafon medical yang tersedia.
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
