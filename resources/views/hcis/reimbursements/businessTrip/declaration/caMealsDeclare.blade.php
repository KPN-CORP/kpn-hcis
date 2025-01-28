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
    var formCountMeals = 0;

    window.addEventListener('DOMContentLoaded', function() {
        formCountMeals = document.querySelectorAll('#form-container-meals > div').length;
    });

    function addMoreFormMealsDec(event) {
        event.preventDefault();
        formCountMeals++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-meals-${formCountMeals}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                  <p class="fs-4 text-primary" style="font-weight: bold;">Meals ${formCountMeals}</p>
    <div class="card-body bg-light p-2 mb-3">
        <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
        <div class="row">
            <!-- Meals Start Plan -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Meals Start Plan</label>
                <input type="date" name="start_bt_meals[]" id="start_bt_meals_${formCountMeals}"
                    class="form-control start-meals" placeholder="mm/dd/yyyy"
                    onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_meals_${formCountMeals}'), document.querySelector('#total_days_bt_meals_${formCountMeals}'))">
            </div>
            <!-- Meals End Plan -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Meals End Plan</label>
                <input type="date" name="end_bt_meals[]" id="end_bt_meals_${formCountMeals}"
                    class="form-control end-meals" placeholder="mm/dd/yyyy"
                    onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_meals_${formCountMeals}'), this, document.querySelector('#total_days_bt_meals_${formCountMeals}'))">
            </div>
            <!-- Total Days -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Days</label>
                <div class="input-group">
                    <input class="form-control bg-light total-days-meals" id="total_days_bt_meals_${formCountMeals}"
                        name="total_days_bt_meals[]" type="number" min="0" value="0" readonly>
                    <div class="input-group-append">
                        <span class="input-group-text">days</span>
                    </div>
                </div>
            </div>
            <!-- Company Code -->
            <div class="col-md-6 mb-2">
                <label class="form-label" for="company_bt_meals_${formCountMeals}">Company Code</label>
                <select class="form-control select2" id="company_bt_meals_${formCountMeals}" name="company_bt_meals[]">
                    <option value="">Select Company...</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->contribution_level_code }}">
                            {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Amount -->
            <div class="col-md-6 mb-2">
                <label class="form-label">Amount</label>
                <div class="input-group mb-3">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_${formCountMeals}"
                        type="text" min="0" value="0"
                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                        oninput="formatInput(this)" onblur="formatOnBlur(this)">
                </div>
            </div>
            <!-- Information -->
            <div class="col-md-12">
                <div class="mb-2">
                    <label class="form-label">Information</label>
                    <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ..."></textarea>
                </div>
            </div>
        </div>
        <!-- Buttons -->
        <div class="row mt-3">
            <div class="d-flex justify-start w-100">
                <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                    onclick="clearFormMeals(${formCountMeals}, event)">Reset</button>
                <button class="btn btn-sm btn-outline-primary"
                    onclick="removeFormMeals(${formCountMeals}, event)">Delete</button>
            </div>
        </div>
    </div>
            `;
        document.getElementById("form-container-meals").appendChild(newForm);
    }

    function addMoreFormMealsReq(event) {
        event.preventDefault();
        formCountMeals++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-meals-${formCountMeals}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                  <p class="fs-4 text-primary" style="font-weight: bold;">Meals ${formCountMeals}</p>
    <div class="card-body bg-light p-2 mb-3">
        <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
        <div class="row">
            <!-- Meals Start Plan -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Meals Start Plan</label>
                <input type="date" name="start_bt_meals[]" id="start_bt_meals_${formCountMeals}"
                    class="form-control start-meals" placeholder="mm/dd/yyyy"
                    onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_meals_${formCountMeals}'), document.querySelector('#total_days_bt_meals_${formCountMeals}'))">
            </div>
            <!-- Meals End Plan -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Meals End Plan</label>
                <input type="date" name="end_bt_meals[]" id="end_bt_meals_${formCountMeals}"
                    class="form-control end-meals" placeholder="mm/dd/yyyy"
                    onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_meals_${formCountMeals}'), this, document.querySelector('#total_days_bt_meals_${formCountMeals}'))">
            </div>
            <!-- Total Days -->
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Days</label>
                <div class="input-group">
                    <input class="form-control bg-light total-days-meals" id="total_days_bt_meals_${formCountMeals}"
                        name="total_days_bt_meals[]" type="number" min="0" value="0" readonly>
                    <div class="input-group-append">
                        <span class="input-group-text">days</span>
                    </div>
                </div>
            </div>
            <!-- Company Code -->
            <div class="col-md-6 mb-2">
                <label class="form-label" for="company_bt_meals_${formCountMeals}">Company Code</label>
                <select class="form-control select2" id="company_bt_meals_${formCountMeals}" name="company_bt_meals[]">
                    <option value="">Select Company...</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->contribution_level_code }}">
                            {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Amount -->
            <div class="col-md-6 mb-2">
                <label class="form-label">Amount</label>
                <div class="input-group mb-3">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_${formCountMeals}"
                        type="text" min="0" value="0"
                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                        oninput="formatInput(this)" onblur="formatOnBlur(this)">
                </div>
            </div>
            <!-- Information -->
            <div class="col-md-12">
                <div class="mb-2">
                    <label class="form-label">Information</label>
                    <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ..."></textarea>
                </div>
            </div>
        </div>
        <!-- Buttons -->
        <div class="row mt-3">
            <div class="d-flex justify-start w-100">
                <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                    onclick="clearFormMeals(${formCountMeals}, event)">Reset</button>
                <button class="btn btn-sm btn-outline-primary"
                    onclick="removeFormMeals(${formCountMeals}, event)">Delete</button>
            </div>
        </div>
    </div>
            `;
        document.getElementById("form-container-meals").appendChild(newForm);
    }

    $('.btn-warning').click(function(event) {
        event.preventDefault();
        var index = $(this).closest('.card-body').index() + 1;
        removeFormMeals(index, event);
    });

    function removeFormMeals(index, event) {
        event.preventDefault();
        if (formCountMeals > 0) {
            const formContainer = document.getElementById(`form-container-bt-meals-${index}`);
            if (formContainer) {
                const nominalInput = formContainer.querySelector(`#nominal_bt_meals_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_meals"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_meals"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-meals-${index}`).remove();
                formCountMeals--;
            }
        }
    }

    function clearFormMeals(index, event) {
        event.preventDefault();
        let nominalValue = cleanNumber(document.querySelector(`#nominal_bt_meals_${index}`).value);
        let total = cleanNumber(document.querySelector('input[name="total_bt_meals"]').value);
        total -= nominalValue;
        document.querySelector('input[name="total_bt_meals"]').value = formatNumber(total);

        // Clear the inputs
        const formContainer = document.getElementById(`form-container-bt-meals-${index}`);
        formContainer.querySelectorAll('input[type="text"], input[type="date"]').forEach((input) => {
            input.value = "";
        });
        formContainer.querySelector("textarea").value = "";

        // Reset nilai untuk nominal BT meals
        document.querySelector(`#nominal_bt_meals_${index}`).value = 0;
        calculateTotalNominalBTTotal();
    }

    function calculateTotalNominalBTMeals() {
        let total = 0;
        document.querySelectorAll('input[name="nominal_bt_meals[]"]').forEach(input => {
            total += cleanNumber(input.value);
        });
        document.getElementById("total_bt_meals").value = formatNumber(total);
    }

    function onNominalChange() {
        calculateTotalNominalBTMeals();
    }
</script>

@if (!empty($caDetail['detail_meals']) && $caDetail['detail_meals'][0]['start_date'] !== null)
    <div id="form-container-meals">
        @foreach ($caDetail['detail_meals'] as $index => $meals)
            <div id="form-container-bt-meals-{{ $loop->index + 1 }}" class="p-2 mb-3 rounded-3"
                style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Meals {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-meals-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3"
                    style="border-radius: 1%;">
                    <div class="row">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
                        <div class="col-md-6">
                            <table class="table"
                                style="border: none; border-collapse: collapse; margin: 0; padding: 0;">
                                <tr style="margin: 0; padding: 0;">
                                    <th class="label" style="border: none; width:40%; padding: 2px 0;">Start Date</th>
                                    <td class="colon" style="border: none; width:1%; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        {{ \Carbon\Carbon::parse($meals['start_date'])->format('d-M-y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none; padding: 2px 0;">End Date</th>
                                    <td class="colon" style="border: none; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        {{ \Carbon\Carbon::parse($meals['end_date'])->format('d-M-y') }}
                                    </td>
                                </tr>
                                <tr style="margin: 0; padding: 0;">
                                    <th class="label" style="border: none; padding: 2px 0;">Total Days</th>
                                    <td class="colon" style="border: none; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        {{ $meals['total_days'] }} Days
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table"
                                style="border: none; border-collapse: collapse; margin: 0; padding: 0;">
                                <tr style="margin: 0; padding: 0;">
                                    <th class="label" style="border: none; width:40%; padding: 2px 0;">Company Code
                                    </th>
                                    <td class="colon" style="border: none; width:1%; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        {{ $meals['company_code'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none; padding: 2px 0;">Amount</th>
                                    <td class="colon" style="border: none; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        Rp. {{ number_format($meals['nominal'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="margin: 0; padding: 0;">
                                    <th class="label" style="border: none; padding: 2px 0;">Information</th>
                                    <td class="colon" style="border: none; padding: 2px 0;">:</td>
                                    <td class="value" style="border: none; padding: 2px 0;">
                                        {{ $meals['keterangan'] }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
                <div id="form-container-bt-meals-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold; ">Meals Declaration</p>
                    @if (isset($declareCa['detail_meals'][$index]))
                        @php
                            $meals_dec = $declareCa['detail_meals'][$index];
                        @endphp
                        <div class="row">
                            <!-- meals Date -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_meals[]" class="form-control"
                                    value="{{ $meals_dec['start_date'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_meals[]"
                                        id="nominal_bt_meals_{{ $loop->index + 1 }}" type="text" min="0"
                                        value="{{ number_format($meals_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information ...">{{ $meals_dec['keterangan'] }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px"
                                onclick="clearFormMeals({{ $loop->index + 1 }}, event)">Reset</button>
                            {{-- <button class="btn btn-warning mr-2" onclick="removeFormMeals({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCa['detail_meals'] as $index => $meals_dec)
            @if (!isset($caDetail['detail_meals'][$index]))
                <div id="form-container-bt-meals-{{ $loop->index + 1 }}" class="p-2 mb-3 rounded-3"
                    style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Meals {{ $loop->index + 1 }}</p>
                    <div class="fs-5 bg-light text-primary p-2">
                        <p class="fs-5 text-primary" style="font-weight: bold; ">Meals Declaration</p>
                        <div class="row">
                            <!-- meals Date -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_meals[]" class="form-control"
                                    value="{{ $meals_dec['start_date'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_meals[]"
                                        id="nominal_bt_meals_{{ $loop->index + 1 }}" type="text" min="0"
                                        value="{{ number_format($meals_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_meals[]" class="form-control">{{ $meals_dec['keterangan'] }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px"
                                    onclick="clearFormMeals({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick="removeFormMeals({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-1">
        <button class="btn btn-primary btn-sm" id="addMoreButtonLainnya" onclick="addMoreFormMealsDec(event)">Add
            More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text"
                min="0"
                value="{{ number_format(array_sum(array_column($declareCa['detail_meals'], 'nominal')), 0, ',', '.') }}"
                readonly>
        </div>
    </div>
@elseif (!empty($declareCa['detail_meals']) && $declareCa['detail_meals'][0]['nominal'] !== null)
    <div id="form-container-meals">
        @foreach ($declareCa['detail_meals'] as $index => $meals_dec)
            @if (!isset($caDetail['detail_meals'][$index]))
                <div id="form-container-bt-meals-{{ $loop->index + 1 }}" class="card-body p-2 mb-3"
                    style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Meals {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Meals Declaration</p>
                        <div class="row">
                            <!-- meals Date -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_meals[]" class="form-control"
                                    value="{{ $meals_dec['start_date'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_meals[]"
                                        id="nominal_bt_meals_{{ $loop->index + 1 }}" type="text" min="0"
                                        value="{{ number_format($meals_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_meals[]" class="form-control">{{ $meals_dec['keterangan'] }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px"
                                    onclick="clearFormMeals({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick="removeFormMeals({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" id="addMoreButtonLainnya" onclick="addMoreFormMealsDec(event)">Add
            More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text"
                min="0"
                value="{{ number_format(array_sum(array_column($declareCa['detail_meals'], 'nominal')), 0, ',', '.') }}"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-meals">
        <div id="form-container-bt-meals-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Meals 1</p>
            <div id="form-container-bt-meals-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
                <div class="row">
                    <!-- meals Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Meals Start Plan</label>
                        <input type="date" name="start_bt_meals[]" id="start_bt_meals_1"
                            class="form-control start-meals" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_meals_1'), document.querySelector('#total_days_bt_meals_1'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Meals End Plan</label>
                        <input type="date" name="end_bt_meals[]" id="end_bt_meals_1"
                            class="form-control end-meals" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_meals_1'), this, document.querySelector('#total_days_bt_meals_1'))">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control bg-light total-days-meals" id="total_days_bt_meals_1"
                                name="total_days_bt_meals[]" type="number" min="0" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                    <!-- Company Code -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="company_bt_meals_1">Company Code</label>
                        <select class="form-control select2" id="company_bt_meals_1" name="company_bt_meals[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_1"
                                type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInput(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                            onclick="clearFormMeals(1, event)">Reset</button>
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="removeFormMeals(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormMealsReq(event)">Add
            More</button>
    </div>

    <div class="mt-2 mb-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
