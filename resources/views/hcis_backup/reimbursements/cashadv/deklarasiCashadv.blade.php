@extends('layouts_.vertical', ['page_title' => 'Cash Advanced'])

@section('css')
    <!-- Sertakan CSS Bootstrap jika diperlukan -->
    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/css/bootstrap.min.css"> --}}
    <style>
        th, td{
            vertical-align: top !important;
        }

        .table > :not(caption) > * > * {
            /* padding: 0.2rem 0.2rem; Sesuaikan padding di sini */
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
            <div class="card shadow-none">
                <div class="card-header mb-2 d-flex bg-primary justify-content-between">
                    <p></p>
                    <h4 class="modal-title text-white" id="viewFormEmployeeLabel">Cash Advance No "<b>{{ $transactions->no_ca }}</b>"</h4>
                    <a href="{{ route('cashadvanced') }}" type="button" class="btn btn-close btn-close-white"></a>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <th style="width:40%;">Employee ID</th>
                                    <td>:</td>
                                    <td>{{ $employee_data->employee_id }}</td>
                                </tr>
                                <tr>
                                    <th>Employee Name</th>
                                    <td>:</td>
                                    <td>{{ $employee_data->fullname }}</td>
                                </tr>
                                <tr>
                                    <th >Unit</th>
                                    <td >:</td>
                                    <td >{{ $employee_data->unit }}</td>
                                </tr>
                                <tr>
                                    <th >Job Level</th>
                                    <td >:</td>
                                    <td >{{ $employee_data->job_level }}</td>
                                </tr>
                                <tr>
                                    <th >Costing Company</th>
                                    <td >:</td>
                                    <td >{{ $transactions->companies->contribution_level }} ({{ $transactions->companies->contribution_level_code }})</td>
                                </tr>
                                <tr>
                                    <th >Destination</th>
                                    <td >:</td>
                                    <td >
                                        @if ($transactions->destination == 'Others')
                                            {{ $transactions->others_location }}
                                        @else
                                            {{ $transactions->destination }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th >CA Purposes</th>
                                    <td >:</td>
                                    <td >{{ $transactions->ca_needs }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 mb-2">
                            <table style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Start Date</th>
                                    <td class="block" style="border: none;">:</td>
                                    <td >{{ $transactions->start_date }}</td>
                                </tr>
                                <tr>
                                    <th >End Date</th>
                                    <td >:</td>
                                    <td >{{$transactions->end_date}}</td>
                                </tr>
                                <tr>
                                    <th >Total Date</th>
                                    <td >:</td>
                                    <td >{{ $transactions->total_days }} Days</td>
                                </tr>
                                <tr>
                                    <th >CA Date Required</th>
                                    <td >:</td>
                                    <td >{{ \Carbon\Carbon::parse($transactions->date_required)->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th >Declaration Estimate</th>
                                    <td >:</td>
                                    <td >{{ \Carbon\Carbon::parse($transactions->declare_estimate)->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th >Cash Advanced Type</th>
                                    <td >:</td>
                                    <td >
                                        @if ($transactions->type_ca == 'dns')
                                            Bussiness Trip
                                        @elseif ($transactions->type_ca == 'entr')
                                            Entertainment
                                        @else
                                            Non Business Trip
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th >No. SPPD</th>
                                    <td >:</td>
                                    <td >
                                        @if ($transactions->no_sppd == NULL)
                                            -
                                        @else
                                            {{ $transactions->no_sppd }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <form enctype="multipart/form-data" id="cashadvancedForm" method="post"
                        action="{{ route('cashadvanced.declare', encrypt($transactions->id)) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-2" style="display: none;">
                                <label class="form-label" for="type">CA Type</label>
                                <select name="ca_type_disabled" id="ca_type" class="form-control bg-light" disabled>
                                    <option value="">-</option>
                                    <option value="dns" {{ $transactions->type_ca == 'dns' ? 'selected' : '' }}>
                                        Business Trip
                                    </option>
                                    <option value="ndns" {{ $transactions->type_ca == 'ndns' ? 'selected' : '' }}>
                                        Non Business Trip
                                    </option>
                                    <option value="entr" {{ $transactions->type_ca == 'entr' ? 'selected' : '' }}>
                                        Entertainment
                                    </option>
                                </select>
                                <input type="hidden" name="contribution_level_code" value="{{ $transactions->contribution_level_code }}">
                                <input type="hidden" name="ca_type" value="{{ $transactions->type_ca }}">
                                <input class="form-control" id="perdiem" name="perdiem" type="hidden" value="{{ $perdiem->amount }}" readonly>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    placeholder="mm/dd/yyyy" value="{{ $transactions->start_date }}">
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    placeholder="mm/dd/yyyy" value="{{ $transactions->end_date }}">
                            </div>
                            <div id="routeInfo" data-route="true"></div>
                            @php
                                $detailCA = json_decode($transactions->detail_ca, true) ?? [];
                                $declareCA = json_decode($transactions->declare_ca, true) ?? [];
                            @endphp
                            <br>
                            <div class="row" id="ca_bt" style="display: none;">
                                @if ($transactions->type_ca == 'dns')
                                    <div class="col-md-12">
                                        <div class="table-responsive-sm">
                                            <div class="d-flex flex-column">
                                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ isset($declareCA['detail_perdiem'][0]['start_date']) && $declareCA['detail_perdiem'][0]['start_date'] ? 'active' : '' }}" id="pills-perdiem-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-perdiem" type="button"
                                                            role="tab" aria-controls="pills-perdiem"
                                                            aria-selected="{{ isset($declareCA['detail_perdiem'][0]['start_date']) && $declareCA['detail_perdiem'][0]['start_date'] ? 'true' : 'false' }}">Perdiem Plan</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && isset($declareCA['detail_transport'][0]['tanggal']) && $declareCA['detail_transport'][0]['tanggal'] ? 'active' : '' }}" id="pills-transport-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-transport" type="button" role="tab"
                                                            aria-controls="pills-transport"
                                                            aria-selected="{{ isset($declareCA['detail_transport'][0]['tanggal']) && $declareCA['detail_transport'][0]['tanggal'] ? 'true' : 'false' }}">Transport Plan</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && !isset($declareCA['detail_transport'][0]['tanggal']) && isset($declareCA['detail_penginapan'][0]['start_date']) && $declareCA['detail_penginapan'][0]['start_date'] ? 'active' : '' }}" id="pills-accomodation-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-accomodation"
                                                            type="button" role="tab" aria-controls="pills-accomodation"
                                                            aria-selected="{{ isset($declareCA['detail_penginapan'][0]['start_date']) && $declareCA['detail_penginapan'][0]['start_date'] ? 'true' : 'false' }}">Accomodation Plan</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && !isset($declareCA['detail_transport'][0]['tanggal']) && !isset($declareCA['detail_penginapan'][0]['start_date']) && isset($declareCA['detail_lainnya'][0]['tanggal']) && $declareCA['detail_lainnya'][0]['tanggal'] ? 'active' : '' }}" id="pills-other-tab" data-bs-toggle="pill"
                                                            data-bs-target="#pills-other" type="button" role="tab"
                                                            aria-controls="pills-other" aria-selected="{{ isset($declareCA['detail_lainnya'][0]['tanggal']) && $declareCA['detail_lainnya'][0]['tanggal'] ? 'true' : 'false' }}">Other Plan</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content" id="pills-tabContent">
                                                    <div class="tab-pane fade {{ isset($declareCA['detail_perdiem'][0]['start_date']) && $declareCA['detail_perdiem'][0]['start_date'] ? 'show active' : '' }}"
                                                        id="pills-perdiem" role="tabpanel"
                                                        aria-labelledby="pills-perdiem-tab">
                                                        @include('hcis.reimbursements.cashadv.form_dec.perdiem')
                                                    </div>
                                                    <div class="tab-pane fade {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && isset($declareCA['detail_transport'][0]['tanggal']) && $declareCA['detail_transport'][0]['tanggal'] ? 'show active' : '' }}"
                                                        id="pills-transport" role="tabpanel"
                                                        aria-labelledby="pills-transport-tab">
                                                        @include('hcis.reimbursements.cashadv.form_dec.transport')
                                                    </div>
                                                    <div class="tab-pane fade {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && !isset($declareCA['detail_transport'][0]['tanggal']) && isset($declareCA['detail_penginapan'][0]['start_date']) && $declareCA['detail_penginapan'][0]['start_date'] ? 'show active' : '' }}"
                                                        id="pills-accomodation" role="tabpanel"
                                                        aria-labelledby="pills-accomodation-tab">
                                                        @include('hcis.reimbursements.cashadv.form_dec.penginapan')
                                                    </div>
                                                    <div class="tab-pane fade {{ !isset($declareCA['detail_perdiem'][0]['start_date']) && !isset($declareCA['detail_transport'][0]['tanggal']) && !isset($declareCA['detail_penginapan'][0]['start_date']) && isset($declareCA['detail_lainnya'][0]['tanggal']) && $declareCA['detail_lainnya'][0]['tanggal'] ? 'show active' : '' }}" id="pills-other" role="tabpanel"
                                                        aria-labelledby="pills-other-tab">
                                                        @include('hcis.reimbursements.cashadv.form_dec.others')
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            @endif
                        </div>
                        <div class="row" id="ca_nbt" style="display: none;">
                            @if ($transactions->type_ca == 'ndns')
                                <div class="col-md-12">
                                    <div class="table-responsive-sm">
                                        <div class="d-flex flex-column">
                                            @include('hcis.reimbursements.cashadv.form_dec.nbt')
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row" id="ca_e" style="display: none;">
                            @if ($transactions->type_ca == 'entr')
                                <div class="col-md-12">
                                    <div class="table-responsive-sm">
                                        <div class="d-flex flex-column">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="pills-detail-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-detail" type="button"
                                                        role="tab" aria-controls="pills-detail"
                                                        aria-selected="true">Detail Entertain Plan</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-relation-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-relation" type="button" role="tab"
                                                        aria-controls="pills-relation" aria-selected="false">Relation Entertain
                                                        Plan</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="pills-detail" role="tabpanel"
                                                    aria-labelledby="pills-detail-tab">
                                                    @include('hcis.reimbursements.cashadv.form_dec.detail')
                                                </div>
                                                <div class="tab-pane fade" id="pills-relation" role="tabpanel"
                                                    aria-labelledby="pills-relation-tab">
                                                    @include('hcis.reimbursements.cashadv.form_dec.relation')
                                                </div>
                                            </div>
                                            <button type="button" id="add-more-e-detail" style="display: none"
                                                class="btn btn-primary mt-3">Add More</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-2 mt-2">
                            <label for="prove_declare" class="form-label">Upload Document</label>

                            <input type="file" id="prove_declare" name="prove_declare[]" accept="image/*, application/pdf" class="form-control mb-2" multiple onchange="previewFiles()">
                            <input type="hidden" name="existing_prove_declare" id="existing-prove-declare" value="{{ $transactions->prove_declare }}">
                            <input type="hidden" name="removed_prove_declare" id="removed-prove-declare" value="[]">

                            <!-- Preview untuk file lama -->
                            <div id="existing-files-label" style="margin-bottom: 10px; font-weight: bold;">
                                @if ($transactions->prove_declare)
                                    
                                    Document on Draft:
                                @endif
                            </div>
                            <div id="existing-file-preview" class="mt-2">
                                @if ($transactions->prove_declare)
                                    @php
                                        $existingFiles = json_decode($transactions->prove_declare, true);
                                    @endphp

                                    @foreach ($existingFiles as $file)
                                        @php $extension = pathinfo($file, PATHINFO_EXTENSION); @endphp
                                        <div class="file-preview" data-file="{{ $file }}" style="position: relative; display: inline-block; margin: 10px;">
                                            @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'PNG', 'JPG', 'JPEG']))
                                                <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ asset($file) }}" alt="Proof Image" style="width: 100px; height: 100px; border: 1px solid rgb(221, 221, 221); border-radius: 5px; padding: 5px;">
                                                </a>
                                            @elseif($extension === 'pdf')
                                                <a href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ asset('images/pdf_icon.png') }}" alt="PDF File">
                                                    <p>Click to view PDF</p>
                                                </a>
                                            @else
                                                <p>File type not supported.</p>
                                            @endif
                                            <span class="remove-existing" data-file="{{ $file }}" style="position: absolute; top: 5px; right: 5px; cursor: pointer; background-color: #ff4d4d; color: #fff; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-weight: bold;">×</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Label untuk new file -->
                            <div id="new-files-label" style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;">
                                New Document:
                            </div>
                            <div id="new-file-preview" class="mt-2"></div>

                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Cash Advanced</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control bg-light" name="totalca_deklarasi" id="totalca_deklarasi"
                                    type="text" min="0"
                                    value="{{ number_format($transactions->total_ca, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Declaration</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control bg-light" name="totalca" id="totalca"
                                    type="text" min="0"
                                    value="{{ number_format($transactions->total_real, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2" >
                            <label class="form-label">Balance</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control bg-light" name="totalca_real" id="totalca_real"
                                    type="text"
                                    value="{{ number_format($transactions->total_cost, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="no_id" id="no_id" value="{{ $transactions->id }}"
                        class="form-control bg-light" readonly>
                    <input type="hidden" name="no_ca" id="no_ca" value="{{ $transactions->no_ca }}"
                        class="form-control bg-light" readonly>
                    <input type="hidden" name="bisnis_numb" id="bisnis_numb" value="{{ $transactions->no_sppd }}"
                        class="form-control bg-light" readonly>
                    <br>
                    <div class="row">
                        <div class="p-4 col-md d-md-flex justify-content-end text-center">
                            <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                            <a href="{{ route('cashadvanced') }}" type="button"
                                class="btn mb-2 btn-outline-secondary px-4 me-2">Cancel</a>
                            <button type="submit" name="action_ca_draft" value="Draft"
                                class=" btn mb-2 btn-secondary btn-pill px-4 me-2 declaration-button">Draft</button>
                            <button type="submit" name="action_ca_submit" value="Pending"
                                class=" btn mb-2 btn-primary btn-pill px-4 me-2 declaration-button">Submit</button>
                        </div>
                    </div>
                </form>
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
            calculateTotalNominalBTBalance();
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

        function calculateTotalNominalBTBalance() {
            const totaldec = cleanNumber(document.getElementById('totalca_deklarasi').value); // Dapatkan nilai dari input pertama
            const totalca = document.getElementById('totalca').value;
            const total = totaldec - cleanNumber(totalca);
            document.getElementById('totalca_real').value = formatNumber(total);
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");
            var bisnis_numb = document.getElementById("bisnis_numb");

            function toggleDivs() {
                if (ca_type.value === "dns") {
                    ca_bt.style.display = "block";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                } else if (ca_type.value === "ndns") {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "block";
                    ca_e.style.display = "none";
                    bisnis_numb.style.value = "";
                } else if (ca_type.value === "entr") {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "block";
                } else {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                    bisnis_numb.style.value = "";
                }
            }

            toggleDivs();
            ca_type.addEventListener("change", toggleDivs);
        });

        function toggleOthers() {
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
                    const totalDays = daysDiff > 0 ? daysDiff + 1 : 0; // Menambahkan 1 untuk menghitung hari awal
                    totalDaysInput.value = totalDays;
                } else {
                    totalDaysInput.value = 0; // Mengatur ke 0 jika tidak valid
                }
            }

            // Menambahkan event listener untuk perubahan di input tanggal
            startDateInput.addEventListener('change', calculateTotalDays);
            endDateInput.addEventListener('change', calculateTotalDays);
        });;

        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = new Date(this.value);
            const declarationEstimateDate = new Date(endDate);
            declarationEstimateDate.setDate(declarationEstimateDate.getDate() + 3);

            const year = declarationEstimateDate.getFullYear();
            const month = String(declarationEstimateDate.getMonth() + 1).padStart(2, '0');
            const day = String(declarationEstimateDate.getDate()).padStart(2, '0');

            document.getElementById('ca_decla').value = `${year}-${month}-${day}`;
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let selectedFiles = [];
            let removedFiles = []; // Menyimpan file yang dihapus

            function updateExistingPreview() {
                const removedFilesInput = document.getElementById('removed-prove-declare');
                removedFilesInput.value = JSON.stringify(removedFiles);

                const previewContainer = document.getElementById('existing-file-preview');
                const existingFiles = Array.from(previewContainer.querySelectorAll('.file-preview'));
                existingFiles.forEach((fileElement) => {
                    const removeButton = fileElement.querySelector('.remove-existing');
                    removeButton.onclick = () => {
                        const filePath = removeButton.getAttribute('data-file');
                        removedFiles.push(filePath); // Tambahkan ke daftar file yang dihapus
                        fileElement.remove(); // Hapus elemen dari preview
                        updateExistingPreview();
                    };
                });
            }

            function updateExistingPreview() {
                const previewContainer = document.getElementById('existing-file-preview');
                const labelContainer = document.getElementById('existing-files-label');
                const existingFiles = Array.from(previewContainer.querySelectorAll('.file-preview'));

                if (existingFiles.length > 0) {
                    labelContainer.style.display = 'block';
                } else {
                    labelContainer.style.display = 'none';
                }

                const removedFilesInput = document.getElementById('removed-prove-declare');
                removedFilesInput.value = JSON.stringify(removedFiles);

                existingFiles.forEach((fileElement) => {
                    const removeButton = fileElement.querySelector('.remove-existing');
                    removeButton.onclick = () => {
                        const filePath = removeButton.getAttribute('data-file');
                        removedFiles.push(filePath);
                        fileElement.remove();
                        updateExistingPreview();
                    };
                });
            }

            function updateNewPreview() {
                const previewContainer = document.getElementById('new-file-preview');
                const labelContainer = document.getElementById('new-files-label');

                previewContainer.innerHTML = '';
                selectedFiles.forEach((file, index) => {
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const fileWrapper = document.createElement('div');
                    fileWrapper.style.position = 'relative';
                    fileWrapper.style.display = 'inline-block';
                    fileWrapper.style.margin = '10px';

                    const removeIcon = document.createElement('span');
                    removeIcon.textContent = '×';
                    removeIcon.style = `
                        position: absolute; top: 5px; right: 5px; cursor: pointer;
                        background-color: #ff4d4d; color: #fff; border-radius: 50%;
                        width: 20px; height: 20px; display: flex; align-items: center;
                        justify-content: center; font-weight: bold;
                    `;
                    removeIcon.onclick = () => {
                        selectedFiles.splice(index, 1);
                        syncFileInput();
                        updateNewPreview();
                    };
                    fileWrapper.appendChild(removeIcon);

                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(file);
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';

                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.alt = "Preview Image";
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.border = '1px solid #ddd';
                        img.style.borderRadius = '5px';
                        img.style.padding = '5px';
                        link.appendChild(img);

                        fileWrapper.appendChild(link);
                    } else if (fileExtension === 'pdf') {
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(file);
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';

                        const icon = document.createElement('img');
                        icon.src = "{{ asset('images/pdf_icon.png') }}";
                        icon.style.maxWidth = '48px';
                        icon.style.marginTop = '10px';
                        link.appendChild(icon);
                        fileWrapper.appendChild(link);

                        const text = document.createElement('p');
                        text.textContent = "Click to view PDF";
                        fileWrapper.appendChild(text);
                    }

                    previewContainer.appendChild(fileWrapper);

                    if (selectedFiles.length > 0) {
                        labelContainer.style.display = 'block';
                    } else {
                        labelContainer.style.display = 'none';
                    }
                });
            }


            function syncFileInput() {
                const dataTransfer = new DataTransfer();
                selectedFiles.forEach(file => dataTransfer.items.add(file));
                const fileInput = document.getElementById('prove_declare');
                fileInput.files = dataTransfer.files;
            }

            window.previewFiles = function () {
                const fileInput = document.getElementById('prove_declare');
                const files = Array.from(fileInput.files);

                const existingFilesCount = document.querySelectorAll('#existing-file-preview .file-preview').length;
                const totalFiles = existingFilesCount + selectedFiles.length; // Total gabungan

                files.forEach(file => {
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire({  
                            icon: 'error',  
                            title: 'File Size Exceeded',  
                            text: `File "${file.name}" exceeds the 2MB size limit.`,  
                        });  
                        return;  
                    }
                    if (!['jpg', 'jpeg', 'png', 'gif', 'pdf'].includes(fileExtension)) {
                        Swal.fire({  
                            icon: 'error',  
                            title: 'Unsupported File Type',  
                            text: `File type "${fileExtension}" not supported.`,  
                        });  
                        return;
                    }
                    if (!selectedFiles.some(existingFile => existingFile.name === file.name)) {
                        if (totalFiles < 10) {
                            selectedFiles.push(file);
                        } else {
                            Swal.fire({  
                                icon: 'error',  
                                title: 'File Limit Exceeded',  
                                text: 'You can upload a maximum of 10 files.',  
                            });  
                        }
                    }
                });

                syncFileInput();
                updateNewPreview();
            };

            updateExistingPreview();
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
                    confirmButtonText: 'Ok'
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
    </script>
    <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <link href="{{ asset('vendor/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/select2/dist/js/select2.min.js') }}"></script>
@endpush
