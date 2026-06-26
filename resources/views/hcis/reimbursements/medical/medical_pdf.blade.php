<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Medical Claim</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            width: 100%;
            height: auto;
        }

        .header img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .content {
            padding: 0px;
        }

        h5 {
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        p {
            margin-top: 4px;
            padding: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td {
            padding: 1px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .label {
            width: 30%;
        }

        .colon {
            width: 20px;
            text-align: center;
        }

        .value {
            width: 70%;
        }

        .section-title {
            margin-top: 20px;
        }

        .table-approve {
            border-collapse: collapse;
            width: 100%;
        }

        .table-approve th,
        .table-approve td {
            border: 1px solid black;
            padding: 1px;
            text-align: center;
        }

        .table-approve .head-row {
            font-weight: bold;
        }

        .table-approve th {
            background-color: #d4d4d4;
        }

        .table-approve .total-row {
            text-align: left;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            text-align: right;
            line-height: 1.5cm;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/kop.jpg') }}" alt="Kop Surat">
    </div>

    <h5 class="center">Form Medical Claim</h5>
    <h5 class="center">(No. {{ $medical_no }})</h5>

    <table>
        <tr>
            <td colspan="3"><b>Employee Data:</b></td>
        </tr>
        <tr>
            <td class="label">Name</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_name }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_email }}</td>
        </tr>
        <tr>
            <td class="label">Account Details</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_account_detail }}</td>
        </tr>
        <tr>
            <td class="label">Dept</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_dept }}</td>
        </tr>
        <tr>
            <td class="label">PT/Location</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee_pt_or_location }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3"><b>MD Submission Details:</b></td>
        </tr>
        <tr>
            <td class="label">Costing Company</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_costing_company }}
            </td>
        </tr>
        <tr>
            <td class="label">Cost Center</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_cost_center }}
            </td>
        </tr>
        <tr>
            <td class="label">Submit Date</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_formatted_submit_date }}
            </td>
        </tr>
        <tr>
            <td class="label">Claim Date</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_formatted_claim_date }}
            </td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_periode }}
            </td>
        </tr>
        <tr>
            <td class="label">Patient Name</td>
            <td class="colon">:</td>
            <td class="value">
                {{ $medical_patient_name }}
            </td>
        </tr>
    </table>

    <table class="table-approve" style="width: 80%;">
        <tr class="head-row">
            <th style="text-align: center;">Medical Type</td>
            <th style="text-align: center;">Opening Balance Medical Plafond</td>
            <th style="text-align: center;">Total Current Medical Claim</td>
            <th style="text-align: center;">Closing Balance Medical Plafond</td>
        </tr>

        @foreach ($medical_details as $medical_detail)
            <tr>
                <td class="label">{{ $medical_detail["type"] }}</td>
                <td>
                    @if (empty($medical_detail["formatted_opening_balance_plafond"]))
                        <span> - </span>
                    @else
                        <span>Rp.</span>
                        <span>{{ $medical_detail["formatted_opening_balance_plafond"] }}</span>
                    @endif
                </td>
                <td>
                    @if (empty($medical_detail["formatted_total_current_claim"]))
                        <span> - </span>
                    @else
                        <span>Rp.</span>
                        <span>{{ $medical_detail["formatted_total_current_claim"] }}</span>
                    @endif
                </td>
                <td>
                    @if (empty($medical_detail["formatted_closing_balance_plafond"]))
                        <span> - </span>
                    @else
                        <span>Rp.</span>
                        <span>{{ $medical_detail["formatted_closing_balance_plafond"] }}</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <tr>
            <td class="label">Total</td>
            @if (empty($medical_formatted_opening_balance_plafond))
                <span> - </span>
            @else
                <span>Rp.</span>
                <span>{{ $medical_formatted_opening_balance_plafond }}</span>
            @endif
            @if (empty($medical_formatted_total_current_claim))
                <span> - </span>
            @else
                <span>Rp.</span>
                <span>{{ $medical_formatted_total_current_claim }}</span>
            @endif
            @if (empty($medical_formatted_closing_balance_plafond))
                <span> - </span>
            @else
                <span>Rp.</span>
                <span>{{ $medical_formatted_closing_balance_plafond }}</span>
            @endif
        </tr>
    </table>

    <div style="page-break-after:always;">
        <table border=0 style="width: 20%; font-size: 11px;">
            <tr>
                <td style="vertical-align: top;">
                    <table class="table-approve" style="width: 100%; text-align: center; display: inline-table;">
                        <tr>
                            <th>Submitted By</th>
                        </tr>
                        <tr>
                            <td>User</td>
                        </tr>
                        <tr>
                            <td><br><br><br><br><br></td>
                        </tr>
                        <tr>
                            <td>{{ $employee_name }}</td>
                        </tr>
                        <tr>
                            <td>{{ $medical_submit_date }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table border=0 style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 100%;">
                    <table class="table-approve" style="text-align:center;">
                        <tr>
                            @foreach ($medical_approvals["labels"] as $medical_approval_label)
                                <td style="width: 20%;">{{ $medical_approval_label }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($medical_approvals["role_names"] as $medical_approval_role_name)
                                <td>{{ $medical_approval_role_name }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($medical_approvals["statuses"] as $medical_approval_status)
                                <td>
                                    @if($medical_approval_status =='approved')
                                        <br><img src="{{ asset('images/approved_64.png')}}" alt="logo">
                                    @else
                                        <br><br><br><br><br>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($medical_approvals["employee_names"] as $medical_approval_employee_name)
                                <td>{{ $medical_approval_employee_name }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($medical_approvals["dates"] as $medical_approval_date)
                                <td>{{ $medical_approval_date }}</td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <footer>
        <script type="text/php">
            if (isset($pdf)) {
                $x = 400;
                $y = 810;
                $text = "Page {PAGE_NUM} of {PAGE_COUNT} Medical Claim No. {{ $medical_no }}";
                $font = null;
                $size = 8;
                $color = array(0, 0, 0);
                $word_space = 0.0;
                $char_space = 0.0;
                $angle = 0.0;

                if ($pdf->get_page_count() > 1) {
                    $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
                }
            }
        </script>
    </footer>
</body>

</html>
