<script>
    var formCountPerdiem = 0;
    let perdiemData = [];

    window.addEventListener('DOMContentLoaded', function() {
        formCountPerdiem = document.querySelectorAll('#form-container-perdiem > div').length;
    });

    function isDateInRange(date, startDate, endDate) {
        const targetDate = new Date(date).setHours(0, 0, 0, 0);
        const start = new Date(startDate).setHours(0, 0, 0, 0);
        const end = new Date(endDate).setHours(0, 0, 0, 0);
        return targetDate >= start && targetDate <= end;
    }


    function isDateUsed(startDate, endDate, index) {
        // Cek apakah tanggal sudah digunakan di form lain
        return perdiemData.some(data => {
            if (data.index !== index) { // Cek untuk index yang berbeda
                // Cek apakah range tanggal bentrok dengan form lain
                return isDateInRange(startDate, data.startDate, data.endDate) ||
                    isDateInRange(endDate, data.startDate, data.endDate) ||
                    isDateInRange(data.startDate, startDate, endDate) ||
                    isDateInRange(data.endDate, startDate, endDate);
            }
            return false;
        });
    }


    function addMoreFormPerdiem(event) {
        event.preventDefault();
        formCountPerdiem++;
        const index = formCountPerdiem;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-perdiem-${formCountPerdiem}`;
        newForm.className = "card-body bg-light p-2 mb-3";
        newForm.innerHTML = `
            <div class="row">
                <!-- Company Code -->
                <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem ${formCountPerdiem}</p>
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="company_bt_perdiem${formCountPerdiem}">Company Code</label>
                    <select class="form-control select2" id="company_bt_perdiem_${formCountPerdiem}" name="company_bt_perdiem[]">
                        <option value="">Select Company...</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->contribution_level_code }}">
                                {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Location Agency -->
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="locationFilter">Location Agency</label>
                    <select class="form-control location-select" name="location_bt_perdiem[]" id="location_bt_perdiem_${formCountPerdiem}">
                        <option value="">Select Location...</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->area }}">
                                {{ $location->area . ' (' . $location->company_name . ')' }}
                            </option>
                        @endforeach
                        <option value="Others">Others</option>
                    </select>
                    <br>
                    <input type="text" name="other_location_bt_perdiem[]" class="form-control form-control-sm other-location" placeholder="Other Location" value="" style="display: none;">
                </div>
            </div>
            <div class="row">
                <!-- Start Perdiem -->
                <div class="col-md-4 mb-2">
                    <label class="form-label">Start Perdiem</label>
                    <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPerdiem(this)">
                </div>

                <!-- End Perdiem -->
                <div class="col-md-4 mb-2">
                    <label class="form-label">End Perdiem</label>
                    <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPerdiem(this)">
                </div>

                <!-- Total Days -->
                <div class="col-md-4 mb-2">
                    <label class="form-label">Total Days</label>
                    <div class="input-group">
                        <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="0" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">days</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount -->
            <div class="mb-2">
                <label class="form-label">Amount</label>
            </div>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">Rp</span>
                </div>
                <input class="form-control form-control-sm bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_${formCountPerdiem}" type="text" value="0" onchange="onNominalChange()">
            </div>
            <br>

            <!-- Action Buttons -->
            <div class="row mt-3">
                <div class="d-flex justify-start w-100">
                    <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormPerdiem(${formCountPerdiem}, event)">Reset</button>
                    <button class="btn btn-warning mr-2" onclick="removeFormPerdiem(${formCountPerdiem}, event)">Delete</button>
                </div>
            </div>
        `;
        document.getElementById("form-container-perdiem").appendChild(newForm);

        // Inisialisasi select2 setelah elemen baru ditambahkan
        $(`#company_bt_perdiem_${formCountPerdiem}, #location_bt_perdiem_${formCountPerdiem}`).select2({
            theme: "bootstrap-5",
        });

        $(`#company_bt_perdiem_${formCountPerdiem}, #location_bt_perdiem_${formCountPerdiem}`).on('change', function() {
            handleDateChange();
        });

        perdiemData.push({ index: index.toString(), startDate: '', endDate: '' });
        // console.log("Data Perdiem setelah Add More:", perdiemData);

        handleDateChange();
    }

    $('.btn-warning').click(function(event) {
        event.preventDefault();
        var index = $(this).closest('.card-body').index() + 1;
        removeFormPerdiem(index, event);
    });

    function removeFormPerdiem(index, event) {
        event.preventDefault();
        if (formCountPerdiem > 0) {
            const formContainer = document.getElementById(`form-container-bt-perdiem-${index}`);
            if (formContainer) {
                // const nominalInput = formContainer.querySelector(`#nominal_bt_perdiem_${index}`);
                const nominalInput = document.querySelector(`#nominal_bt_perdiem_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_perdiem"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_perdiem"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                formContainer.remove();

                perdiemData = perdiemData.filter(data => data.index !== index.toString());
                console.log("Data Perdiem setelah dihapus:", perdiemData); // Cek di console

                calculateTotalNominalBTPerdiem();
            }
        }
    }

    function removeFormPerdiemDec(index, event) {
        event.preventDefault();
        if (formCountPerdiem > 0) {
            const formContainer = document.getElementById(`form-container-bt-perdiem-dec-${index}`);
            if (formContainer) {
                // const nominalInput = formContainer.querySelector(`#nominal_bt_perdiem_${index}`);
                const nominalInput = document.querySelector(`#nominal_bt_perdiem_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_perdiem"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_perdiem"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                formContainer.remove();

                perdiemData = perdiemData.filter(data => data.index !== index.toString());
                console.log("Data Perdiem setelah dihapus:", perdiemData); // Cek di console

                calculateTotalNominalBTPerdiem();
            }
        }
    }

    function clearFormPerdiem(index, event) {
        event.preventDefault();
        if (formCountPerdiem > 0) {
            const nominalInput = document.querySelector(`#nominal_bt_perdiem_${index}`);
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(document.querySelector('input[name="total_bt_perdiem"]').value);
                total -= nominalValue;
                document.querySelector('input[name="total_bt_perdiem"]').value = formatNumber(total);
                nominalInput.value = 0;
                calculateTotalNominalBTTotal();
            }

            const formContainer = document.getElementById(`form-container-bt-perdiem-${index}`);
            if (formContainer) {
                formContainer.querySelectorAll('input[type="text"], input[type="date"]').forEach(input => {
                    input.value = '';
                });

                formContainer.querySelectorAll('input[type="number"]').forEach(input => {
                    input.value = 0;
                });

                const companyCodeSelect = formContainer.querySelector(`#company_bt_perdiem_${index}`);
                if (companyCodeSelect) {
                    companyCodeSelect.selectedIndex = 0; // Reset the select element to the default option
                    var event = new Event('change');
                    companyCodeSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
                }

                const locationSelect = formContainer.querySelector(`#location_bt_perdiem_${index}`);
                if (locationSelect) {
                    locationSelect.selectedIndex = 0; // Reset the select element to the default option
                    var event = new Event('change');
                    locationSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
                }

                formContainer.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });

                formContainer.querySelectorAll('textarea').forEach(textarea => {
                    textarea.value = '';
                });

                calculateTotalNominalBTTotal();
            }

            perdiemData = perdiemData.filter(data => data.index !== index.toString());
        }
    }

    function calculateTotalDaysPerdiem(input) {
        const formGroup = input.closest('.row').parentElement;
        const startDateInput = formGroup.querySelector('input.start-perdiem');
        const endDateInput = formGroup.querySelector('input.end-perdiem');
        const totalDaysInput = formGroup.querySelector('input.total-days-perdiem');
        const perdiemInput = document.getElementById('perdiem');
        const allowanceInput = formGroup.querySelector('input[name="nominal_bt_perdiem[]"]');

        const formIndex = formGroup.getAttribute('id').match(/\d+/)[0];
        // Cek apakah tanggal sudah digunakan di form lain
        if (isDateUsed(startDateInput.value, endDateInput.value, formIndex)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal telah digunakan',
                text: 'Silakan pilih tanggal yang berbeda!',
                timer: 2000
            });
            startDateInput.value = '';
            endDateInput.value = '';
            return;
        }

        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
                const diffTime = Math.abs(endDate - startDate);
                const totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDaysInput.value = totalDays;

                const perdiem = parseFloat(perdiemInput.value) || 0;
                let allowance = totalDays * perdiem;

                const locationSelect = formGroup.querySelector('select[name="location_bt_perdiem[]"]');
                const otherLocationInput = formGroup.querySelector('input[name="other_location_bt_perdiem[]"]');

                if (locationSelect.value === "Others" || otherLocationInput.value.trim() !== '') {
                    allowance *= 1;
                } else {
                    allowance *= 0.5;
                }

                allowanceInput.value = formatNumberPerdiem(allowance);
                calculateTotalNominalBTPerdiem();
            } else {
                totalDaysInput.value = 0;
                allowanceInput.value = 0;
            }
        } else {
            totalDaysInput.value = 0;
            allowanceInput.value = 0;
        }

        // Cek apakah data Perdiem untuk index ini sudah ada, jika ada update, jika belum tambahkan
        const existingPerdiemIndex = perdiemData.findIndex(data => data.index === formIndex);

        if (existingPerdiemIndex !== -1) {
            // Jika ada, perbarui data di array
            perdiemData[existingPerdiemIndex].startDate = startDateInput.value;
            perdiemData[existingPerdiemIndex].endDate = endDateInput.value;
        }  else {
            perdiemData.push({
                index: formIndex,
                startDate: startDateInput.value,
                endDate: endDateInput.value
            });
        }
    }

    function calculateTotalNominalBTPerdiem() {
        let total = 0;
        document.querySelectorAll('input[name="nominal_bt_perdiem[]"]').forEach(input => {
            total += cleanNumber(input.value);
        });
        document.querySelector('input[name="total_bt_perdiem"]').value = formatNumber(total);
        calculateTotalNominalBTTotal();
    }

    function onNominalChange() {
        calculateTotalNominalBTPerdiem();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation to handle changes on dynamically added select elements
        document.getElementById('form-container-perdiem').addEventListener('change', function(event) {
            if (event.target && event.target.classList.contains('location-select')) {
                toggleOthersBT(event.target);
            }
        });

        // Function to toggle the visibility of the 'Others' input field
        function toggleOthersBT(selectElement) {
            const formGroup = selectElement.closest('.mb-2').parentElement;
            const othersInput = formGroup.querySelector('input[name="other_location_bt_perdiem[]"]');
            if (selectElement.value === "Others") {
                othersInput.style.display = 'block';
                othersInput.required = true;
            } else {
                othersInput.style.display = 'none';
                othersInput.required = false;
                othersInput.value = "";
            }
        }

        // Add event listener to the existing select elements on page load
        document.querySelectorAll('.location-select').forEach(function(selectElement) {
            selectElement.addEventListener('change', function() {
                toggleOthersBT(this);
            });
        });
    });


</script>
@if (!empty($detailCA['detail_perdiem']) && $detailCA['detail_perdiem'][0]['start_date'] !== null)
    {{-- Form Edit --}}
    <div id="form-container-perdiem">
        @foreach ($detailCA['detail_perdiem'] as $index => $perdiem)
            <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="p-2 mb-4 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Perdiem {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-perdiem-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <div class="row">
                        <!-- Company Code -->
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Request</p>
                        <div class="col-md-6">
                            <table class="table" style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Company Code</th>
                                    <td class="colon" style="border: none; width:1%;">:</td>
                                    <td class="value" style="border: none;">{{ $perdiem['company_code'] }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Location Agency</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">
                                        @if($perdiem['location'] == 'Others')
                                            {{$perdiem['other_location']}}
                                        @else
                                            {{$perdiem['location']}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Amount</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">Rp. {{ number_format($perdiem['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table" style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Start Perdiem</th>
                                    <td class="colon" style="border: none; width:1%;">:</td>
                                    <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">End Perdiem</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Amount</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{$perdiem['total_days']}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-bt-perdiem-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                    <input type="hidden" value="{{$perdiem['location']}}" name="location_bt_perdiem[]">
                    @if (isset($declareCA['detail_perdiem'][$index]))
                        @php
                            $perdiem_dec = $declareCA['detail_perdiem'][$index];
                        @endphp
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-warning mr-2" onclick="removeFormPerdiemDec({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_perdiem'] as $index => $perdiem_dec)
            @if (!isset($detailCA['detail_perdiem'][$index]))
                <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem {{ $loop->index + 1 }}</p>
                    <div id="form-container-bt-perdiem-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-warning mr-2" onclick="removeFormPerdiem({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="{{ number_format(array_sum(array_column($declareCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@elseif (!empty($declareCA['detail_perdiem']) && $declareCA['detail_perdiem'][0]['start_date'] !== null)
    <div id="form-container-perdiem">
        @foreach ($declareCA['detail_perdiem'] as $index => $perdiem_dec)
            @if (!isset($detailCA['detail_perdiem'][$index]))
                <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem {{ $loop->index + 1 }}</p>
                    <div id="form-container-bt-perdiem-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-warning mr-2" onclick="removeFormPerdiem({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="{{ number_format(array_sum(array_column($declareCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@else
    {{-- Form Add --}}
    <div id="form-container-perdiem">
        <div id="form-container-bt-perdiem-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Perdiem 1</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                <div class="row">
                    <!-- Company Code -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="company_bt_perdiem1">Company Code</label>
                        <select class="form-control select2" id="company_bt_perdiem_1" name="company_bt_perdiem[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Agency -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="locationFilter">Location Agency</label>
                        <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_1" onchange="toggleOtherLocation(this, 1)">
                            <option value="">Select location...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->area }}">
                                    {{ $location->area." (".$location->company_name.")" }}
                                </option>
                            @endforeach
                            <option value="Others">Others</option>
                        </select>
                        <div id="other-location-1" class="mt-3">
                            <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Start Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Start Perdiem</label>
                        <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- End Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">End Perdiem</label>
                        <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- Total Days -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Amount</label>
                </div>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control form-control-sm bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_1" type="text" value="0" onchange="onNominalChange()">
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormPerdiem(1, event)">Reset</button>
                        <button class="btn btn-warning mr-2" onclick="removeFormPerdiem(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="0" readonly>
        </div>
    </div>
@endif

{{-- <script src="{{ asset('js/cashAdvanced/perdiem.js') }}"></script> --}}

<script>
    document.addEventListener("DOMContentLoaded", initializeDateInputs);
</script>
