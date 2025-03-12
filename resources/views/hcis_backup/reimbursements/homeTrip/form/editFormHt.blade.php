@extends('layouts_.vertical', ['page_title' => 'Home Trip'])

@section('css')
    <!-- Sertakan CSS Bootstrap jika diperlukan -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/css/bootstrap.min.css">
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
                            <li class="breadcrumb-item"><a href="{{ route('home-trip') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        @include('hcis.reimbursements.businessTrip.modal')
        <div class="d-sm-flex align-items-center justify-content-center">
            <div class="card col-md-12">
                <div class="card-header d-flex bg-primary text-white justify-content-between">
                    <h4 class="modal-title" id="viewFormEmployeeLabel">Edit Home Trip - <b>{{ $ticket->no_tkt }}</b></h4>
                    <a href="{{ route('home-trip') }}" type="button" class="btn btn-close btn-close-white"></a>
                </div>
                <div class="card-body" @style('overflow-y: auto;')>
                    <div class="container-fluid">
                        <form id="btEditForm" method="post"
                            action="{{ route('home-trip-form.put', ['id' => $ticket->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Name</label>
                                        <input type="text" name="name" id="name"
                                            value="{{ $employee_data->fullname }}"
                                            class="form-control bg-light form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Unit</label>
                                        <input type="text" name="unit" id="unit"
                                            value="{{ $employee_data->unit }}" class="form-control bg-light form-control-sm"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Grade</label>
                                        <input type="text" name="grade" id="grade"
                                            value="{{ $employee_data->job_level }}"
                                            class="form-control bg-light form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                            <div id="tiket_div">
                                <div class="d-flex flex-column gap-1" id="ticket_forms_container">
                                    <?php
                                    $maxForms = 5;
                                    $ticketCount = count($ticketData);

                                    if ($ticketCount === 0) {
                                        $ticketCount = 1;
                                        $ticketData = [null]; // Set an empty form data
                                    }

                                    for ($i = 0; $i < $ticketCount; $i++) :
                                        $ticket = $ticketData[$i];
                                        $displayNumber = $i + 1;
                                    ?>
                                    <div class="card bg-light shadow-none" id="ticket-form-<?php echo $i; ?>"
                                        style="display: <?php echo $i <= $ticketCount ? 'block' : 'none'; ?>;">
                                        <div class="card-body">
                                            <div class="h5 text-uppercase">
                                                <b>TICKET <?php echo $displayNumber; ?></b>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label" for="np_tkt">Passengers Name</label>
                                                    <select class="form-select form-select-sm select2"
                                                        id="np_tkt_<?php echo $i; ?>" name="np_tkt[]" required>
                                                        <option value="" disabled
                                                            {{ empty($ticket['np_tkt']) ? 'selected' : '' }}>--- Choose
                                                            Passengers ---</option>
                                                        @if ($employeeInHomeTrip)
                                                            <option value="{{ $employeeInHomeTrip->name }}"
                                                                {{ $ticket['np_tkt'] == $employeeInHomeTrip->name ? 'selected' : '' }}>
                                                                {{ $employeeInHomeTrip->name }} (Me)
                                                            </option>
                                                        @endif

                                                        @foreach ($familyMembers as $family)
                                                            <option value="{{ $family->name }}"
                                                                {{ $ticket['np_tkt'] == $family->name ? 'selected' : '' }}>
                                                                {{ $family->name }} ({{ $family->relation_type }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label" for="">NIK</label>
                                                    <div class="mb-2">
                                                        <input type="number" name="noktp_tkt[]"
                                                            id="noktp_tkt_<?php echo $i; ?>"
                                                            class="form-control form-control-sm" required
                                                            placeholder="No KTP" value="{{ $ticket['noktp_tkt'] ?? '' }}"
                                                            oninput="if(this.value.length > 16) this.value = this.value.slice(0, 16);">
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label"
                                                        for="jenis_tkt_<?php echo $i; ?>">Transportation Type</label>
                                                    <div class="input-group">
                                                        <select class="form-select form-select-sm" name="jenis_tkt[]"
                                                            id="jenis_tkt_<?php echo $i; ?>" required>
                                                            <option value=""
                                                                {{ empty($ticket['jenis_tkt']) ? 'selected' : '' }}>Select
                                                                Transportation Type</option>
                                                            <option value="Train"
                                                                {{ $ticket['jenis_tkt'] == 'Train' ? 'selected' : '' }}>
                                                                Train</option>
                                                            <option value="Bus"
                                                                {{ $ticket['jenis_tkt'] == 'Bus' ? 'selected' : '' }}>Bus
                                                            </option>
                                                            <option value="Airplane"
                                                                {{ $ticket['jenis_tkt'] == 'Airplane' ? 'selected' : '' }}>
                                                                Airplane</option>
                                                            <option value="Car"
                                                                {{ $ticket['jenis_tkt'] == 'Car' ? 'selected' : '' }}>Car
                                                            </option>
                                                            <option value="Ferry"
                                                                {{ $ticket['jenis_tkt'] == 'Ferry' ? 'selected' : '' }}>
                                                                Ferry</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">From</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm" name="dari_tkt[]"
                                                            type="text" placeholder="ex. Yogyakarta (YIA)" required
                                                            value="{{ $ticket['dari_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">To</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm" name="ke_tkt[]"
                                                            type="text" placeholder="ex. Jakarta (CGK)" required
                                                            value="{{ $ticket['ke_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label for="type_tkt_<?php echo $i; ?>" class="form-label">Ticket
                                                        Type</label>
                                                    <select class="form-select form-select-sm" name="type_tkt[]" required>
                                                        <option value="One Way"
                                                            {{ $ticket['type_tkt'] == 'One Way' ? 'selected' : '' }}>One
                                                            Way</option>
                                                        <option value="Round Trip"
                                                            {{ $ticket['type_tkt'] == 'Round Trip' ? 'selected' : '' }}>
                                                            Round Trip</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Date</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm"
                                                            id="tgl_brkt_tkt_<?php echo $i; ?>" name="tgl_brkt_tkt[]"
                                                            type="date" onchange="validateDates(<?php echo $i; ?>)"
                                                            required value="{{ $ticket['tgl_brkt_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Time</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm"
                                                            id="jam_brkt_tkt_<?php echo $i; ?>" name="jam_brkt_tkt[]"
                                                            type="time" onchange="validateDates(<?php echo $i; ?>)"
                                                            required value="{{ $ticket['jam_brkt_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="round-trip-options"
                                                style="display: {{ isset($ticket['type_tkt']) && $ticket['type_tkt'] == 'Round Trip' ? 'block' : 'none' }};">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Return Date</label>
                                                        <div class="input-group">
                                                            <input class="form-control form-control-sm"
                                                                name="tgl_plg_tkt[]" type="date"
                                                                id="tgl_plg_tkt_<?php echo $i; ?>"
                                                                onchange="validateDates(<?php echo $i; ?>)"
                                                                value="{{ $ticket['tgl_plg_tkt'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Return Time</label>
                                                        <div class="input-group">
                                                            <input class="form-control form-control-sm"
                                                                id="jam_plg_tkt_<?php echo $i; ?>" name="jam_plg_tkt[]"
                                                                type="time"
                                                                onchange="validateDates(<?php echo $i; ?>)"
                                                                value="{{ $ticket['jam_plg_tkt'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-2">
                                                    <label class="form-label">Information</label>
                                                    <textarea class="form-control" name="ket_tkt[]" rows="3" placeholder="Add ticket details" required>{{ $ticket['ket_tkt'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger remove-ticket-btn"
                                                    id="remove-ticket-btn" data-form-id="<?php echo $i; ?>">Remove
                                                    Data</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary add-ticket-btn mb-2"
                                    id="add-ticket-btn">Add Home Trip Data</button>
                            </div>


                            <input type="hidden" name="status" value="Pending L1" id="status">
                            <input type="hidden" id="formActionType" name="formActionType" value="">


                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-outline-primary rounded-pill me-2"
                                    name="action_draft" id="save-draft" value="Draft" id="save-draft">Save as
                                    Draft</button>
                                <button type="submit" class="btn btn-primary rounded-pill submit-button"
                                    name="action_submit" value="Pending L1" id="submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

                    // Show SweetAlert confirmation with the input summary
                    Swal.fire({
                        title: "Do you want to submit this request?",
                        html: `You won't be able to revert this!`, // Use 'html' instead of 'text'
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#AB2F2B",
                        cancelButtonColor: "#CCCCCC",
                        confirmButtonText: "Yes, submit it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const input = document.createElement('input');
                            input.type =
                                'hidden'; // Hidden input so it doesn't show in the form
                            input.name = button.name; // Use the button's name attribute
                            input.value = button.value; // Use the button's value attribute

                            form.appendChild(input); // Append the hidden input to the form
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    <script>
        function validateDates(index) {
            const departureDateInput = document.getElementById(`tgl_brkt_tkt_${index}`);
            const returnDateInput = document.getElementById(`tgl_plg_tkt_${index}`);
            const departureTimeInput = document.getElementById(`jam_brkt_tkt_${index}`);
            const returnTimeInput = document.getElementById(`jam_plg_tkt_${index}`);

            if (
                departureDateInput &&
                returnDateInput &&
                departureTimeInput &&
                returnTimeInput
            ) {
                const departureDate = new Date(departureDateInput.value);
                const returnDate = new Date(returnDateInput.value);

                // Check if return date is earlier than departure date
                if (returnDate < departureDate) {
                    Swal.fire({
                        title: "Warning!",
                        text: "Return date cannot be earlier than the departure date.",
                        icon: "error",
                        confirmButtonColor: "#AB2F2B",
                        confirmButtonText: "OK",
                    });
                    returnDateInput.value = ""; // Reset the return date if it's invalid
                    return; // Stop further validation
                }

                // If the dates are the same, check the times
                if (returnDate.getTime() === departureDate.getTime()) {
                    const departureTime = departureTimeInput.value;
                    const returnTime = returnTimeInput.value;

                    if (departureTime && returnTime && returnTime < departureTime) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Return time cannot be earlier than the departure time.",
                            icon: "error",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        returnTimeInput.value = ""; // Reset the return time if it's invalid
                    }
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            let formTicketCount =
                document.querySelectorAll('[id^="ticket-form-"]').length || 1;
            const maxTicketForms = 5;
            const ticketFormsContainer = document.getElementById("ticket_forms_container");
            const addTicketButton = document.getElementById("add-ticket-btn");

            function toggleRequiredAttributes(form, isRequired) {
                const fields = [
                    'input[name="jk_tkt[]"]',
                    'input[name="np_tkt[]"]',
                    'input[name="noktp_tkt[]"]',
                    'input[name="tlp_tkt[]"]',
                    'input[name="dari_tkt[]"]',
                    'input[name="ke_tkt[]"]',
                    'input[name="tgl_brkt_tkt[]"]',
                    'input[name="jam_brkt_tkt[]"]',
                    'select[name="jenis_tkt[]"]',
                    'select[name="type_tkt[]"]',
                ];

                fields.forEach((selector) => {
                    const field = form.querySelector(selector);
                    if (field) {
                        if (isRequired) {
                            field.setAttribute("required", "");
                        } else {
                            field.removeAttribute("required");
                        }
                    }
                });

                const typeSelect = form.querySelector('select[name="type_tkt[]"]');
                const returnDateField = form.querySelector('input[name="tgl_plg_tkt[]"]');
                const returnTimeField = form.querySelector('input[name="jam_plg_tkt[]"]');

                // Update required fields for round trip option
                function updateReturnFields() {
                    if (isRequired && typeSelect && typeSelect.value === "Round Trip") {
                        returnDateField.setAttribute("required", "");
                        returnTimeField.setAttribute("required", "");
                    } else {
                        returnDateField && returnDateField.removeAttribute("required");
                        returnTimeField && returnTimeField.removeAttribute("required");
                    }
                }

                if (typeSelect) {
                    typeSelect.addEventListener("change", updateReturnFields);
                    updateReturnFields();
                }
            }

            function updateFormNumbers() {
                const forms = ticketFormsContainer.querySelectorAll('[id^="ticket-form-"]');
                forms.forEach((form, index) => {
                    const formNumber = index + 1;
                    form.querySelector(".h5.text-uppercase b").textContent = `TICKET ${formNumber}`;
                    form.id = `ticket-form-${formNumber}`;
                    form.querySelector(".remove-ticket-btn").dataset.formId = formNumber;

                    updateFormElementIds(form, formNumber);
                });
                formTicketCount = forms.length;
                updateRemoveButtons();
                updateAddButtonVisibility();
            }

            function updateAddButtonVisibility() {
                addTicketButton.style.display = formTicketCount < maxTicketForms ? "inline-block" : "none";
            }

            function updateFormElementIds(form, formNumber) {
                const elements = form.querySelectorAll("[id],[name],[onchange]");
                elements.forEach((element) => {
                    // Update IDs
                    if (element.id) {
                        element.id = element.id.replace(/\d+$/, formNumber);
                    }
                    // Update names
                    if (element.name) {
                        element.name = element.name.replace(/\[\d*\]/, `[${formNumber}]`);
                    }
                    // Update onchange attributes
                    if (element.hasAttribute("onchange")) {
                        const onchangeValue = element.getAttribute("onchange");
                        const updatedOnchangeValue = onchangeValue.replace(/\d+/, formNumber);
                        element.setAttribute("onchange", updatedOnchangeValue);
                    }
                });
            }

            function updateRemoveButtons() {
                const removeButtons = document.querySelectorAll(".remove-ticket-btn");
                removeButtons.forEach((button) => {
                    button.style.display = formTicketCount > 1 ? "inline-block" : "none";
                });
            }


            function addNewTicketForm() {
                if (formTicketCount < maxTicketForms) {
                    formTicketCount++;
                    const newTicketForm = createNewTicketForm(formTicketCount);
                    ticketFormsContainer.insertAdjacentHTML("beforeend", newTicketForm);
                    const addedForm = ticketFormsContainer.lastElementChild;
                    toggleRequiredAttributes(addedForm, true); // Making all fields required by default
                    updateFormNumbers();
                    initializeAllSelect2();
                } else {
                    Swal.fire({
                        title: "Warning!",
                        text: "You have reached the maximum number of tickets (5).",
                        icon: "error",
                        confirmButtonColor: "#AB2F2B",
                        confirmButtonText: "OK",
                    });
                }
            }

            document.getElementById("add-ticket-btn").addEventListener("click", addNewTicketForm);

            ticketFormsContainer.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-ticket-btn")) {
                    const formId = e.target.dataset.formId;
                    document.getElementById(`ticket-form-${formId}`).remove();
                    updateFormNumbers();
                }
            });

            ticketFormsContainer.addEventListener("change", function(e) {
                if (e.target.name && e.target.name.startsWith("type_tkt")) {
                    const roundTripOptions = e.target.closest(".card-body").querySelector(
                        ".round-trip-options");
                    if (roundTripOptions) {
                        if (e.target.value === "Round Trip") {
                            roundTripOptions.style.display = "block";
                        } else {
                            roundTripOptions.style.display = "none";

                            // Reset values within roundTripOptions
                            const inputs = roundTripOptions.querySelectorAll("input, select, textarea");
                            inputs.forEach(input => {
                                if (input.type === "checkbox" || input.type === "radio") {
                                    input.checked = false;
                                } else {
                                    input.value = "";
                                }
                            });
                        }
                    }
                }
            });

            function initializeAllSelect2() {
                document.querySelectorAll(".selection2").forEach(function(selectElement) {
                    $(selectElement).select2({
                        theme: "bootstrap-5",
                        width: "100%",
                        minimumInputLength: 0,
                        allowClear: true,
                        placeholder: "Please Select Passengers",
                        ajax: {
                            url: "/search/passengers",
                            dataType: "json",
                            delay: 250,
                            data: function(params) {
                                return {
                                    searchTerm: params.term || "",
                                    page: params.page || 1,
                                };
                            },
                            processResults: function(data, params) {
                                params.page = params.page || 1;

                                // Combine employee and dependents into a single results array
                                const results = [];

                                // Add the employee data first (if any)
                                if (data.employee && data.employee.length > 0) {
                                    data.employee.forEach(function(employee) {
                                        results.push({
                                            id: employee.fullname,
                                            text: `${employee.fullname} (${employee.relation_type})`,
                                        });
                                    });
                                }

                                // Add dependents data next (if any)
                                if (data.dependents && data.dependents.length > 0) {
                                    data.dependents.forEach(function(dependent) {
                                        results.push({
                                            id: dependent.fullname,
                                            text: `${dependent.fullname} (${dependent.relation_type})`,
                                        });
                                    });
                                }

                                return {
                                    results: results,
                                    pagination: {
                                        more: params.page * 30 < data
                                            .total_count, // Adjust as needed
                                    },
                                };
                            },
                            cache: true,
                        },
                    });
                });
            }

            // Call the function to initialize Select2 for existing forms
            initializeAllSelect2();

            function createNewTicketForm(formNumber) {
                return `
        <div class="card bg-light shadow-none" id="ticket-form-${formNumber}" style="display: block;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>TICKET ${formNumber}</b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label" for="jk_tkt">Passengers Name</label>
                        <select class="form-select form-select-sm selection2" id="np_tkt_${formNumber}" name="np_tkt[]" required>
                            <option value="" selected>--- Choose Passengers ---</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                         <label class="form-label" for="jk_tkt">NIK</label>
                        <div class="mb-2">
                            <input type="number" name="noktp_tkt[]" id="noktp_tkt_${formNumber}" class="form-control form-control-sm" required placeholder="No KTP" oninput="if(this.value.length > 16) this.value = this.value.slice(0, 16);">
                        </div>
                    </div>
                     <div class="col-md-4 mb-2">
                        <label class="form-label" for="jenis_tkt_${formNumber}">Transportation Type</label>
                        <div class="input-group">
                            <select class="form-select form-select-sm" name="jenis_tkt[]" id="jenis_tkt_${formNumber}" required>
                                <option value="">Select Transportation Type</option>
                                <option value="Train">Train</option>
                                <option value="Bus">Bus</option>
                                <option value="Airplane">Airplane</option>
                                <option value="Car">Car</option>
                                <option value="Ferry">Ferry</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-2">
                        <label class="form-label">From</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="dari_tkt[]" type="text" placeholder="ex. Yogyakarta (YIA)" required>
                        </div>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="form-label">To</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="ke_tkt[]" type="text" placeholder="ex. Jakarta (CGK)" required>
                        </div>
                    </div>
                      <div class="col-md-2 mb-2">
                        <label for="type_tkt_${formNumber}" class="form-label">Ticket Type</label>
                        <select class="form-select form-select-sm" name="type_tkt[]" required>
                            <option value="One Way" selected>One Way</option>
                            <option value="Round Trip">Round Trip</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" id="tgl_brkt_tkt_${formNumber}" name="tgl_brkt_tkt[]" type="date" onchange="validateDates(${formNumber})" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Time</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" id="jam_brkt_tkt_${formNumber}" name="jam_brkt_tkt[]" type="time" onchange="validateDates(${formNumber})" required>
                        </div>
                    </div>
                </div>

                <div class="round-trip-options" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Return Date</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="tgl_plg_tkt[]" type="date" id="tgl_plg_tkt_${formNumber}" onchange="validateDates(${formNumber})">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Return Time</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" id="jam_plg_tkt_${formNumber}" name="jam_plg_tkt[]" type="time" onchange="validateDates(${formNumber})">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label for="ket_tkt_${formNumber}" class="form-label">Information</label>
                        <textarea class="form-control" id="ket_tkt_${formNumber}" name="ket_tkt[]" rows="3" placeholder="This field is for adding ticket details, e.g., Citilink, Garuda Indonesia, etc." required></textarea>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-ticket-btn" id="remove-ticket-btn">Remove Data</button>
                </div>
            </div>
        </div>`;
            }

        });
    </script>
@endsection
