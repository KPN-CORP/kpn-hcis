@extends('layouts_.vertical', ['page_title' => 'Cash Advanced'])

@section('css')
<style>
    th {
        color: white !important;
        text-align: center;
    }

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
        background-color: #AB2F2B !important;
        border-bottom: 2px solid #AB2F2B !important;
        padding-right: 6px;
        box-shadow: inset 2px 0 0 #AB2F2B;
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
        background-color: #AB2F2B !important;
        border-right: 2px solid #AB2F2B !important;
        padding-right: 10px;
        box-shadow: inset 2px 0 0 #AB2F2B;
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
        {{-- <style>
            thead th.sticky {
                position: sticky;
                left: 0;
                background-color: white; /* Pastikan background tidak transparan */
                z-index: 1; /* Agar tetap di atas elemen lain */
            }

            tbody td.sticky {
                position: sticky;
                left: 0;
                background-color: white;
                z-index: 1;
            }
        </style> --}}
        <!-- Page Heading -->
        <div class="row">
            <!-- Breadcrumb Navigation -->
            <div class="col-md-6 mt-3 ms-mb-3">
                <div class="page-title-box d-flex align-items-center">
                    <ol class="breadcrumb mb-0" style="display: flex; align-items: center; padding-left: 0;">
                        <li class="breadcrumb-item" style="font-size: 32px; display: flex; align-items: center;">
                            <a href="/reimbursements" style="text-decoration: none;" class="text-primary">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ $parentLink }}
                        </li>
                        <li class="breadcrumb-item active">
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
                                <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                            </div>
                        </div>
                        @include('hcis.reimbursements.cashadv.navigation.navigationCashadv')
                        <div class="table-responsive">
                            <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th class="sticky-col-header" style="background-color: white">Cash Advance No</th>
                                        <th>Type</th>
                                        <th>Company</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total CA</th>
                                        <th>Total Settlement</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ca_transactions as $ca_transaction)
                                        <tr>
                                            <td class="text-center" >{{ $loop->index + 1 }}</td>
                                            <td style="background-color: white;" class="sticky-col">{{ $ca_transaction->no_ca }}</td>
                                            @if ($ca_transaction->type_ca == 'dns')
                                                <td>Business Trip</td>
                                            @elseif($ca_transaction->type_ca == 'ndns')
                                                <td>Non Business Trip</td>
                                            @elseif($ca_transaction->type_ca == 'entr')
                                                <td>Entertainment</td>
                                            @endif
                                            <td>{{ $ca_transaction->contribution_level_code }}</td>
                                            <td>{{ \Carbon\Carbon::parse($ca_transaction->start_date)->format('d-M-y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($ca_transaction->end_date)->format('d-M-y') }}</td>
                                            <td>Rp. {{ number_format($ca_transaction->total_ca) }}</td>
                                            <td>Rp. {{ number_format($ca_transaction->total_real) }}</td>
                                            <td>
                                                @if ($ca_transaction->total_cost < 0)
                                                    <span class="text-danger">Rp. {{ number_format($ca_transaction->total_cost) }}</span>
                                                @else
                                                    <span class="text-success">Rp. {{ number_format(abs($ca_transaction->total_cost)) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ca_transaction->approval_extend == 'Pending')
                                                    <p class="badge text-bg-warning" style="pointer-events: auto; cursor: default;" title="{{$ca_transaction->approval_extend." - ".$ca_transaction->extName}}">
                                                        {{ "Extend : ".$ca_transaction->approval_extend }}
                                                    </p>
                                                @elseif ($ca_transaction->approval_sett == 'Rejected')
                                                    <p class="badge text-bg-{{ $ca_transaction->approval_sett == 'Approved' ? 'success' : ($ca_transaction->approval_sett == 'Declaration' ? 'info' : ($ca_transaction->approval_sett == 'Pending' ? 'warning' : ($ca_transaction->approval_sett == 'Rejected' ? 'danger' : ($ca_transaction->approval_sett == 'Draft' ? 'secondary' : ($ca_transaction->approval_sett == 'On Progress' ? 'warning' : 'default'))))) }}" style="pointer-events: auto; cursor: default;"
                                                        title="{{ htmlspecialchars($ca_transaction->approval_sett . ' - ' . $ca_transaction->settName) . ' <br> ' . htmlspecialchars($reason[$ca_transaction->id] ?? 'Unknown Reason') }}">
                                                        {{ $ca_transaction->approval_sett }}
                                                    </p>
                                                @else
                                                    <p class="badge text-bg-{{ $ca_transaction->approval_sett == 'Approved' ? 'success' : ($ca_transaction->approval_sett == 'Declaration' ? 'info' : ($ca_transaction->approval_sett == 'Pending' ? 'warning' : ($ca_transaction->approval_sett == 'Rejected' ? 'danger' : ($ca_transaction->approval_sett == 'Draft' ? 'secondary' : ($ca_transaction->approval_sett == 'On Progress' ? 'warning' : 'default'))))) }}" style="pointer-events: auto; cursor: default;" title="{{

                                                        $ca_transaction->approval_sett." - ".$ca_transaction->settName}}">
                                                        {{ $ca_transaction->approval_sett }}
                                                    </p>
                                                @endif
                                                {{-- {{dd($reason) }} --}}
                                            </td>
                                            <td class="text-center">
                                                @if ($ca_transaction->approval_sett == 'Approved')
                                                    <a href="{{ route('cashadvanced.downloadDeclare', $ca_transaction->id) }}" target="_blank" class="btn btn-outline-info" title="Print"><i class="bi bi-file-earmark-arrow-down"></i></a>
                                                @elseif ($ca_transaction->approval_sett == 'Waiting for Declaration')
                                                    @if ($ca_transaction->approval_extend == 'Pending')
                                                    @else
                                                        <a href="{{ route('cashadvanced.deklarasi', encrypt($ca_transaction->id)) }}" class="btn btn-outline-warning" title="Deklarasi" ><i class="ri-edit-box-line"></i></a>
                                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalExtend"
                                                                data-no-id="{{ $ca_transaction->id }}"
                                                                data-no-ca="{{ $ca_transaction->no_ca }}"
                                                                data-start-date="{{ $ca_transaction->start_date }}"
                                                                data-end-date="{{ $ca_transaction->end_date }}"
                                                                data-total-days="{{ $ca_transaction->total_days }}">
                                                            <i class="ri-calendar-line"></i>
                                                        </button>
                                                    @endif
                                                @elseif ($ca_transaction->approval_sett == 'Pending')
                                                    <a href="{{ route('cashadvanced.downloadDeclare', $ca_transaction->id) }}" target="_blank" class="btn btn-outline-info" title="Print"><i class="bi bi-file-earmark-arrow-down"></i></a>
                                                {{-- @elseif ($ca_transaction->approval_sett == 'Reject') --}}
                                                @elseif ($ca_transaction->approval_sett == 'Draft')
                                                    <a href="{{ route('cashadvanced.deklarasi', encrypt($ca_transaction->id)) }}" class="btn btn-outline-warning" title="Deklarasi" ><i class="ri-edit-box-line"></i></a>
                                                @elseif ($ca_transaction->approval_sett == '' || $ca_transaction->approval_sett == 'Rejected')
                                                    @if ($ca_transaction->approval_extend == 'Pending')
                                                    @else
                                                        <button type="button" class="btn btn-outline-primary" title="Extend CA" data-bs-toggle="modal" data-bs-target="#modalExtend"
                                                                data-no-id="{{ $ca_transaction->id }}"
                                                                data-no-ca="{{ $ca_transaction->no_ca }}"
                                                                data-start-date="{{ $ca_transaction->start_date }}"
                                                                data-end-date="{{ $ca_transaction->end_date }}"
                                                                data-total-days="{{ $ca_transaction->total_days }}">
                                                            <i class="ri-calendar-line"></i>
                                                        </button>
                                                        <a href="{{ route('cashadvanced.deklarasi', encrypt($ca_transaction->id)) }}" class="btn btn-outline-warning" title="Deklarasi" ><i class="ri-edit-box-line"></i></a>
                                                        {{-- <a href="#" class="btn btn-outline-primary" title="Extend" ><i class="ri-calendar-line"></i></a> --}}
                                                    @endif
                                                @elseif ($ca_transaction->approval_sett != 'Pending')
                                                    <a href="{{ route('cashadvanced.deklarasi', encrypt($ca_transaction->id)) }}" class="btn btn-outline-warning" title="Deklarasi" ><i class="ri-edit-box-line"></i></a>
                                                @endif
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
    {{-- <script src="{{ asset('resources/js/jquery.min.js') }}"></script>
    <script src="{{ asset('resources/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('resources/js/dataTables.min.js') }}"></script> --}}
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

                    startDateInput.value = startDate;
                    endDateInput.value = endDate;
                    extStartDateInput.value = startDate; // Mengisi ext_start_date dengan start_date
                    extEndDateInput.value = endDate; // Mengisi ext_end_date dengan end_date

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
