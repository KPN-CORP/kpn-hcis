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
                            <li class="breadcrumb-item"><a href="{{ route('medical.admin') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex bg-primary text-white justify-content-between">
                        <h4 class="mb-0">Medical Data - {{ $medic->no_medic }}</h4>
                        <a href="javascript:history.back()" type="button" class="btn-close btn-close-white"
                            aria-label="Close"></a>
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
                                    <textarea class="form-control form-control-sm" id="admin_notes" name="admin_notes" rows="3"
                                        placeholder="Note from Admin...">{{ $medic->admin_notes }}</textarea>
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
                                                                @elseif (in_array($extension, ['pdf', 'PDF']))
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
                                        <div class="text-danger">No Attachment uploaded</div>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="status" value="Pending" id="status">

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill submit-button"
                                    name="action_submit" value="Pending" id="submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <script src="{{ asset('/js/medical/medical-edit.js') }}"></script> --}}
    <script>
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

            function generateBpjsCoverForms(selectedTypes) {
                var bpjsCoverContainer = $("#bpjsCoverContainer");
                bpjsCoverContainer.empty(); // Hapus field BPJS Cover sebelumnya

                if (selectedTypes && selectedTypes.length > 0) {
                    selectedTypes.forEach(function (type) {
                        var formGroupBpjs = `
                        <div class="col-md-3 mb-3">
                            <label for="bpjs_cover_${type}" class="form-label">${type} BPJS Cover</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency-input" id="bpjs_cover_${type}" name="bpjs_cover[${type}]" placeholder="0" value="0" required>
                            </div>
                        </div>
                        `;
                        bpjsCoverContainer.append(formGroupBpjs);
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
                    generateBpjsCoverForms(selectedTypes);
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

        //Medical Form JS
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
                            <label for="${type}" class="form-label">${type} Claim</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency-input" id="${type}" name="medical_costs[${type}]" placeholder="0" value="${formattedValue}" required>
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
        var typeToBalanceMap = @json($balanceData);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.submit-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();

                    const form = document.getElementById('medicForm');

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    let hasInvalidCosts = false;
                    let exceededPlafond = false;
                    let exceededType = '';

                    document.querySelectorAll('[name^="medical_costs["]').forEach(input => {
                        let type = input.name.match(/\[(.*?)\]/)[1];
                        let value = input.value.replace(/\D/g,
                            ""); // Remove non-digit characters
                        let parsedValue = parseInt(value, 10) || 0; // Get the numeric value

                        // Get the plafond value for this medical type directly
                        let plafondInput = document.getElementById(
                            `medical_plafond_${type}`);
                        let plafondValue = plafondInput.value; // Directly take the value

                        // Remove any formatting like dots (for thousands) from the plafondValue
                        let plafondNumber = parseInt(plafondValue.replace(/\./g, ''), 10) ||
                            0; // Remove periods

                        if (parsedValue <= 0) {
                            hasInvalidCosts =
                                true; // Invalid if the value is zero or negative
                            return; // Skip further checks if invalid
                        }

                        // Check if the plafond is negative
                        if (plafondNumber < 0) {
                            if (parsedValue > 0) {
                                exceededPlafond = true;
                                exceededType = type;
                            }
                        } else {

                            if (parsedValue > plafondNumber) {
                                exceededPlafond = true;
                                exceededType = type;
                            }
                        }
                    });

                    // Show alert if the plafond is exceeded
                    if (exceededPlafond) {
                        Swal.fire({
                            title: "Plafond Exceeded",
                            text: `The cost for ${exceededType} exceeds the available plafond.`,
                            icon: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#AB2F2B",
                        });
                        return; // Prevent form submission
                    }
                    // If invalid costs exist, show a simple alert and stop submission
                    if (hasInvalidCosts) {
                        Swal.fire({
                            title: "Invalid Medical Costs",
                            text: "Please fill value for the medical type you selected.",
                            icon: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#AB2F2B",
                        });
                        return; // Prevent form submission
                    }

                    // Gather dynamic medical costs
                    let medicalCosts = {};
                    document.querySelectorAll('[name^="medical_costs["]').forEach(input => {
                        let type = input.name.match(/\[(.*?)\]/)[1];
                        let value = input.value.replace(/\D/g,
                            ""); // Remove non-digit characters
                        medicalCosts[type] = parseInt(value, 10) || 0;
                    });


                    // Create a table for medical costs
                    let medicalCostsTable = `
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <th colspan="3" style="text-align: left; padding: 8px;">Medical Costs</th>
            </tr>
            ${Object.entries(medicalCosts).map(([type, cost]) => `
                                                                                                                      <tr>
                                                                                                                        <td style="width: 40%; text-align: left; padding: 8px;">${type}</td>
                                                                                                                        <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                                                                                                                        <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${cost.toLocaleString('id-ID')}</strong></td>
                                                                                                                        </tr>
                                                                                                                        `).join('')}

                </table>
            `;

                    // Calculate total cost
                    const totalCost = Object.values(medicalCosts).reduce((sum, cost) => sum + cost,
                        0);

                    const inputSummary = `
        ${medicalCostsTable}
        <hr style="margin: 20px 0;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="width: 40%; text-align: left; padding: 8px;">Total Cost</td>
                <td style="width: 10%; text-align: right; padding: 8px;">:</td>
                <td style="width: 50%; text-align: left; padding: 8px;">Rp. <strong>${totalCost.toLocaleString('id-ID')}</strong></td>
            </tr>
        </table>
    `;

                    Swal.fire({
                        title: "Do you want to submit this request?",
                        html: `You won't be able to revert this!<br><br>${inputSummary}`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#AB2F2B",
                        cancelButtonColor: "#CCCCCC",
                        confirmButtonText: "Yes, submit it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = button.name;
                            input.value = button.value;

                            form.appendChild(input);
                            form.submit();
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
                    event.preventDefault();

                    const form = document.getElementById('medicForm');

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    let hasInvalidCosts = false;
                    let exceededPlafond = false;
                    let exceededType = ''; // To store which type exceeded the plafond
                    document.querySelectorAll('[name^="medical_costs["]').forEach(input => {
                        let type = input.name.match(/\[(.*?)\]/)[1];
                        let value = input.value.replace(/\D/g,
                            ""); // Remove non-digit characters
                        let parsedValue = parseInt(value, 10) || 0; // Get the numeric value

                        // Get the plafond value for this medical type directly
                        let plafondInput = document.getElementById(
                            `medical_plafond_${type}`);
                        let plafondValue = plafondInput.value; // Directly take the value

                        // Remove any formatting like dots (for thousands) from the plafondValue
                        let plafondNumber = parseInt(plafondValue.replace(/\./g, ''), 10) ||
                            0; // Remove periods

                        // Check if the cost is valid (must be greater than 0)
                        if (parsedValue <= 0) {
                            hasInvalidCosts =
                                true; // Invalid if the value is zero or negative
                            return; // Skip further checks if invalid
                        }

                        // Check if the plafond is negative
                        if (plafondNumber < 0) {
                            // If input is positive, show alert immediately
                            if (parsedValue > 0) {
                                exceededPlafond = true;
                                exceededType = type;
                            }
                        } else {
                            // Check if input exceeds plafond directly
                            if (parsedValue > plafondNumber) {
                                exceededPlafond = true;
                                exceededType = type;
                            }
                        }
                    });
                    // Show alert if the plafond is exceeded
                    if (exceededPlafond) {
                        Swal.fire({
                            title: "Plafond Exceeded",
                            text: `The cost for ${exceededType} exceeds the available plafond.`,
                            icon: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#AB2F2B",
                        });
                        return; // Prevent form submission
                    }
                    // If invalid costs exist, show a simple alert and stop submission
                    if (hasInvalidCosts) {
                        Swal.fire({
                            title: "Invalid Medical Costs",
                            text: "Please fill value for the medical type you selected.",
                            icon: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#AB2F2B",
                        });
                    } else {
                        // No invalid costs, submit the form immediately
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = button.name;
                        input.value = button.value;

                        form.appendChild(input);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
