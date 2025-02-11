@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
@endsection

<style>
    .nav-link {
        color: black;
        border-bottom: 2px solid transparent;
        transition: color 0.3s ease, border-bottom 0.3s ease;
    }

    .nav-link.active {
        color: #AB2F2B;
        /* Primary color */
        border-bottom: 2px solid #AB2F2B;
        font-weight: bold;
        /* Underline with primary color */
    }

    .nav-link:hover {
        color: #AB2F2B;
        /* Change color on hover */
    }
</style>
<style>
    .nav-link {
        color: black;
        border-bottom: 2px solid transparent;
        transition: color 0.3s ease, border-bottom 0.3s ease;
    }

    .nav-link.active {
        color: #AB2F2B;
        /* Primary color */
        border-bottom: 2px solid #AB2F2B;
        font-weight: bold;
        /* Underline with primary color */
    }

    .nav-link:hover {
        color: #AB2F2B;
        /* Change color on hover */
    }
</style>
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('businessTrip.admin') }}">{{ $parentLink }}</a></li>
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
                        <form action="/businessTrip/declaration/admin/status/{{ $n->id }}" method="POST" id="btEditForm"
                            enctype="multipart/form-data">
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
                                            <td>{{ isset($date->declare_estimate) ? date('d M Y', strtotime($date->declare_estimate)) : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>CA Withdrawal Date</th>
                                            <td class="block">:</td>
                                            <td>{{ isset($date->date_required) ? date('d M Y', strtotime($date->date_required)) : '-' }}
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
                                    // $detailCA = isset($ca) && $ca->detail_ca ? json_decode($ca->detail_ca, true) : [];
                                    // $declareCA =
                                    //     isset($ca) && $ca->declare_ca ? json_decode($ca->declare_ca, true) : [];

                                    // dd($detailCA);
                                    // dd($declareCA);
                                    // dd($declareCA['detail_transport']);

                                @endphp

                                <!-- 1st Form -->
                                <div class="row mt-2" id="ca_div">
                                    <div class="col-md-12">
                                        <div class="d-flex flex-column gap-2">
                                            <ul class="nav nav-tabs nav-pills mb-2" id="pills-tab" role="tablist">
                                                @if ($dnsTab == true)
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link <?php echo (!$entrTab && $dnsTab) ? 'active' : (($dnsTab && $entrTab) ? 'active' : ''); ?>" id="pills-cashAdvanced-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-cashAdvanced"
                                                            type="button" role="tab" aria-controls="pills-cashAdvanced"
                                                            aria-selected="true">Cash Advanced</button>
                                                    </li>
                                                @endif
                                                @if ($entrTab == true)
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link <?php echo ($entrTab && !$dnsTab) ? 'active' : ''; ?>" id="pills-caEntertain-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-caEntertain"
                                                            type="button" role="tab" aria-controls="pills-caEntertain"
                                                            aria-selected="false">CA Entertain</button>
                                                    </li>
                                                @endif
                                            </ul>

                                            <div class="tab-content" id="pills-tabContent">
                                                <!-- Cash Advance Content -->
                                                @include('hcis.reimbursements.businessTrip.declaration-admin.btCaDeclarationAdmin')

                                                <!-- CA Entertain Content -->
                                                @include('hcis.reimbursements.businessTrip.declaration-admin.btEntDeclarationAdmin')
                                            </div>
                                        </div>

                                        {{-- CHANGE REASON --}}
                                        @php
                                            $caNote = $dnsData->ca_note ?? $entrData->ca_note ?? '';
                                        @endphp
                                        @if ($entrTab == true || $dnsTab == true)
                                            <div class="mb-3">
                                                <label class="form-label">Change Note</label>
                                                @if($caNote)
                                                <textarea class="form-control form-control-sm" id="ca_note" name="ca_note" rows="3"
                                                    placeholder="Add note if you do any changes">{{ $caNote }}</textarea>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label">Accept Status</label>
                                            <select class="form-select" name="accept_status" id="accept-status"
                                                required>
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

                                        <div class="col-md-12 mb-2 mt-2">
                                            <label for="prove_declare" class="form-label">Uploaded Proof</label>
                                            @if ((isset($dnsData->prove_declare) && $dnsData->prove_declare) || (isset($entrData->prove_declare) && $entrData->prove_declare))
                                                <input type="hidden" name="existing_prove_declare" id="existing-prove-declare" value="{{ $dnsData->prove_declare ?? $entrData->prove_declare }}">
                                                <input type="hidden" name="removed_prove_declare" id="removed-prove-declare" value="[]">

                                                <!-- Preview untuk file lama -->
                                                <div id="existing-files-label" style="margin-bottom: 10px; font-weight: bold;">
                                                    @if ($dnsData->prove_declare ?? $entrData->prove_declare)

                                                        Document Uploaded:
                                                    @endif
                                                </div>
                                                <div id="existing-file-preview" class="mt-2">
                                                    @if ($dnsData->prove_declare ?? $entrData->prove_declare)
                                                        @php
                                                            // Ambil data dari dnsData atau entrData
                                                            $proveDeclare = $dnsData->prove_declare ?? $entrData->prove_declare;

                                                            // Cek apakah data adalah JSON atau string biasa
                                                            $decodedData = json_decode($proveDeclare, true);
                                                            $existingFiles = is_array($decodedData) ? $decodedData : (!empty($proveDeclare) ? [$proveDeclare] : []);
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
                                            @else
                                                <div class="text-danger">No proof uploaded</div>
                                            @endif
                                        </div>

                                        {{-- <input type="hidden" name="status" value="Declaration L1" id="status"> --}}
                                        <input type="hidden" name="no_id" value="{{ $date->id ?? 0 }}">
                                        <input type="hidden" name="ca_id" value="{{ $date->no_ca ?? 0 }}">
                                        <input class="form-control" id="group_company" name="group_company"
                                            type="hidden" value="{{ $employee_data->group_company }}" readonly>
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
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        //CA JS
        function handleCaForms() {
            const caCheckbox = document.getElementById("cashAdvancedCheckbox");
            const perdiemCheckbox = document.getElementById("perdiemCheckbox");
            const caDiv = document.getElementById("ca_bt");
            const caPerdiem = document.getElementById("ca_perdiem");

            caCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    // Show form when checked
                    caDiv.style.display = "block";
                } else {
                    // Hide form and reset all fields when unchecked
                    caDiv.style.display = "none";
                    resetFields("ca_bt"); // Pass the container ID to reset the fields
                }
            });
            perdiemCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    // Show form when checked
                    caPerdiem.style.display = "block";
                } else {
                    // Hide form and reset all fields when unchecked
                    caPerdiem.style.display = "none";
                    resetFieldsPerdiem("ca_perdiem"); // Pass the container ID to reset the fields
                }
            });
        }

        function resetFieldsPerdiem() {
            // Per Diem-related fields
            const companyBtPerdiemFields = document.getElementsByName(
                "company_bt_perdiem[]"
            );
            const locationBtPerdiemFields = document.getElementsByName(
                "location_bt_perdiem[]"
            );
            const nominalBtPerdiemFields = document.getElementsByName(
                "nominal_bt_perdiem[]"
            );
            const otherLocationBtPerdiemFields = document.getElementsByName(
                "other_location_bt_perdiem[]"
            );
            const startBtPerdiemFields =
                document.getElementsByName("start_bt_perdiem[]");
            const endBtPerdiemFields = document.getElementsByName("end_bt_perdiem[]");
            const totalDaysBtPerdiemFields = document.getElementsByName(
                "total_days_bt_perdiem[]"
            );
            const totalBtPerdiem = document.getElementsByName("total_bt_perdiem");

            // Reset values to empty or default
            companyBtPerdiemFields.forEach((field) => {
                field.selectedIndex = 0; // Set to first option (assuming it's the "Select Company..." option)
            });
            locationBtPerdiemFields.forEach((field) => {
                field.selectedIndex = 0; // Set to first option (assuming it's the "Select Company..." option)
            });
            nominalBtPerdiemFields.forEach((field) => (field.value = 0));
            otherLocationBtPerdiemFields.forEach((field) => (field.value = ""));
            startBtPerdiemFields.forEach((field) => (field.value = ""));
            endBtPerdiemFields.forEach((field) => (field.value = ""));
            totalDaysBtPerdiemFields.forEach((field) => (field.value = 0));
            totalBtPerdiem.forEach((field) => (field.value = 0));

            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        function resetFields() {
            // Transport-related fields
            const transportDateFields = document.getElementsByName(
                "tanggal_bt_transport[]"
            );
            const companyCodeFields = document.getElementsByName(
                "company_bt_transport[]"
            );
            const nominalFields = document.getElementsByName("nominal_bt_transport[]");
            const informationFields = document.getElementsByName(
                "keterangan_bt_transport[]"
            );
            const totalBtTrans = document.getElementsByName("total_bt_transport");

            // Accommodation-related fields
            const startDateFields = document.getElementsByName("start_bt_penginapan[]");
            const endDateFields = document.getElementsByName("end_bt_penginapan[]");
            const totalDaysFields = document.getElementsByName(
                "total_days_bt_penginapan[]"
            );
            const hotelNameFields = document.getElementsByName(
                "hotel_name_bt_penginapan[]"
            );
            const companyPenginapanFields = document.getElementsByName(
                "company_bt_penginapan[]"
            );
            const nominalPenginapanFields = document.getElementsByName(
                "nominal_bt_penginapan[]"
            );
            const totalPenginapan = document.getElementsByName("total_bt_penginapan");

            // Others-related fields
            const tanggalBtLainnya = document.getElementsByName("tanggal_bt_lainnya[]");
            const nominalBtLainnya = document.getElementsByName("nominal_bt_lainnya[]");
            const keteranganBtLainnya = document.getElementsByName(
                "keterangan_bt_lainnya[]"
            );
            const totalBtLainnya = document.getElementsByName("total_bt_lainnya");

            // Reset transport date fields
            transportDateFields.forEach((field) => {
                field.value = ""; // Reset to empty
            });

            // Reset company code fields (set to default "Select Company...")
            companyCodeFields.forEach((field) => {
                field.selectedIndex = 0; // Set to first option (assuming it's the "Select Company..." option)
            });

            // Reset nominal fields
            nominalFields.forEach((field) => {
                field.value = ""; // Reset to empty
            });

            // Reset information fields
            informationFields.forEach((field) => {
                field.value = ""; // Reset to empty
            });

            // Reset total fields for transport
            totalBtTrans.forEach((field) => {
                field.value = 0; // Reset to 0
            });

            // Reset accommodation-related fields
            startDateFields.forEach((field) => (field.value = ""));
            endDateFields.forEach((field) => (field.value = ""));
            totalDaysFields.forEach((field) => (field.value = "")); // Reset total days to empty or 0
            hotelNameFields.forEach((field) => (field.value = ""));
            companyPenginapanFields.forEach((field) => (field.selectedIndex = 0)); // Set to "Select Company..."
            nominalPenginapanFields.forEach((field) => (field.value = 0)); // Reset amount
            totalPenginapan.forEach((field) => (field.value = 0)); // Reset amount

            // Reset others-related fields
            tanggalBtLainnya.forEach((field) => {
                field.value = ""; // Reset to empty
            });
            nominalBtLainnya.forEach((field) => {
                field.value = ""; // Reset to empty
            });
            keteranganBtLainnya.forEach((field) => {
                field.value = ""; // Reset to empty
            });
            totalBtLainnya.forEach((field) => {
                field.value = 0; // Reset to 0
            });

            // Recalculate the total CA after reset
            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        function cleanNumber(value) {
            return parseFloat(value.replace(/\./g, "").replace(/,/g, "")) || 0;
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatNumberPerdiem(num) {
            return num.toLocaleString("id-ID");
        }

        function parseNumberPerdiem(value) {
            return parseFloat(value.replace(/\./g, "").replace(/,/g, "")) || 0;
        }

        function parseNumber(value) {
            return parseFloat(value.replace(/\./g, "")) || 0;
        }

        function formatInput(input) {
            let value = input.value.replace(/\./g, "");
            value = parseFloat(value);
            if (!isNaN(value)) {
                input.value = formatNumber(Math.floor(value));
            } else {
                input.value = formatNumber(0);
            }
            calculateTotalNominalBTPerdiem();
            calculateTotalNominalBTMeals();
            calculateTotalNominalBTTransport();
            calculateTotalNominalBTPenginapan();
            calculateTotalNominalBTLainnya();
            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        function calculateTotalNominalBTENTTotal() {
            let total = 0;
            document.querySelectorAll('input[name="totalca"]').forEach(input => {
                total += parseNumber(input.value);
            });
            document.querySelectorAll('input[name="totalca_ca_deklarasi"]').forEach(input => {
                total += parseNumber(input.value);
            });
            console.log(document.querySelectorAll('input[name="totalca"]'));
            document.querySelector('input[name="total_ca_ent"]').value = formatNumber(total);
        }

        function calculateTotalNominalBTTotal() {
            let total = 0;
            document
                .querySelectorAll('input[name="total_bt_perdiem"]')
                .forEach((input) => {
                    total += parseNumber(input.value);
                });
            document
                .querySelectorAll('input[name="total_bt_meals"]')
                .forEach((input) => {
                    total += parseNumber(input.value);
                });
            document
                .querySelectorAll('input[name="total_bt_transport"]')
                .forEach((input) => {
                    total += parseNumber(input.value);
                });
            document
                .querySelectorAll('input[name="total_bt_penginapan"]')
                .forEach((input) => {
                    total += parseNumber(input.value);
                });
            document
                .querySelectorAll('input[name="total_bt_lainnya"]')
                .forEach((input) => {
                    total += parseNumber(input.value);
                });
            document.querySelector('input[name="totalca_ca_deklarasi"]').value = formatNumber(total);
        }

        function toggleDivs() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");
            var div_bisnis_numb_dns = document.getElementById("div_bisnis_numb_dns");
            var div_bisnis_numb_ent = document.getElementById("div_bisnis_numb_ent");
            var bisnis_numb = document.getElementById("bisnis_numb");
            var div_allowance = document.getElementById("div_allowance");

            if (ca_type.value === "dns") {
                ca_bt.style.display = "block";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "block";
                div_bisnis_numb_ent.style.display = "none";
                div_allowance.style.display = "block";
            } else if (ca_type.value === "ndns") {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "block";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "none";
                bisnis_numb.style.value = "";
                div_allowance.style.display = "none";
            } else if (ca_type.value === "entr") {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "block";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "block";
            } else {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb_dns.style.display = "none";
                div_bisnis_numb_ent.style.display = "none";
                bisnis_numb.style.value = "";
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
            input.value = input.value.replace(/[^0-9]/g, "");
        }

        document.addEventListener("DOMContentLoaded", function() {
            const startDateInput = document.getElementById("start_date");
            const endDateInput = document.getElementById("end_date");
            const totalDaysInput = document.getElementById("totaldays");
            const perdiemInput = document.getElementById("perdiem");
            const allowanceInput = document.getElementById("allowance");
            const othersLocationInput = document.getElementById("others_location");
            const transportInput = document.getElementById("transport");
            const accommodationInput = document.getElementById("accommodation");
            const otherInput = document.getElementById("other");
            const totalcaInput = document.getElementById("totalca");
            const nominal_1Input = document.getElementById("nominal_1");
            const nominal_2Input = document.getElementById("nominal_2");
            const nominal_3Input = document.getElementById("nominal_3");
            const nominal_4Input = document.getElementById("nominal_4");
            const nominal_5Input = document.getElementById("nominal_5");
            const caTypeInput = document.getElementById("ca_type");

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, "")) || 0;
            }

            function formatInput(input) {
                let value = input.value.replace(/\./g, "");
                value = parseFloat(value);
                if (!isNaN(value)) {
                    // input.value = formatNumber(value);
                    input.value = formatNumber(Math.floor(value));
                } else {
                    input.value = formatNumber(0);
                }

                calculateTotalCA();
            }

            function calculateTotalDays() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const groupCompany = document.getElementById("group_company");
                // console.log("proses calculate");

                if (startDate && endDate && !isNaN(startDate) && !isNaN(endDate)) {
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                    const totalDays = daysDiff > 0 ? daysDiff + 1 : 0 + 1;
                    totalDaysInput.value = totalDays;

                    const perdiem = parseFloat(perdiemInput.value) || 0;
                    let allowance = totalDays * perdiem;

                    if (groupCompany.value !== "Plantations") {
                        allowance *= 1;
                    } else if (othersLocationInput.value.trim() !== "") {
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

            function calculateTotalCA() {
                const allowance = parseNumber(allowanceInput.value);
                const transport = parseNumber(transportInput.value);
                const accommodation = parseNumber(accommodationInput.value);
                const other = parseNumber(otherInput.value);
                const nominal_1 = parseNumber(nominal_1Input.value);
                const nominal_2 = parseNumber(nominal_2Input.value);
                const nominal_3 = parseNumber(nominal_3Input.value);
                const nominal_4 = parseNumber(nominal_4Input.value);
                const nominal_5 = parseNumber(nominal_5Input.value);

                // Perbaiki penulisan caTypeInput.value
                const ca_type = caTypeInput.value;

                let totalca = 0;
                if (ca_type === "dns") {
                    totalca = allowance + transport + accommodation + other;
                } else if (ca_type === "ndns") {
                    totalca = transport + accommodation + other;
                    allowanceInput.value = 0;
                } else if (ca_type === "entr") {
                    totalca = nominal_1 + nominal_2 + nominal_3 + nominal_4 + nominal_5;
                    allowanceInput.value = 0;
                }

                // totalcaInput.value = formatNumber(totalca.toFixed(2));
                totalcaInput.value = formatNumber(Math.floor(totalca));
            }

            startDateInput.addEventListener("change", calculateTotalDays);
            endDateInput.addEventListener("change", calculateTotalDays);
            othersLocationInput.addEventListener("input", calculateTotalDays);
            caTypeInput.addEventListener("change", calculateTotalDays);
            [
                transportInput,
                accommodationInput,
                otherInput,
                allowanceInput,
                nominal_1,
                nominal_2,
                nominal_3,
                nominal_4,
                nominal_5,
            ].forEach((input) => {
                input.addEventListener("input", () => formatInput(input));
            });
        });

        document.getElementById("end_date").addEventListener("change", function() {
            const endDate = new Date(this.value);
            const declarationEstimateDate = new Date(endDate);
            declarationEstimateDate.setDate(declarationEstimateDate.getDate() + 3);

            const year = declarationEstimateDate.getFullYear();
            const month = String(declarationEstimateDate.getMonth() + 1).padStart(
                2,
                "0"
            );
            const day = String(declarationEstimateDate.getDate()).padStart(2, "0");

            document.getElementById("ca_decla").value = `${year}-${month}-${day}`;
        });
    </script>
    <script>
        var formCountMeals = 0;

        window.addEventListener("DOMContentLoaded", function() {
            formCountMeals = document.querySelectorAll(
                "#form-container-meals > div"
            ).length;
        });

        $(".btn-warning").click(function(event) {
            event.preventDefault();
            var index = $(this).closest(".card-body").index() + 1;
            removeFormMeals(index, event);
        });

        function removeFormMeals(index, event) {
            event.preventDefault();
            if (formCountMeals > 0) {
                const formContainer = document.getElementById(
                    `form-container-bt-meals-${index}`
                );
                if (formContainer) {
                    const nominalInput = formContainer.querySelector(
                        `#nominal_bt_meals_${index}`
                    );
                    if (nominalInput) {
                        let nominalValue = cleanNumber(nominalInput.value);
                        let total = cleanNumber(
                            document.querySelector('input[name="total_bt_meals"]').value
                        );
                        total -= nominalValue;
                        document.querySelector('input[name="total_bt_meals"]').value =
                            formatNumber(total);
                        calculateTotalNominalBTTotal();
                        calculateTotalNominalBTENTTotal();
                    }
                    $(`#form-container-bt-meals-${index}`).remove();
                    formCountMeals--;
                }
            }
        }

        function clearFormMeals(index, event) {
            event.preventDefault();
            let nominalValue = cleanNumber(
                document.querySelector(`#nominal_bt_meals_${index}`).value
            );
            let total = cleanNumber(
                document.querySelector('input[name="total_bt_meals"]').value
            );
            total -= nominalValue;
            document.querySelector('input[name="total_bt_meals"]').value =
                formatNumber(total);

            // Clear the inputs
            const formContainer = document.getElementById(
                `form-container-bt-meals-${index}`
            );
            formContainer
                .querySelectorAll('input[type="text"], input[type="date"]')
                .forEach((input) => {
                    input.value = "";
                });
            formContainer.querySelector("textarea").value = "";

            // Reset nilai untuk nominal BT Meals
            document.querySelector(`#nominal_bt_meals_${index}`).value = 0;
            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        function calculateTotalNominalBTMeals() {
            let total = 0;
            document
                .querySelectorAll('input[name="nominal_bt_meals[]"]')
                .forEach((input) => {
                    total += cleanNumber(input.value);
                });
            document.getElementById("total_bt_meals").value = formatNumber(total);
        }

        function onNominalChange() {
            calculateTotalNominalBTMeals();
        }
    </script>
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
                    const totalBtMeals = document.getElementById('total_bt_meals').value;
                    const totalBtPenginapan = document.getElementById('total_bt_penginapan').value;
                    const totalBtTransport = document.getElementById('total_bt_transport').value;
                    const totalBtLainnya = document.getElementById('total_bt_lainnya').value;
                    const totalBtCa = document.getElementById('totalca_ca_deklarasi').value;
                    const totalEntCa = document.getElementById('totalca').value;
                    const group_company = document.getElementById('group_company').value;

                    function parseCurrency(value) {
                        // Hapus tanda titik dan ubah ke angka
                        return parseFloat(value.replace(/\./g, '')) || 0;
                    }

                    // Konversi nilai ke angka
                    const totalBtCaNum = parseCurrency(totalBtCa);
                    const totalEntCaNum = parseCurrency(totalEntCa);

                    // Hitung total declaration
                    const totalDeclaration = totalBtCaNum + totalEntCaNum;

                    // Format angka ke format mata uang (opsional)
                    function formatCurrency(value) {
                        return value.toLocaleString('id-ID');
                    }


                    // Create a message with the input values, each on a new line with bold titles
                    let inputSummary = `
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    `;

                    // Tambahkan total allowance jika totalBtCa tidak kosong
                    if (parseFloat(totalBtCa) > 0) {
                        inputSummary += `
                            <tr>
                                <th style="width: 40%; text-align: left; padding: 8px;">Total {{ $allowance }}</th>
                                <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                                <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtPerdiem}</strong></td>
                            </tr>
                            `;

                        // Conditionally add the "Total Meals" row
                        if (group_company != 'KPN Plantations' && group_company != 'Plantations') {
                            inputSummary += `
                            <tr>
                                <th style="width: 40%; text-align: left; padding: 8px;">Total Meals</th>
                                <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                                <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtMeals}</strong></td>
                            </tr>`;
                        }

                        inputSummary += `
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
                        `;
                    }

                    inputSummary += `
                        </table>
                        <hr style="margin: 20px 0;">
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    `;

                    // Tampilkan Total Entertain Declaration jika totalEntCa lebih besar dari 0
                    if (parseFloat(totalBtCa) > 0) {
                        inputSummary += `
                            <tr>
                                <th style="width: 45%; text-align: left; padding: 8px;">Total Cash Advanced Declaration</th>
                                <td style="width: 5%; text-align: right; padding: 8px;">:</td>
                                <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalBtCa}</strong></td>
                            </tr>
                        `;
                    }

                    // Tampilkan Total Entertain Declaration jika totalEntCa lebih besar dari 0
                    if (parseFloat(totalEntCa) > 0) {
                        inputSummary += `
                            <tr>
                                <th style="width: 45%; text-align: left; padding: 8px;">Total Entertain Declaration</th>
                                <td style="width: 5%; text-align: right; padding: 8px;">:</td>
                                <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalEntCa}</strong></td>
                            </tr>
                        `;
                    }

                    inputSummary += `
                        </table>
                    `;

                    if ((parseFloat(totalBtCa) > 0) && (parseFloat(totalEntCa) > 0)) {
                        inputSummary += `
                            <hr style="margin: 20px 0;">
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <th style="width: 45%; text-align: left; padding: 8px;">Total Declaration</th>
                                    <td style="width: 5%; text-align: right; padding: 8px;">:</td>
                                    <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${formatCurrency(totalDeclaration)}</strong></td>
                                </tr>
                            </table>
                        `;
                    }

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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.draft-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent immediate form submission

                    const form = document.getElementById('btEditForm');

                    // Check if the form is valid before proceeding
                    if (!form.checkValidity()) {
                        form.reportValidity(); // Show validation messages if invalid
                        return; // Exit if the form is not valid
                    }

                    const input = document.createElement('input');
                    input.type =
                        'hidden'; // Hidden input so it doesn't show in the form
                    input.name = button.name; // Use the button's name attribute
                    input.value = button.value; // Use the button's value attribute

                    form.appendChild(input); // Append the hidden input to the form
                    form.submit(); // Submit the form only if confirmed
                });
            });
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
@endsection
