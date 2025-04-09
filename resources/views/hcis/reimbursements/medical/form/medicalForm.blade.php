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
                            <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ $parentLink }}</a></li>
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
                        <h4 class="mb-0">Add Medical Usage</h4>
                        <a href="/medical" type="button" class="btn-close btn-close-white" aria-label="Close"></a>
                    </div>
                    <div class="card-body">
                        <form id="medicForm" action="/medical/form-add/post" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-4 mb-2">
                                    <label for="patient_name" class="form-label">Patient Name</label>
                                    <select class="form-select form-select-sm select2" id="patient_name" name="patient_name"
                                        required>
                                        <option value="" disabled selected>--- Choose Patient ---</option>
                                        <option value="{{ $employee_name->fullname }}">
                                            {{ $employee_name->fullname }} (Me)
                                        </option>
                                        @if (!$isProbation)
                                            @foreach ($families as $family)
                                                <option value="{{ $family->name }}">
                                                    {{ $family->name }} ({{ $family->relation_type }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label for="nama" class="form-label">Hospital/Clinic Name</label>
                                    <input type="text" class="form-control form-control-sm" id="hospital_name"
                                        name="hospital_name" placeholder="ex: RS. Murni Teguh" required>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="disease" class="form-label">Disease</label>
                                    <select class="form-select form-select-sm select2" id="disease" name="disease"
                                        required>
                                        <option value="" disabled selected>--- Choose Disease ---</option>
                                        @foreach ($diseases as $disease)
                                            @if ($disease->disease_name !== 'Dental (Scalling)' || !$hasScalling)
                                                <option value="{{ $disease->disease_name }}">
                                                    {{ $disease->disease_name_detail }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="keperluan" class="form-label">No. Invoice</label>
                                    <input type="text" class="form-control form-control-sm" id="no_invoice"
                                        name="no_invoice" rows="3" placeholder="Please add your invoice number ..."
                                        required></input>
                                </div>
                                <div class="col-md-6 mb-1">
                                    <label for="medical_date" class="form-label">Medical Date</label>
                                    <input type="date" class="form-control form-control-sm" id="date" name="date"
                                        required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label for="medical_type" class="form-label">Medical Type</label>
                                    <select class="form-select form-select-sm select2" id="medical_type"
                                        name="medical_type[]" multiple required>
                                        {{-- <option value="" selected>--- Choose Medical Type ---</option> --}}
                                        @foreach ($medical_type as $type)
                                        @if ($type->name !== 'Glasses' || !$hasGlasses) <!-- Existing condition for 'Glasses' -->
                                            @if ($type->name !== 'Maternity' || $isMarried) <!-- Exclude 'Maternity' if not married -->
                                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Dynamic Forms --}}
                            <div id="balanceContainer" class="row"></div>
                            <div id="dynamicForms" class="row"></div>

                            <div class="row mb-2">
                                <div class="col-md-12 mt-2">
                                    <label for="" class="form-label">Detail Information</label>
                                    <textarea class="form-control form-control-sm" id="coverage_detail" name="coverage_detail" rows="3"
                                        placeholder="Please add more detail of disease ..." required></textarea>
                                </div>
                            </div>
                            @php
                                use Illuminate\Support\Facades\Storage;
                            @endphp

                            <div class="col-md-12 mb-2 mt-2">
                                <label for="medical_proof" class="form-label">Upload Document</label>
                                <input type="file" id="medical_proof" name="medical_proof[]" accept="image/*, application/pdf" class="form-control mb-2" multiple onchange="previewFiles()">
                                @if (isset($medic->medical_proof) && $medic->medical_proof)
                                    <input type="hidden" name="existing_medical_proof" id="existing-prove-declare" value="{{ $transactions->medical_proof }}">
                                    <input type="hidden" name="removed_medical_proof" id="removed-prove-declare" value="[]">

                                    <!-- Preview untuk file lama -->
                                    <div id="existing-files-label" style="margin-bottom: 10px; font-weight: bold;">
                                        @if ($transactions->medical_proof)
                                            
                                            Document on Draft:
                                        @endif
                                    </div>
                                    <div id="existing-file-preview" class="mt-2">
                                        @if ($transactions->medical_proof)
                                            @php
                                                $existingFiles = json_decode($transactions->medical_proof, true);
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
                                @endif

                                <!-- Label untuk new file -->

                                <div id="new-files-label" style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;">
                                    New Document:
                                </div>

                                <div id="new-file-preview" class="mt-2"></div>

                            </div>
                            <input type="hidden" name="status" value="Pending L1" id="status">

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-outline-primary rounded-pill me-2 draft-button"
                                    name="action_draft" id="save-draft" value="Draft" id="save-draft">Save as
                                    Draft</button>
                                <button type="submit" class="btn btn-primary rounded-pill submit-button"
                                    name="action_submit" value="Pending" id="submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--<script src="{{ asset('/js/medical/medical.js') }}"></script>-->
    <script>
    //Medical Form JS
    $(document).ready(function () {
        // Handle change event on medical type selection
        $("#date, #medical_type").on("change", function () {
            const selectedDate = $("#date").val();
            const selectedYear = selectedDate
                ? new Date(selectedDate).getFullYear()
                : null;
            const selectedTypes = $("#medical_type").val();
            const balanceContainer = $("#balanceContainer");
    
            balanceContainer.empty(); // Clear previous balances
    
            if (selectedYear && selectedTypes && selectedTypes.length > 0) {
                selectedTypes.forEach(function (type) {
                    // Fetch the balance based on type and year
                    const balance = typeToBalanceMap[type]?.[selectedYear] || 0;
                    const balanceGroup = `
                    <div class="col-md-3 mb-3">
                        <label for="${type}" class="form-label">${type} Plafond (${selectedYear})</label>
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
        });
    
        function formatCurrency(value) {
            return new Intl.NumberFormat("id-ID").format(value); // Format number as currency
        }
    });
    $(document).ready(function () {
        var typeToNameMap = {};
        medicalTypeData.forEach(function (type) {
            typeToNameMap[type.medical_type] = type.name;
        });
    
        $("#medical_type").on("change", function () {
            var selectedTypes = $(this).val();
            var dynamicForms = $("#dynamicForms");
            dynamicForms.empty();
    
            if (selectedTypes && selectedTypes.length > 0) {
                selectedTypes.forEach(function (type) {
                    var formGroup = `
                    <div class="col-md-3 mb-3">
                        <label for="${type}" class="form-label">${type} Claim</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input" id="${type}" name="medical_costs[${type}]" placeholder="0" required>
                        </div>
                    </div>
                `;
                    dynamicForms.append(formGroup);
                });
    
                // Re-initialize currency formatting for new inputs
                initCurrencyFormatting();
            }
        });
    
        function initCurrencyFormatting() {
            $(".currency-input").on("input", function () {
                var value = $(this).val().replace(/\D/g, "");
                $(this).val(formatCurrency(value));
            });
        }
    
        function formatCurrency(value) {
            return new Intl.NumberFormat("id-ID").format(value);
        }
    
        // Initialize currency formatting
        initCurrencyFormatting();
    });
    
    function formatCurrency(input) {
        // Your currency formatting logic here
        let value = input.value.replace(/\D/g, ""); // Remove non-digit characters
        if (value) {
            value = (parseInt(value, 10) || 0).toLocaleString("id-ID"); // Format number
            input.value = value;
        }
    }
    
    // Ambil tanggal hari ini
    const today = new Date();

    // Set tanggal ke 2 bulan yang lalu
    const twoMonthsAgo = new Date();
    twoMonthsAgo.setMonth(today.getMonth() - 2);
    twoMonthsAgo.setDate(today.getDate() + 1); // Ditambah 1 agar hasilnya H-2 bulan + 1 hari

    // Format tanggal ke YYYY-MM-DD
    const formattedToday = today.toISOString().split("T")[0];
    const formattedTwoMonthsAgo = twoMonthsAgo.toISOString().split("T")[0];

    // Set atribut min dan max untuk input date
    const dateInput = document.getElementById("date");
    dateInput.setAttribute("min", formattedTwoMonthsAgo);
    dateInput.setAttribute("max", formattedToday); // Opsional: Batasi sampai hari ini

    </script>
    <script>
        var medicalTypeData = @json($medical_type);
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
                const fileInput = document.getElementById('medical_proof');
                fileInput.files = dataTransfer.files;
            }

            window.previewFiles = function () {
                const fileInput = document.getElementById('medical_proof');
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
