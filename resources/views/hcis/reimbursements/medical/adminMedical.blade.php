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
                    <a href="{{ route('medical.confirmation') }}" class="mb-2 mt-2 me-1 btn btn-primary"><i class="bi bi-card-checklist"></i> Confirmation</a>
                    <a href="{{ route('medical.report') }}" class="mb-2 mt-2 me-1 btn btn-primary"><i class="bi bi-file-earmark-post"></i> Report</a>
                    <a class="mb-2 mt-2 me-1 btn btn-primary" data-bs-toggle="modal" data-bs-target="#importExcelHealtCoverage" type="button"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Import from Excel</a>
                </div>
            </div>
        </div>
        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="{{ route('medical.admin') }}" method="GET">
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
                                        
                                        <th class="text-center">Employee ID</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Join Date</th>
                                        <th class="text-center">Period</th>
                                        @foreach ($master_medical as $master_medicals)
                                            <th class="text-center">{{ $master_medicals->name }}</th>
                                        @endforeach
                                        <th class="text-center">Detail Plafond</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (request()->get('stat') == '-')
                                    @else
                                        @foreach ($med_employee as $med_employees)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                
                                                <td>{{ $med_employees->employee_id }}</td>
                                                <td>{{ $med_employees->fullname }}</td>
                                                <td>{{ $med_employees->date_of_joining }}</td>
                                                <td>{{ $med_employees->period }}</td>
                                                @foreach ($master_medical as $master_medical_item)
                                                    <td class="text-center">
                                                        {{ isset($balances[$med_employees->employee_id][$master_medical_item->name]) ? 'Rp. ' . number_format($balances[$med_employees->employee_id][$master_medical_item->name], 0, ',', '.') : '-' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <a href="{{ route('medical.detail', encrypt($med_employees->employee_id)) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="ri-edit-box-line"></i>
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

    <script src="{{ asset('/js/cashAdvanced/adminPage.js') }}"></script>
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

    @if (session('refresh'))
        <script>
            // Refresh the page after 1 seconds
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        </script>
    @endif
@endpush
