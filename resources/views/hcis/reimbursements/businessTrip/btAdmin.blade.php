@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <style>
        th {
            color: white !important;
            text-align: center;
        }

        #dt-length-0 {
            margin-bottom: 10px;
        }

        .table {
            border-collapse: separate;
            width: 100%;
            /* position: relative; */
            overflow: auto;
        }

        .table thead th {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            top: 0 !important;
            z-index: 2 !important;
            background-color: #AB2F2B !important;
            border-bottom: 2px solid #ddd !important;
            padding-right: 6px;
            /* box-shadow: inset 2px 0 0 #fff; */
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
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            /* box-shadow: inset 2px 0 0 #fff; */
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
    <div class="container-fluid">
        <div class="row">
            <!-- Breadcrumb Navigation -->
            <div class="col-md-6 mt-3">
                <div class="page-title-box d-flex align-items-center">
                    <ol class="breadcrumb mb-0" style="display: flex; align-items: center; padding-left: 0;">
                        <li class="breadcrumb-item" style="font-size: 25px; display: flex; align-items: center;">
                            <a href="/travel" style="text-decoration: none;" class="text-primary">
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

            <!-- Export Excel -->
            <div class="col-md-6 mt-4 mb-2 text-end">
                <a href="{{ route('export.excel', [
                    'start-date' => request()->query('start-date'),
                    'end-date' => request()->query('end-date'),
                ]) }}"
                    class="btn btn-outline-success rounded-pill btn-action">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export to Excel
                </a>
            </div>
        </div>
    </div>
    @include('hcis.reimbursements.businessTrip.modal')

    <div class="card">
        <div class="card-body">
            <form class="date-range mb-2" method="GET" action="{{ route('businessTrip-filterDate.admin') }}">
                <div class="row align-items-end">
                    <h3 class="card-title">SPPD Data</h3>

                    <div class="col-md-5">
                        <label for="start-date" class="mb-2 mt-2">Departure Date:</label>
                        <input type="date" id="start-date" name="start-date" class="form-control"
                            value="{{ request()->query('start-date') }}">
                    </div>
                    <div class="col-md-5">
                        <label for="end-date" class="mb-2 mt-2">To:</label>
                        <input type="date" id="end-date" name="end-date" class="form-control"
                            value="{{ request()->query('end-date') }}">
                    </div>
                    <div class="col-md-2 mt-2">
                        <button type="submit" class="btn btn-primary rounded-pill w-100">Find</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title">{{ $link }}</h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-dark-subtle"><i
                                                class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch" id="customsearch"
                                        class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.."
                                        aria-label="search" aria-describedby="search">
                                </div>
                            </div>
                            <form method="GET" action="{{ route('businessTrip.admin') }}">
                                @php
                                    $currentFilter = request('filter', 'all');
                                @endphp
                                <div class="d-flex flex-wrap gap-2 mt-1 mb-2 justify-content-start">
                                    <button type="submit" name="filter" value="all"
                                        class="btn {{ $currentFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        All
                                    </button>
                                    {{-- <a href="{{ route('businessTrip.admin.division') }}"
                                        class="btn {{ request()->routeIs('businessTrip.admin.division') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Division
                                    </a> --}}
                                    <button type="submit" name="filter" value="request"
                                        class="btn {{ $currentFilter === 'request' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Request
                                    </button>
                                    <button type="submit" name="filter" value="declaration"
                                        class="btn {{ $currentFilter === 'declaration' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Declaration
                                    </button>
                                    <button type="submit" name="filter" value="return_refund"
                                        class="btn {{ $currentFilter === 'return_refund' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Return/Refund
                                    </button>
                                    <button type="submit" name="filter" value="done"
                                        class="btn {{ $currentFilter === 'done' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Done
                                    </button>
                                    <button type="submit" name="filter" value="rejected"
                                        class="btn {{ $currentFilter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                        Rejected
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive" style="overflow-y: auto">
                                <table class="table table-sm table-hover" id="scheduleTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th class="sticky-col-header">No SPPD</th>
                                            <th>Name</th>
                                            <th>Destination</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>CA</th>
                                            <th>Ticket</th>
                                            <th>Hotel</th>
                                            <th>Taxi</th>
                                            <th>Status</th>
                                            <th style="">Approve</th>
                                            <th style="width: 270px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($sppd as $idx => $n)
                                            <tr>
                                                <td scope="row" style="text-align: center;">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="sticky-col">{{ $n->no_sppd }}</td>
                                                <td>{{ $n->nama }}</td>
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
                                                                'Destination' => $sppd->where('no_sppd', $n->no_sppd)->first()->tujuan,
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
                                                                        'Passengers Name' => $ticket->np_tkt,
                                                                        'Unit' => $ticket->unit,
                                                                        'Gender' => $ticket->jk_tkt,
                                                                        'NIK' => $ticket->noktp_tkt,
                                                                        'Phone No.' => $ticket->tlp_tkt,
                                                                        'From' => $ticket->dari_tkt,
                                                                        'To' => $ticket->ke_tkt,
                                                                        'Departure Date' => date('d-M-Y', strtotime($ticket->tgl_brkt_tkt)),
                                                                        'Time' => !empty($ticket->jam_brkt_tkt) ? date('H:i', strtotime($ticket->jam_brkt_tkt)) : 'No Data',
                                                                        'Return Date' => isset($ticket->tgl_plg_tkt) ? date('d-M-Y', strtotime($ticket->tgl_plg_tkt)) : 'No Data',
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
                                                                        'Check In' => date('d-M-Y', strtotime($hotel->tgl_masuk_htl)),
                                                                        'Check Out' => date('d-M-Y', strtotime($hotel->tgl_keluar_htl)),
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
                                                <td style="align-content: center">
                                                    <span
                                                        class="badge rounded-pill bg-{{ $n->status == 'Approved' || $n->status == 'Declaration Approved' || $n->status == 'Verified'
                                                            ? 'success'
                                                            : ($n->status == 'Rejected' || $n->status == 'Declaration Rejected' || $n->status == 'Return/Refund'
                                                                ? 'danger'
                                                                : (in_array($n->status, ['Pending L1', 'Pending L2', 'Declaration L1', 'Declaration L2', 'Waiting Submitted'])
                                                                    ? 'warning'
                                                                    : ($n->status == 'Draft'
                                                                        ? 'secondary'
                                                                        : (in_array($n->status, ['Doc Accepted'])
                                                                            ? 'info'
                                                                            : 'secondary')))) }}"
                                                        style="font-size: 12px; padding: 0.5rem 1rem; cursor: {{ ($n->status == 'Rejected' || $n->status == 'Declaration Rejected') && isset($btApprovals[$n->id]) ? 'pointer' : 'default' }};"
                                                        @if (($n->status == 'Rejected' || $n->status == 'Declaration Rejected') && isset($btApprovals[$n->id])) onclick="showRejectInfo('{{ $n->id }}')"
                                                         title="Click to see rejection reason" @endif
                                                        @if ($n->status == 'Pending L1') title="L1 Manager: {{ $managerL1Names[$n->manager_l1_id] ?? 'Unknown' }}"
                                                        @elseif ($n->status == 'Pending L2')
                                                            title="L2 Manager: {{ $managerL2Names[$n->manager_l2_id] ?? 'Unknown' }}"
                                                            @elseif($n->status == 'Declaration L1') title="L1 Manager: {{ $managerL1Names[$n->manager_l1_id] ?? 'Unknown' }}"
                                                        @elseif($n->status == 'Declaration L2') title="L2 Manager: {{ $managerL2Names[$n->manager_l2_id] ?? 'Unknown' }}" @endif>
                                                        {{ $n->status == 'Approved' ? 'Request Approved' : $n->status }}
                                                    </span>
                                                </td>

                                                <td style="text-align: center; align-content: center">
                                                    <button type="button" class="btn btn-outline-success rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#approvalDecModal"
                                                        data-id="{{ $n->id }}" data-sppd="{{ $n->no_sppd }}"
                                                        data-status="{{ $n->status }}"
                                                        data-manager-l1="{{ $managerL1Names[$n->manager_l1_id] ?? 'Unknown' }}"
                                                        data-manager-l2="{{ $managerL2Names[$n->manager_l2_id] ?? 'Unknown' }}"
                                                        title="Approval Update">
                                                        <i class="bi bi-list-check"></i>
                                                    </button>

                                                </td>
                                                <td style="text-align: center; align-content: center">
                                                    <form id="deleteForm_{{ $n->id }}" method="POST"
                                                        action="/businessTrip/admin/delete/{{ $n->id }}"
                                                        style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" id="no_sppd_{{ $n->id }}"
                                                            value="{{ $n->no_sppd }}">
                                                        <button type="button"
                                                            class="btn btn-outline-danger rounded-pill mb-1 delete-button"
                                                            data-id="{{ $n->id }}"
                                                            {{ $n->status === 'Diterima' ? 'disabled' : '' }}>
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('export.admin', ['id' => $n->id, 'types' => 'sppd,ca,tiket,hotel,taksi']) }}"
                                                        class="btn btn-outline-info rounded-pill mb-1">
                                                        <i class="bi bi-download"></i>
                                                    </a>

                                                    @php
                                                        $today = \Carbon\Carbon::today()->format('Y-m-d');
                                                    @endphp
                                                    @if (
                                                        $n->status != 'Pending L1' &&
                                                            $n->status != 'Pending L2' &&
                                                            $n->status != 'Rejected' &&
                                                            $n->status != 'Verified' &&
                                                            $n->status != 'Declaration L1' &&
                                                            $n->status != 'Declaration L2' &&
                                                            $n->status != 'Declaration Rejected')
                                                        <form method="GET"
                                                            action="/businessTrip/declaration/admin/{{ $n->id }}"
                                                            style="display: inline-block;">
                                                            <button type="submit"
                                                                class="btn btn-outline-success rounded-pill mb-1"
                                                                data-toggle="tooltip" title="Deklarasi">
                                                                <i class="bi bi-card-checklist"></i>
                                                            </button>
                                                        </form>
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

                {{-- APPROVAL MODAL --}}
                <div class="modal fade" id="approvalDecModal" tabindex="-1" aria-labelledby="approvalDecModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="approvalDecModalLabel">Approval Business Trip Update - <span
                                        id="modalSPPD"></span></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            @if (isset($n))
                                <form id="approveForm" action="{{ route('admin.approve', ['id' => $n->id]) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Manager L1 -->
                                            <div class="col-md-12 mb-3">
                                                <div
                                                    class="d-flex flex-column align-items-start border-danger-subtle px-2 mx-2 py-2">
                                                    <label class="col-form-label mb-2 text-dark">Approval Request:</label>

                                                    <!-- Manager L1 Name & Buttons -->
                                                    <div class="mb-3 w-100">
                                                        <div>
                                                            <strong>Manager L1:</strong>
                                                            <span id="managerL1Name"></span>
                                                        </div>
                                                        <div class="mt-2 d-flex justify-content-start"
                                                            id="l1ActionContainer">
                                                            <!-- Will be populated by JavaScript -->
                                                        </div>
                                                    </div>

                                                    <!-- Manager L2 Name & Buttons -->
                                                    <div class="mb-3 w-100">
                                                        <div>
                                                            <strong>Manager L2:</strong>
                                                            <span id="managerL2Name"></span>
                                                        </div>
                                                        <div class="mt-2 d-flex justify-content-start"
                                                            id="l2ActionContainer">
                                                            <!-- Will be populated by JavaScript -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-6 mb-3">
                                                <div class="d-flex flex-column align-items-start p-2">
                                                    <label class="col-form-label mb-2 text-dark">Approval
                                                        Declaration:</label>

                                                    <!-- Manager L1 Name & Buttons -->
                                                    <div class="mb-3 w-100">
                                                        <div>
                                                            <strong>Manager L1:</strong>
                                                            <span id="managerL1NameDeclare"></span>
                                                        </div>
                                                        <div class="mt-2 d-flex justify-content-start"
                                                            id="l1ActionContainerDeclare">
                                                            <!-- Will be populated by JavaScript -->
                                                        </div>
                                                    </div>

                                                    <!-- Manager L2 Name & Buttons -->
                                                    <div class="mb-3 w-100">
                                                        <div>
                                                            <strong>Manager L2:</strong>
                                                            <span id="managerL2NameDeclare"></span>
                                                        </div>
                                                        <div class="mt-2 d-flex justify-content-start"
                                                            id="l2ActionContainerDeclare">
                                                            <!-- Will be populated by JavaScript -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-primary rounded-pill"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="detailModal" tabindex="-1" role="dialog"
                    aria-labelledby="detailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h4 class="modal-title text-white" id="detailModalLabel">Detail Information</h4>
                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <h6 id="detailTypeHeader" class="mb-3"></h6>
                                <div id="detailContent"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary rounded-pill"
                                    data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason Modal -->
                <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title text-white" id="rejectReasonModalLabel">Rejection Information</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Rejected by</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <span id="rejectedBy"></span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>Rejection reason</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <span id="rejectionReason"></span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>Rejection date</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <span id="rejectionDate"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary rounded-pill"
                                    data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason Modal -->
                <div class="modal fade" id="rejectReasonForm" tabindex="-1" aria-labelledby="rejectReasonFormLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light border-bottom-0">
                                <h5 class="modal-title" id="rejectReasonFormLabel"
                                    style="color: #333; font-weight: 600;">Rejection
                                    Reason</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form id="rejectReasonForm" method="POST">
                                    @csrf
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="status_approval" value="Rejected">

                                    <div class="mb-3">
                                        <label for="reject_info" class="form-label"
                                            style="color: #555; font-weight: 500;">Please
                                            provide a reason for rejection:</label>
                                        <textarea class="form-control border-2" name="reject_info" id="reject_info" rows="4" required
                                            style="resize: vertical; min-height: 100px;"></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-outline-primary rounded-pill me-2"
                                            data-bs-dismiss="modal" style="min-width: 100px;">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill"
                                            style="min-width: 100px;">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
                <script>
                    document.getElementById('rejectReasonForm').addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget; // Button that triggered the modal
                        const btId = button.getAttribute('data-id'); // Get the ID
                        const form = this.querySelector('form');
                        if (form && btId) {
                            form.action = `/businessTrip/status/reject/${btId}`; // Update form action with correct path
                            // Add method override for PUT request
                            let methodInput = form.querySelector('input[name="_method"]');
                            if (!methodInput) {
                                methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                form.appendChild(methodInput);
                            }
                            methodInput.value = 'PUT';
                        }
                    });

                    function formatDateToCustomString(dateString) {
                        const date = new Date(dateString);
                        const day = date.getDate().toString().padStart(2, '0'); // Add leading zero for single-digit days
                        const month = date.toLocaleString('en-US', {
                            month: 'short'
                        }); // Get abbreviated month name
                        const year = date.getFullYear();
                        return `${day}-${month}-${year}`;
                    }
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const approvalModal = document.getElementById('approvalDecModal');
                        if (approvalModal) {
                            approvalModal.addEventListener('show.bs.modal', function(event) {
                                // Get the button that triggered the modal
                                const button = event.relatedTarget;
                                const btId = button.getAttribute('data-id');
                                const sppdNo = button.getAttribute('data-sppd');
                                const status = button.getAttribute('data-status');
                                const managerL1 = button.getAttribute('data-manager-l1');
                                const managerL2 = button.getAttribute('data-manager-l2');
                                const form = document.getElementById('approveForm');

                                if (form && btId) {
                                    form.action = `/businessTrip/status/approve/${btId}`;
                                    let methodInput = form.querySelector('input[name="_method"]');
                                    if (!methodInput) {
                                        methodInput = document.createElement('input');
                                        methodInput.type = 'hidden';
                                        methodInput.name = '_method';
                                        form.appendChild(methodInput);
                                    }
                                    methodInput.value = 'PUT';
                                }

                                // Update modal content
                                document.getElementById('modalSPPD').textContent = sppdNo;
                                document.getElementById('managerL1Name').textContent = managerL1;
                                document.getElementById('managerL2Name').textContent = managerL2;
                                // document.getElementById('managerL1NameDeclare').textContent = managerL1;
                                // document.getElementById('managerL2NameDeclare').textContent = managerL2;

                                // Get the containers
                                const l1Container = document.getElementById('l1ActionContainer');
                                const l2Container = document.getElementById('l2ActionContainer');

                                // const l1ContainerDeclare = document.getElementById('l1ActionContainerDeclare');
                                // const l2ContainerDeclare = document.getElementById('l2ActionContainerDeclare');


                                // Clear previous content
                                l1Container.innerHTML = '';
                                l2Container.innerHTML = '';
                                // l1ContainerDeclare.innerHTML = '';
                                // l2ContainerDeclare.innerHTML = '';

                                approvalModal.addEventListener('click', function(e) {
                                    if (e.target.matches('.btn-success')) {
                                        e.preventDefault();
                                        const form = document.getElementById('approveForm');
                                        if (form) {
                                            form.submit();
                                        }
                                    }
                                });

                                // Handle L1 container content
                                if (status === 'Pending L1') {
                                    l1Container.innerHTML = `
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill me-2" data-id="${btId}">Approve</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill"
                                            data-bs-toggle="modal" data-bs-target="#rejectReasonForm" data-id="${btId}">Reject</button>
                                `;
                                } else {
                                    l1Container.innerHTML = `<div id="approvalDataL1" class="w-100"></div>`;
                                }

                                // Handle L2 container content
                                if (status === 'Pending L2') {
                                    l2Container.innerHTML = `
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill me-2" data-id="${btId}">Approve</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill"
                                                data-bs-toggle="modal" data-bs-target="#rejectReasonForm" data-id="${btId}">Reject</button>
                                    `;
                                } else {
                                    l2Container.innerHTML = `<div id="approvalDataL2" class="w-100"></div>`;
                                }
                                // if (status === 'Declaration L1') {
                                //     l1ContainerDeclare.innerHTML = `
                                //     <button type="submit" class="btn btn-success btn-sm rounded-pill me-2" data-id="${btId}">Approve Declaration</button>
                                //     <button type="button" class="btn btn-outline-danger btn-sm rounded-pill"
                                //             data-bs-toggle="modal" data-bs-target="#rejectReasonForm" data-id="${btId}">Reject</button>
                                // `;
                                // } else {
                                //     l1ContainerDeclare.innerHTML =
                                //         `<div id="approvalDataL1Declare" class="w-100"></div>`;
                                // }

                                // // Handle L2 Declaration container content
                                // if (status === 'Declaration L2') {
                                //     l2ContainerDeclare.innerHTML = `
                                //     <button type="submit" class="btn btn-success btn-sm rounded-pill me-2" data-id="${btId}">Approve Declaration</button>
                                //     <button type="button" class="btn btn-outline-danger btn-sm rounded-pill"
                                //             data-bs-toggle="modal" data-bs-target="#rejectReasonForm" data-id="${btId}">Reject</button>
                                // `;
                                // } else {
                                //     l2ContainerDeclare.innerHTML =
                                //         `<div id="approvalDataL2Declare" class="w-100"></div>`;
                                // }

                                // Get and display approval data
                                const approvals = @json($btApproved);
                                const filteredApprovals = approvals.filter(approval => approval.bt_id === btId);

                                // Display approval data if containers exist
                                const approvalDataL1 = document.getElementById('approvalDataL1');
                                const approvalDataL2 = document.getElementById('approvalDataL2');
                                // const approvalDataL1Declare = document.getElementById('approvalDataL1Declare');
                                // const approvalDataL2Declare = document.getElementById('approvalDataL2Declare');

                                if (approvalDataL1) {
                                    const l1Approvals = filteredApprovals.filter(a => a.layer === 1 && a
                                        .approval_status === 'Pending L2');
                                    const l1Rejections = filteredApprovals.filter(a => a.layer === 1 && a
                                        .approval_status === 'Rejected');
                                    if (l1Approvals.length > 0) {
                                        approvalDataL1.innerHTML = l1Approvals.map(approval => `
                                                        <div class="border rounded p-2 mb-2">
                                                            <strong>Status:</strong> ${approval.approval_status}<br>
                                                            <strong>Approved By:</strong> ${approval.employee_id}<br>
                                                             <strong>Approved At:</strong> ${formatDateToCustomString(approval.approved_at)}<br>
                                                            <strong>Processed By:</strong> ${approval.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                                        </div>
                                                    `).join('');
                                    } else if (l1Rejections.length > 0) {
                                        approvalDataL1.innerHTML += l1Rejections.map(rejection => `
                                          <div class="border rounded p-2 mb-2 bg-warning">
                                                            <strong>Status:</strong> ${rejection.approval_status}<br>
                                                            <strong>Rejected By:</strong> ${rejection.employee_id}<br>
                                                            <strong>Rejected At:</strong> ${formatDateToCustomString(rejection.approved_at)}<br>
                                                            <strong>Rejection Info:</strong> ${rejection.reject_info || 'No additional info provided'}<br>
                                                            <strong>Processed By:</strong> ${rejection.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                                        </div>
                                                    `).join('');
                                    } else {
                                        approvalDataL1.innerHTML =
                                            '<p class="text-muted">No L1 Request found</p>';
                                    }
                                }

                                if (approvalDataL2) {
                                    const l2Approvals = filteredApprovals.filter(a => a.layer === 2 && a
                                        .approval_status === 'Approved');
                                    const l2Rejections = filteredApprovals.filter(a => a.layer === 2 && a
                                        .approval_status === 'Rejected');
                                    if (l2Approvals.length > 0) {
                                        approvalDataL2.innerHTML = l2Approvals.map(approval => `
                                            <div class="border rounded p-2 mb-2">
                                                <strong>Status:</strong> ${approval.approval_status}<br>
                                                <strong>Approved By:</strong> ${approval.employee_id}<br>
                                                <strong>Approved At:</strong> ${formatDateToCustomString(approval.approved_at)}<br>
                                                <strong>Processed By:</strong> ${approval.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                            </div>
                                `).join('');
                                    } else if (l2Rejections.length > 0) {
                                        approvalDataL2.innerHTML += l2Rejections.map(rejection => `
                                        <div class="border rounded p-2 mb-2 bg-warning">
                                            <strong>Status:</strong> ${rejection.approval_status}<br>
                                            <strong>Rejected By:</strong> ${rejection.employee_id}<br>
                                            <strong>Rejected At:</strong> ${formatDateToCustomString(rejection.approved_at)}<br>
                                            <strong>Rejection Info:</strong> ${rejection.reject_info || 'No additional info provided'}<br>
                                            <strong>Processed By:</strong> ${rejection.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                        </div>
                                    `).join('');
                                    } else {
                                        approvalDataL2.innerHTML =
                                            '<p class="text-muted">No L2 Request found</p>';
                                    }
                                }
                                // if (approvalDataL1Declare) {
                                //     const l1Declarations = filteredApprovals.filter(a =>
                                //         a.layer === 1 &&
                                //         (a.approval_status === 'Declaration L2')
                                //     );
                                //     const l1DeclarationsReject = filteredApprovals.filter(a =>
                                //         a.layer === 1 &&
                                //         (a.approval_status === 'Declaration Rejected')
                                //     );
                                //     if (l1Declarations.length > 0) {
                                //         approvalDataL1Declare.innerHTML = l1Declarations.map(approval => `
                                //         <div class="border rounded p-2 mb-2">
                                //             <strong>Status:</strong> ${approval.approval_status}<br>
                                //             <strong>Approved By:</strong> ${approval.employee_id}<br>
                                //             <strong>Approved At:</strong> ${formatDateToCustomString(approval.approved_at)}<br>
                                //             <strong>Processed By:</strong> ${approval.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                //         </div>
                                //     `).join('');
                                //     } else if (l1DeclarationsReject.length > 0) {
                                //         approvalDataL1Declare.innerHTML += l1DeclarationsReject.map(rejection => `
                                //         <div class="border rounded p-2 mb-2 bg-warning">
                                //             <strong>Status:</strong> ${rejection.approval_status}<br>
                                //             <strong>Rejected By:</strong> ${rejection.employee_id}<br>
                                //             <strong>Rejected At:</strong> ${formatDateToCustomString(rejection.approved_at)}<br>
                                //             <strong>Rejection Info:</strong> ${rejection.reject_info || 'No additional info provided'}<br>
                                //             <strong>Processed By:</strong> ${rejection.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                //         </div>
                                //     `).join('');
                                //     } else {
                                //         approvalDataL1Declare.innerHTML =
                                //             '<p class="text-muted">No L1 declarations found</p>';
                                //     }
                                // }

                                // if (approvalDataL2Declare) {
                                //     const l2Declarations = filteredApprovals.filter(a =>
                                //         a.layer === 2 &&
                                //         (a.approval_status === 'Declaration Approved')
                                //     );
                                //     const l2DeclarationsReject = filteredApprovals.filter(a =>
                                //         a.layer === 2 &&
                                //         (a.approval_status === 'Declaration Rejected')
                                //     );
                                //     if (l2Declarations.length > 0) {
                                //         approvalDataL2Declare.innerHTML = l2Declarations.map(approval => `
                                //         <div class="border rounded p-2 mb-2">
                                //             <strong>Status:</strong> ${approval.approval_status}<br>
                                //             <strong>Approved By:</strong> ${approval.employee_id}<br>
                                //             <strong>Approved At:</strong> ${formatDateToCustomString(approval.approved_at)}<br>
                                //             <strong>Processed By:</strong> ${approval.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                //         </div>
                                //     `).join('');
                                //     } else if (l2DeclarationsReject.length > 0) {
                                //         approvalDataL2Declare.innerHTML += l2DeclarationsReject.map(rejection => `
                                //         <div class="border rounded p-2 mb-2 bg-warning">
                                //             <strong>Status:</strong> ${rejection.approval_status}<br>
                                //             <strong>Rejected By:</strong> ${rejection.employee_id}<br>
                                //             <strong>Rejected At:</strong> ${formatDateToCustomString(rejection.approved_at)}<br>
                                //             <strong>Rejection Info:</strong> ${rejection.reject_info || 'No additional info provided'}<br>
                                //             <strong>Processed By:</strong> ${rejection.by_admin === 'T' ? 'Admin' : 'Layer Manager'}
                                //         </div>
                                //     `).join('');
                                //     } else {
                                //         approvalDataL2Declare.innerHTML =
                                //             '<p class="text-muted">No L2 declarations found</p>';
                                //     }
                                // }
                            });
                        }
                    });

                    document.addEventListener('DOMContentLoaded', function() {
                        const rejectModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'), {
                            keyboard: true,
                            backdrop: 'static'
                        });

                        const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
                        closeButtons.forEach(button => {
                            button.addEventListener('click', () => {
                                rejectModal.hide();
                            });
                        });

                        function formatDate(dateTimeString) {
                            // Create a new Date object from the dateTimeString
                            var date = new Date(dateTimeString);

                            // Extract day, month, year, hours, and minutes
                            var day = ('0' + date.getDate()).slice(-2); // Ensure two digits
                            var month = ('0' + (date.getMonth() + 1)).slice(-2); // Month is 0-based, so we add 1
                            var year = date.getFullYear();
                            var hours = ('0' + date.getHours()).slice(-2);
                            var minutes = ('0' + date.getMinutes()).slice(-2);

                            // Format the date as d/m/Y H:I
                            return `${day}/${month}/${year} ${hours}:${minutes}`;
                        }

                        window.showRejectInfo = function(transactionId) {
                            var btApprovals = {!! json_encode($btApprovals) !!};
                            var employeeName = {!! json_encode($employeeName) !!}; // Add this line

                            var approval = btApprovals[transactionId];
                            if (approval) {
                                var rejectedBy = employeeName[approval.employee_id] || 'N/A'; // Retrieve fullname
                                document.getElementById('rejectedBy').textContent = ': ' + rejectedBy;
                                document.getElementById('rejectionReason').textContent = ': ' + (approval.reject_info ||
                                    'N/A');
                                var rejectionDate = approval.approved_at ? formatDate(approval.approved_at) : 'N/A';
                                document.getElementById('rejectionDate').textContent = ': ' + rejectionDate;
                                rejectModal.show();
                            } else {
                                console.error('Approval information not found for transaction ID:', transactionId);
                            }
                        };

                        // Add event listener for modal hidden event
                        document.getElementById('rejectReasonModal').addEventListener('hidden.bs.modal', function() {
                            console.log('Modal closed');
                        });
                    });

                    window.addEventListener('resize', function() {
                        document.body.style.display = 'none';
                        document.body.offsetHeight; // Force a reflow
                        document.body.style.display = '';
                    });

                    function getDate() {
                        var today = new Date();
                        var dd = today.getDate();
                        var mm = today.getMonth() + 1; // January is 0!
                        var yyyy = today.getFullYear();

                        if (dd < 10) {
                            dd = '0' + dd;
                        }
                        if (mm < 10) {
                            mm = '0' + mm;
                        }

                        // Correct date format for input fields
                        var formattedToday = yyyy + '-' + mm + '-' + dd;
                        console.log(formattedToday);

                        var startDateElement = document.getElementById("start-date");
                        var endDateElement = document.getElementById("end-date");

                        // Only set the value if it's not already set
                        if (!startDateElement.value) {
                            startDateElement.value = formattedToday;
                        }
                        if (!endDateElement.value) {
                            endDateElement.value = formattedToday;
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            getDate();
                        });

                        document.getElementById('recordsPerPage').addEventListener('change', function() {
                            const perPage = this.value;
                            const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
                            window.location.search = `?per_page=${perPage}&page=${currentPage}`;
                        });

                        function confirmDelete(id) {
                            if (confirm("Are you sure you want to delete this item?")) {
                                document.getElementById('deleteForm_' + id).submit();
                            }
                        }

                    }

                    // Ensure the DOM is fully loaded before manipulating it
                    document.addEventListener('DOMContentLoaded', function() {
                        getDate();
                    });

                    function confirmDelete(id) {
                        if (confirm("Are you sure you want to delete this item?")) {
                            document.getElementById('deleteForm_' + id).submit();
                        }
                    }

                    $(document).ready(function() {
                        $('.btn-detail').click(function() {
                            var ca = $(this).data('ca');
                            var tiket = $(this).data('tiket');
                            var hotel = $(this).data('hotel');
                            var taksi = $(this).data('taksi');

                            function createTableHtml(data, title) {
                                var tableHtml = '<h5>' + title + '</h5>';
                                tableHtml +=
                                    '<div class="table-responsive"><table class="table table-sm"><thead><tr>';
                                var isArray = Array.isArray(data) && data.length > 0;

                                // Assuming all objects in the data array have the same keys, use the first object to create headers
                                if (isArray) {
                                    for (var key in data[0]) {
                                        if (data[0].hasOwnProperty(key)) {
                                            tableHtml += '<th>' + key + '</th>';
                                        }
                                    }
                                } else if (typeof data === 'object') {
                                    // If data is a single object, create headers from its keys
                                    for (var key in data) {
                                        if (data.hasOwnProperty(key)) {
                                            tableHtml += '<th>' + key + '</th>';
                                        }
                                    }
                                }

                                tableHtml += '</tr></thead><tbody>';

                                // Loop through each item in the array and create a row for each
                                if (isArray) {
                                    data.forEach(function(row) {
                                        tableHtml += '<tr>';
                                        for (var key in row) {
                                            if (row.hasOwnProperty(key)) {
                                                tableHtml += '<td>' + row[key] + '</td>';
                                            }
                                        }
                                        tableHtml += '</tr>';
                                    });
                                } else if (typeof data === 'object') {
                                    // If data is a single object, create a single row
                                    tableHtml += '<tr>';
                                    for (var key in data) {
                                        if (data.hasOwnProperty(key)) {
                                            tableHtml += '<td>' + data[key] + '</td>';
                                        }
                                    }
                                    tableHtml += '</tr>';
                                }

                                tableHtml += '</tbody></table>';
                                return tableHtml;
                            }

                            // $('#detailTypeHeader').text('Detail Information');
                            $('#detailContent').empty();

                            try {
                                var content = '';

                                if (ca && ca !== 'undefined') {
                                    var caData = typeof ca === 'string' ? JSON.parse(ca) : ca;
                                    content += createTableHtml(caData, 'CA Detail');
                                }
                                if (tiket && tiket !== 'undefined') {
                                    var tiketData = typeof tiket === 'string' ? JSON.parse(tiket) : tiket;
                                    content += createTableHtml(tiketData, 'Ticket Detail');
                                }
                                if (hotel && hotel !== 'undefined') {
                                    var hotelData = typeof hotel === 'string' ? JSON.parse(hotel) : hotel;
                                    content += createTableHtml(hotelData, 'Hotel Detail');
                                }
                                if (taksi && taksi !== 'undefined') {
                                    var taksiData = typeof taksi === 'string' ? JSON.parse(taksi) : taksi;
                                    content += createTableHtml(taksiData, 'Taxi Detail');
                                }

                                if (content !== '') {
                                    $('#detailContent').html(content);
                                } else {
                                    $('#detailContent').html('<p>No detail information available.</p>');
                                }

                                $('#detailModal').modal('show');
                            } catch (e) {
                                $('#detailContent').html('<p>Error loading data</p>');
                            }
                        });

                        $('#detailModal').on('hidden.bs.modal', function() {
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                padding: ''
                            });
                            $('.modal-backdrop').remove();
                        });
                    });

                    $(document).ready(function() {
                        var table = $('#yourTableId').DataTable({
                            "pageLength": 10 // Set default page length
                        });
                        // Set to 10 entries per page
                        $('#dt-length-0').val(10);

                        // Trigger the change event to apply the selected value
                        $('#dt-length-0').trigger('change');
                    });
                </script>
            @endsection
