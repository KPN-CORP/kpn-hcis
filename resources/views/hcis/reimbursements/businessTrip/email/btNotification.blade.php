<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Business Trip Notification</title>
</head>

<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $base64Image }}" 
             alt="Kop Surat" 
             style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>  
    <h2>Business Trip Request Notification</h2>
    <p>Dear Sir/Madam: <b>{{ $managerName }}</b></p><br>
    <p><b>{{ $employeeName }}</b> {{ $textNotification }}</p>
    <br>
    <table>
        <tr>
            <td><b>No SPPD</b></td>
            <td>:</td>
            <td>{{ $businessTrip->no_sppd }}</td>
        </tr>
        <tr>
            <td><b>Employee Name</b></td>
            <td>:</td>
            <td>{{ $businessTrip->nama }}</td>
        </tr>
        <tr>
            <td><b>Start Date</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($businessTrip->mulai)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><b>End Date</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($businessTrip->kembali)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><b>Type of Service</b></td>
            <td>:</td>
            <td>{{ ucwords(strtolower($businessTrip->jns_dinas)) }}</td>
        </tr>
        <tr>
            <td><b>Location</b></td>
            <td>:</td>
            <td>{{ $businessTrip->tujuan }}</td>
        </tr>
        <tr>
            <td><b>Trip Purpose</b></td>
            <td>:</td>
            <td>{{ $businessTrip->keperluan }}</td>
        </tr>
        <tr>
            <td><b>PT</b></td>
            <td>:</td>
            <td>{{ $businessTrip->bb_perusahaan }}</td>
        </tr>
        <tr>
            <td><b>Cash Advance</b></td>
            <td>:</td>
            <td>{{ $businessTrip->ca === 'Ya' ? 'Yes' : ($businessTrip->ca === 'Tidak' ? 'No' : $businessTrip->ca) }}</td>
        </tr>
        <tr>
            <td><b>Ticket</b></td>
            <td>:</td>
            <td>{{ $businessTrip->tiket === 'Ya' ? 'Yes' : ($businessTrip->tiket === 'Tidak' ? 'No' : $businessTrip->tiket) }}</td>
        </tr>
        <tr>
            <td><b>Hotel</b></td>
            <td>:</td>
            <td>{{ $businessTrip->hotel === 'Ya' ? 'Yes' : ($businessTrip->hotel === 'Tidak' ? 'No' : $businessTrip->hotel) }}</td>
        </tr>
        <tr>
            <td><b>Voucher Taxi</b></td>
            <td>:</td>
            <td>{{ $businessTrip->taksi === 'Ya' ? 'Yes' : ($businessTrip->taksi === 'Tidak' ? 'No' : $businessTrip->taksi) }}</td>
        </tr>
    </table>
    @if ($businessTrip->ca === 'Ya')
        <table style="border-collapse: collapse; width: 40%; margin-top: 8px; font-size: 10px;">
            <tr>
                <th colspan="3" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                    <b>Cash Advanced Details :</b>
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Category</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</th>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Allowance</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $caDetails['total_days_perdiem'] ?? 0 }} Days</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    Rp. {{ number_format($caDetails['total_amount_perdiem'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Transport</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    Rp. {{ number_format($caDetails['total_amount_transport'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Accommodation</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $caDetails['total_days_accommodation'] ?? 0 }} Days</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    Rp. {{ number_format($caDetails['total_amount_accommodation'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Others</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                    Rp. {{ number_format($caDetails['total_amount_others'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    @endif
    @if ($businessTrip->tiket === 'Ya')
        <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
            <tr>
                <th colspan="8" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                    <b>Ticket Details :</b>
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Passenger Name</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Transport Type</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">From</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">To</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Ticket Type</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Departure</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Return</th>

            </tr>
            @foreach ($ticketDetails as $index => $ticket)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $ticket->np_tkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $ticket->jenis_tkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $ticket->dari_tkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $ticket->ke_tkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $ticket->type_tkt }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                        {{ \Carbon\Carbon::parse($ticket->tgl_brkt_tkt)->format('d M Y') }} at
                        {{ \Carbon\Carbon::parse($ticket->jam_brkt_tkt)->format('H:i') }} WIB
                    </td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                        @if ($ticket->type_tkt == 'Round Trip')
                            {{ $ticket->tgl_plg_tkt }} at {{ $ticket->jam_plg_tkt }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
    @if ($businessTrip->hotel === 'Ya')
        <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
            <tr>
                <th colspan="7" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                    <b>Hotel Details :</b>
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Hotel Name</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">No. Hotel</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Location</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Check-in</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Check-out</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Nights</th>
            </tr>
            @foreach ($hotelDetails as $index => $hotel)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $hotel->nama_htl }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $hotel->no_htl }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $hotel->lokasi_htl }}</td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                        {{ \Carbon\Carbon::parse($hotel->tgl_masuk_htl)->format('d M Y') }}
                    </td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                        {{ \Carbon\Carbon::parse($hotel->tgl_keluar_htl)->format('d M Y') }}
                    </td>
                    <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $hotel->total_hari }} Nights</td>
                </tr>
            @endforeach
        </table>    
    @endif
    @if ($businessTrip->taksi === 'Ya' && $taksiDetails)
        <table style="border-collapse: collapse; width: 30%; margin-top: 8px; font-size: 10px;">
            <tr>
                <th colspan="2" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                    <b>Voucher Details :</b>
                </th>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Detail</th>
                <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Value</th>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"><b>Total Voucher</b></td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $taksiDetails->no_vt }} Voucher</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"><b>Nominal Voucher</b></td>
                <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Rp. {{ number_format($taksiDetails->nominal_vt, 0, ',', '.') }}</td>
            </tr>
        </table>  
    @endif

    <hr>
    <p>For approval or rejection of the Business Trip, you can choose the following links:</p>
    <p>
        <a href="{{ $approvalLink }}" style="font-size: 20px;">Approve</a>    /     
        <a href="{{ $rejectionLink }}" style="font-size: 20px;">Reject</a>
    </p>

    <p>If you have any questions, please contact the respective business unit GA. </p>
    <br>
    <p><strong>----------------</strong></p>
    <p>Human Capital - KPN Corp</p>

    <p>Thank you,</p>
    <p>HC System</p>

</body>

</html>
