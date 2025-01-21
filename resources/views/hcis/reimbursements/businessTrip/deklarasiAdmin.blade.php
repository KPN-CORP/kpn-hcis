@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('businessTrip.admin') }}">{{ $parentLink }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Declaration Data - {{ $n->no_sppd }}</h4>
                        <a href="{{ route('businessTrip.admin') }}" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="card-body">
                        <form action="/businessTrip/declaration/admin/status/{{ $n->id }}" method="POST"
                            id="btEditForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('hcis.reimbursements.businessTrip.modal')
                            <div class="row">
                                <div class="col-md-6">
                                    <table width="100%" class="">
                                        <tr>
                                            <th width="40%">Employee ID</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->employee_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Employee Name</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->fullname ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unit</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->unit ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Job Level</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->job_level ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Designation</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->designation_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cash Advance Type</th>
                                            <td class="block">:</td>
                                            <td> Business Trip</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table width="100%">
                                        <tr>
                                            <th width="40%">Start Date</th>
                                            <td class="block">: </td>
                                            <td width="60%"> {{ $n->mulai ? date('d M Y', strtotime($n->mulai)) : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td class="block">:</td>
                                            <td> {{ $n->kembali ? date('d M Y', strtotime($n->kembali)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Estimated Declaration</th>
                                            <td class="block">:</td>
                                            <td>{{ isset($ca->declare_estimate) ? date('d M Y', strtotime($ca->declare_estimate)) : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date Required</th>
                                            <td class="block">:</td>
                                            <td>{{ isset($ca->date_required) ? date('d M Y', strtotime($ca->date_required)) : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Costing Company</th>
                                            <td class="block">:</td>
                                            <td> ({{ $n->bb_perusahaan ?? '-' }})</td>
                                        </tr>
                                        <tr>
                                            <th>Purposes</th>
                                            <td class="block">:</td>
                                            <td>{{ $n->keperluan ?? '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Destination</th>
                                            <td class="block">:</td>
                                            <td> {{ $n->tujuan ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="">
                                    <input type="hidden" class="form-control bg-light" id="divisi" name="divisi"
                                        style="cursor:not-allowed;" value="{{ $employee_data->unit }}" readonly>
                                </div>
                                <div class="">
                                    <input type="hidden" class="form-control bg-light" id="tujuan" name="tujuan"
                                        style="cursor:not-allowed;" value="{{ $n->tujuan }}" readonly>
                                    <input type="hidden" class="form-control" id="keperluan" name="keperluan"
                                        value="{{ $n->keperluan }}"></input>
                                </div>
                                @php
                                    $detailCA = isset($ca) && $ca->detail_ca ? json_decode($ca->detail_ca, true) : [];
                                    $declareCA =
                                        isset($ca) && $ca->declare_ca ? json_decode($ca->declare_ca, true) : [];

                                    // dd($detailCA);
                                    // dd($declareCA);
                                    // dd($declareCA['detail_transport']);

                                @endphp
                                <script>
                                    // Pass the PHP array into a JavaScript variable
                                    const initialDetailCA = @json($detailCA);
                                    const initialDeclareCA = @json($declareCA);
                                </script>
                                <!-- 1st Form -->
                                <div class="row mt-2" id="ca_div">
                                    <div class="col-md-12">
                                        <div class="d-flex flex-column gap-2">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="pills-perdiem-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-perdiem" type="button"
                                                        role="tab" aria-controls="pills-perdiem"
                                                        aria-selected="true">{{$allowance}}</button>
                                                </li>

                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-transport-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-transport" type="button" role="tab"
                                                        aria-controls="pills-transport"
                                                        aria-selected="false">Transport</button>
                                                </li>

                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-accomodation-tab"
                                                        data-bs-toggle="pill" data-bs-target="#pills-accomodation"
                                                        type="button" role="tab" aria-controls="pills-accomodation"
                                                        aria-selected="false">Accommodation</button>
                                                </li>

                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="pills-other-tab" data-bs-toggle="pill"
                                                        data-bs-target="#pills-other" type="button" role="tab"
                                                        aria-controls="pills-other" aria-selected="false">Other</button>
                                                </li>
                                            </ul>
                                            {{-- <div class="card"> --}}
                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel"
                                                    aria-labelledby="pills-perdiem-tab">
                                                    {{-- ca perdiem content --}}
                                                    @include('hcis.reimbursements.businessTrip.declaration.caPerdiemDeclare')
                                                </div>
                                                <div class="tab-pane fade" id="pills-transport" role="tabpanel"
                                                    aria-labelledby="pills-transport-tab">
                                                    {{-- ca transport content --}}
                                                    @include('hcis.reimbursements.businessTrip.declaration.caTransportDeclare')
                                                </div>
                                                <div class="tab-pane fade" id="pills-accomodation" role="tabpanel"
                                                    aria-labelledby="pills-accomodation-tab">
                                                    {{-- ca accommodatioon content --}}
                                                    @include('hcis.reimbursements.businessTrip.declaration.caAccommodationDeclare')</div>
                                                <div class="tab-pane fade" id="pills-other" role="tabpanel"
                                                    aria-labelledby="pills-other-tab">
                                                    {{-- ca others content --}}
                                                    @include('hcis.reimbursements.businessTrip.declaration.caOtherDeclare')
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Total Cash Advanced</label>
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input class="form-control bg-light" name="totalca_deklarasi"
                                                        id="totalca_declarasi" type="text" min="0"
                                                        value="{{ number_format($ca->total_ca ?? '0', 0, ',', '.') }}"
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Total Cash Advanced Deklarasi</label>
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input class="form-control bg-light" name="totalca" id="totalca"
                                                        type="text" min="0"
                                                        value="{{ number_format($ca->total_real ?? '0', 0, ',', '.') }}"
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2" style="display:none">
                                                <label class="form-label">Total Cost</label>
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input class="form-control bg-light" name="" id=""
                                                        type="text" min="0"
                                                        value="{{ number_format($ca->total_cost ?? '0', 0, ',', '.') }}"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Accept Status</label>
                                            <select class="form-select" name="accept_status" id="accept-status" required>
                                                <option value="" disabled
                                                    {{ !in_array($n->status, ['Verified', 'Doc Accepted', 'Return/Refund']) ? 'selected' : '' }}>
                                                    --- Choose Acceptance Status ---</option>
                                                <option value="Verified"
                                                    {{ old('accept_status', $n->status) == 'Verified' ? 'selected' : '' }}>
                                                    Verified
                                                </option>
                                                <option value="Doc Accepted"
                                                    {{ old('accept_status', $n->status) == 'Doc Accepted' ? 'selected' : '' }}>
                                                    Doc
                                                    Accepted</option>
                                                <option value="Return/Refund"
                                                    {{ old('accept_status', $n->status) == 'Return/Refund' ? 'selected' : '' }}>
                                                    Return/Refund</option>
                                            </select>
                                        </div>
                                        @php
                                            use Illuminate\Support\Facades\Storage;
                                        @endphp
                                        <label for="" class="form-label">Uploaded Proof</label>
                                        @if (isset($ca->prove_declare) && $ca->prove_declare)
                                            @php
                                                // Get the file extension
                                                $fileExtension = pathinfo($ca->prove_declare, PATHINFO_EXTENSION);
                                                // Set the image based on the file type
                                                $imageSrc = '';
                                                if (in_array($fileExtension, ['pdf'])) {
                                                    $imageSrc = 'https://img.icons8.com/color/48/000000/pdf.png'; // Replace with the path to your PDF icon
                                                } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                    $imageSrc = Storage::url($ca->prove_declare); // Image files should display their own thumbnail
                                                } else {
                                                    $imageSrc = 'https://img.icons8.com/color/48/000000/file.png'; // Replace with the path to your default icon
                                                }
                                            @endphp
                                            <div class="file-preview text-left">
                                                <a href="{{ Storage::url($ca->prove_declare) }}" target="_blank"
                                                    style="text-decoration: none;">
                                                    <img src="{{ $imageSrc }}" alt="{{ $fileExtension }} file"
                                                        class="file-icon" style="width: 50px; height: 50px;">
                                                    <div style="margin-top: 5px;"><u>View Proof</u></div>
                                                </a>
                                            @else
                                                <div class="text-danger">No proof uploaded</div>
                                        @endif

                                        {{-- <input type="hidden" name="status" value="Declaration L1" id="status"> --}}
                                        <input type="hidden" name="no_id" value="{{ $ca->id ?? 0 }}">
                                        <input type="hidden" name="ca_id" value="{{ $ca->no_ca ?? 0 }}">
                                        <input class="form-control" id="perdiem" name="perdiem" type="hidden"
                                            value="{{ $perdiem->amount ?? 0 }}" readonly>
                                        {{-- <input type="hidden" name="status" value="Declaration L1" id="status"> --}}
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit"
                                                class="btn btn-primary rounded-pill submit-button">Submit</button>
                                        </div>
                                        <div class="" style="visibility: hidden">
                                            <input class="form-select" id="bb_perusahaan" name="bb_perusahaan"
                                                value="{{ $n->bb_perusahaan }}">
                                            </input>
                                        </div>
                                        <input type="hidden" id="mulai" name="mulai"
                                            value="{{ $n->mulai ?? 0 }}">
                                        <input type="hidden" id="kembali" name="kembali"
                                            value="{{ $n->kembali ?? 0 }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/js/businessTrip.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.submit-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent immediate form submission

                    const form = document.getElementById('btEditForm');

                    // Check if the form is valid before proceeding
                    if (!form.checkValidity()) {
                        form.reportValidity(); // Show validation messages if invalid
                        return; // Exit if the form is not valid
                    }

                    // Retrieve the values from the input fields
                    const totalBtPerdiem = document.getElementById('total_bt_perdiem').value;
                    const totalBtPenginapan = document.getElementById('total_bt_penginapan').value;
                    const totalBtTransport = document.getElementById('total_bt_transport').value;
                    const totalBtLainnya = document.getElementById('total_bt_lainnya').value;
                    const accStatus = document.getElementById('accept-status').value;
                    const totalCa = document.getElementById('totalca').value;

                    // Create a message with the input values, each on a new line with bold titles
                    const inputSummary = `
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Total {{$allowance}}</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtPerdiem}</strong></td>
                        </tr>
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Total Accommodation</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtPenginapan}</strong></td>
                        </tr>
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Total Transport</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtTransport}</strong></td>
                        </tr>
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Total Others</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtLainnya}</strong></td>
                        </tr>
                    </table>
                    <hr style="margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Total Declaration</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalCa}</strong></td>
                        </tr>
                        <tr>
                            <th style="width: 40%; text-align: left; padding: 8px;">Status</th>
                            <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                            <td style="width: 50%; text-align: left; padding: 8px;"><strong>${accStatus}</strong></td>
                        </tr>
                    </table>
                            `;

                    // Show SweetAlert confirmation with the input summary
                    Swal.fire({
                        title: "Do you want to submit this request?",
                        html: `You won't be able to revert this!<br><br>${inputSummary}`, // Use 'html' instead of 'text'
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#AB2F2B",
                        cancelButtonColor: "#CCCCCC",
                        confirmButtonText: "Yes, submit it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create a hidden input field to hold the action value
                            const input = document.createElement('input');
                            input.type =
                                'hidden'; // Hidden input so it doesn't show in the form
                            input.name = button.name; // Use the button's name attribute
                            input.value = button.value; // Use the button's value attribute

                            form.appendChild(input); // Append the hidden input to the form
                            form.submit(); // Submit the form only if confirmed
                        }
                    });
                });
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
        document.addEventListener("DOMContentLoaded", function() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");
            var div_bisnis_numb = document.getElementById("div_bisnis_numb");
            var bisnis_numb = document.getElementById("bisnis_numb");
            var div_allowance = document.getElementById("div_allowance");

            function toggleDivs() {
                if (ca_type.value === "dns") {
                    ca_bt.style.display = "block";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                    div_bisnis_numb.style.display = "block";
                    div_allowance.style.display = "block";
                } else if (ca_type.value === "ndns") {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "block";
                    ca_e.style.display = "none";
                    div_bisnis_numb.style.display = "none";
                    bisnis_numb.style.value = "";
                    div_allowance.style.display = "none";
                } else if (ca_type.value === "entr") {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "block";
                    div_bisnis_numb.style.display = "block";
                } else {
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                    div_bisnis_numb.style.display = "none";
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
            const perdiemInput = document.getElementById('perdiem');
            const allowanceInput = document.getElementById('allowance');
            const othersLocationInput = document.getElementById('others_location');
            const transportInput = document.getElementById('transport');
            const accommodationInput = document.getElementById('accommodation');
            const otherInput = document.getElementById('other');
            const totalcaInput = document.getElementById('totalca');
            const nominal_1Input = document.getElementById('nominal_1');
            const nominal_2Input = document.getElementById('nominal_2');
            const nominal_3Input = document.getElementById('nominal_3');
            const nominal_4Input = document.getElementById('nominal_4');
            const nominal_5Input = document.getElementById('nominal_5');
            const caTypeInput = document.getElementById('ca_type');

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, '')) || 0;
            }

            function calculateTotalDays() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                if (startDate && endDate && !isNaN(startDate) && !isNaN(endDate)) {
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                    const totalDays = daysDiff > 0 ? daysDiff + 1 : 0 + 1;
                    totalDaysInput.value = totalDays;

                    const perdiem = parseFloat(perdiemInput.value) || 0;
                    let allowance = totalDays * perdiem;

                    if (othersLocationInput.value.trim() !== '') {
                        allowance *= 1; // allowance * 50%
                    } else {
                        allowance *= 0.5;
                    }

                    allowanceInput.value = formatNumber(Math.floor(allowance));
                } else {
                    totalDaysInput.value = 0;
                    allowanceInput.value = 0;
                }
                calculateTotalCA();
            }

            startDateInput.addEventListener('change', calculateTotalDays);
            endDateInput.addEventListener('change', calculateTotalDays);
            othersLocationInput.addEventListener('input', calculateTotalDays);
            caTypeInput.addEventListener('change', calculateTotalDays);
            [transportInput, accommodationInput, otherInput, allowanceInput, nominal_1, nominal_2, nominal_3,
                nominal_4, nominal_5
            ].forEach(input => {
                input.addEventListener('input', () => formatInput(input));
            });
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = new Date(this.value);
            const declarationEstimateDate = new Date(endDate);
            declarationEstimateDate.setDate(declarationEstimateDate.getDate() + 3);

            const year = declarationEstimateDate.getFullYear();
            const month = String(declarationEstimateDate.getMonth() + 1).padStart(2, '0');
            const day = String(declarationEstimateDate.getDate()).padStart(2, '0');

            document.getElementById('ca_decla').value = `${year}-${month}-${day}`;
        });

        function previewFile() {
            const fileInput = document.getElementById('prove_declare');
            const file = fileInput.files[0];
            const preview = document.getElementById('existing-file-preview');
            preview.innerHTML = ''; // Kosongkan preview sebelumnya

            if (file) {
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    const img = document.createElement('img');
                    img.style.maxWidth = '200px';
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                } else if (fileExtension === 'pdf') {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(file);
                    link.target = '_blank';
                    const icon = document.createElement('img');
                    icon.src = "https://img.icons8.com/color/48/000000/pdf.png";
                    icon.style.maxWidth = '48px';
                    link.appendChild(icon);
                    const text = document.createElement('p');
                    text.textContent = "Click to view PDF";
                    preview.appendChild(link);
                    preview.appendChild(text);
                } else {
                    preview.textContent = 'File type not supported.';
                }
            }
        }
        document.getElementById('mulai').addEventListener('change', handleDateChange);
        document.getElementById('kembali').addEventListener('change', handleDateChange);

        function handleDateChange() {
            const startDateInput = document.getElementById('mulai');
            const endDateInput = document.getElementById('kembali');

            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            // Set the min attribute of the end_date input to the selected start_date
            endDateInput.min = startDateInput.value;

            // Validate dates
            if (endDate < script startDate) {
                alert("End Date cannot be earlier than Start Date");
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
@endsection
