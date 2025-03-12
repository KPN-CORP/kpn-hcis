<!DOCTYPE html>
<html>

<head>
    <title>Business Trip Data</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        .header-logo {
            width: 50%;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 14pt;
            margin: 10px 0 20px 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 0.5pt solid #ddd;
            padding: 4px 6px;
            font-size: 8pt;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <img src="{{ public_path('images/kop.jpg') }}" alt="KPNCORP" class="header-logo">
    <h2>Business Trip Data</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 2%">No.</th>
                <th style="width: 8%">Name</th>
                <th style="width: 10%">SPPD Number</th>
                <th style="width: 8%">Division</th>
                <th style="width: 6%">Start</th>
                <th style="width: 6%">Return</th>
                <th style="width: 8%">Destination</th>
                <th style="width: 6%">Type of Trip</th>
                <th style="width: 10%">Purpose</th>
                <th style="width: 4%">CA</th>
                <th style="width: 4%">Ticket</th>
                <th style="width: 4%">Hotel</th>
                <th style="width: 4%">Taxi</th>
                <th style="width: 6%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($businessTrips as $trip)
                <tr>

                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td>{{ $trip->nama }}</td>
                    <td>{{ $trip->no_sppd }}</td>
                    <td>{{ $trip->divisi }}</td>
                    <td>{{ $trip->mulai }}</td>
                    <td>{{ $trip->kembali }}</td>
                    <td>{{ $trip->tujuan }}</td>
                    <td>{{ $trip->jns_dinas }}</td>
                    <td>{{ $trip->keperluan }}</td>
                    <td>{{ $trip->ca }}</td>
                    <td>{{ $trip->tiket }}</td>
                    <td>{{ $trip->hotel }}</td>
                    <td>{{ $trip->taksi }}</td>
                    <td>{{ $trip->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
