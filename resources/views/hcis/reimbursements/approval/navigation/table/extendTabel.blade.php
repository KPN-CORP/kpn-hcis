<div class="table-responsive">
    <table class="table table-sm dt-responsive nowrap scheduleTable" width="100%"
        cellspacing="0">
        <thead class="thead-light">
            <tr class="text-center">
                <th>No</th>
                <th class="sticky-col-header" style="background-color: #ab2f2b">Cash Advance No</th>
                <th>Type</th>
                <th>Requestor</th>
                <th>Company</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Extend End Date</th>
                <th style="width: 1%">Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ca_transactions_ext as $transaction)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="background-color: white;" class="sticky-col">{{ $transaction->no_ca }}</td>
                    @if($transaction->type_ca == 'dns')
                        <td>Business Trip</td>
                    @elseif($transaction->type_ca == 'ndns')
                        <td>Non Business Trip</td>
                    @elseif($transaction->type_ca == 'entr')
                        <td>Entertainment</td>
                    @endif
                    <td>{{ $transaction->employee->fullname }}</td>
                    <td>{{ $transaction->contribution_level_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->start_date)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->end_date)->format('d-m-Y') }}</td>
                    <td>{{ $extendTime[$transaction->id]['ext_end_date'] }}</td>
                    <td style="width: 1px">{{ $extendTime[$transaction->id]['reason_extend'] }}</td>
                    <td>
                        <p class="badge text-bg-{{ $transaction->approval_extend == 'Approved' ? 'success' : ($transaction->approval_extend == 'Declaration' ? 'info' : ($transaction->approval_extend == 'Pending' ? 'warning' : ($transaction->approval_extend == 'Rejected' ? 'danger' : ($transaction->approval_extend == 'Draft' ? 'secondary' : 'success')))) }}"
                             title="Waiting Approve by: {{ isset($fullnames[$transaction->extend_id]) ? $fullnames[$transaction->extend_id] : 'Unknown Employee' }}">
                            {{ $transaction->approval_extend }}
                        </p>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalExtend"
                                data-no-id="{{ $transaction->id }}"
                                data-no-ca="{{ $transaction->no_ca }}"
                                data-start-date="{{ $transaction->start_date }}"
                                data-end-date="{{ $transaction->end_date }}"
                                data-total-days="{{ $transaction->total_days }}"
                                data-end-date-ext="{{ $extendTime[$transaction->id]['ext_end_date'] }}"
                                data-total-days-ext="{{ $extendTime[$transaction->id]['ext_total_days'] }}"
                                data-reason-ext="{{ $extendTime[$transaction->id]['reason_extend'] }}"
                                >
                            <i class="ri-calendar-line"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


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
                const extTotal = this.getAttribute('data-total-days-ext');

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