@extends('layouts_.vertical', ['page_title' => 'Medical (Admin)'])

@section('css')
    <style>
        #example_filter {
            margin-bottom: 20px;
            /* Adjust as needed */
        }

        th {
            color: white !important;
            text-align: center;
        }

        table {
            white-space: nowrap;
            width: 100%;
        }

        tr.sticky {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--stickyBackground);
        }

        th.sticky,
        td.sticky {
            position: sticky;
            left: 0;
            background: var(--stickyBackground);
        }

        table.dataTable>tbody>tr.child ul.dtr-details {
            width: 100%;
            vertical-align: middle !important;
        }

        table.dataTable>tbody>tr.child ul.dtr-details>li {
            display: flex;
            align-items: center !important;
        }

        table.dataTable>tbody>tr.child span.dtr-title {
            min-width: 120px !important;
            max-width: 120px !important;
            text-wrap: wrap !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Breadcrumb Section -->
            <div class="col-md-6 mb-2 d-flex align-items-center">
                <ol class="breadcrumb mb-0" style="align-items: center; padding-left: 0;">
                    <li class="breadcrumb-item">
                        <a href="/reimbursements">
                            {{ $parentLink }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="/medical/admin">
                            {{ $sublink }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ $link }}
                    </li>
                </ol>
            </div>
            @include('hcis.reimbursements.medical.navigation.modalMedical')
            @include('hcis.reimbursements.businessTrip.modal')

            @if (request()->routeIs('medical.detail'))
                <div class="col-md-6 mb-2 d-flex justify-content-center justify-content-md-end align-items-center">
                    <a href="{{ route('exportmed-detail.excel', $employee_id) }}"
                        class="btn btn-outline-success rounded-pill btn-action me-1">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export to Excel
                    </a>
                    <a href="{{ route('medical-form.add-admin', encrypt($employee_id)) }}" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-circle"></i> Add Medical
                    </a>
                </div>
            @endif
        </div>

        @if (request()->routeIs('medical.confirmation'))
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="{{ route('medical.confirmation') }}" method="GET">
                                <div class="container-fluid p-2">
                                    <div class="row align-items-end g-1">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">Unit Location:</label>
                                            <select class="form-select select2" aria-label="Status" id="stat"
                                                name="stat">
                                                <option value="" {{ request()->get('stat') == '-' ? 'selected' : '' }}>All
                                                    Location</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->work_area }}"
                                                        {{ $location->work_area == request()->get('stat') ? 'selected' : '' }}>
                                                        {{ $location->area . ' (' . $location->company_name . ')' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-2">
                                            <button class="btn btn-primary w-100" type="submit">Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="card shadow-none p-1 py-3 px-2">
                @if (request()->routeIs('medical.detail'))
                    <div class="d-flex justify-content-center">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                    aria-selected="true">History</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-profile" type="button" role="tab"
                                    aria-controls="pills-profile" aria-selected="false">Plafon Medical</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-contact" type="button" role="tab"
                                    aria-controls="pills-contact" aria-selected="false">Family Data</button>
                            </li>
                        </ul>
                    </div>
                @endif
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        @include('hcis.reimbursements.medical.admin.historyMedicalAdmin')
                    </div>
                    @if (request()->routeIs('medical.detail'))
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                            @include('hcis.reimbursements.medical.admin.plafonMedicalAdmin')
                        </div>
                        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                            @include('hcis.reimbursements.medical.admin.familyDataAdmin')
                        </div>
                    @endif
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

    <script src="{{ asset('/js/medical/medical.js') }}"></script>
    <script>
        var isConfirmationRoute = @json(request()->routeIs('medical.confirmation'));
        //medical table
        $("#example").DataTable({
            responsive: {
                details: {
                    type: "column",
                    target: "tr",
                },
            },
            columnDefs: [
                {
                    className: "control",
                    orderable: false,
                    targets: 0,
                },
            ],
            order: [1, "asc"],
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
        });
    </script>
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
                if (!dateTimeString) return 'N/A';
                var date = new Date(dateTimeString);
                var day = ('0' + date.getDate()).slice(-2);
                var month = ('0' + (date.getMonth() + 1)).slice(-2);
                var year = date.getFullYear();
                var hours = ('0' + date.getHours()).slice(-2);
                var minutes = ('0' + date.getMinutes()).slice(-2);
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            window.showRejectInfo = function(transactionId) {
                var rejectMedic = {!! json_encode($rejectMedic) !!};
                var employeeName = {!! json_encode($employees) !!};

                var rejectionInfo = rejectMedic[transactionId];
                if (rejectionInfo) {
                    var rejectedBy = employeeName[rejectionInfo.rejected_by] || 'N/A';
                    document.getElementById('rejectedBy').textContent = ': ' + rejectedBy;
                    document.getElementById('rejectionReason').textContent = ': ' + (rejectionInfo
                        .reject_info || 'N/A');

                    // Use rejected_at instead of approved_at
                    var rejectionDate = formatDate(rejectionInfo.rejected_at);
                    document.getElementById('rejectionDate').textContent = ': ' + rejectionDate;
                    rejectModal.show();
                } else {
                    console.error('Rejection information not found for transaction ID:', transactionId);
                }
            };

            document.getElementById('rejectReasonModal').addEventListener('hidden.bs.modal', function() {
                // console.log('Modal closed');
            });
        });
    </script>
@endsection
