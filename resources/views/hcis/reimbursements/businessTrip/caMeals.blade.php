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
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Meals ${formCountMeals}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_meals[]" class="form-control" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_${formCountMeals}" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormMeals(${formCountMeals}, event)">Reset</button>
                                <button class="btn btn-sm btn-outline-primary" onclick="removeFormMeals(${formCountMeals}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
        document.getElementById("form-container-meals").appendChild(newForm);
    }

</script>

@if (!empty($detailCA['detail_meals']) && $detailCA['detail_meals'][0]['tanggal'] !== null)
    <div id="form-container-meals">
        @foreach ($detailCA['detail_meals'] as $meals)
            <div id="form-container-bt-meals-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Meals {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-meals-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Meals Request</p>
                    <div class="row">
                        <!-- meals Date -->
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="tanggal_bt_meals[]" class="form-control" value="{{$meals['tanggal']}}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($meals['nominal'], 0, ',', '.') }}" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
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
                            <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormMeals({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-sm btn-outline-primary" onclick="removeFormMeals({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonMeals" onclick="addMoreFormMealsReq(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text" min="0" value="{{ number_format(array_sum(array_column($detailCA['detail_meals'], 'nominal')), 0, ',', '.') }}" readonly>
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
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_bt_meals[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_bt_meals[]" id="nominal_bt_meals_1" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12 mb-2">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_meals[]" class="form-control" placeholder="Write your information here ..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormMeals(1, event)">Reset</button>
                        <button class="btn btn-sm btn-outline-primary" onclick="removeFormMeals(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormMealsReq(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Meals</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_meals" id="total_bt_meals" type="text" min="0" value="0" readonly>
        </div>
    </div>
@endif

