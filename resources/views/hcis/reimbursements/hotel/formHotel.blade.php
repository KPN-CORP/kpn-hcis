@extends('layouts_.vertical', ['page_title' => 'Hotel'])

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
                            <li class="breadcrumb-item"><a href="{{ route('hotel') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="d-sm-flex align-items-center justify-content-center">
            <div class="card col-md-12">
                <div class="card-header d-flex bg-primary text-white justify-content-between">
                    <h4 class="modal-title" id="viewFormEmployeeLabel">Add Hotel Data</h4>
                    <a href="{{ route('hotel') }}" type="button" class="btn btn-close btn-close-white"></a>
                </div>
                <div class="card-body" @style('overflow-y: auto;')>
                    <div class="container-fluid">
                        <form id="btEditForm" method="post" action="{{ route('hotel.submit') }}">@csrf
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="mb-2">
                                        <label class="form-label" for="name">Business Trip Number</label>
                                        <select class="form-select select2 form-select-sm" id="bisnis_numb"
                                            name="bisnis_numb">
                                            <option value="-">No Business Trip</option>
                                            @foreach ($no_sppds as $no_sppd)
                                                <option value="{{ $no_sppd->no_sppd }}">{{ $no_sppd->no_sppd }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="mb-2">
                                        <label class="form-label" for="contribution_level_code">Costing Company</label>
                                        <select class="form-control select2 form-select-sm" id="contribution_level_code"
                                            name="contribution_level_code" required>
                                            <option value="" selected disabled>Select Costing Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->contribution_level_code }}">
                                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Hotel Forms Start -->
                            <div id="hotel_div">
                                <div class="d-flex flex-column gap-1" id="hotel_forms_container">
                                    <?php
                                    $i = 1;
                                    // for ($i = 1; $i <= 5; $i++) :
                                    ?>
                                    <div class="card bg-light shadow-none" id="hotel-form-<?php echo $i; ?>"
                                        style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
                                        <div class="card-body">
                                            <div class="h5 text-uppercase">
                                                <b>Hotel <?php echo $i; ?></b>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Hotel Name</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm" name="nama_htl[]"
                                                            type="text" placeholder="ex: Hyatt" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Hotel Location</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm" name="lokasi_htl[]"
                                                            type="text" placeholder="ex: Jakarta" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Bed Size</label>
                                                    <select class="form-select form-select-sm select2" name="bed_htl[]"
                                                        required>
                                                        <option value="Single Bed">Single Bed</option>
                                                        <option value="Twin Bed">Twin Bed</option>
                                                        <option value="King Bed">King Bed</option>
                                                        <option value="Super King Bed">Super King Bed</option>
                                                        <option value="Extra Bed">Extra Bed</option>
                                                        <option value="Baby Cot">Baby Cot</option>
                                                        <option value="Sofa Bed">Sofa Bed</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Total Room</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm" name="jmlkmr_htl[]"
                                                            type="number" min="1" placeholder="ex: 1" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Check In Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="tgl_masuk_htl[]" id="check-in-<?php echo $i; ?>"
                                                        onchange="calculateTotalDays(<?php echo $i; ?>)" required>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Check Out Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="tgl_keluar_htl[]" id="check-out-<?php echo $i; ?>"
                                                        onchange="calculateTotalDays(<?php echo $i; ?>)" required>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Total Nights</label>
                                                    <input type="number" class="form-control form-control-sm bg-light"
                                                        name="total_hari[]" id="total-days-<?php echo $i; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger remove-hotel-btn">Remove
                                                    Data</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary add-hotel-btn mb-2">Add Hotel
                                    Data</button>
                            </div>
                            <!-- Dynamic Hotel Forms End -->

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
        //Hotel Validation Date
        function calculateTotalDays(index) {
            const checkInInput = document.getElementById(`check-in-${index}`);
            const checkOutInput = document.getElementById(`check-out-${index}`);
            const totalDaysInput = document.getElementById(`total-days-${index}`);

            if (!checkInInput || !checkOutInput) {
                return; // Ensure elements are present before proceeding
            }

            // Parse the dates
            const checkInDate = new Date(checkInInput.value);
            const checkOutDate = new Date(checkOutInput.value);
            console.log(checkInDate);
            console.log(checkOutDate);


            // Validate Check In Date
            // Ensure Check Out Date is not earlier than Check In Date
            if (checkOutDate < checkInDate) {
                Swal.fire({
                    title: "Warning!",
                    text: "Check Out date cannot be earlier than Check In date.",
                    icon: "error",
                    confirmButtonColor: "#AB2F2B",
                    confirmButtonText: "OK",
                });
                checkOutInput.value = ""; // Reset the Check Out field
                totalDaysInput.value = ""; // Clear total days
                return;
            }

            // Calculate the total days if all validations pass
            if (checkInDate && checkOutDate) {
                // Check if same day
                if (checkInDate.getTime() === checkOutDate.getTime()) {
                    totalDaysInput.value = 1;
                } else {
                    // Calculate difference in days including both start and end dates
                    const diffTime = checkOutDate - checkInDate;
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    totalDaysInput.value = diffDays;
                }
            } else {
                totalDaysInput.value = "";
            }
        }

        // Attach event listeners to the hotel forms
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".hotel-form").forEach((form, index) => {
                const i = index + 1; // Adjust for 1-based index

                form.querySelector('input[name="tgl_masuk_htl[]"]').addEventListener(
                    "change",
                    () => calculateTotalDays(i)
                );
                form.querySelector('input[name="tgl_keluar_htl[]"]').addEventListener(
                    "change",
                    () => calculateTotalDays(i)
                );
            });
        });
    </script>
    <script>
        //Hotel JS
        document.addEventListener("DOMContentLoaded", function() {
            let formHotelCount = 1;
            const maxHotelForms = 5;
            const hotelFormsContainer = document.getElementById("hotel_forms_container");
            const addHotelButton = document.querySelector(".add-hotel-btn");

            function updateFormNumbers() {
                const forms = hotelFormsContainer.querySelectorAll('[id^="hotel-form-"]');
                forms.forEach((form, index) => {
                    const formNumber = index + 1;
                    form.querySelector(".h5.text-uppercase b").textContent = `Hotel ${formNumber}`;
                    form.id = `hotel-form-${formNumber}`;
                    form.querySelector(".remove-hotel-btn").dataset.formId = formNumber;

                    updateFormElementIds(form, formNumber);
                });
                formHotelCount = forms.length;
                updateButtonVisibility();
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

            function toggleRequiredAttributes(form, isRequired) {
                const fields = [
                    'input[name="nama_htl[]"]',
                    'input[name="lokasi_htl[]"]',
                    'select[name="bed_htl[]"]',
                    'input[name="jmlkmr_htl[]"]',
                    'input[name="tgl_masuk_htl[]"]',
                    'input[name="tgl_keluar_htl[]"]',
                ];

                fields.forEach((selector) => {
                    const field = form.querySelector(selector);
                    if (field) {
                        field.required = isRequired;
                    }
                });
            }

            function toggleRequiredAttributes(form, isRequired = true) {
                // Loop through each input field in the form
                form.querySelectorAll('input, select, textarea').forEach((input) => {
                    if (isRequired) {
                        input.setAttribute('required', 'required'); // Add required attribute
                    } else {
                        input.removeAttribute('required'); // Remove required attribute
                    }
                });
            }


            function updateButtonVisibility() {
                addHotelButton.style.display = formHotelCount < maxHotelForms ? "inline-block" : "none";
                const removeButtons = hotelFormsContainer.querySelectorAll(".remove-hotel-btn");
                removeButtons.forEach((button) => {
                    button.style.display = formHotelCount > 1 ? "inline-block" : "none";
                });
            }

            function addNewHotelForm() {
                if (formHotelCount < maxHotelForms) {
                    formHotelCount++;
                    const newHotelForm = createNewHotelForm(formHotelCount);
                    hotelFormsContainer.insertAdjacentHTML("beforeend", newHotelForm);
                    const addedForm = hotelFormsContainer.lastElementChild;

                    // Make the new form fields required
                    toggleRequiredAttributes(addedForm, true);

                    updateFormNumbers(); // Update form numbering if applicable
                    updateButtonVisibility(); // Adjust visibility of buttons

                } else {
                    Swal.fire({
                        title: "Warning!",
                        text: "You have reached the maximum number of hotels (5).",
                        icon: "error",
                        confirmButtonColor: "#AB2F2B",
                        confirmButtonText: "OK",
                    });
                }
            }

            addHotelButton.addEventListener("click", addNewHotelForm);

            hotelFormsContainer.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-hotel-btn")) {
                    const formId = e.target.dataset.formId;
                    document.getElementById(`hotel-form-${formId}`).remove();
                    updateFormNumbers();
                }
            });

            function createNewHotelForm(formNumber) {
                return `
                <div class="card bg-light shadow-none" id="hotel-form-${formNumber}" style="display: block;">
                    <div class="card-body">
                        <div class="h5 text-uppercase">
                            <b>Hotel ${formNumber}</b>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Hotel Name</label>
                                <div class="input-group">
                                    <input class="form-control form-control-sm" name="nama_htl[]" type="text" placeholder="ex: Hyatt">
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Hotel Location</label>
                                <div class="input-group">
                                    <input class="form-control form-control-sm" name="lokasi_htl[]" type="text" placeholder="ex: Jakarta">
                                </div>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Bed Size</label>
                                <select class="form-select form-select-sm" name="bed_htl[]">
                                    <option value="Single Bed">Single Bed</option>
                                    <option value="Twin Bed">Twin Bed</option>
                                    <option value="King Bed">King Bed</option>
                                    <option value="Super King Bed">Super King Bed</option>
                                    <option value="Extra Bed">Extra Bed</option>
                                    <option value="Baby Cot">Baby Cot</option>
                                    <option value="Sofa Bed">Sofa Bed</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Total Room</label>
                                <div class="input-group">
                                    <input class="form-control form-control-sm" name="jmlkmr_htl[]" type="number" min="1" placeholder="ex: 1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Check In Date</label>
                                <input type="date" class="form-control form-control-sm" id="check-in-${formNumber}" name="tgl_masuk_htl[]" onchange="calculateTotalDays(${formNumber})">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Check Out Date</label>
                                <input type="date" class="form-control form-control-sm" id="check-out-${formNumber}" name="tgl_keluar_htl[]" onchange="calculateTotalDays(${formNumber})">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Nights</label>
                                <input type="number" class="form-control form-control-sm bg-light" id="total-days-${formNumber}" name="total_hari[]" readonly>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn" data-form-id="${formNumber}">Remove Data</button>
                        </div>
                    </div>
                </div>`;
            }

            // Initial setup
            updateButtonVisibility();
        });
    </script>
@endsection
