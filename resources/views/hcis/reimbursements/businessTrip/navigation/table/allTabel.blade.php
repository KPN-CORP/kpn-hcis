<div class="table-responsive">
    <table class="table table-sm table-hover defaultTable" width="100%" cellspacing="0">
        <thead class="thead-light">
            <tr>
                <th>No</th>
                <th>Name</th>
                <th class="sticky-col-header">No SPPD</th>
                <th>Destination</th>
                <th>Start</th>
                <th>End</th>
                <th>CA</th>
                <th>Ticket</th>
                <th>Hotel</th>
                <th>Taxi</th>
                <th>Status</th>
                <th style="width: 80px">Action</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($bt_all as $idx => $n)
                <tr>
                    <td scope="row" style="text-align: center;">
                        {{ $loop->iteration }}
                    </td>
                    <td>{{ $n->nama }}</td>
                    <td class="sticky-col">{{ $n->no_sppd }}</td>
                    <td>{{ $n->tujuan }}</td>
                    <td>{{ \Carbon\Carbon::parse($n->mulai)->format('d-M-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($n->kembali)->format('d-M-Y') }}</td>
                    <td style="text-align: center; align-content: center">
                        @if ($n->ca == 'Ya' && isset($caTransactions[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal"
                                data-target="#detailModal" style="cursor: pointer"
                                data-ca="{{ json_encode([
                                    'No. CA' => $caTransactions[$n->no_sppd]->no_ca,
                                    'No. SPPD' => $caTransactions[$n->no_sppd]->no_sppd,
                                    'Unit' => $caTransactions[$n->no_sppd]->unit,
                                    'Destination' => $bt_all->where('no_sppd', $n->no_sppd)->first()->tujuan,
                                    'CA Total' => 'Rp ' . number_format($caTransactions[$n->no_sppd]->total_ca, 0, ',', '.'),
                                    'Total Real' => 'Rp ' . number_format($caTransactions[$n->no_sppd]->total_real, 0, ',', '.'),
                                    'Total Cost' => 'Rp ' . number_format($caTransactions[$n->no_sppd]->total_cost, 0, ',', '.'),
                                    'Start' => date('d-M-Y', strtotime($caTransactions[$n->no_sppd]->start_date)),
                                    'End' => date('d-M-Y', strtotime($caTransactions[$n->no_sppd]->end_date)),
                                ]) }}"><u>Details</u></a>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center; align-content: center">
                        @if ($n->tiket == 'Ya' && isset($tickets[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal"
                                data-target="#detailModal" style="cursor: pointer"
                                data-tiket="{{ json_encode(
                                    $tickets[$n->no_sppd]->map(function ($ticket) {
                                        return [
                                            // 'No. Ticket' => $ticket->no_tkt ?? 'No Data',
                                            'No. SPPD' => $ticket->no_sppd,
                                            'No. Ticket' => $ticket->no_tkt,
                                            'Passengers Name' => $ticket->np_tkt,
                                            'Unit' => $ticket->unit,
                                            'Gender' => $ticket->jk_tkt,
                                            'NIK' => $ticket->noktp_tkt,
                                            'Phone No.' => $ticket->tlp_tkt,
                                            'From' => $ticket->dari_tkt,
                                            'To' => $ticket->ke_tkt,
                                            'Departure Date' => date('d-m-Y', strtotime($ticket->tgl_brkt_tkt)),
                                            'Time' => !empty($ticket->jam_brkt_tkt) ? date('H:i', strtotime($ticket->jam_brkt_tkt)) : 'No Data',
                                            'Return Date' => isset($ticket->tgl_plg_tkt) ? date('d-m-Y', strtotime($ticket->tgl_plg_tkt)) : 'No Data',
                                            'Return Time' => !empty($ticket->jam_plg_tkt) ? date('H:i', strtotime($ticket->jam_plg_tkt)) : 'No Data',
                                        ];
                                    }),
                                ) }}">
                                <u>Details</u></a>
                        @else
                            -
                        @endif


                    </td>
                    <td style="text-align: center; align-content: center">
                        @if ($n->hotel == 'Ya' && isset($hotel[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal"
                                data-target="#detailModal" style="cursor: pointer"
                                data-hotel="{{ json_encode(
                                    $hotel[$n->no_sppd]->map(function ($hotel) {
                                        return [
                                            'No. Hotel' => $hotel->no_htl,
                                            'No. SPPD' => $hotel->no_sppd,
                                            'Unit' => $hotel->unit,
                                            'Hotel Name' => $hotel->nama_htl,
                                            'Location' => $hotel->lokasi_htl,
                                            'Room' => $hotel->jmlkmr_htl,
                                            'Bed' => $hotel->bed_htl,
                                            'Check In' => date('d-m-Y', strtotime($hotel->tgl_masuk_htl)),
                                            'Check Out' => date('d-m-Y', strtotime($hotel->tgl_keluar_htl)),
                                            'Total Days' => $hotel->total_hari,
                                        ];
                                    }),
                                ) }}">
                                <u>Details</u></a>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center; align-content: center">
                        @if ($n->taksi == 'Ya' && isset($taksi[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal"
                                data-target="#detailModal" style="cursor: pointer"
                                data-taksi="{{ json_encode([
                                    'Total Voucher' => $taksi[$n->no_sppd]->no_vt . ' Voucher',
                                    'No. SPPD' => $taksi[$n->no_sppd]->no_sppd,
                                    'Unit' => $taksi[$n->no_sppd]->unit,
                                    'Nominal' => 'Rp ' . number_format($taksi[$n->no_sppd]->nominal_vt, 0, ',', '.'),
                                ]) }}"><u>Details<u></a>
                        @else
                            -
                        @endif
                    </td>
                    <td style="align-content: center;">
                        <span
                            class="badge rounded-pill bg-{{ $n->status == 'Approved'
                                ? 'success'
                                : ($n->status == 'Rejected' || $n->status == 'Return' || $n->status == 'return/refunds'
                                    ? 'danger'
                                    : (in_array($n->status, ['Pending L1', 'Pending L2', 'Declaration L1', 'Declaration L2', 'Waiting Submitted'])
                                        ? 'warning'
                                        : ($n->status == 'Draft'
                                            ? 'secondary'
                                            : (in_array($n->status, ['Doc Accepted', 'verified'])
                                                ? 'primary'
                                                : 'secondary')))) }}"
                            style="
                        font-size: 12px;
                        padding: 0.5rem 1rem;">
                            {{ $n->status }}
                        </span>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <a class="btn btn-primary rounded-pill"
                            href="{{ $n->status === 'Declaration L1' || $n->status === 'Declaration L2' ? route('businessTrip.approvalDetail.dekalrasi', ['id' => $n->id]) : route('businessTrip.approvalDetail', ['id' => $n->id]) }}"
                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            Act
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if (session('message'))
            <script>
                alert('{{ session('message') }}');
            </script>
        @endif
    </table>
</div>

<script>
    $(document).ready(function() {
        $('.defaultTable').DataTable();
    });
</script>
