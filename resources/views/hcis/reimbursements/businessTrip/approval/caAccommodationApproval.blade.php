<script src="{{ asset('/js/btCashAdvanced/penginapan.js') }}"></script>

<script>
    function addMoreFormPenginapanReq(event) {
        event.preventDefault();
        formCountPenginapan++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-penginapan-${formCountPenginapan}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation ${formCountPenginapan}</p>
                <div class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Request</p>
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
                    <div class="row">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormPenginapan(${formCountPenginapan}, event)">Reset</button>
                            <button class="btn btn-sm btn-outline-primary" onclick="removeFormPenginapan(${formCountPenginapan}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        document.getElementById("form-container-penginapan").appendChild(newForm);

        $(`#company_bt_penginapan_${formCountPenginapan}`).select2({
            theme: "bootstrap-5",
        });
    }
</script>

@if (!empty($detailCA['detail_penginapan']) && $detailCA['detail_penginapan'][0]['start_date'] !== null)
    <div id="form-container-penginapan">
        @foreach ($detailCA['detail_penginapan'] as $penginapan)
            <div id="form-container-bt-penginapan-{{ $loop->index + 1 }}" class="card-body p-2 mb-3"
                style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-penginapan-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Request</p>
                    <div class="row">
                        <!-- Penginapan Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Accommodation Start Plan</label>
                            <input type="date" name="start_bt_penginapan[]"
                                id="start_bt_penginapan_{{ $loop->index + 1 }}" class="form-control start-penginapan bg-light"
                                value="{{ $penginapan['start_date'] }}" placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_1'), document.querySelector('#total_days_bt_penginapan_1'))" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Accommodation End Plan</label>
                            <input type="date" name="end_bt_penginapan[]"
                                id="end_bt_penginapan_{{ $loop->index + 1 }}" class="form-control end-penginapan bg-light"
                                value="{{ $penginapan['end_date'] }}" placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_{{ $loop->index + 1 }}'), this, document.querySelector('#total_days_bt_penginapan_1'))" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light total-days-penginapan"
                                    id="total_days_bt_penginapan_{{ $loop->index + 1 }}"
                                    name="total_days_bt_penginapan[]" type="number" min="0"
                                    value="{{ $penginapan['total_days'] }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" name="hotel_name_bt_penginapan[]" class="form-control bg-light"
                                placeholder="ex: Westin" id="hotel_name_bt_penginapan_{{ $loop->index + 1 }}"
                                value="{{ $penginapan['hotel_name'] }}" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Company Code</label>
                            <select class="form-control select2" id="company_bt_penginapan_{{ $loop->index + 1 }}"
                                name="company_bt_penginapan[]" disabled>
                                <option value="">Select Company...</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}"
                                        @if ($company->contribution_level_code == $penginapan['company_code']) selected @endif>
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
                                <input class="form-control bg-light" name="nominal_bt_penginapan[]"
                                    id="nominal_bt_penginapan_{{ $loop->index + 1 }}" type="text" min="0"
                                    value="{{ number_format($penginapan['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)" onblur="formatOnBlur(this)" readonly>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-2">
        <label class="form-label">Total Accommodation</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_penginapan" id="total_bt_penginapan" type="text"
                min="0"
                value="{{ number_format(array_sum(array_column($detailCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-penginapan">
        <div id="form-container-bt-transport-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Accommodation 1</p>
            <div id="form-container-bt-transport-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Accommodation Request</p>
                <div class="row">
                    <!-- Penginapan Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation Start Plan</label>
                        <input type="date" name="start_bt_penginapan[]" id="start_bt_penginapan_1"
                            class="form-control start-penginapan bg-light" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_penginapan_1'), document.querySelector('#total_days_bt_penginapan_1'))" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Accommodation End Plan</label>
                        <input type="date" name="end_bt_penginapan[]" id="end_bt_penginapan_1"
                            class="form-control end-penginapan bg-light" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_penginapan_1'), this, document.querySelector('#total_days_bt_penginapan_1'))" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control bg-light total-days-penginapan" id="total_days_bt_penginapan_1"
                                name="total_days_bt_penginapan[]" type="number" min="0" value="0"
                                readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <input type="text" name="hotel_name_bt_penginapan[]" class="form-control bg-light"
                            placeholder="ex: Westin" id="hotel_name_bt_penginapan_1" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Company Code</label>
                        <select class="form-control select2" id="company_bt_penginapan_1"
                            name="company_bt_penginapan[]" disabled>
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
                            <input class="form-control bg-light" name="nominal_bt_penginapan[]" id="nominal_bt_penginapan_1"
                                type="text" min="0" value="0"
                                oninput="formatInput(this)" onblur="formatOnBlur(this)" readonly>
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Accommodation</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_penginapan" id="total_bt_penginapan" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
