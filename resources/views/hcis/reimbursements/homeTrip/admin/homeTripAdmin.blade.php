@extends('layouts_.vertical', ['page_title' => 'Medical (Admin)'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css', 'node_modules/daterangepicker/daterangepicker.css', 'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css', 'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css', 'node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css', 'node_modules/flatpickr/dist/flatpickr.min.css'])
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
                    <a href="{{ route('reimbursements') }}" class="mb-2 mt-2 me-4 page-title"><i class="ri-arrow-left-circle-line"></i></a>
                    <a href="{{ route('ticket.admin', ['tkt_type' => 'Cuti']) }}" class="mb-2 mt-2 btn btn-primary"> <i class="bi bi-file-earmark-post"></i> Report  </a>
                    <button id="hometrip-add-plafond-button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#hometrip-plafond-add" type="button" title="Add Plafond"
                        data-admin-id="{{ auth()->user()->employee->id }}"
                    >
                        <i class="ri-add-box-line"></i> Add Plafond
                    </button>
                </div>
            </div>
        </div>
        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="{{ route('home-trip.admin') }}" method="GET">
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

                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Employee Name</label>
                                        <input type="text" name="customsearch" id="customsearch" value="{{ request()->get('customsearch') }}" class="form-control"
                                            placeholder="Employee Name">
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
        <div class="row" id="tableFilter">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title">{{ $link }}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th class="text-center">No</th>
                                        <th class="text-center">NIK</th>
                                        <th class="text-center">Employee ID</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Join Date</th>
                                        <th class="text-center">Family Count</th>
                                        <th class="text-center">Period</th>
                                        <th class="text-center">Ticket Count</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (request()->get('stat') == '-')
                                    @else
                                        @foreach ($ht_employee as $index => $ht_employees)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">{{ $ht_employees->ktp }}</td>
                                                <td class="text-center">{{ $ht_employees->employee_id }}</td>
                                                <td class="text-center">{{ $ht_employees->fullname }}</td>
                                                <td class="text-center">{{ $ht_employees->date_of_joining }}</td>
                                                <td class="text-center">{{ $ht_employees->family_count }}</td>
                                                <td class="text-center">{{ $ht_employees->latest_period }}</td>
                                                <td id="hometrip-plafond-{{ $index }}" class="text-center">{{ $ht_employees->ticket_count }}</td>
                                                <td class="text-center">
                                                    <button id="hometrip-plafond-button-submit-{{ $index }}" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#hometrip-plafond-edit" type="button" title="Adjust Plafond"
                                                        data-index="{{ $index }}"
                                                        data-admin-id="{{ auth()->user()->employee->id }}"
                                                        data-employee-id="{{ $ht_employees->employee_id }}"
                                                        data-employee-name="{{ $ht_employees->fullname }}"
                                                        data-period="{{ $ht_employees->latest_period }}"
                                                        data-quota="{{ $ht_employees->ticket_count }}"
                                                    >
                                                        <i class="ri-add-box-line"></i>
                                                    </button>
                                                    <a href="{{ route('home-trip.detail', encrypt($ht_employees->employee_id)) }}"
                                                        class="btn btn-outline-primary" title="View Detail">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hometrip-plafond-add" tabindex="-1" aria-labelledby="hometrip-plafond-add-title" aria-hidden="true">
        <div class="modal-dialog">
            <form id="hometrip-plafond-add-form" method="POST" action="{{ route('home-trip.admin.add-plafond') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="hometrip-plafond-add-title">Add Plafond</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="hometrip-plafond-add-input-admin-id" name="admin_id" value="{{ auth()->user()->employee->id }}">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Period</label>
                            <select id="hometrip-plafond-add-input-period" name="period" class="form-select" required>
                                <option value="">Select period...</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Employee</label>
                            <select id="hometrip-plafond-add-input-employee-id" name="employee_id" class="form-select" disabled required>
                                <option value="">Select employee...</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Quota</label>
                            <input id="hometrip-plafond-add-input-quota" type="number" name="quota" class="form-control" step="1" min="0" disabled required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="hometrip-plafond-add-submit" type="submit" class="btn btn-primary" disabled>Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="hometrip-plafond-edit" tabindex="-1" aria-labelledby="hometrip-plafond-edit" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <form id="hometrip-plafond-edit-form" method="POST" action="{{ route('home-trip.admin.update-plafond') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="hometrip-plafond-edit-title">Adjust Plafond</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input id="hometrip-plafond-edit-input-data-index" type="hidden" name="hometrip-plafond-edit-input-data-index" disabled>
                            <input id="hometrip-plafond-edit-input-admin-id" type="hidden" name="hometrip-plafond-edit-input-admin-id" disabled>
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="hometrip-plafond-edit-input-employee-id">Employee ID</label>
                                <input id="hometrip-plafond-edit-input-employee-id" type="text" name="hometrip-plafond-edit-input-employee-id" class="form-control" disabled>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="hometrip-plafond-edit-input-employee-name">Employee Name</label>
                                <input id="hometrip-plafond-edit-input-employee-name" type="text" name="hometrip-plafond-edit-input-employee-name" class="form-control" disabled>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="hometrip-plafond-edit-input-period">Period</label>
                                <input id="hometrip-plafond-edit-input-period" type="text" name="hometrip-plafond-edit-input-period" class="form-control" disabled>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="hometrip-plafond-edit-input-quota">Quota</label>
                                <input id="hometrip-plafond-edit-input-quota" type="number" name="hometrip-plafond-edit-input-quota" class="form-control" step="1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="hometrip-plafond-edit-submit" type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('hcis.reimbursements.medical.navigation.modalMedical')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
@endsection
@section('script')
    {{-- @vite(['resources/js/pages/demo.form-advanced.js']) --}}

    <!-- Include jQuery -->
    {{-- <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script> --}}

    <!-- Include Bootstrap Date Range Picker -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{-- <script src="{{ asset('/js/cashAdvanced/adminPage.js') }}"></script> --}}
    {{-- @include('js.hcis.cashAdvanced.adminPage') --}}
@endsection
@push('scripts')
    <script>
        // Periksa apakah ada pesan sukses
        var successMessage = "{{ session('success') }}";

        // Jika ada pesan sukses, tampilkan sebagai alert
        if (successMessage) {
            Swal.fire({
                title: "Success!",
                text: successMessage,
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: "Ok",
            });
        }

        function redirectToExportExcel() {
            const route = "{{ route('exportmed.excel') }}";

            // Ambil nilai dari input
            const stat = document.getElementById("stat").value;
            const customSearch = document.getElementById("customsearch").value;

            // Buat elemen form
            const form = document.createElement("form");
            form.method = "GET";
            form.action = route;

            // Tambahkan input tersembunyi untuk setiap parameter
            const statInput = document.createElement("input");
            statInput.type = "hidden";
            statInput.name = "stat";
            statInput.value = stat;

            const customSearchInput = document.createElement("input");
            customSearchInput.type = "hidden";
            customSearchInput.name = "customsearch";
            customSearchInput.value = customSearch;

            // Tambahkan input ke form
            form.appendChild(statInput);
            form.appendChild(customSearchInput);

            // Tambahkan form ke body dan kirim
            document.body.appendChild(form);
            form.submit();
        }

        // Event listener untuk menangkap date range yang dipilih
        $("#singledaterange").on("apply.daterangepicker", function(ev, picker) {
            var startDate = picker.startDate.format("YYYY-MM-DD");
            var endDate = picker.endDate.format("YYYY-MM-DD");

            // Panggil fungsi untuk mendapatkan data yang difilter
            filterTableByDateRange(startDate, endDate);
        });

        // Fungsi untuk mengirimkan tanggal yang dipilih ke server dan memperbarui tabel
        function filterTableByDateRange(startDate, endDate) {
            $.ajax({
                url: '{{ route('cashadvanced.admin') }}', // Route yang sudah Anda buat
                type: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                },
                success: function(response) {
                    // Perbarui tabel dengan data yang difilter
                    // $('#scheduleTable tbody').html(response);
                    // $('#tableFilter').html(response);
                },
                error: function(xhr) {
                    // console.error(xhr);
                },
            });
        }
        //script modal
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalElem = document.getElementById('hometrip-plafond-add');

            const employeeSelect = $('#hometrip-plafond-add-input-employee-id');
            const periodSelect = $('#hometrip-plafond-add-input-period');
            const quotaInput = $('#hometrip-plafond-add-input-quota');
            const submitButton = $('#hometrip-plafond-add-submit');

            const eligibleEmployeeUrl = "{{ route('home-trip.admin.get-eligible-employees') }}";
            const eligiblePeriodUrl = "{{ route('home-trip.admin.get-eligible-periods') }}";

            function resetForm() {
                periodSelect.html('<option value="">Select Period...</option>').prop('disabled', true);
                employeeSelect.html('<option value="">Select Employee...</option>').prop('disabled', true);
                quotaInput.val('').prop('disabled', true);
                submitButton.prop('disabled', true);
            }

            modalElem.addEventListener('show.bs.modal', function (event) {
                resetForm();

                var button = event.relatedTarget;
                var dataAdminID = button.getAttribute('data-admin-id');

                document.getElementById("hometrip-plafond-add-input-admin-id").value = dataAdminID;

                $.ajax({
                    url: eligiblePeriodUrl,
                    type: 'GET',
                    dataType: 'json',

                    success: function (response) {
                        periodSelect.empty();

                        if (!response.status || !response.data || response.data.length === 0) {
                            periodSelect.html('<option value="">No period available</option>');

                            Swal.fire({
                                icon: 'warning',
                                title: 'No Eligible Period',
                                text: 'Cannot add plafond because there are no eligible period available.'
                            });

                            return;
                        }

                        periodSelect.append('<option value="">Select Period...</option>');

                        $.each(response.data, function (index, period) {
                            periodSelect.append(`
                                <option value="${period}">
                                    ${period}
                                </option>
                            `);
                        });

                        periodSelect.prop('disabled', false);
                    },

                    error: function () {
                        periodSelect.html('<option value="">Failed to load period</option>');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to retrieve period list.'
                        });
                    }
                });
            });

            $('#hometrip-plafond-add-form').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitButton = $('#hometrip-plafond-add-submit');
                const dataAdminID = $('#hometrip-plafond-add-input-admin-id').val();

                submitButton.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        actor_id: dataAdminID,
                        employee_id: dataEmployeeID,
                        period: dataPeriod,
                        quota: dataQuota
                    },
                    dataType: 'json',

                    success: function (response) {
                        if (response.status) {
                            $('#hometrip-plafond-add').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Plafond successfully added.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                //
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: response.message || 'Failed to add plafond.'
                            });

                            submitButton.prop('disabled', false).text('Submit');
                        }
                    },

                    error: function (xhr) {
                        let message = 'An error occurred while processing your request.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            if (xhr.responseJSON.errors) {
                                message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message
                        });
                    },

                    complete: function () {
                        submitButton.prop('disabled', false).text('Submit');
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalElem = document.getElementById('hometrip-plafond-edit');

            modalElem.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var dataIndex = button.getAttribute('data-index');
                var dataAdminID = button.getAttribute('data-admin-id');
                var dataEmployeeID = button.getAttribute('data-employee-id');
                var dataEmployeeName = button.getAttribute('data-employee-name');
                var dataPeriod = button.getAttribute('data-period');
                var dataQuota = button.getAttribute('data-quota');

                document.getElementById("hometrip-plafond-edit-input-data-index").value = dataIndex;
                document.getElementById("hometrip-plafond-edit-input-admin-id").value = dataAdminID;
                document.getElementById("hometrip-plafond-edit-input-employee-id").value = dataEmployeeID;
                document.getElementById("hometrip-plafond-edit-input-employee-name").value = dataEmployeeName;
                document.getElementById("hometrip-plafond-edit-input-period").value = dataPeriod;
                document.getElementById("hometrip-plafond-edit-input-quota").value = dataQuota;
            });

            $('#hometrip-plafond-edit-form').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitButton = $('#hometrip-plafond-edit-submit');
                const dataIndex = $('#hometrip-plafond-edit-input-data-index').val();
                const dataAdminID = $('#hometrip-plafond-edit-input-admin-id').val();
                const dataEmployeeID = $('#hometrip-plafond-edit-input-employee-id').val();
                const dataPeriod = $('#hometrip-plafond-edit-input-period').val();
                const dataQuota = $('#hometrip-plafond-edit-input-quota').val();

                submitButton.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        actor_id: dataAdminID,
                        employee_id: dataEmployeeID,
                        period: dataPeriod,
                        quota: dataQuota
                    },
                    dataType: 'json',

                    success: function (response) {
                        if (response.status) {
                            $('#hometrip-plafond-edit').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Plafond successfully adjusted.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $(`#hometrip-plafond-button-submit-${dataIndex}`).attr('data-quota', dataQuota);
                                document.getElementById("hometrip-plafond-" + dataIndex).innerHTML = dataQuota;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: response.message || 'Failed to adjust plafond.'
                            });

                            submitButton.prop('disabled', false).text('Submit');
                        }
                    },

                    error: function (xhr) {
                        let message = 'An error occurred while processing your request.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            if (xhr.responseJSON.errors) {
                                message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message
                        });
                    },

                    complete: function () {
                        submitButton.prop('disabled', false).text('Submit');
                    }
                });
            });
        });
    </script>
@endpush
