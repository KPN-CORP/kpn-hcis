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

            <!-- Add Data Button -->
            <div class="col-md-6 mt-4 mb-2 text-end">
                @if ($disableBT >= 2)
                    <a href="#" class="btn btn-primary rounded-pill" onclick="showPendingAlert(); return false;">
                        <i class="bi bi-plus-circle"></i> Add Data
                    </a>
                @else
                    <a href="{{ route('businessTrip.add') }}" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-circle"></i> Add Data
                    </a>
                @endif
            </div>
        </div>
    </div>
    @include('hcis.reimbursements.businessTrip.modal')

    {{-- <div class="card"> --}}
    <div class="card-body">
        <form class="date-range mb-2" method="GET" action="{{ route('businessTrip-filterDate') }}" style="display:none">
            <div class="row align-items-end">
                <h3 class="card-title">SPPD Data</h3>

                <div class="col-md-5">
                    <label for="start-date" class="mb-2">Departure Date:</label>
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
                        @php
                            // Get the current filter value from the request
                            $currentFilter = request('filter');
                        @endphp

                        <form method="GET" action="{{ route('businessTrip') }}">
                            <div class="d-flex flex-wrap gap-2 mt-1 mb-2 justify-content-start">
                                <button type="submit" name="filter" value="all"
                                    class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    All
                                </button>
                                <button type="submit" name="filter" value="request"
                                    class="btn {{ $filter === 'request' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    Request
                                </button>
                                <button type="submit" name="filter" value="declaration"
                                    class="btn {{ $filter === 'declaration' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    Declaration
                                </button>
                                <button type="submit" name="filter" value="done"
                                    class="btn {{ $filter === 'done' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    Done
                                </button>
                                <button type="submit" name="filter" value="draft"
                                    class="btn {{ $filter === 'draft' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    Draft
                                </button>
                                <button type="submit" name="filter" value="rejected"
                                    class="btn {{ $filter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm">
                                    Rejected
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover nowrap" id="defaultTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th class="sticky-col-header">No SPPD</th>
                                        <th>Destination</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>CA</th>
                                        <th>Hotel</th>
                                        <th>Mess</th>
                                        <th>Ticket</th>
                                        <th>Taxi</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($sppd as $idx => $n)
                                        <tr>
                                            <td scope="row" style="text-align: center;">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="sticky-col">{{ $n->no_sppd }}</td>
                                            <td>{{ $n->tujuan }}</td>
                                            <td>{{ \Carbon\Carbon::parse($n->mulai)->format('d-M-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($n->kembali)->format('d-M-Y') }}</td>
                                            {{-- {{use App\Models\CATransaction;}} --}}
                                            {{-- {{ dd($caTransactions->where('no_sppd', $n->no_sppd));}} --}}
                                            <td style="text-align: center">
                                                @if ($n->ca == 'Ya' && isset($caTransactions[$n->no_sppd]))
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
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
                                                @if ($n->hotel == 'Ya' && isset($hotel[$n->no_sppd]))
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
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
                                            <td style="text-align: center">
                                                @if ($n->mess == 'Ya' && isset($mess[$n->no_sppd]))
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
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
                                            <td style="text-align: center">
                                                @if ($n->tiket == 'Ya' && isset($tickets[$n->no_sppd]))
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
                                                        data-tiket="{{ json_encode(
                                                            $tickets[$n->no_sppd]->map(function ($ticket) {
                                                                return [
                                                                    'No. SPPD' => $ticket->no_sppd,
                                                                    'No. Ticket' => $ticket->no_tkt,
                                                                    'Passengers Name' => $ticket->np_tkt,
                                                                    'Unit' => $ticket->unit,
                                                                    'Gender' => $ticket->jk_tkt,
                                                                    'NIK' => $ticket->noktp_tkt,
                                                                    'Phone No.' => $ticket->tlp_tkt,
                                                                    'Transport Type.' => $ticket->jenis_tkt,
                                                                    'From' => $ticket->dari_tkt,
                                                                    'To' => $ticket->ke_tkt,
                                                                    'Information' => $ticket->ket_tkt ?? 'No Data',
                                                                    'Purposes' => $ticket->jns_dinas_tkt,
                                                                    'Ticket Type' => $ticket->type_tkt,
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
                                            <td style="text-align: center">
                                                @if ($n->taksi == 'Ya' && isset($taksi[$n->no_sppd]))
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
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
                                            <td style="align-content: center">
                                                <span
                                                    class="badge rounded-pill bg-{{ $n->status == 'Approved' || $n->status == 'Declaration Approved' || $n->status == 'Verified'
                                                        ? 'success'
                                                        : ($n->status == 'Rejected' || $n->status == 'Return/Refund' || $n->status == 'Declaration Rejected'
                                                            ? 'danger'
                                                            : (in_array($n->status, ['Pending L1', 'Pending L2', 'Declaration L1', 'Declaration L2', 'Waiting Submitted', 'Extend L1'])
                                                                ? 'warning'
                                                                : ($n->status == 'Draft'
                                                                    ? 'secondary'
                                                                    : (in_array($n->status, ['Doc Accepted', 'Request Revision', 'Declaration Revision'])
                                                                        ? 'info'
                                                                        : 'secondary')))) }}"
                                                    style="font-size: 12px; padding: 0.5rem 1rem; cursor: pointer;"
                                                    @if (($n->status == 'Rejected' || $n->status == 'Declaration Rejected') && isset($btApprovals[$n->id])) onclick="showRejectInfo('{{ $n->id }}')"
                                                    @elseif ($n->status == 'Pending L1')
                                                        onclick="showManagerInfo('L1 Manager', '{{ $managerL1Names[$n->manager_l1_id] ?? 'Unknown' }}')"
                                                    @elseif ($n->status == 'Pending L2')
                                                        onclick="showManagerInfo('L2 Manager', '{{ $managerL2Names[$n->manager_l2_id] ?? 'Unknown' }}')"
                                                    @elseif ($n->status == 'Declaration L1')
                                                        onclick="showManagerInfo('L1 Manager', '{{ $managerL1Names[$n->manager_l1_id] ?? 'Unknown' }}')"
                                                    @elseif ($n->status == 'Declaration L2')
                                                        onclick="showManagerInfo('L2 Manager', '{{ $managerL2Names[$n->manager_l2_id] ?? 'Unknown' }}')" @endif>
                                                    {{ $n->status == 'Approved' ? 'Request Approved' : $n->status }}
                                                </span>
                                            </td>
                                            <td style="align-content: center">
                                                @if ($n->status == 'Draft' || $n->status == 'Request Revision')
                                                    <form method="GET"
                                                        action="/businessTrip/form/update/{{ $n->id }}"
                                                        style="display: inline-block;">
                                                        <button type="submit"
                                                            class="btn btn-outline-warning rounded-pill my-1"
                                                            {{ $n->status === 'Diterima' ? 'disabled' : '' }}
                                                            data-toggle="tooltip" title="Edit">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                    </form>
                                                    <form id="deleteForm_{{ $n->id }}" method="POST"
                                                        action="/businessTrip/delete/{{ $n->id }}"
                                                        style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" id="no_sppd_{{ $n->id }}"
                                                            value="{{ $n->no_sppd }}">
                                                        <button type="button"
                                                            class="btn btn-outline-danger rounded-pill delete-button"
                                                            data-id="{{ $n->id }}"
                                                            {{ $n->status === 'Diterima' ? 'disabled' : '' }}>
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('export', ['id' => $n->id, 'types' => 'sppd,ca,tiket,hotel,taksi,mess', 'deklarasi']) }}"
                                                        class="btn btn-outline-info rounded-pill">
                                                        <i class="bi bi-download"></i>
                                                    </a>

                                                    @php
                                                        $today = \Carbon\Carbon::today()->format('Y-m-d');
                                                    @endphp
                                                    @if (
                                                        // $n->kembali < $today &&
                                                        $n->status == 'Approved' ||
                                                            $n->status == 'Declaration Draft' ||
                                                            $n->status == 'Declaration Rejected' ||
                                                            $n->status == 'Declaration Revision')
                                                        <form method="GET"
                                                            action="/businessTrip/declaration/{{ $n->id }}"
                                                            style="display: inline-block;">
                                                            <button type="submit"
                                                                class="btn btn-outline-success rounded-pill"
                                                                data-toggle="tooltip" title="Deklarasi">
                                                                <i class="bi bi-card-checklist"></i>
                                                            </button>
                                                        </form>

                                                        <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalExtend"
                                                            data-no-id="{{ $n->id }}"
                                                            data-no-ca="{{ $n->no_sppd }}"
                                                            data-start-date="{{ $n->mulai }}"
                                                            data-end-date="{{ $n->kembali }}"
                                                        >
                                                            <i class="ri-calendar-line"></i>
                                                        </button>
                                                    @endif
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

            <!-- Detail Modal -->
            <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
                aria-hidden="true">
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

            <div class="modal fade" id="modalExtend" tabindex="-1" aria-labelledby="modalExtendLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title text-center fs-5" id="modalExtendLabel">Extending End Date - <label id="ext_no_ca"></label></h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('businessTrip.extend') }}">@csrf
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
                                        <input type="date" name="ext_end_date" id="ext_end_date" class="form-control" placeholder="mm/dd/yyyy" required>
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
                                        <textarea name="ext_reason" id="ext_reason" class="form-control" required></textarea>
                                    </div>
                                    <input type="hidden" name="no_id" id="no_id">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="extendButton">Extending</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
            {{-- <script src="{{ asset('public/js/ca.js') }}"></script> --}}
            <script>
                function showManagerInfo(managerType, managerName) {
                    Swal.fire({
                        title: managerType,
                        text: managerName,
                        icon: 'info',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }

                function showPendingAlert() {
                    Swal.fire({
                        title: 'Cannot Add Data!',
                        text: 'You still have 2 Outstanding BT Please Check Your Request or Declaration',
                        icon: 'warning',
                        confirmButtonColor: "#9a2a27",
                        confirmButtonText: 'OK'
                    });
                }
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
                        // console.log('Modal closed');
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
                    // console.log(formattedToday);

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

                    // document.getElementById('recordsPerPage').addEventListener('change', function() {
                    //     const perPage = this.value;
                    //     const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
                    //     window.location.search = `?per_page=${perPage}&page=${currentPage}`;
                    // });

                    // function confirmDelete(id) {
                    //     if (confirm("Are you sure you want to delete this item?")) {
                    //         document.getElementById('deleteForm_' + id).submit();
                    //     }
                    // }

                }
                // Initialize DataTable for all tables with the class 'data-table'
                document.querySelectorAll('.data-table').forEach(function(table) {
                    new DataTable(table, {
                        fixedColumns: {
                            start: 1,
                            end: 1
                        },
                        paging: false,
                        scrollCollapse: true,
                        scrollX: true,
                        scrollY: 300
                    });
                });



                // Ensure the DOM is fully loaded before manipulating it
                document.addEventListener('DOMContentLoaded', function() {
                    getDate();
                });

                // function confirmDelete(id) {
                //     if (confirm("Are you sure you want to delete this item?")) {
                //         document.getElementById('deleteForm_' + id).submit();
                //     }
                // }

                $(document).ready(function() {
                    $('.btn-detail').click(function() {
                        var ca = $(this).data('ca');
                        var tiket = $(this).data('tiket');
                        var hotel = $(this).data('hotel');
                        var taksi = $(this).data('taksi');
                        var mess = $(this).data('mess');

                        function createTableHtml(data, title) {
                            var tableHtml = '<h5>' + title + '</h5>';
                            tableHtml += '<div class="table-responsive">' + // Added this for horizontal scrolling
                                '<table class="table table-sm table-bordered nowrap w-100" cellspacing="0">' +
                                // Added w-100 and table-bordered
                                '<thead><tr>';
                            var isArray = Array.isArray(data) && data.length > 0;

                            // Assuming all objects in the data array have the same keys, use the first object to create headers
                            if (isArray) {
                                for (var key in data[0]) {
                                    if (data[0].hasOwnProperty(key)) {
                                        tableHtml += '<th class="text-nowrap">' + key +
                                            '</th>'; // Added text-nowrap to prevent header wrapping
                                    }
                                }
                            } else if (typeof data === 'object') {
                                // If data is a single object, create headers from its keys
                                for (var key in data) {
                                    if (data.hasOwnProperty(key)) {
                                        tableHtml += '<th class="text-nowrap">' + key + '</th>';
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
                            if (mess && mess !== 'undefined') {
                                var messData = typeof mess === 'string' ? JSON.parse(mess) : mess;
                                content += createTableHtml(messData, 'Mess Detail');
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


                //page settings
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
        @endsection
