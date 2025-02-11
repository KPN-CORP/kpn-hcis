<script src="{{ asset('/js/btCashAdvanced/meals.js') }}"></script>

<script>
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
</script>

@if (!empty($caDetail['detail_meals']) && $caDetail['detail_meals'][0]['start_date'] !== null)
    <div id="form-container-meals">
        @foreach ($caDetail['detail_meals'] as $meals)
            <div id="form-container-bt-meals-{{ $loop->index + 1 }}" class="card-body p-2 mb-3"
                style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Meals {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-meals-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
                    <div class="row">
                        <!-- meals Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Meals Start Plan</label>
                            <input type="date" name="start_bt_meals[]" id="start_bt_meals_{{ $loop->index + 1 }}"
                                class="form-control start-meals" value="{{ $meals['start_date'] }}"
                                placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(this, document.getElementById('end_bt_meals_1'), document.querySelector('#total_days_bt_meals_1'))">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Meals End Plan</label>
                            <input type="date" name="end_bt_meals[]" id="end_bt_meals_{{ $loop->index + 1 }}"
                                class="form-control end-meals" value="{{ $meals['end_date'] }}" placeholder="mm/dd/yyyy"
                                onchange="calculateTotalDaysPenginapan(document.getElementById('start_bt_meals_{{ $loop->index + 1 }}'), this, document.querySelector('#total_days_bt_meals_1'))">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light total-days-meals"
                                    id="total_days_bt_meals_{{ $loop->index + 1 }}" name="total_days_bt_meals[]"
                                    type="number" min="0" value="{{ $meals['total_days'] }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="company_bt_meals{{ $loop->index + 1 }}">Company
                                Code</label>
                            <select class="form-control select2" id="company_bt_meals_{{ $loop->index + 1 }}"
                                name="company_bt_meals[]">
                                <option value="">Select Company...</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}"
                                        @if ($company->contribution_level_code == $meals['company_code']) selected @endif>
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
                                <input class="form-control" name="nominal_bt_meals[]"
                                    id="nominal_bt_meals_{{ $loop->index + 1 }}" type="text" min="0"
                                    value="{{ number_format($meals['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)" onblur="formatOnBlur(this)"
                                    placeholder="0">
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="col-md-12 mb-2">
                            <div class="mb-2">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ...">{{ $meals['keterangan'] }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                                onclick="clearFormMeals({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="removeFormMeals({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonMeals" onclick="addMoreFormMealsReq(event)">Add
            More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text" min="0"
                value="{{ number_format(array_sum(array_column($caDetail['detail_meals'], 'nominal')), 0, ',', '.') }}"
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

    <div class="mt-2">
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
