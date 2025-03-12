@extends('layouts_.vertical', ['page_title' => 'Cash Advanced'])

@section('css')
<style>
    .table {
            border-collapse: separate;
            width: 100%;
            position: relative;
            overflow: auto;
        }

        .table thead th {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            top: 0 !important;
            z-index: 2 !important;
            background-color: #fff !important;
            border-bottom: 2px solid #ddd !important;
            padding-right: 6px;
            box-shadow: inset 2px 0 0 #fff;
        }

        .table tbody td {
            background-color: #fff !important;
            padding-right: 10px;
            position: relative;
        }

        .table th.sticky-col-header {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            left: 0 !important;
            z-index: 3 !important;
            background-color: #fff !important;
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            box-shadow: inset 2px 0 0 #fff;
        }

        .table td.sticky-col {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            left: 0 !important;
            z-index: 1 !important;
            background-color: #fff !important;
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            box-shadow: inset 6px 0 0 #fff;
        }
</style>
@endsection

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Breadcrumb Navigation -->
            <div class="col-md-6 mt-3 ms-mb-3">
                <div class="page-title-box d-flex align-items-center">
                    <ol class="breadcrumb mb-0" style="display: flex; align-items: center; padding-left: 0;">
                        <li class="breadcrumb-item" style="font-size: 25px; display: flex; align-items: center;">
                            <a href="/reimbursements" style="text-decoration: none;" class="text-primary">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item" >
                            {{ $parentLink }}
                        </li>
                        <li class="breadcrumb-item" >
                            {{ $link }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title">{{ $link }}</h3>
                            <div class="input-group" style="width: 30%;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                </div>
                                <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="search.." aria-label="search" aria-describedby="search" >
                            </div>
                        </div>
                        @include('hcis.reimbursements.approval.navigation.navigationApproval')
                        <div class="table-responsive">
                            <table class="table table-sm dt-responsive nowrap" id="scheduleTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th class="sticky-col-header" style="background-color: white">Cash Advance No</th>
                                        <th>Type</th>
                                        <th>Requestor</th>
                                        <th>Company</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Extend End Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ca_transactions as $transaction)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
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
                                            <td>{{ $extendTime[$transaction->id]['reason_extend'] }}</td>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('hcis.reimbursements.cashadv.navigation.modalCashadv')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const totalDaysInput = document.getElementById('totaldays');

            const extStartDateInput = document.getElementById('ext_start_date');
            const extEndDateInput = document.getElementById('ext_end_date');
            const extTotalDaysInput = document.getElementById('ext_totaldays');
            const extReason = document.getElementById('ext_reason');

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
                calculateExtTotalDays();
            });

            // Mengisi modal saat tombol edit ditekan
            const editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const startDate = this.getAttribute('data-start-date');
                    const endDate = this.getAttribute('data-end-date');
                    const endDateExt = this.getAttribute('data-end-date-ext');
                    const totalDaysExt = this.getAttribute('data-total-days-ext');
                    const reasonExt = this.getAttribute('data-reason-ext');
                    const caNumber = this.getAttribute('data-no-ca');
                    const idNumber = this.getAttribute('data-no-id');

                    startDateInput.value = startDate;
                    endDateInput.value = endDate;
                    extStartDateInput.value = startDate; // Mengisi ext_start_date dengan start_date
                    extEndDateInput.value = endDateExt; // Mengisi ext_end_date dengan end_date
                    extReason.value = reasonExt;

                    document.getElementById('ext_no_ca').textContent = caNumber;
                    document.getElementById('no_id').value = idNumber; // Mengisi input no_id

                    calculateTotalDays(); // Hitung total hari saat modal dibuka
                    calculateExtTotalDays(); // Hitung total hari untuk ext saat modal dibuka
                    updateExtEndDateMin(); // Update min date saat modal dibuka
                });
            });
        });
    </script>
@endpush
