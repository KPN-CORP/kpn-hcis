<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cash Advanced</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        h5, p {
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        td {
            padding: 5px;
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
        .approve tr, .approve td, .approve th {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
        }
        .flex-container {
            display: flex;
            justify-content: space-between;
        }
        .no-border td {
            border: none;
        }
        @media print {
            .flex-container {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
            }
            .signature-box {
                border: 1px solid black;
                text-align: center;
                padding: 10px;
                width: 45%;
            }
        }
    </style>
</head>
<body>
    <h2 class="center">Permintaan Uang Muka Karyawan - Dinas</h2>
    <h3 class="center">No. {{ $transactions->no_ca }}</h3>

    <table class="no-border">
        <tr>
            <td colspan="3"><b>Data Karyawan :</b></td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->fullname }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->email }}</td>
        </tr>
        <tr>
            <td class="label">Telp</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->phone }}</td>
        </tr>
        <tr>
            <td class="label">Detail Rekening</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->bank_details }}</td>
        </tr>
        <tr>
            <td class="label">Divisi/Dept</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->unit }}</td>
        </tr>
        <tr>
            <td class="label">PT/Lokasi</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->employee->company_name }}</td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td colspan="3"><b>Data Perjalanan Dinas :</b></td>
        </tr>
        <tr>
            <td class="label">PT</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->destination }}</td>
        </tr>
        <tr>
            <td class="label">Mulai</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->start_date }}</td>
        </tr>
        <tr>
            <td class="label">Berakhir</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->end_date }}</td>
        </tr>
        <tr>
            <td class="label">Total</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->total_days }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal CA dibutuhkan</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->date_required }}</td>
        </tr>
        <tr>
            <td class="label">Estimasi Deklarasi</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->declare_estimate }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td class="colon">:</td>
            <td class="value">{{ $transactions->ca_needs }}</td>
        </tr>
    </table>

    <table class="approve">
        <tr>
            <td colspan="5" style="background-color: #c6e0b4; text-align: center"><b>Rencana Perjalanan :</b></td>
        </tr>
        <tr>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Lokasi dinas</th>
            <th>Company Code</th>
            <th>Jl Hr</th>
        </tr>
        <tr>
            <td>18-Jul-24</td>
            <td>18-Jul-24</td>
            <td>Kebun BSU</td>
            <td>BSU</td>
            <td>1</td>
        </tr>
        <tr>
            <td>18-Jul-24</td>
            <td>18-Jul-24</td>
            <td>Kebun BSU</td>
            <td>BSU</td>
            <td>1</td>
        </tr>
        <tr>
            <td>18-Jul-24</td>
            <td>18-Jul-24</td>
            <td>Kebun BSU</td>
            <td>BSU</td>
            <td>1</td>
        </tr>
        <tr>
            <td>18-Jul-24</td>
            <td>18-Jul-24</td>
            <td>Kebun BSU</td>
            <td>BSU</td>
            <td>1</td>
        </tr>
        <tr>
            <td style="text-align: left" colspan="4">Total</td>
            <td>6</td>
        </tr>
    </table>

    <table class="approve" style="width: 70%; margin-bottom:20px;">
        <tr>
            <td colspan="3" style="background-color: #c6e0b4; text-align: center"><b>Detail Perdiem :</b></td>
        </tr>
        <tr>
            <th rowspan="2">Jenis Uang Muka</th>
            <th colspan="2">Estimasi</th>
        </tr>
        <tr>
            <td>Total Hari</td>
            <td>Biaya</td>
        </tr>
        <tr>
            <td>Uang Saku</td>
            <td>6</td>
            <td>390.000</td>
        </tr>
        <tr>
            <td>Transportasi</td>
            <td></td>
            <td>1.000.000</td>
        </tr>
        <tr>
            <td>Penginapan</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Lainnya</td>
            <td></td>
            <td>5.000.000</td>
        </tr>
        <tr>
            <td colspan="2">Total</td>
            <td>6.390.000</td>
        </tr>
    </table>

    <div class="flex-container">
        <table class="approve" style="width: 20%; margin-top:150px; text-align:center; ">
            <tr>
                <td>Diajukan</td>
            </tr>
            <tr>
                <td>User</td>
            </tr>
            <tr>
                <td><br><br><br><br></td>
            </tr>
            <tr>
                <td>Kevin</td>
            </tr>
            <tr>
                <td>Tgl..</td>
            </tr>
        </table>
        <table class="approve" style="margin-left:279px; margin-top:-160px; width: 60%; text-align:center;">
            <tr>
                <td colspan="3">Verifikasi</td>
            </tr>
            <tr>
                <td>Dept Head User</td>
                <td>Dept Head User</td>
                <td>Dept Head Ap</td>
            </tr>
            <tr>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
            </tr>
            <tr>
                <td>Leo</td>
                <td>Hifni</td>
                <td>Lie Na</td>
            </tr>
            <tr>
                <td>Tgl..</td>
                <td>Tgl..</td>
                <td>Tgl..</td>
            </tr>
        </table>
        <table class="approve" style="width: 100%; text-align:center;">
            <tr>
                <td colspan="5">Approval</td>
            </tr>
            <tr>
                <td style="width: 20%">Div Head User</td>
                <td>Director User</td>
                <td>Div Head HC</td>
                <td>CFO</td>
                <td>CEO</td>
            </tr>
            <tr>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
                <td><br><br><br><br></td>
            </tr>
            <tr>
                <td>Ivan</td>
                <td>...</td>
                <td>...</td>
                <td>...</td>
                <td>...</td>
            </tr>
            <tr>
                <td>Tgl..</td>
                <td>Tgl..</td>
                <td>Tgl..</td>
                <td>Tgl..</td>
                <td>Tgl..</td>
            </tr>
        </table>
    </div>
</body>
</html>
