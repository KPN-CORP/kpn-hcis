    @extends('layouts_.vertical', ['page_title' => 'Hotel (Admin)'])

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
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">{{ $parentLink }}</li>
                                <li class="breadcrumb-item active">{{ $link }}</li>
                            </ol>
                        </div>
                        <a href="{{ route('travel') }}" class="page-title"><i
                                class="ri-arrow-left-circle-line"></i></a>
                        {{-- <button type="button" class="page-title btn btn-warning rounded-pill">Back</button> --}}
                    </div>
                </div>
            </div>
            <!-- Content Row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="{{ route('hotel.admin') }}" method="GET">
                                <div class="input-group">
                                    <label class="col-form-label">Check in Date : </label>

                                    <input type="date" class="form-control mx-2" id="start_date" name="start_date"
                                        placeholder="Start Date" title="Start Date"
                                        value="{{ request()->get('start_date') }}">
                                    <label class="col-form-label"> - </label>
                                    <input type="date" class="form-control mx-2" id="end_date" name="end_date"
                                        placeholder="End Date" title="End Date" value="{{ request()->get('end_date') }}">

                                    <div class="input-group-append mx-2">
                                        <button class="btn btn-primary" type="submit">Filter</button>
                                    </div>
                                    <div class="input-group-append">
                                        @if (isset($_GET['start_date']) && $_GET['start_date'] !== '')
                                            <button style="display: block" class="btn btn-success w-100" type="button"
                                                onclick="redirectToExportExcel()">
                                                <i class="ri-file-excel-2-line"></i> Export
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title">{{ $link }}</h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white w-border-dark-subtle"><i
                                                class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch" id="customsearch"
                                        class="form-control w-border-dark-subtle border-left-0" placeholder="Search.."
                                        aria-label="search" aria-describedby="search">
                                    {{-- &nbsp;&nbsp;&nbsp; --}}
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover dt-responsive nowrap mt-2" id="defaultTable"
                                    width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>No SPPD</th>
                                            <th style="text-align: left">No Hotel</th>
                                            <th>Requestor</th>
                                            <th>Hotel Name</th>
                                            <th>Location</th>
                                            <th style="text-align: left">Total Hotel</th>
                                            <th>Details</th>
                                            <th>Status</th>
                                            <th>Approval</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td style="text-align: center">{{ $loop->index + 1 }}</td>
                                                <td style="text-align: left">{{ $transaction->no_sppd }}</td>
                                                <td style="text-align: left">{{ $transaction->no_htl }}</td>
                                                <td>{{ $transaction->employee->fullname }}</td>
                                                <td>{{ $transaction->nama_htl }}</td>
                                                <td>{{ $transaction->lokasi_htl }}</td>
                                                <td style="text-align: left">
                                                    {{ $hotelCounts[$transaction->no_htl]['total'] ?? 1 }} Hotels</td>
                                                <td style="text-align: left">
                                                    <a class="text-info btn-detail" data-toggle="modal"
                                                        data-target="#detailModal" style="cursor: pointer"
                                                        data-hotel="{{ json_encode(
                                                            $hotel[$transaction->no_htl]->map(function ($hotel) {
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
                                                        style="font-size: 12px; padding: 0.5rem 1rem; cursor: {{ ($transaction->approval_status == 'Rejected' || $transaction->approval_status == 'Declaration Rejected') && isset($hotelApprovals[$transaction->id]) ? 'pointer' : 'default' }};"
                                                        @if (
                                                            ($transaction->approval_status == 'Rejected' || $transaction->approval_status == 'Declaration Rejected') &&
                                                                isset($hotelApprovals[$transaction->id])) onclick="showRejectInfo('{{ $transaction->id }}')"
                                                    title="Click to see rejection reason" @endif
                                                        @if ($transaction->approval_status == 'Pending L1') title="L1 Manager: {{ $transaction->manager_l1_name ?? 'Unknown' }}"
                                                    @elseif ($transaction->approval_status == 'Pending L2')
                                                    title="L2 Manager: {{ $transaction->manager_l2_name ?? 'Unknown' }}" @endif>
                                                        {{ $transaction->approval_status == 'Approved' ? 'Approved' : $transaction->approval_status }}
                                                    </span>

                                                </td>
                                                <td class="text-center">
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-outline-success rounded-pill" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approvalModal"
                                                        data-id="{{ $transaction->id }}" 
                                                        data-no="{{ $transaction->no_htl }}" 
                                                        data-sppd="{{ $transaction->no_sppd }}"
                                                        data-status="{{ $transaction->approval_status }}"
                                                        data-manager-l1="{{ $managerL1Name ?? 'Unknown' }}" 
                                                        data-manager-l2="{{ $managerL2Name ?? 'Unknown' }}">
                                                        <i class="bi bi-list-check"></i>
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-success rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#bookingModal"
                                                        data-no-id="{{ $transaction->id }}"
                                                        data-no-htl="{{ $transaction->no_htl }}">
                                                        <i class="bi bi-ticket-perforated"></i>
                                                    </button>
                                                    <a href="{{ route('hotel.export', ['id' => $transaction->id]) }}"
                                                        class="btn btn-sm btn-outline-info rounded-pill" target="_blank">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('hotel.delete.admin', encrypt($transaction->id)) }}"
                                                        method="POST" style="display:inline;"
                                                        id="deleteForm_{{ $transaction->no_htl }}">
                                                        @csrf
                                                        {{-- Hidden input to store `no_htl` --}}
                                                        <input type="hidden" id="no_sppd_{{ $transaction->no_htl }}"
                                                            value="{{ $transaction->no_htl }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-danger delete-button"
                                                            title="Delete" data-id="{{ $transaction->no_htl }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
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

        @include('hcis.reimbursements.hotel.navigation.modalHotel')

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
                    var hotelApprovals = {!! json_encode($hotelApprovals) !!};
                    var employeeName = {!! json_encode($employeeName) !!}; // Add this line

                    var approval = hotelApprovals[transactionId];
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
                var table = $('#yourTableId').DataTable({
                    "pageLength": 10 // Set default page length
                });
                // Set to 10 entries per page
                $('#dt-length-0').val(10);

                // Trigger the change event to apply the selected value
                $('#dt-length-0').trigger('change');
            });

            $(document).ready(function() {
                $('.btn-detail').click(function() {
                    var hotel = $(this).data('hotel');

                    function createTableHtml(data, title) {
                        var tableHtml = '<h5>' + title + '</h5>';
                        tableHtml += `<div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>`;
                        var isArray = Array.isArray(data) && data.length > 0;

                        // Create headers
                        if (isArray) {
                            for (var key in data[0]) {
                                if (data[0].hasOwnProperty(key)) {
                                    tableHtml += '<th>' + key + '</th>';
                                }
                            }
                        } else if (typeof data === 'object') {
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    tableHtml += '<th>' + key + '</th>';
                                }
                            }
                        }

                        tableHtml += '</tr></thead><tbody>';

                        // Create rows
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
                            tableHtml += '<tr>';
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    tableHtml += '<td>' + data[key] + '</td>';
                                }
                            }
                            tableHtml += '</tr>';
                        }

                        tableHtml += '</tbody></table></div>';
                        return tableHtml;
                    }

                    // Saat menggunakan fungsi createTableHtml dan menginisialisasi DataTable
                    $(document).ready(function() {
                        var data = [ /* array atau objek data */ ];
                        var title = "Table Title";

                        // 1. Generate HTML tabel
                        var tableHtml = createTableHtml(data, title);

                        // 2. Masukkan tabel ke dalam DOM
                        $('#tableContainer').html(tableHtml);

                        // 3. Inisialisasi DataTable setelah tabel ada di DOM
                        $('.scheduleTable').DataTable({
                            paging: false,
                            searching: false,
                        });
                    });


                    // $('#detailTypeHeader').text('Detail Information');
                    $('#detailContent').empty();

                    try {
                        var content = '';

                        if (hotel && hotel !== 'undefined') {
                            var hotelData = typeof hotel === 'string' ? JSON.parse(hotel) : hotel;
                            content += createTableHtml(hotelData, 'Hotel Detail');
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

            function redirectToExportExcel() {
                const route = "{{ route('hotel.excel') }}";

                const startDate = document.getElementById("start_date").value;
                const endDate = document.getElementById("end_date").value;

                // Create a form element
                const form = document.createElement("form");
                form.method = "GET";
                form.action = route;

                const startDateInput = document.createElement("input");
                startDateInput.type = "hidden";
                startDateInput.name = "start_date";
                startDateInput.value = startDate;

                const endDateInput = document.createElement("input");
                endDateInput.type = "hidden";
                endDateInput.name = "end_date";
                endDateInput.value = endDate;

                form.appendChild(startDateInput);
                form.appendChild(endDateInput);

                // Append the form to the body and submit it
                document.body.appendChild(form);
                form.submit();
            }

            const bookButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            bookButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const htlNumber = this.getAttribute('data-no-htl');
                    const idNumber = this.getAttribute('data-no-id');

                    document.getElementById('book_no_htl').textContent = htlNumber;
                    document.getElementById('book_no_id').value = idNumber; // Mengisi input no_id
                });
            });
        </script>
    @endsection
