@extends('layouts_.vertical', ['page_title' => 'Medical'])

@section('css')
    <style>
        th {
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('medical.approval') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        @include('hcis.reimbursements.businessTrip.modal')

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex bg-primary text-white justify-content-between">
                        <h4 class="mb-0">Medical Data - {{ $medic->no_medic }}</h4>
                        <a href="/medical/approval" type="button" class="btn-close btn-close-white" aria-label="Close"></a>
                    </div>
                    <div class="card-body">
                        <form id="medicForm" action="/medical/admin/form-update/update/{{ $medic->usage_id }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row mb-2">
                                <div class="col-md-4 mb-2">
                                    <label for="patient_name" class="form-label">Patient Name</label>
                                    <select class="form-select form-select-sm select2" id="patient_name" name="patient_name"
                                        disabled>
                                        <option value="{{ $medic->patient_name }}" disabled selected>
                                            {{ $medic->patient_name }}</option>
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label for="nama" class="form-label">Hospital Name</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="hospital_name"
                                        name="hospital_name" placeholder="ex: RS. Murni Teguh"
                                        value="{{ $medic->hospital_name }}" readonly>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="disease" class="form-label">Disease</label>
                                    <select class="form-select form-select-sm select2" id="disease" name="disease"
                                        disabled>
                                        <option value="" disabled selected>--- Choose Disease ---</option>
                                        @foreach ($diseases as $disease)
                                            <option value="{{ $disease->disease_name }}"
                                                {{ $disease->disease_name === $selectedDisease ? 'selected' : '' }}>
                                                {{ $disease->disease_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="keperluan" class="form-label">No. Invoice</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="no_invoice"
                                        name="no_invoice" rows="3" placeholder="Please add your invoice number ..."
                                        value="{{ $medic->no_invoice }}" readonly></input>
                                </div>
                                <div class="col-md-6 mb-1">
                                    <label for="medical_date" class="form-label">Medical Date</label>
                                    <input type="date" class="form-control form-control-sm bg-light" id="date"
                                        name="date" value="{{ $medic->date }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label for="medical_type" class="form-label">Medical Type</label>
                                    <select class="form-select form-select-sm select2" id="medical_type"
                                        name="medical_type[]" multiple disabled>
                                        {{-- <option value="" selected>--- Choose Medical Type ---</option> --}}
                                        @foreach ($medical_type as $type)
                                            <option value="{{ $type->name }}"
                                                @if ($selectedMedicalTypes->contains($type->name)) selected @endif>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Dynamic Forms --}}
                            <div id="balanceContainer" class="row"></div>
                            <div id="dynamicForms" class="row"></div>
                            <div id="bpjsCoverContainer" class="row"></div>

                            <div class="row mb-2">
                                <div class="col-md-12 mt-2">
                                    <label for="" class="form-label">Admin Notes</label>
                                    <textarea class="form-control form-control-sm bg-light" id="admin_notes" name="admin_notes" rows="3"
                                        placeholder="Note from Admin..." disabled>{{ $medic->admin_notes }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-12 mt-2">
                                    <label for="" class="form-label">Detail Information</label>
                                    <textarea class="form-control form-control-sm bg-light" id="coverage_detail" name="coverage_detail" rows="3"
                                        placeholder="Please add more detail of disease ..." readonly>{{ $medic->coverage_detail }}</textarea>
                                </div>
                            </div>
                            @php
                                use Illuminate\Support\Facades\Storage;
                            @endphp
                            <div class="row mb-2">
                                <div class="col-md-12 mt-2">
                                    @if (isset($medic->medical_proof) && $medic->medical_proof)
                                        <div class="col-md-12 mb-2 mt-2">
                                            <!-- Preview untuk file lama -->
                                            <div id="existing-files-label" style="margin-bottom: 10px; font-weight: bold;">
                                                @if ($medic->medical_proof)
                                                    
                                                    Attachment:
                                                @endif
                                            </div>
                                            <div id="existing-file-preview" class="mt-2">
                                                @if ($medic->medical_proof)
                                                    @php
                                                        $medicalProof = $medic->medical_proof;

                                                        // Jika null, inisialisasi sebagai array kosong
                                                        if (is_null($medicalProof)) {
                                                            $existingFiles = [];
                                                        } else {
                                                            // Coba decode JSON
                                                            $decoded = json_decode($medicalProof, true);

                                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                                // Jika decoding berhasil, gunakan hasilnya
                                                                $existingFiles = $decoded;
                                                            } else {
                                                                // Jika bukan JSON, asumsikan format lama (string biasa)
                                                                $existingFiles = [$medicalProof];
                                                            }
                                                        }

                                                        // Debug hasil akhir
                                                    @endphp
            
                                                    @foreach ($existingFiles as $file)
                                                        @php $extension = pathinfo($file, PATHINFO_EXTENSION); @endphp
                                                        <div class="file-preview" data-file="{{ $file }}" style="position: relative; display: inline-block; margin: 10px;">
                                                            @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'PNG', 'JPG', 'JPEG']))

                                                                <a href="{{ Storage::url($file) }}" target="_blank" rel="noopener noreferrer">
                                                                    <img src="{{ Storage::url($file) }}" alt="Proof Image" style="width: 100px; height: 100px; border: 1px solid rgb(221, 221, 221); border-radius: 5px; padding: 5px;">
                                                                </a>
                                                            @elseif($extension === 'pdf')
                                                                <a href="{{ Storage::url($file) }}" target="_blank" rel="noopener noreferrer">

                                                                    <img src="{{ asset('images/pdf_icon.png') }}" alt="PDF File">
                                                                    <p>Click to view PDF</p>
                                                                </a>
                                                            @else
                                                                <p>File type not supported.</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-danger">No proof uploaded</div>
                                    @endif
                                </div>
                            </div>

                            <input type="hidden" id="no_sppd" value="{{ $medic->no_medic }}">
                        </form>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#rejectReasonModal" style="padding: 0.5rem 1rem; margin-right: 10px">
                                Reject
                            </button>
                            <form method="POST"
                                action="{{ route('medical-approval-form.put', ['id' => $medic->usage_id]) }}"
                                style="display: inline-block; margin-right: 5px;" class="status-form"
                                id="approve-form-{{ $medic->usage_id }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_approval" value="Done">
                                <button type="button" class="btn btn-success rounded-pill approve-button"
                                    style="padding: 0.5rem 1rem;" data-id="{{ $medic->usage_id }}">
                                    Approve
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title" id="rejectReasonModalLabel" style="color: #333; font-weight: 600;">Rejection
                        Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="rejectReasonForm" method="POST"
                        action="{{ route('medical-approval-form.put', ['id' => $medic->usage_id]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_approval" value="Rejected">

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

    <!--<script src="{{ asset('/js/medical/medical-approval.js') }}"></script>-->
    <script>
        
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
                {
                    className: "none", // This will hide Disease and the 4 dynamic columns
                    targets: [8, 9, 10, 11, 12], // Disease (8) and the 4 dynamic medical columns (9-12)
                },
                {
                    responsivePriority: 1,
                    targets: 0,
                },

                {
                    responsivePriority: 4,
                    targets: 3,
                },
            ],
            order: [1, "asc"],
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
        });

        //Medical Form JS
        $(document).ready(function () {
            // Function to generate balance display based on selected types and year
            function generateBalanceDisplay(selectedTypes, selectedYear) {
                var balanceContainer = $("#balanceContainer");
                balanceContainer.empty(); // Clear previous balances

                if (selectedTypes && selectedTypes.length > 0) {
                    selectedTypes.forEach(function (type) {
                        // Fetch balance based on type and year
                        var balance = typeToBalanceMap[type]?.[selectedYear] || 0; // Default to 0 if not found
                        var balanceGroup = `
                        <div class="col-md-3 mb-3">
                            <label for="${type}" class="form-label">${type} Plafond</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="medical_plafond_${type}" class="form-control bg-light" value="${formatCurrency(
                            balance
                        )}" readonly>
                            </div>
                        </div>
                        `;
                        balanceContainer.append(balanceGroup); // Append the balance input dynamically
                    });
                }
            }

            // Function to handle changes in medical type and date
            function handleInputChange() {
                var selectedDate = $("#date").val();
                var selectedYear = selectedDate
                    ? new Date(selectedDate).getFullYear()
                    : null;
                var selectedTypes = $("#medical_type").val();

                if (selectedYear && selectedTypes) {
                    generateBalanceDisplay(selectedTypes, selectedYear);
                }
            }

            // Attach change event listeners to the medical type dropdown and date input
            $("#medical_type, #date").on("change", handleInputChange);

            // Initial load handling
            handleInputChange();

            // Utility function to format numbers as currency
            function formatCurrency(value) {
                return new Intl.NumberFormat("id-ID").format(value); // Format number as currency
            }
        });

        $(document).ready(function () {
            var typeToNameMap = {};
            medicalTypeData.forEach(function (type) {
                typeToNameMap[type.medical_type] = type.name;
            });

            // Function to generate dynamic forms based on selected types
            function generateDynamicForms(selectedTypes) {
                var dynamicForms = $("#dynamicForms");
                dynamicForms.empty();

                if (selectedTypes && selectedTypes.length > 0) {
                    selectedTypes.forEach(function (type) {
                        var balanceValue = balanceMapping[type] || ""; // Get the balance from mapping or set to empty
                        var formattedValue = formatCurrency(balanceValue); // Format the initial value
                        var formGroup = `
                        <div class="col-md-3 mb-3">
                            <label for="${type}" class="form-label">${type}</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency-input" id="${type}" name="medical_costs[${type}]" placeholder="0" value="${formattedValue}" disabled>
                            </div>
                        </div>
                    `;
                        dynamicForms.append(formGroup);
                    });

                    // Re-initialize currency formatting for new inputs
                    initCurrencyFormatting();
                }
            }

            // Event listener for the medical type dropdown
            $("#medical_type").on("change", function () {
                var selectedTypes = $(this).val();
                generateDynamicForms(selectedTypes);
            });

            function initCurrencyFormatting() {
                $(".currency-input")
                    .off("input")
                    .on("input", function () {
                        var value = $(this).val().replace(/\D/g, "");
                        $(this).val(formatCurrency(value));
                    });
            }

            function formatCurrency(value) {
                // Remove non-digit characters and parse as integer
                var numericValue =
                    parseInt(value.toString().replace(/\D/g, ""), 10) || 0;
                // Format the number
                return new Intl.NumberFormat("id-ID").format(numericValue);
            }

            // Initialize currency formatting
            initCurrencyFormatting();

            // Step to initialize the dynamic forms on page load with selected values
            var initialSelectedTypes = $("#medical_type").val();
            generateDynamicForms(initialSelectedTypes); // Call this function to set initial forms
        });

        $(document).ready(function () {
            var typeToNameMap = {};
            medicalTypeData.forEach(function (type) {
                typeToNameMap[type.medical_type] = type.name;
            });

            // Function to generate dynamic forms based on selected types
            function generateBpjsCoverContainer(selectedTypes) {
                var bpjsCoverContainer = $("#bpjsCoverContainer");
                bpjsCoverContainer.empty();

                if (selectedTypes && selectedTypes.length > 0) {
                    selectedTypes.forEach(function (type) {
                        var balanceValue = balanceBPJSMapping[type] || ""; // Get the balance from mapping or set to empty
                        var formattedValue = formatCurrency(balanceValue); // Format the initial value
                        var formGroup = `
                        <div class="col-md-3 mb-3">
                            <label for="${type}" class="form-label">${type} BPJS Cover</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency-input" id="${type}" name="medical_costs[${type}]" placeholder="0" value="${formattedValue}" disabled>
                            </div>
                        </div>
                    `;
                        bpjsCoverContainer.append(formGroup);
                    });

                    // Re-initialize currency formatting for new inputs
                    initCurrencyFormatting();
                }
            }

            // Event listener for the medical type dropdown
            $("#medical_type").on("change", function () {
                var selectedTypes = $(this).val();
                generateBpjsCoverContainer(selectedTypes);
            });

            function initCurrencyFormatting() {
                $(".currency-input")
                    .off("input")
                    .on("input", function () {
                        var value = $(this).val().replace(/\D/g, "");
                        $(this).val(formatCurrency(value));
                    });
            }

            function formatCurrency(value) {
                // Remove non-digit characters and parse as integer
                var numericValue =
                    parseInt(value.toString().replace(/\D/g, ""), 10) || 0;
                // Format the number
                return new Intl.NumberFormat("id-ID").format(numericValue);
            }

            // Initialize currency formatting
            initCurrencyFormatting();

            // Step to initialize the dynamic forms on page load with selected values
            var initialSelectedTypes = $("#medical_type").val();
            generateBpjsCoverContainer(initialSelectedTypes); // Call this function to set initial forms
        });

        // This function is kept outside for global access if needed
        function formatCurrency(input) {
            if (typeof input === "object" && input.value !== undefined) {
                // If input is an element
                let value = input.value.replace(/\D/g, "");
                if (value) {
                    value = (parseInt(value, 10) || 0).toLocaleString("id-ID");
                    input.value = value;
                }
            } else {
                // If input is a value
                let value = input.toString().replace(/\D/g, "");
                return (parseInt(value, 10) || 0).toLocaleString("id-ID");
            }
        }

        //date medical
        const today = new Date();
        // Set the date for two weeks ago
        const twoWeeksAgo = new Date();
        twoWeeksAgo.setDate(today.getDate() - 60);

        // Format the dates to YYYY-MM-DD
        const formattedToday = today.toISOString().split("T")[0];
        const formattedTwoWeeksAgo = twoWeeksAgo.toISOString().split("T")[0];

        // Set the min attribute for the input to two weeks ago
        const dateInput = document.getElementById("date");
        dateInput.setAttribute("min", formattedTwoWeeksAgo);
        dateInput.setAttribute("max", formattedToday); // Optional: To limit selection to today

    </script>
    <script>
        var medicalTypeData = @json($medical_type);
        var balanceMapping = @json($balanceMapping);
        var balanceBPJSMapping = @json($balanceBPJSMapping);
        var typeToBalanceMap = @json($balanceData);
    </script>
@endsection
