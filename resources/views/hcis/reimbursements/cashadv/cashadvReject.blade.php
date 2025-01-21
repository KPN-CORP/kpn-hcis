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
                    <li class="breadcrumb-item">
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
                            <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search">
                        </div>
                    </div>
                    @include('hcis.reimbursements.cashadv.navigation.navigationCashadv')
                    <div class="table-responsive">
                        <table class="table table-sm dt-responsive nowrap" id="scheduleTable" width="100%"
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
                                    <th style="width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ca_transactions as $ca_transaction)
                                <tr>
                                    <td class="text-center">{{ $loop->index + 1 }}</td>
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
                                            <span class="text-danger">Rp. -{{ number_format(abs($ca_transaction->total_cost)) }}</span>
                                            @else
                                            <span class="text-success">Rp. {{ number_format($ca_transaction->total_cost) }}</span>
                                            @endif
                                    </td>

                                    <td>
                                        @if ($ca_transaction->approval_status == 'Approved' || $ca_transaction->approval_status == 'Done')
                                            @if ($ca_transaction->approval_sett == 'Waiting for Declaration')
                                                <p class="badge text-bg-success"

                                                title="Approved By: {{ isset($fullnames[$ca_transaction->status_id]) ? $fullnames[$ca_transaction->status_id] : 'Unknown Employee' }} and Waiting for Declaration">
                                                {{ $ca_transaction->approval_status }}
                                                </p>
                                            @else
                                                <p class="badge text-bg-success"

                                                title="Approved By: {{ isset($fullnames[$ca_transaction->status_id]) ? $fullnames[$ca_transaction->status_id] : 'Unknown Employee' }}">
                                                {{ $ca_transaction->approval_status }}
                                                </p>
                                            @endif
                                        @elseif ($ca_transaction->approval_status == 'Pending')
                                        <p class="badge text-bg-{{ $ca_transaction->approval_status == 'Approved' ? 'success' : ($ca_transaction->approval_status == 'Declaration' ? 'info' : ($ca_transaction->approval_status == 'Pending' ? 'warning' : ($ca_transaction->approval_status == 'Rejected' ? 'danger' : ($ca_transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}"
                                             title="Waiting Approve by: {{ isset($fullnames[$ca_transaction->status_id]) ? $fullnames[$ca_transaction->status_id] : 'Unknown Employee' }}">
                                            {{ $ca_transaction->approval_status }}
                                        </p>
                                        @elseif ($ca_transaction->approval_status == 'Rejected')
                                        <p class="badge text-bg-{{ $ca_transaction->approval_status == 'Approved' ? 'success' : ($ca_transaction->approval_status == 'Declaration' ? 'info' : ($ca_transaction->approval_status == 'Pending' ? 'warning' : ($ca_transaction->approval_status == 'Rejected' ? 'danger' : ($ca_transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}"
                                             title="Rejected by: {{ isset($fullnames[$ca_transaction->status_id]) ? $fullnames[$ca_transaction->status_id] : 'Unknown Employee' }}">
                                            {{ $ca_transaction->approval_status }}
                                        </p>
                                        @elseif ($ca_transaction->approval_status == 'Draft')
                                        <p class="badge text-bg-{{ $ca_transaction->approval_status == 'Approved' ? 'success' : ($ca_transaction->approval_status == 'Declaration' ? 'info' : ($ca_transaction->approval_status == 'Pending' ? 'warning' : ($ca_transaction->approval_status == 'Rejected' ? 'danger' : ($ca_transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}"
                                             title="Waiting for Submit">
                                            {{ $ca_transaction->approval_status }}
                                        </p>
                                        @elseif ($ca_transaction->approval_status == 'Declaration')
                                        <p class="badge text-bg-{{ $ca_transaction->approval_status == 'Approved' ? 'success' : ($ca_transaction->approval_status == 'Declaration' ? 'info' : ($ca_transaction->approval_status == 'Pending' ? 'warning' : ($ca_transaction->approval_status == 'Rejected' ? 'danger' : ($ca_transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}"
                                             title="Waiting for Declaration">
                                            {{ $ca_transaction->approval_status }}
                                        </p>
                                        @endif
                                    </td>
                                    {{-- {{dd($reason)}} --}}
                                    <td>{{ $reason[$ca_transaction->id] ?? '-' }}</td>
                                    <td>
                                        @if ($ca_transaction->approval_status == 'Rejected')
                                            <a href="{{ route('cashadvanced.edit', encrypt($ca_transaction->id)) }}" class="btn btn-outline-warning" title="Edit" ><i class="ri-edit-box-line"></i></a>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
@endsection

@push('scripts')
<script>
    // Periksa apakah ada pesan sukses
    var successMessage = "{{ session('success') }}";

    // Jika ada pesan sukses, tampilkan sebagai alert
    if (successMessage) {
        if (successMessage) {
            Swal.fire({
                title: 'Success!',
                text: successMessage,
                icon: 'success', // Mengubah ke 'success' jika ini merupakan pesan sukses
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'Ok'
            });
        }
    }
</script>
@endpush
