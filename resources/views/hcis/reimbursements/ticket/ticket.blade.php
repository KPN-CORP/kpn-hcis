@extends('layouts_.vertical', ['page_title' => 'Ticket'])

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
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
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
            <div class="col-md-6 mt-4 mb-2 text-end">
                <a href="{{ route('ticket.form') }}" class="btn btn-primary rounded-pill shadow"><i
                        class="bi bi-plus-circle"></i> Add Ticket</a>
            </div>
        </div>
        @include('hcis.reimbursements.businessTrip.modal')
        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        @php
                            $currentFilter = $filter;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3>Ticket Data</h3>
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

                        <form method="GET" action="{{ route('ticket') }}">
                            <button type="submit" name="filter" value="request"
                                class="btn {{ $filter === 'request' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm me-1 mb-3">
                                Request
                            </button>
                            <button type="submit" name="filter" value="rejected"
                                class="btn {{ $filter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill btn-sm me-1 mb-3">
                                Rejected
                            </button>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover dt-responsive nowrap" id="defaultTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>No. SPPD</th>
                                        <th>No. Ticket</th>
                                        <th>Total Tickets</th>
                                        <th>Purposes</th>
                                        {{-- <th>Ticket Type</th> --}}
                                        {{-- <th>Requestor</th> --}}
                                        {{-- <th>Transportation Type</th> --}}
                                        {{-- <th>Passengers Name</th> --}}
                                        <th>From/To</th>
                                        {{-- <th>To</th> --}}
                                        {{-- <th>Departure</th> --}}
                                        {{-- <th>Departure Time</th> --}}
                                        {{-- <th>Homecoming</th> --}}
                                        {{-- <th>Homecoming Time</th> --}}
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td style="text-align: center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $transaction->no_sppd }}</td>
                                            <td>{{ $transaction->no_tkt }}</td>
                                            <td style="text-align: left">
                                                {{ $ticketCounts[$transaction->no_tkt]['total'] ?? 1 }} Tickets</td>
                                            <td>{{ $transaction->jns_dinas_tkt }}</td>
                                            {{-- <td>{{ $transaction->np_tkt }}</td> --}}
                                            <td>{{ $transaction->dari_tkt . '/' . $transaction->ke_tkt }}</td>
                                            <td class="text-info">
                                                <a class="text-info btn-detail" data-toggle="modal"
                                                    data-target="#detailModal" style="cursor: pointer"
                                                    data-tiket="{{ json_encode(
                                                        $ticket[$transaction->no_tkt]->map(function ($ticket) {
                                                            return [
                                                                // 'No. Ticket' => $ticket->no_tkt ?? 'No Data',
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
                                                    <u>Details</u>
                                                </a>
                                            </td>

                                            <td style="align-content: center">
                                                <span
                                                    class="badge rounded-pill bg-{{ $transaction->approval_status == 'Approved' ||
                                                    $transaction->approval_status == 'Declaration Approved' ||
                                                    $transaction->approval_status == 'Verified'
                                                        ? 'success'
                                                        : ($transaction->approval_status == 'Rejected' ||
                                                        $transaction->approval_status == 'Return/Refund' ||
                                                        $transaction->approval_status == 'Declaration Rejected'
                                                            ? 'danger'
                                                            : (in_array($transaction->approval_status, [
                                                                'Pending L1',
                                                                'Pending L2',
                                                                'Declaration L1',
                                                                'Declaration L2',
                                                                'Waiting Submitted',
                                                            ])
                                                                ? 'warning'
                                                                : ($transaction->approval_status == 'Draft'
                                                                    ? 'secondary'
                                                                    : (in_array($transaction->approval_status, ['Doc Accepted'])
                                                                        ? 'info'
                                                                        : 'secondary')))) }}"
                                                    style="font-size: 12px; padding: 0.5rem 1rem; cursor: {{ ($transaction->approval_status == 'Rejected' || $transaction->approval_status == 'Declaration Rejected') && isset($ticketApprovals[$transaction->id]) ? 'pointer' : 'default' }};"
                                                    @if (
                                                        ($transaction->approval_status == 'Rejected' || $transaction->approval_status == 'Declaration Rejected') &&
                                                            isset($ticketApprovals[$transaction->id])) onclick="showRejectInfo('{{ $transaction->id }}')"
                                                    title="Click to see rejection reason" @endif
                                                    @if ($transaction->approval_status == 'Pending L1') title="L1 Manager: {{ $managerL1Names ?? 'Unknown' }}"
                                                    @elseif ($transaction->approval_status == 'Pending L2')
                                                    title="L2 Manager: {{ $managerL2Names ?? 'Unknown' }}"
                                                    @elseif($transaction->approval_status == 'Declaration L1') title="L1 Manager: {{ $managerL1Names ?? 'Unknown' }}"
                                                    @elseif($transaction->approval_status == 'Declaration L2') title="L2 Manager: {{ $managerL2Names ?? 'Unknown' }}" @endif>
                                                    {{ $transaction->approval_status == 'Approved' ? 'Approved' : $transaction->approval_status }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if ($transaction->approval_status == 'Draft')
                                                    <a href="{{ route('ticket.edit', encrypt($transaction->id)) }}"
                                                        class="btn btn-sm rounded-pill btn-outline-warning" title="Edit">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <form action="{{ route('ticket.delete', encrypt($transaction->id)) }}"
                                                        method="POST" style="display:inline;"
                                                        id="deleteForm_{{ $transaction->no_tkt }}">
                                                        @csrf
                                                        <input type="hidden" id="no_sppd_{{ $transaction->no_tkt }}"
                                                            value="{{ $transaction->no_tkt }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-danger delete-button"
                                                            title="Delete" data-id="{{ $transaction->no_tkt }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('ticket.export', ['id' => $transaction->id]) }}"
                                                        class="btn btn-sm btn-outline-info rounded-pill" target="_blank">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                            </td>
                                    @endif
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
                    </div>
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
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">
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


    {{-- @push('scripts') --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <script>
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
                var ticketApprovals = {!! json_encode($ticketApprovals) !!};
                var employeeName = {!! json_encode($employeeName) !!}; // Add this line

                var approval = ticketApprovals[transactionId];
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

        $(document).ready(function() {
            $('.btn-detail').click(function() {
                var tiket = $(this).data('tiket');

                function createTableHtml(data, title) {
                    var tableHtml = '<h5>' + title + '</h5>';
                    tableHtml += '<div class="table-responsive"><table class="table table-sm"><thead><tr>';
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

                    if (tiket && tiket !== 'undefined') {
                        var tiketData = typeof tiket === 'string' ? JSON.parse(tiket) : tiket;
                        content += createTableHtml(tiketData, 'Ticket Detail');
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
