<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Reminder – Business Trip Declaration</title>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">
        Reminder – Business Trip Declaration
    </h5>
    <p style="margin: 4px 0; padding: 2px;">
        Dear <strong>{{ $employee->fullname ?? "-" }}</strong>
    </p>
    <p style="margin: 4px 0; padding: 2px;">
        This is a reminder that your <strong>Business Trip Declaration</strong> is still pending.
    </p>
    <div style="overflow-x: auto; max-width: 100%;">
        <ul>
            <li>
                <strong>Business Trip No:</strong> {{ $transaction->no_sppd ?? "-" }}
            </li>
            <li>
                <strong>Business Trip Period:</strong> {{ $transaction->mulai ?? "" }} - {{ $transaction->kembali ?? "" }}
            </li>
            <li>
                <strong>Destination:</strong> {{ $transaction->tujuan ?? "-" }}
            </li>
        </ul>
        <p>
            Please complete your Business Trip Declaration through the HC System as soon as possible.
        </p>
        <p>
            {{ $actionUrl ?? 'https://apps.hcis.live/businessTrip' }}
        </p>
        <p>
            If you have already completed the declaration, please disregard this email.
        </p>
        <p>
            Thank you for your attention and cooperation.
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
