@extends('layouts_.vertical', ['page_title' => 'Cash Advanced'])

@section('css')
    <!-- Sertakan CSS Bootstrap jika diperlukan -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/css/bootstrap.min.css">
    <style>
        input[type="date"][readonly] {
            pointer-events: all;
            background-color: #fff; /* Pastikan tampilan tetap seperti input normal */
            cursor: pointer;
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
                            <li class="breadcrumb-item"><a href="{{ route('cashadvanced') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="d-sm-flex align-items-center justify-content-center">
            <div class="card col-md-12">
                <div class="card-header d-flex bg-primary justify-content-between">
                    <h4 class="modal-title text-white" id="viewFormEmployeeLabel">Add Data</h4>
                    <a href="{{ route('cashadvanced') }}" type="button" class="btn btn-close btn-close-white"></a>
                </div>
                <div class="card-body" @style('overflow-y: auto;')>
                    <div class="container-fluid">
                        <form id="cashadvancedForm" method="post" action="{{ route('cashadvanced.submit') }}">@csrf
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="start">Employee ID</label>
                                    <input type="text" name="name" id="name"
                                        value="{{ $employee_data->employee_id }}" class="form-control bg-light"
                                        style="cursor: none;" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="start">Employee Name</label>
                                    <input type="text" name="name" id="name"
                                        value="{{ $employee_data->fullname }}" class="form-control bg-light"
                                        style="cursor: none;" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="start">Unit</label>
                                    <input type="text" name="unit" id="unit" value="{{ $employee_data->unit }}"
                                        class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="start">Job Level</label>
                                    <input type="text" name="grade" id="grade"
                                        value="{{ $employee_data->job_level }}" class="form-control bg-light" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="ca_type">CA Type</label>
                                    <select name="ca_type" id="ca_type" class="form-control" onchange="toggleDivs()">
                                        <option value="">-</option>
                                        {{-- <option value="dns">Business Travel</option> --}}
                                        <option value="ndns">Non Business Travel</option>
                                        <option value="entr">Entertainment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="name">Costing Company</label>
                                    <select class="form-control select2" id="companyFilter" name="companyFilter" required>
                                        <option value="">Select Company...</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->contribution_level_code }}">
                                                {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="locationFilter">Destination</label>
                                    <select class="form-control select2" id="locationFilter" name="locationFilter"
                                        onchange="toggleOthers()" required>
                                        <option value="">Select location...</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->area }}">
                                                {{ $location->area . ' (' . $location->company_name . ')' }}</option>
                                        @endforeach
                                        <option value="Others">Others</option>
                                    </select>
                                    <br><input type="text" name="others_location" id="others_location"
                                        class="form-control mt-3" placeholder="Other Location" value=""
                                        style="display: none;">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label" for="name">CA Purposes</label>
                                    <textarea name="ca_needs" id="ca_needs" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label" for="start">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        placeholder="mm/dd/yyyy" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label" for="start">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        placeholder="mm/dd/yyyy" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label" for="start">Total Days</label>
                                    <div class="input-group">
                                        <input class="form-control bg-light" id="totaldays" name="totaldays"
                                            type="text" min="0" value="0" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                    <input class="form-control" id="perdiem" name="perdiem" type="hidden" value="{{ $perdiem->amount }}" readonly>
                                        {{-- value="{{ $perdiem->amount }}" readonly> --}}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="start">CA Date Required</label>
                                    <span class="ri-information-line" title="If there are changes, please contact the AR&AP team" onclick="showPopup(this)"></span>
                                    <input type="date" name="ca_required" id="ca_required" class="form-control"
                                        placeholder="mm/dd/yyyy" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Declaration Estimate</label>
                                        <input type="date" name="ca_decla" id="ca_decla" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6 mb-2" id="div_bisnis_numb_dns" style="display: none;">
                                <label class="form-label" for="name">Business Travel Number</label>
                                <select class="form-control select2" id="bisnis_numb_dns" name="bisnis_numb_dns">
                                    <option value="">Select</option>
                                    @foreach ($no_sppds as $no_sppd)
                                        @if (!$noSppdListENT->contains($no_sppd->no_sppd)) <!-- Hanya tampilkan jika tidak ada di $noSppdListENT -->
                                            <option value="{{ $no_sppd->no_sppd }}">
                                                {{ $no_sppd->no_sppd }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="row" id="div_bisnis_numb_ent" style="display: none;">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label" for="name">Business Travel Number</label>
                                    <select class="form-control select2" id="bisnis_numb_ent" name="bisnis_numb_ent">
                                        <option value="">Select</option>
                                        @foreach ($no_sppds as $no_sppd)
                                            @if (!$noSppdListENT->contains($no_sppd->no_sppd)) <!-- Hanya tampilkan jika tidak ada di $noSppdListENT -->
                                                <option value="{{ $no_sppd->no_sppd }}">
                                                    {{ $no_sppd->no_sppd }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <br>

                            <div class="row" id="ca_bt" style="display: none;">
                                <div class="col-md-12">
                                    <div class="table-responsive-sm">
                                        <div class="d-flex flex-column gap-2">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="pills-perdiem-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-perdiem" type="button"
                                                        role="tab" aria-controls="pills-perdiem"
                                                        aria-selected="true">Perdiem</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-transport-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-transport" type="button" role="tab"
                                                        aria-controls="pills-transport" aria-selected="false">Transport
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-accomodation-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-accomodation"
                                                        type="button" role="tab" aria-controls="pills-accomodation"
                                                        aria-selected="false">Accomodation</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-other-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-other" type="button" role="tab"
                                                        aria-controls="pills-other" aria-selected="false">Other</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel"
                                                    aria-labelledby="pills-perdiem-tab">
                                                    @include('hcis.reimbursements.cashadv.form.perdiem')
                                                </div>
                                                <div class="tab-pane fade" id="pills-transport" role="tabpanel"
                                                    aria-labelledby="pills-transport-tab">
                                                    @include('hcis.reimbursements.cashadv.form.transport')
                                                </div>
                                                <div class="tab-pane fade" id="pills-accomodation" role="tabpanel"
                                                    aria-labelledby="pills-accomodation-tab">
                                                    @include('hcis.reimbursements.cashadv.form.penginapan')
                                                </div>
                                                <div class="tab-pane fade" id="pills-other" role="tabpanel"
                                                    aria-labelledby="pills-other-tab">
                                                    @include('hcis.reimbursements.cashadv.form.others')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="ca_nbt" style="display: none;">
                                <div class="col-md-12">
                                    <div class="table-responsive-sm">
                                        <div class="d-flex flex-column">
                                            @include('hcis.reimbursements.cashadv.form.nbt')
                                            <button type="button" id="add-more" style="display: none"
                                                class="btn btn-primary mt-3">Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="ca_e" style="display: none;">
                                <div class="col-md-12">
                                    <div class="table-responsive-sm">
                                        <div class="d-flex flex-column gap-2">
                                            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="pills-detail-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-detail" type="button"
                                                        role="tab" aria-controls="pills-detail"
                                                        aria-selected="true">Detail Entertainment</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-relation-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-relation" type="button" role="tab"
                                                        aria-controls="pills-relation" aria-selected="false">Detail Receiver
                                                    </button>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="pills-detail" role="tabpanel"
                                                    aria-labelledby="pills-detail-tab">
                                                    @include('hcis.reimbursements.cashadv.form.detail')
                                                </div>
                                                <div class="tab-pane fade" id="pills-relation" role="tabpanel"
                                                    aria-labelledby="pills-relation-tab">
                                                    @include('hcis.reimbursements.cashadv.form.relation')
                                                </div>
                                            </div>
                                            <button type="button" id="add-more-e-detail" style="display: none"
                                                class="btn btn-primary mt-3">Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label">Total Cash Advanced</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input class="form-control bg-light" name="totalca" id="totalca"
                                            type="text" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="p-3 col-md d-md-flex justify-content-end text-center">
                            <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                            <a href="{{ route('cashadvanced') }}" type="button" class="btn mb-2 btn-outline-secondary px-4 me-2">Cancel</a>
                            <button type="submit" name="action_ca_draft" value="Draft" class="btn mb-2 btn-secondary btn-pill px-4 me-2 submit-button">Draft</button>
                            <button type="submit" name="action_ca_submit" value="Pending" class="btn mb-2 btn-primary btn-pill px-4 me-2 submit-button">Submit</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    @include('hcis.reimbursements.cashadv.navigation.modalCashadv')
@endsection
<!-- Tambahkan script JavaScript untuk mengumpulkan nilai repeat_days[] -->
@push('scripts')
    <script>
        // Disable manual typing on input fields
        document.querySelectorAll('input[type="date"]').forEach(function (input) {
            input.addEventListener('keydown', function (e) {
                e.preventDefault(); // Prevent manual typing
            });
        });
    </script>
    <script>
        document.getElementById('start_date').addEventListener('input', function () {
            const startDate = new Date(this.value);
            const caRequiredField = document.getElementById('ca_required');

            caRequiredField.value = '';

            if (startDate) {
                startDate.setDate(startDate.getDate() - 2);
                const minDate = startDate.toISOString().split('T')[0];
                caRequiredField.setAttribute('min', minDate);
                caRequiredField.value = minDate;
            }
        });

    </script>
    <script>
        function cleanNumber(value) {
            return parseFloat(value.replace(/\./g, '').replace(/,/g, '')) || 0;
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatNumberPerdiem(num) {
            return num.toLocaleString('id-ID');
        }

        function parseNumberPerdiem(value) {
            return parseFloat(value.replace(/\./g, '').replace(/,/g, '')) || 0;
        }

        function parseNumber(value) {
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        function formatInput(input) {
            let value = input.value.replace(/\./g, '');
            value = parseFloat(value);
            if (!isNaN(value)) {
                input.value = formatNumber(Math.floor(value));
            } else {
                input.value = formatNumber(0);
            }
            calculateTotalNominalBTPerdiem();
            calculateTotalNominalBTTransport();
            calculateTotalNominalBTPenginapan();
            calculateTotalNominalBTLainnya();
            calculateTotalNominalBTTotal();
        }

        function calculateTotalNominalBTTotal() {
            let total = 0;
            document.querySelectorAll('input[name="total_bt_perdiem"]').forEach(input => {
                total += parseNumber(input.value);
            });
            document.querySelectorAll('input[name="total_bt_transport"]').forEach(input => {
                total += parseNumber(input.value);
            });
            document.querySelectorAll('input[name="total_bt_penginapan"]').forEach(input => {
                total += parseNumber(input.value);
            });
            document.querySelectorAll('input[name="total_bt_lainnya"]').forEach(input => {
                total += parseNumber(input.value);
            });
            document.querySelector('input[name="totalca"]').value = formatNumber(total);
        }

    </script>
    <script>
        function toggleDivs() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");
            var div_bisnis_numb_dns = document.getElementById("div_bisnis_numb_dns");
            var div_bisnis_numb_ent = document.getElementById("div_bisnis_numb_ent");

            if (ca_type.value === "dns") {
                ca_bt.style.display = "block";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "block";
                div_bisnis_numb_ent.style.display = "none";
            } else if (ca_type.value === "ndns"){
                ca_bt.style.display = "none";
                ca_nbt.style.display = "block";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "none";
            } else if (ca_type.value === "entr"){
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "block";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "block";
            } else{
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "none";
            }
        }

        function toggleOthers() {
            // ca_type ca_nbt ca_e
            var locationFilter = document.getElementById("locationFilter");
            var others_location = document.getElementById("others_location");

            if (locationFilter.value === "Others") {
                others_location.style.display = "block";
            } else {
                others_location.style.display = "none";
                others_location.value = "";
            }
        }

        function validateInput(input) {
            //input.value = input.value.replace(/[^0-9,]/g, '');
            input.value = input.value.replace(/[^0-9]/g, '');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const totalDaysInput = document.getElementById('totaldays');

            function calculateTotalDays() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                // Memastikan kedua tanggal valid
                if (startDate && endDate && !isNaN(startDate) && !isNaN(endDate)) {
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                    const totalDays = daysDiff >= 0 ? daysDiff + 1 : 0; // Menambahkan 1 untuk menghitung hari awal
                    totalDaysInput.value = totalDays;
                } else {
                    totalDaysInput.value = 0; // Mengatur ke 0 jika tidak valid
                }
            }

            // Menambahkan event listener untuk perubahan di input tanggal
            startDateInput.addEventListener('change', calculateTotalDays);
            endDateInput.addEventListener('change', calculateTotalDays);
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const holidays = {!! json_encode($holiday) !!};
            const endDate = new Date(this.value);
            const declarationEstimateDate = new Date(endDate);
            // Menambahkan 3 hari kerja
            let daysToAdd = 0;
            while (daysToAdd < 3) {
                declarationEstimateDate.setDate(declarationEstimateDate.getDate() + 1);
                const dayOfWeek = declarationEstimateDate.getDay();
                const declarationDateString = declarationEstimateDate.toISOString().split('T')[0]; // Format YYYY-MM-DD

                // Cek apakah tanggal adalah hari Sabtu (6) atau Minggu (0)
                if (dayOfWeek !== 6 && dayOfWeek !== 0 && !holidays.includes(declarationDateString)) {
                    daysToAdd++;
                }
            }

            const year = declarationEstimateDate.getFullYear();
            const month = String(declarationEstimateDate.getMonth() + 1).padStart(2, '0');
            const day = String(declarationEstimateDate.getDate()).padStart(2, '0');

            document.getElementById('ca_decla').value = `${year}-${month}-${day}`;
        });
    </script>
    <script>
        document.getElementById('start_date').addEventListener('change', handleDateChange);
        document.getElementById('end_date').addEventListener('change', handleDateChange);

        function handleDateChange() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            // Set the min attribute of the end_date input to the selected start_date
            endDateInput.min = startDateInput.value;

            // Validate dates
            if (endDate < startDate) {
                Swal.fire({
                    title: 'Cannot Sett Date!',
                    text: 'End Date cannot be earlier than Start Date.',
                    icon: 'warning',
                    confirmButtonColor: "#9a2a27",
                    confirmButtonText: 'Ok',
                });
                endDateInput.value = "";
            }

            // Update min and max values for all dynamic perdiem date fields
            document.querySelectorAll('input[name="start_bt_perdiem[]"]').forEach(function(input) {
                input.min = startDateInput.value;
                input.max = endDateInput.value;
            });

            document.querySelectorAll('input[name="end_bt_perdiem[]"]').forEach(function(input) {
                input.min = startDateInput.value;
                input.max = endDateInput.value;
            });

            document.querySelectorAll('input[name="total_days_bt_perdiem[]"]').forEach(function(input) {
                calculateTotalDaysPerdiem(input);
            });
        }

        function showPopup(element) {
            const message = element.getAttribute("title");
            Swal.fire({
                icon: 'info',
                title: 'Information',
                text: message,
                confirmButtonText: 'OK'
            });
        }
    </script>
    <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <link href="{{ asset('vendor/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/select2/dist/js/select2.min.js') }}"></script>
@endpush
