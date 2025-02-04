@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">

    {{-- <style>
        table {
            white-space: nowrap;
        }

        .table-responsive.table-container {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        .table-responsive.table-container table {
            margin-top: 0 !important;
        }

        .table-responsive.table-container thead tr:first-child th {
            border-top: none;
        }
    </style> --}}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('businessTrip.approval') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Declaration Data - {{ $n->no_sppd }}</h4>
                        <a href="{{ route('businessTrip.approval') }}" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="card-body">
                        <form action="/businessTrip/declaration/update/{{ $n->id }}" method="POST" id="btEditForm"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('hcis.reimbursements.businessTrip.modal')
                            <!-- Employee Data Table -->
                            <div class="row">
                                <div class="col-md-6">
                                    <table width="100%" class="">
                                        <tr>
                                            <th width="40%">Employee ID</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Employee Name</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->fullname }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unit</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->unit }}</td>
                                        </tr>
                                        <tr>
                                            <th>Job Level</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->job_level }}</td>
                                        </tr>
                                        <tr>
                                            <th>Designation</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->designation_name }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table width="100%">
                                        <tr>
                                            <th width="40%">Start Date</th>
                                            <td class="block">: </td>
                                            <td width="60%"> {{ date('d M Y', strtotime($n->mulai)) }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td class="block">:</td>
                                            <td> {{ date('d M Y', strtotime($n->kembali)) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Costing Company</th>
                                            <td class="block">:</td>
                                            <td> ({{ $n->bb_perusahaan }})</td>
                                        </tr>
                                        <tr>
                                            <th>Destination</th>
                                            <td class="block">:</td>
                                            <td> {{ $n->tujuan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Purposes</th>
                                            <td class="block">:</td>
                                            <td>{{ $n->keperluan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cash Advance Type</th>
                                            <td class="block">:</td>
                                            <td> Business Trip</td>
                                        </tr>
                                    </table>
                                </div>

                                @php
                                    // Provide default empty arrays if caDetail or sections are not set
                                    $detailPerdiem = $caDetail['detail_perdiem'] ?? [];
                                    $detailTransport = $caDetail['detail_transport'] ?? [];
                                    $detailPenginapan = $caDetail['detail_penginapan'] ?? [];
                                    $detailLainnya = $caDetail['detail_lainnya'] ?? [];
                                @endphp

                                <div class="col-md-12 mt-2">
                                    <div class="d-flex flex-column gap-2">
                                        <ul class="nav nav-tabs nav-pills mb-2" id="pills-tab" role="tablist">
                                            @if ($dnsTab == true)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="pills-cashAdvanced-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-cashAdvanced"
                                                        type="button" role="tab" aria-controls="pills-cashAdvanced"
                                                        aria-selected="true">Cash Advanced</button>
                                                </li>
                                            @endif
                                            @if ($entrTab == true)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-caEntertain-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-caEntertain"
                                                        type="button" role="tab" aria-controls="pills-caEntertain"
                                                        aria-selected="false">CA Entertain</button>
                                                </li>
                                            @endif
                                        </ul>
    
                                        <div class="tab-content" id="pills-tabContent">
                                            <!-- Cash Advance Content -->
                                            @include('hcis.reimbursements.businessTrip.approvalDec.btCaApprovalDec')
    
                                            <!-- CA Entertain Content -->
                                            @include('hcis.reimbursements.businessTrip.approvalDec.btEntApprovalDec')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                // Provide default empty arrays if any section is not set
                                $detailPerdiem = $caDetail['detail_perdiem'] ?? [];
                                $detailTransport = $caDetail['detail_transport'] ?? [];
                                $detailPenginapan = $caDetail['detail_penginapan'] ?? [];
                                $detailLainnya = $caDetail['detail_lainnya'] ?? [];
                                $entertainDetail = $caDetail['detail_e'] ?? [];
                                $entertainRelation = $caDetail['relation_e'] ?? [];
                            @endphp
                            <input type="hidden" id="no_sppd" value="{{ $n->no_sppd }}">
                            @php
                                $detailPerdiem2 = $declareCa['detail_perdiem'] ?? [];
                                $detailTransport2 = $declareCa['detail_transport'] ?? [];
                                $detailPenginapan2 = $declareCa['detail_penginapan'] ?? [];
                                $detailLainnya2 = $declareCa['detail_lainnya'] ?? [];
                                $entertainDetail2 = $declareCa['detail_e'] ?? [];
                                $entertainRelation2 = $declareCa['relation_e'] ?? [];
                            @endphp
                        </form>
                        @php
                            use Illuminate\Support\Facades\Storage;
                        @endphp
                        <div class="col-md-12 mb-2 mt-2">
                            @if ((isset($dns->prove_declare) && $dns->prove_declare) || (isset($entr->prove_declare) && $entr->prove_declare))
                                <input type="hidden" name="existing_prove_declare" id="existing-prove-declare" value="{{ $entr->prove_declare ?? $dns->prove_declare }}">
                                <input type="hidden" name="removed_prove_declare" id="removed-prove-declare" value="[]">

                                <!-- Preview untuk file lama -->
                                <div id="existing-files-label" style="margin-bottom: 10px; font-weight: bold;">
                                    @if ($dns->prove_declare ?? $entr->prove_declare)
                                        
                                        Uploaded Proof:
                                    @endif
                                </div>
                                <div id="existing-file-preview" class="mt-2">
                                    @if (optional($dns)->prove_declare ?? optional($entr)->prove_declare)  
                                        @php
                                            $proveDeclare = $dns->prove_declare ?? $entr->prove_declare; // Fix kondisi ternary yang salah

                                            // Cek apakah data dalam format JSON atau masih string lama
                                            if (!empty($proveDeclare)) {
                                                $decodedData = json_decode($proveDeclare, true);
                                                $existingFiles = is_array($decodedData) ? $decodedData : [$proveDeclare]; // Jika bukan array, ubah menjadi array
                                            } else {
                                                $existingFiles = [];
                                            }
                                        @endphp   

                                        <div id="existing-file-preview" class="mt-2">
                                            @if (count($existingFiles) > 1)
                                                @foreach ($existingFiles as $file)
                                                    @php $extension = pathinfo($file, PATHINFO_EXTENSION); @endphp
                                                    <div class="file-preview" data-file="{{ $file }}" style="position: relative; display: inline-block; margin: 10px;">
                                                        @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                            <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                                <img src="{{ asset($file) }}" alt="Proof Image" style="width: 100px; height: 100px; border: 1px solid rgb(221, 221, 221); border-radius: 5px; padding: 5px;">
                                                            </a>
                                                        @elseif ($extension === 'pdf')
                                                            <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                                <img src="{{ asset('images/pdf_icon.png') }}" alt="PDF File">
                                                                <p>Click to view PDF</p>
                                                            </a>
                                                        @else
                                                            <p>File type not supported.</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else  
                                                @php
                                                    $file = $existingFiles[0] ?? ''; // Ambil satu file jika hanya ada satu file
                                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                                @endphp

                                                @if (!empty($file))
                                                    <div class="file-preview" data-file="{{ $file }}" style="position: relative; display: inline-block; margin: 10px;">
                                                        @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                            <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                                <img src="{{ asset($file) }}" alt="Proof Image" style="width: 100px; height: 100px; border: 1px solid rgb(221, 221, 221); border-radius: 5px; padding: 5px;">
                                                            </a>
                                                        @elseif ($extension === 'pdf')
                                                            <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                                <img src="{{ asset('images/pdf_icon.png') }}" alt="PDF File">
                                                                <p>Click to view PDF</p>
                                                            </a>
                                                        @else
                                                            <p>File type not supported.</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif                                                          
                                        </div>
                                    @endif

                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <!-- Decline Form -->
                            <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#rejectReasonModal" style="padding: 0.5rem 1rem; margin-right: 5px">
                                Reject
                            </button>

                            <form method="POST" action="{{ route('confirm.deklarasi', ['id' => $n->id]) }}"
                                style="display: inline-block; margin-right: 5px;" class="status-form"
                                id="approve-form-{{ $n->id }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_approval"
                                    value="{{ Auth::user()->id == $n->manager_l1_id ? 'Pending L2' : 'Declaration Approved' }}">
                                <button type="button" class="btn btn-success rounded-pill approve-button"
                                    style="padding: 0.5rem 1rem;" data-id="{{ $n->id }}">
                                    Approve
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- </div> --}}

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title" id="rejectReasonModalLabel" style="color: #333; font-weight: 600;">
                        Rejection
                        Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="rejectReasonForm" method="POST"
                        action="{{ route('confirm.deklarasi', ['id' => $n->id]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_approval" value="Declaration Rejected">

                        <div class="mb-3">
                            <label for="reject_info" class="form-label" style="color: #555; font-weight: 500;">Please
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
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light rounded-4 border-0 shadow" style="border-radius: 1rem;">
                <div class="modal-body text-center p-5" style="padding: 2rem;">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 100px; color: #AB2F2B !important;"></i>
                    </div>
                    <h4 class="mb-3 fw-bold" style="font-size: 32px; color: #AB2F2B !important;">Success!</h4>
                    <p class="mb-4" id="successModalBody" style="font-size: 20px;">
                        <!-- The success message will be inserted here -->
                    </p>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        //JS TABLE
        $(document).ready(function() {
            var tableIds = [
                '#transportTable',
                '#transportTableDec',
                '#otherTable',
                '#otherTableDec',
                '#penginapanTable',
                '#penginapanTableDec'
            ];

            // Loop through each table ID
            $.each(tableIds, function(index, tableId) {
                // Check if DataTable is already initialized and destroy it
                if ($.fn.dataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy();
                }

                // Initialize DataTable
                $(tableId).DataTable({
                    paging: false,
                    info: false,
                    searching: false,
                    autoWidth: false,
                });
            });
        });


        var tableIdPerdiem = [
            '#perdiemTable',
            '#perdiemTableDec',
        ];
        tableIdPerdiem.forEach(function(id) {

            $(id).DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr',
                    },
                },
                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        responsivePriority: 1,
                        targets: 0,
                        visible: true
                    }, // Ensure the No column is visible
                    {
                        responsivePriority: 2,
                        targets: -1
                    }
                ],
                order: [1, 'asc'],
                info: false,
                paging: false,
                searching: false,
            });
        });
        // $('#otherTableDec').DataTable({
        //     paging: false,
        //     info: false,
        //     searching: false,
        //     ordering: true, // Enable sorting if needed
        //     autoWidth: false, // Prevent automatic column width adjustment
        // });


        // function confirmSubmission(event) {
        //     event.preventDefault(); // Stop the form from submitting immediately

        //     // Display a confirmation alert
        //     const userConfirmed = confirm("Are you sure you want to approve this request?");

        //     if (userConfirmed) {
        //         // If the user confirms, submit the form
        //         event.target.closest('form').submit();
        //     } else {
        //         // If the user cancels, do nothing
        //         alert("Approval cancelled.");
        //     }
        // }
        document.getElementById('rejectReasonForm').addEventListener('submit', function(event) {
            const reason = document.getElementById('reject_info').value.trim();
            if (!reason) {
                alert('Please provide a reason for rejection.');
                event.preventDefault(); // Stop form submission if no reason is provided
            }
        });

        // Add event listener to the decline button to open the modal
        document.getElementById('declineButton').addEventListener('click', function() {
            $('#rejectReasonModal').modal('show');
        });
    </script>
@endsection
