<div class="table-responsive">
    <table class="table table-sm table-hover defaultTable" width="100%" cellspacing="0">
        <thead class="thead-light">
            <tr>
                <th>No</th>
                <th>Name</th>
                <th class="sticky-col-header">No SPPD</th>
                <th>Destination</th>
                <th>Purpose</th>
                <th>Start</th>
                <th>End</th>
                <th>CA</th>
                <th>Mess</th>
                <th>Hotel</th>
                <th>Ticket</th>
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
                    <td>{{ $n->keperluan }}</td>
                    <td>{{ \Carbon\Carbon::parse($n->mulai)->format('d-M-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($n->kembali)->format('d-M-Y') }}</td>
                    <td style="text-align: center; align-content: center">
                        @if ($n->ca == 'Ya' && isset($caTransactions[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                                style="cursor: pointer"
                                data-ca="{{ json_encode(
                                    $caTransactions->get($n->no_sppd, collect())->map(function ($transaction) {
                                            return [
                                                'No. CA' => $transaction->no_ca,
                                                'No. SPPD' => $transaction->no_sppd,
                                                'Type' => $transaction->type_ca === 'dns' ? 'Business Trip' : 'Entertain', // Conditional assignment
                                                'Unit' => $transaction->unit,
                                                'Destination' => $transaction->destination,
                                                'CA Total' => 'Rp ' . number_format($transaction->total_ca, 0, ',', '.'),
                                                'Total Real' => 'Rp ' . number_format($transaction->total_real, 0, ',', '.'),
                                                'Total Cost' => 'Rp ' . number_format($transaction->total_cost, 0, ',', '.'),
                                                'Start' => date('d-M-Y', strtotime($transaction->start_date)),
                                                'End' => date('d-M-Y', strtotime($transaction->end_date)),
                                            ];
                                        })->values(),
                                ) }}"><u>Details</u></a>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center">
                        @if ($n->mess == 'Ya' && isset($mess[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                                style="cursor: pointer"
                                data-mess="{{ json_encode(
                                    $mess[$n->no_sppd]->map(function ($mess) {
                                        return [
                                            'No. Mess' => $mess->no_mess,
                                            'No. SPPD' => $mess->no_sppd,
                                            'Unit' => $mess->unit,
                                            'Mess Location' => $mess->lokasi_mess,
                                            'Room' => $mess->jmlkmr_mess,
                                            'Check In' => date('d-M-Y', strtotime($mess->tgl_masuk_mess)),
                                            'Check Out' => date('d-M-Y', strtotime($mess->tgl_keluar_mess)),
                                            'Total Days' => $mess->total_hari_mess,
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
                            <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                                style="cursor: pointer"
                                data-hotel="{{ json_encode(
                                    $hotel[$n->no_sppd]->map(function ($hotel) {
                                        return [
                                            'No. Hotel' => $hotel->no_htl,
                                            'No. SPPD' => $hotel->no_sppd,
                                            'Colleague No. SPPD' => $hotel->no_sppd_htl,
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
                        @if ($n->tiket == 'Ya' && isset($tickets[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                                style="cursor: pointer"
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
                        @if ($n->taksi == 'Ya' && isset($taksi[$n->no_sppd]))
                            <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                                style="cursor: pointer"
                                data-taksi="{{ json_encode([
                                    'Total Voucher' => $taksi[$n->no_sppd]->no_vt . ' Voucher',
                                    'No. SPPD' => $taksi[$n->no_sppd]->no_sppd,
                                    'Unit' => $taksi[$n->no_sppd]->unit,
                                    'Details' => $taksi[$n->no_sppd]->vt_detail,
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
                                    : (in_array($n->status, ['Pending L1', 'Pending L2', 'Declaration L1', 'Declaration L2', 'Waiting Submitted', 'Extend L1', 'Extend L2'])
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
                        <h1>{{ $result[$n->id]['ext_end_date'] ?? '' }}</h1>
                        @if ($n->status == 'Extend L1' || $n->status == 'Extend L2')
                            <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalExtend" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                data-no-id="{{ $n->id }}"
                                data-no-ca="{{ $n->no_sppd }}"
                                data-start-date="{{ $n->mulai }}"
                                data-end-date="{{ $n->kembali }}"
                                data-end-date-ext="{{ $extendTime[$n->id]['ext_end_date'] ?? '' }}"  
                                data-reason-ext="{{ $extendTime[$n->id]['reason_extend'] }}"
                            >
                                Act
                            </button>
                        @else
                            <a class="btn btn-primary rounded-pill"
                                href="{{ $n->status === 'Declaration L1' || $n->status === 'Declaration L2' ? route('businessTrip.approvalDetail.dekalrasi', ['id' => $n->id]) : route('businessTrip.approvalDetail', ['id' => $n->id]) }}"
                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                Act
                            </a>
                        @endif
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

<div class="modal fade" id="modalExtend" tabindex="-1" aria-labelledby="modalExtendLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-center fs-5" id="modalExtendLabel">Extending End Date - <label id="ext_no_ca">3123333123</label></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('businessTrip.approvalExtended') }}">@csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="start">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="end">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="total">Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light" id="totaldays" name="totaldays" type="text" min="0" value="0" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <p class="text-center mt-2">--<b>Changing too</b>--</p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_start">Start Date</label>
                            <input type="date" name="ext_start_date" id="ext_start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_end">New End Date</label>
                            <input type="date" name="ext_end_date" id="ext_end_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_total">New Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light" id="ext_totaldays" name="ext_totaldays" type="text" min="0" value="0" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="reason">Reason</label>
                            <textarea name="ext_reason" id="ext_reason" class="form-control bg-light" readonly></textarea>
                        </div>
                        <input type="hidden" name="no_id" id="no_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="action_ca_reject" value="Reject" class="btn btn-primary" id="extendButton">Reject</button>
                    <button type="submit" name="action_ca_approve" value="Pending" class="btn btn-primary" id="extendButton">Approved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.defaultTable').DataTable();
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const totalDaysInput = document.getElementById('totaldays');

        const extStartDateInput = document.getElementById('ext_start_date');
        const extEndDateInput = document.getElementById('ext_end_date');
        const extTotalDaysInput = document.getElementById('ext_totaldays');

        const extNoCa = document.getElementById('ext_no_ca');

        // Menghitung total hari untuk start_date dan end_date
        function calculateTotalDays() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            if (startDate && endDate && startDate <= endDate) {
                const timeDiff = endDate - startDate;
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                totalDaysInput.value = daysDiff;
            } else {
                totalDaysInput.value = 0;
            }
        }

        // Menghitung total hari untuk ext_start_date dan ext_end_date
        function calculateExtTotalDays() {
            const extStartDate = new Date(extStartDateInput.value);
            const extEndDate = new Date(extEndDateInput.value);
            if (extStartDate && extEndDate && extStartDate <= extEndDate) {
                const timeDiff = extEndDate - extStartDate;
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                extTotalDaysInput.value = daysDiff;
            } else {
                extTotalDaysInput.value = 0;
            }
        }

        // Mengatur min date untuk ext_end_date
        function updateExtEndDateMin() {
            const extStartDate = extStartDateInput.value;
            extEndDateInput.min = extStartDate; // Set min date untuk ext_end_date
        }

        // Event listener untuk menghitung total hari saat tanggal berubah
        startDateInput.addEventListener('change', calculateTotalDays);
        endDateInput.addEventListener('change', calculateTotalDays);

        extStartDateInput.addEventListener('change', function() {
            updateExtEndDateMin(); // Update min date saat ext_start_date diubah
            calculateExtTotalDays();
        });

        extEndDateInput.addEventListener('change', function() {
            if (new Date(extEndDateInput.value) < new Date(extStartDateInput.value)) {
                Swal.fire({
                    title: 'Cannot Sett Date!',
                    text: 'End Date cannot be earlier than Start Date.',
                    icon: 'warning',
                    confirmButtonColor: "#9a2a27",
                    confirmButtonText: 'Ok',
                });
                extEndDateInput.value = ""; // Reset jika salah
            }
            calculateExtTotalDays();
        });

        // Mengisi modal saat tombol edit ditekan
        const editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const startDate = this.getAttribute('data-start-date');
                const endDate = this.getAttribute('data-end-date');
                const caNumber = this.getAttribute('data-no-ca');
                const idNumber = this.getAttribute('data-no-id');
                const extReason = this.getAttribute('data-reason-ext');
                const extEnd = this.getAttribute('data-end-date-ext');

                startDateInput.value = startDate;
                endDateInput.value = endDate;
                extStartDateInput.value = startDate; // Mengisi ext_start_date dengan start_date
                extEndDateInput.value = endDate; // Mengisi ext_end_date dengan end_date

                document.getElementById('ext_no_ca').textContent = caNumber;
                document.getElementById('no_id').value = idNumber; // Mengisi input no_id
                document.getElementById('ext_end_date').value = extEnd; // Mengisi input no_id
                document.getElementById('ext_reason').value = extReason || "Ga masuk sumpah";

                calculateTotalDays(); // Hitung total hari saat modal dibuka
                calculateExtTotalDays(); // Hitung total hari untuk ext saat modal dibuka
                updateExtEndDateMin(); // Update min date saat modal dibuka
            });
        });
    });
</script>