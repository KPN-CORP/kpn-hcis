<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Business Trip Notification</title>
</head>

<body>
    <h1>New Business Trip Request</h1>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>
    <p><strong>No. SPPD:</strong> {{ $businessTrip->no_sppd }}</p>
    <p><strong>Employee Name:</strong> {{ $businessTrip->nama }}</p>
    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($businessTrip->mulai)->format('d M Y') }}</p>
    <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($businessTrip->kembali)->format('d M Y') }}</p>
    <p><strong>Type of Service:</strong> {{ ucwords(strtolower($businessTrip->jns_dinas)) }}</p>
    <p><strong>Location:</strong> {{ $businessTrip->tujuan }}</p>
    <p><strong>Trip Purpose:</strong> {{ $businessTrip->keperluan }}</p>
    <p><strong>PT :</strong> {{ $businessTrip->bb_perusahaan }}</p>
    <hr>
    <p><strong>Cash Advance:</strong>
        {{ $businessTrip->ca === 'Ya' ? 'Yes' : ($businessTrip->ca === 'Tidak' ? 'No' : $businessTrip->ca) }}
    </p>
    @if ($businessTrip->ca === 'Ya')
        <p><strong><u>CA Details:</u></strong></p>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Category</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Total Days</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Perdiem Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Allowance</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_perdiem'] ?? 0 }} Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_perdiem'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Transport Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Transport</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">-
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_transport'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Accommodation Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Accommodation</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $caDetails['total_days_accommodation'] ?? 0 }}
                        Days
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_accommodation'] ?? 0, 0, ',', '.') }}</td>
                </tr>

                <!-- Others Row -->
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Others</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">-
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                        {{ number_format($caDetails['total_amount_others'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <p><strong>Ticket:</strong>
        {{ $businessTrip->tiket === 'Ya' ? 'Yes' : ($businessTrip->tiket === 'Tidak' ? 'No' : $businessTrip->tiket) }}
    </p>
    @if ($businessTrip->tiket === 'Ya')
        <p><strong><u>Ticket Details:</u></strong></p>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">#</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Passenger Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Transport Type</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">From</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">To</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Ticket Type</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Departure</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Return</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticketDetails as $index => $ticket)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $ticket->np_tkt }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $ticket->jenis_tkt }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $ticket->dari_tkt }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $ticket->ke_tkt }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $ticket->type_tkt }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            {{ \Carbon\Carbon::parse($ticket->tgl_brkt_tkt)->format('d M Y') }} at
                            {{ \Carbon\Carbon::parse($ticket->jam_brkt_tkt)->format('H:i') }} WIB
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            @if ($ticket->type_tkt == 'Round Trip')
                                {{ $ticket->tgl_plg_tkt }} at {{ $ticket->jam_plg_tkt }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    <p><strong>Hotel:</strong>
        {{ $businessTrip->hotel === 'Ya' ? 'Yes' : ($businessTrip->hotel === 'Tidak' ? 'No' : $businessTrip->hotel) }}
    </p>

    @if ($businessTrip->hotel === 'Ya')
        <p><strong><u>Hotel Details:</u></strong></p>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">#</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Hotel Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">No. Hotel</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Location</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Check-in</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Check-out</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Total Nights</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hotelDetails as $index => $hotel)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $hotel->nama_htl }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $hotel->no_htl }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $hotel->lokasi_htl }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            {{ \Carbon\Carbon::parse($hotel->tgl_masuk_htl)->format('d M Y') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            {{ \Carbon\Carbon::parse($hotel->tgl_keluar_htl)->format('d M Y') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $hotel->total_hari }} Nights</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    <p><strong>Voucher Taxi:</strong>
        {{ $businessTrip->taksi === 'Ya' ? 'Yes' : ($businessTrip->taksi === 'Tidak' ? 'No' : $businessTrip->taksi) }}
    </p>
    @if ($businessTrip->taksi === 'Ya' && $taksiDetails)
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Detail</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>Total Voucher</strong></td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $taksiDetails->no_vt }} Voucher</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>Nominal Voucher</strong></td>
                    <td style="border: 1px solid #ddd; padding: 8px;">Rp.
                        {{ number_format($taksiDetails->nominal_vt, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <hr>
    <p>For approval or rejection of the Business Trip, you can choose the following links:</p>
    <p><a href="{{ $approvalLink }}">Approve</a> / <a href="{{ $rejectionLink }}">Reject</a></p>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
