<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Incorrect Employee Cost Center</title>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">
    <h5 style="font-size: 13px; margin: 0; padding: 0; margin-bottom: 10px;">
        Incorrect Employee Cost Center
    </h5>
    <p style="margin: 4px 0; padding: 2px;">
        Dear <strong>HC Team</strong>,
    </p>
    <p style="margin: 4px 0; padding: 2px;">
        This is a reminder that the cost center for employee name <strong>{{ $employee->fullname ?? "-" }}</strong> is incorrect.
    </p>
    <div style="overflow-x: auto; max-width: 100%;">
        <ul>
            <li>
                <strong>Employee Name:</strong> {{ $employee->fullname ?? "-" }}
            </li>
            <li>
                <strong>Business Unit:</strong> {{ $employee->group_company ?? "-" }}
            </li>
            <li>
                <strong>Company:</strong> {{ $employee->company_name ?? "-" }} ({{ $employee->contribution_level_code ?? "-" }})
            </li>
            <li>
                <strong>Cost Center:</strong> {{ $employee->cost_center_code ?? "-" }}
            </li>
        </ul>
        <p>
            Please fix the cost center through the HC System as soon as possible.
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
