<script src="{{ asset('/js/btCashAdvanced/penginapan.js') }}"></script>
<script>
    var formCountPenginapan = 0;

    window.addEventListener('DOMContentLoaded', function() {
        formCountPenginapan = document.querySelectorAll('#form-container-penginapan > div').length;
    });

    function addMoreFormPenginapanDec(event) {
        event.preventDefault();
        formCountPenginapan++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-penginapan-${formCountPenginapan}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Penginapan ${formCountPenginapan}</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Penginapan Declaration</p>
                <div class="row">
                    <!-- Penginapan Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation Start Plan</label>
                        <input type="date" name="start_bt_penginapan[]"
                            id="start_bt_penginapan_${formCountPenginapan}"
                            class="form-control start-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_${formCountPenginapan}'), document.querySelector('#total_days_bt_penginapan_${formCountPenginapan}'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation End Plan</label>
                        <input type="date" name="end_bt_penginapan[]"
                            id="end_bt_penginapan_${formCountPenginapan}"
                            class="form-control end-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_${formCountPenginapan}'), this, document.querySelector('#total_days_bt_penginapan_${formCountPenginapan}'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input
                                class="form-control bg-light total-days-penginapan"
                                id="total_days_bt_penginapan_${formCountPenginapan}"
                                name="total_days_bt_penginapan[]"
                                type="number" min="0"
                                value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <input type="text"
                            name="hotel_name_bt_penginapan[]"
                            class="form-control" placeholder="ex: Westin">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Company Code</label>
                        <select class="form-control select2"
                            id="company_bt_penginapan_${formCountPenginapan}"
                            name="company_bt_penginapan[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control"
                                name="nominal_bt_penginapan[]"
                                id="nominal_bt_penginapan_${formCountPenginapan}" type="text"
                                min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInput(this)"
                                onblur="formatOnBlur(this)">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan(${formCountPenginapan}, event)">Reset</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="removeFormPenginapan(${formCountPenginapan}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.getElementById("form-container-penginapan").appendChild(newForm);

        $(`#company_bt_penginapan_${formCountPenginapan}`).select2({
            theme: "bootstrap-5",
        });
    }

    function addMoreFormPenginapanReq(event) {
        event.preventDefault();
        formCountPenginapan++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-penginapan-${formCountPenginapan}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Penginapan ${formCountPenginapan}</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Penginapan Request</p>
                <div class="row">
                    <!-- Penginapan Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation Start Plan</label>
                        <input type="date" name="start_bt_penginapan[]"
                            id="start_bt_penginapan_${formCountPenginapan}"
                            class="form-control start-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_${formCountPenginapan}'), document.querySelector('#total_days_bt_penginapan_${formCountPenginapan}'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation End Plan</label>
                        <input type="date" name="end_bt_penginapan[]"
                            id="end_bt_penginapan_${formCountPenginapan}"
                            class="form-control end-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_${formCountPenginapan}'), this, document.querySelector('#total_days_bt_penginapan_${formCountPenginapan}'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input
                                class="form-control bg-light total-days-penginapan"
                                id="total_days_bt_penginapan_${formCountPenginapan}"
                                name="total_days_bt_penginapan[]"
                                type="number" min="0"
                                value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <input type="text"
                            name="hotel_name_bt_penginapan[]"
                            class="form-control" placeholder="ex: Westin">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Company Code</label>
                        <select class="form-control select2"
                            id="company_bt_penginapan_${formCountPenginapan}"
                            name="company_bt_penginapan[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control"
                                name="nominal_bt_penginapan[]"
                                id="nominal_bt_penginapan_${formCountPenginapan}" type="text"
                                min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInput(this)"
                                onblur="formatOnBlur(this)">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan(${formCountPenginapan}, event)">Reset</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="removeFormPenginapan(${formCountPenginapan}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.getElementById("form-container-penginapan").appendChild(newForm);

        $(`#company_bt_penginapan_${formCountPenginapan}`).select2({
            theme: "bootstrap-5",
        });
    }

    $('.btn-warning').click(function(event) {
        event.preventDefault();
        var index = $(this).closest('.card-body').index() + 1;
        removeFormPenginapan(index, event);
    });

    function removeFormPenginapan(index, event) {
        event.preventDefault();
        if (formCountPenginapan > 0) {
            const formContainer = document.getElementById(`form-container-bt-penginapan-${index}`);
            if (formContainer) {
                const nominalInput = formContainer.querySelector(`#nominal_bt_penginapan_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_penginapan"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_penginapan"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-penginapan-${index}`).remove();
                formCountPenginapan--;
            }
        }
    }

    function removeFormPenginapanDec(index, event) {
        event.preventDefault();
        if (formCountPenginapan > 0) {
            const formContainer = document.getElementById(`form-container-bt-penginapan-${index}`);
            if (formContainer) {
                const nominalInput = formContainer.querySelector(`#nominal_bt_penginapan_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_penginapan"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_penginapan"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-penginapan-dec-${index}`).remove();
                formCountPenginapan--;
            }
        }
    }

    function clearFormPenginapan(index, event) {
        event.preventDefault();
        let nominalValue = cleanNumber(document.querySelector(`#nominal_bt_penginapan_${formCountPenginapan}`).value);
        let total = cleanNumber(document.querySelector('input[name="total_bt_penginapan"]').value);
        total -= nominalValue;
        document.querySelector('input[name="total_bt_penginapan"]').value = formatNumber(total);

        let formContainer = document.getElementById(`form-container-bt-penginapan-${index}`);

        formContainer.querySelectorAll('input[type="text"], input[type="date"]').forEach(input => {
            input.value = '';
        });

        formContainer.querySelectorAll('input[type="number"]').forEach(input => {
            input.value = 0;
        });

        formContainer.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });

        formContainer.querySelectorAll('textarea').forEach(textarea => {
            textarea.value = '';
        });

        document.querySelector(`#nominal_bt_penginapan_${formCountPenginapan}`).value = 0;
        calculateTotalNominalBTTotal();
    }

    function calculateTotalNominalBTPenginapan() {
        let total = 0;
        document.querySelectorAll('input[name="nominal_bt_penginapan[]"]').forEach(input => {
            total += cleanNumber(input.value);
        });
        document.querySelector('input[name="total_bt_penginapan"]').value = formatNumber(total);
    }

    function onNominalChange() {
        calculateTotalNominalBTPenginapan();
    }

    function calculateTotalDaysPenginapan(startInput, endInput, totalDaysInput) {
        const startDate = new Date(startInput.value);
        const endDate = new Date(endInput.value);

        // Set the minimum date for the endDate input
        endInput.min = startInput.value;

        if(startDate && endDate && endDate >= startDate) {
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Convert time to days
            totalDaysInput.value = daysDiff > 0 ? daysDiff : 0; // Ensure non-negative
        } else {
            totalDaysInput.value = 0; // Set to 0 if invalid dates
            endInput.value = '';
        }

        if(endDate < startDate){
            Swal.fire({
                icon: 'error',
                title: 'End Date cannot be earlier than Start Date',
                text: 'Choose another date!',
                timer: 3000,
                confirmButtonColor: "#AB2F2B",
                confirmButtonText: "OK",
            });
        }

    }
</script>

@if (!empty($detailCA['detail_penginapan']) && $detailCA['detail_penginapan'][0]['start_date'] !== null && $detailCA['detail_penginapan'][0]['end_date'] !== null)
    <div id="form-container-penginapan">
        @foreach($detailCA['detail_penginapan'] as $index =>$penginapan)
            <div id="form-container-bt-penginapan-{{ $loop->index + 1 }}" class="p-2 mb-3 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Penginapan {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-penginapan-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <div class="row">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Request</p>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Hotel Name</th>
                                    <td class="block">:</td>
                                    <td>{{ $penginapan['hotel_name'] }}</td>
                                </tr>
                                <tr>
                                    <th width="40%">Company Code</th>
                                    <td class="block">:</td>
                                    <td>{{ $penginapan['company_code'] }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td class="block">:</td>
                                    <td> Rp {{ number_format($penginapan['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Start Plan</th>
                                    <td class="block">:</td>
                                    <td> {{ date('d M Y', strtotime($penginapan['start_date'])) }} </td>
                                </tr>
                                <tr>
                                    <th width="40%">End Plan</th>
                                    <td class="block">:</td>
                                    <td> {{ date('d M Y', strtotime($penginapan['end_date'])) }} </td>
                                </tr>
                                <tr>
                                    <th>Total Days</th>
                                    <td class="block">:</td>
                                    <td>{{$penginapan['total_days']}} Days</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-bt-penginapan-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Declaration</p>
                    @if (isset($declareCA['detail_penginapan'][$index]))
                        @php
                            $penginapan_dec = $declareCA['detail_penginapan'][$index];
                        @endphp
                        <div class="row">
                            <!-- Penginapan Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Accommodation Start Plan</label>
                                <input type="date" name="start_bt_penginapan[]"
                                    id="start_bt_penginapan_{{ $loop->index + 1 }}"
                                    class="form-control start-penginapan"
                                    value="{{$penginapan_dec['start_date']}}"
                                    placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_{{ $loop->index + 1 }}'), document.querySelector('#total_days_bt_penginapan_{{ $loop->index + 1 }}'))">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Accommodation End Plan</label>
                                <input type="date" name="end_bt_penginapan[]"
                                    id="end_bt_penginapan_{{ $loop->index + 1 }}"
                                    class="form-control end-penginapan"
                                    value="{{$penginapan_dec['end_date']}}"
                                    placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_{{ $loop->index + 1 }}'), this, document.querySelector('#total_days_bt_penginapan_{{ $loop->index + 1 }}'))">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input
                                        class="form-control bg-light total-days-penginapan"
                                        id="total_days_bt_penginapan_{{ $loop->index + 1 }}"
                                        name="total_days_bt_penginapan[]"
                                        type="number" min="0"
                                        value="{{$penginapan_dec['total_days']}}"
                                        readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Hotel Name</label>
                                <input type="text"
                                    name="hotel_name_bt_penginapan[]"
                                    class="form-control" placeholder="ex: Westin"
                                    id="hotel_name_bt_penginapan_{{ $loop->index + 1 }}"
                                    value="{{$penginapan_dec['hotel_name']}}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Company Code</label>
                                <select class="form-control select2"
                                    id="company_bt_penginapan_{{ $loop->index + 1 }}"
                                    name="company_bt_penginapan[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $penginapan_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control"
                                        name="nominal_bt_penginapan[]"
                                        id="nominal_bt_penginapan_{{ $loop->index + 1 }}" type="text"
                                        min="0"
                                        value="{{ number_format($penginapan_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)"
                                        onblur="formatOnBlur(this)">
                                </div>
                            </div>
                        </div>
                    @endif
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan({{ $loop->index + 1 }}, event)">Reset</button>
                            {{-- <button class="btn btn-warning mr-2" onclick="removeFormPenginapan({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_penginapan'] as $index => $penginapan_dec)
            @if (!isset($detailCA['detail_penginapan'][$index]))
                <div id="form-container-bt-penginapan-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <!-- Penginapan Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Accommodation Start Plan</label>
                            <input type="date" name="start_bt_penginapan[]"
                                id="start_bt_penginapan_{{ $loop->index + 1 }}"
                                class="form-control start-penginapan"
                                value="{{$penginapan_dec['start_date']}}"
                                placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_1'), document.querySelector('#total_days_bt_penginapan_1'))">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Accommodation End Plan</label>
                            <input type="date" name="end_bt_penginapan[]"
                                id="end_bt_penginapan_{{ $loop->index + 1 }}"
                                class="form-control end-penginapan"
                                value="{{$penginapan_dec['end_date']}}"
                                placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_{{ $loop->index + 1 }}'), this, document.querySelector('#total_days_bt_penginapan_1'))">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Days</label>
                            <div class="input-group">
                                <input
                                    class="form-control bg-light total-days-penginapan"
                                    id="total_days_bt_penginapan_{{ $loop->index + 1 }}"
                                    name="total_days_bt_penginapan[]"
                                    type="number" min="0"
                                    value="{{$penginapan_dec['total_days']}}"
                                    readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">Hotel Name</label>
                            <input type="text"
                                name="hotel_name_bt_penginapan[]"
                                class="form-control" placeholder="ex: Westin"
                                id="hotel_name_bt_penginapan_{{ $loop->index + 1 }}"
                                value="{{$penginapan_dec['hotel_name']}}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Company Code</label>
                            <select class="form-control select2"
                                id="company_bt_penginapan_{{ $loop->index + 1 }}"
                                name="company_bt_penginapan[]">
                                <option value="">Select Company...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}"
                                        @if($company->contribution_level_code == $penginapan_dec['company_code']) selected @endif>
                                        {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control"
                                    name="nominal_bt_penginapan[]"
                                    id="nominal_bt_penginapan_{{ $loop->index + 1 }}" type="text"
                                    min="0"
                                    value="{{ number_format($penginapan_dec['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)"
                                    onblur="formatOnBlur(this)">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="removeFormPenginapan({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormPenginapanDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Accommodation</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_penginapan"
                id="total_bt_penginapan" type="text"
                min="0" value="{{ number_format(array_sum(array_column($declareCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@elseif (!empty($declareCA['detail_penginapan']) && $declareCA['detail_penginapan'][0]['start_date'] !== null && $declareCA['detail_penginapan'][0]['end_date'] !== null)
    <div id="form-container-penginapan">
        @foreach ($declareCA['detail_penginapan'] as $index => $penginapan_dec)
            @if (!isset($detailCA['detail_penginapan'][$index]))
                <div id="form-container-bt-penginapan-{{ $loop->index + 1 }}" class="p-2 mb-3 rounded-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Declaration</p>
                        <div class="row">
                            <!-- Penginapan Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Accommodation Start Plan</label>
                                <input type="date" name="start_bt_penginapan[]"
                                    id="start_bt_penginapan_{{ $loop->index + 1 }}"
                                    class="form-control start-penginapan"
                                    value="{{$penginapan_dec['start_date']}}"
                                    placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_1'), document.querySelector('#total_days_bt_penginapan_1'))">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Accommodation End Plan</label>
                                <input type="date" name="end_bt_penginapan[]"
                                    id="end_bt_penginapan_{{ $loop->index + 1 }}"
                                    class="form-control end-penginapan"
                                    value="{{$penginapan_dec['end_date']}}"
                                    placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_{{ $loop->index + 1 }}'), this, document.querySelector('#total_days_bt_penginapan_1'))">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input
                                        class="form-control bg-light total-days-penginapan"
                                        id="total_days_bt_penginapan_{{ $loop->index + 1 }}"
                                        name="total_days_bt_penginapan[]"
                                        type="number" min="0"
                                        value="{{$penginapan_dec['total_days']}}"
                                        readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Hotel Name</label>
                                <input type="text"
                                    name="hotel_name_bt_penginapan[]"
                                    class="form-control" placeholder="ex: Westin"
                                    id="hotel_name_bt_penginapan_{{ $loop->index + 1 }}"
                                    value="{{$penginapan_dec['hotel_name']}}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Company Code</label>
                                <select class="form-control select2"
                                    id="company_bt_penginapan_{{ $loop->index + 1 }}"
                                    name="company_bt_penginapan[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $penginapan_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control"
                                        name="nominal_bt_penginapan[]"
                                        id="nominal_bt_penginapan_{{ $loop->index + 1 }}" type="text"
                                        min="0"
                                        value="{{ number_format($penginapan_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)"
                                        onblur="formatOnBlur(this)">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="removeFormPenginapan({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-1">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormPenginapanDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Accommodation</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_penginapan"
                id="total_bt_penginapan" type="text"
                min="0" value="{{ number_format(array_sum(array_column($declareCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@else
    <div id="form-container-penginapan">
        <div id="form-container-bt-transport-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation 1</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Declaration</p>
                <div class="row">
                    <!-- Penginapan Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation Start Plan</label>
                        <input type="date" name="start_bt_penginapan[]"
                            id="start_bt_penginapan_1"
                            class="form-control start-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_1'), document.querySelector('#total_days_bt_penginapan_1'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation End Plan</label>
                        <input type="date" name="end_bt_penginapan[]"
                            id="end_bt_penginapan_1"
                            class="form-control end-penginapan"
                            placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_1'), this, document.querySelector('#total_days_bt_penginapan_1'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input
                                class="form-control bg-light total-days-penginapan"
                                id="total_days_bt_penginapan_1"
                                name="total_days_bt_penginapan[]"
                                type="number" min="0"
                                value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <input type="text"
                            name="hotel_name_bt_penginapan[]"
                            class="form-control" placeholder="ex: Westin" id="hotel_name_bt_penginapan_1">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Company Code</label>
                        <select class="form-control select2"
                            id="company_bt_penginapan_1"
                            name="company_bt_penginapan[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control"
                                name="nominal_bt_penginapan[]"
                                id="nominal_bt_penginapan_1" type="text"
                                min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInput(this)"
                                onblur="formatOnBlur(this)">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormPenginapan(1, event)">Reset</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="removeFormPenginapan(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormPenginapanDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Accommodation</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_penginapan"
                id="total_bt_penginapan" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
