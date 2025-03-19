{{-- <script src="{{ asset('/js/btCashAdvanced/others.js') }}"></script> --}}
@include('js.hcis.btCashAdvanced.others')
<script>
    var formCountOthers = 0;

    window.addEventListener('DOMContentLoaded', function() {
        formCountOthers = document.querySelectorAll('#form-container-lainnya > div').length;
    });

    function addMoreFormLainnyaDec(event) {
        event.preventDefault();
        formCountOthers++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-lainnya-${formCountOthers}`;
        newForm.className = "card-body p-2 mb-2 bg-light rounded-3";
        // newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses ${formCountOthers}</p>
                <div class="card-body bg-white p-2 rounded-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Other Expenses Declaration</p>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="name">Type of Others</label>
                            <select class="form-control select2" id="type_bt_lainnya_${formCountOthers}" name="type_bt_lainnya[]">
                                <option value="">Select Type...</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Airport Tax">Airport Tax</option>
                                <option value="Porter">Porter</option>
                                <option value="Excess Baggage">Excess Baggage</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_${formCountOthers}" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_lainnya[]" class="form-control" placeholder="Write your information ..."></textarea>
                            </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya(${formCountOthers}, event)">Reset</button>
                            <button class="btn btn-outline-danger btn-sm" onclick="removeFormLainnya(${formCountOthers}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        document.getElementById("form-container-lainnya").appendChild(newForm);

        $(`#type_bt_lainnya_${formCountOthers}`).select2({
            theme: "bootstrap-5",
        });

        $(`#type_bt_lainnya_${formCountOthers}`).on('change', function() {
            handleDateChange();
        });

        handleDateChange();
    }

    function addMoreFormLainnyaReq(event) {
        event.preventDefault();
        formCountOthers++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-lainnya-${formCountOthers}`;
        newForm.className = "card-body p-2 mb-2 rounded-3 bg-light";
        // newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses ${formCountOthers}</p>
                <div class="card-body bg-white p-2 rounded-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Other Expenses Declaration</p>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="name">Type of Others</label>
                            <select class="form-control select2" id="type_bt_lainnya_${formCountOthers}" name="type_bt_lainnya[]">
                                <option value="">Select Type...</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Airport Tax">Airport Tax</option>
                                <option value="Porter">Porter</option>
                                <option value="Excess Baggage">Excess Baggage</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_${formCountOthers}" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_lainnya[]" class="form-control" placeholder="Write your information ..."></textarea>
                            </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya(${formCountOthers}, event)">Reset</button>
                            <button class="btn btn-outline-danger btn-sm" onclick="removeFormLainnya(${formCountOthers}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        document.getElementById("form-container-lainnya").appendChild(newForm);
    }

    $('.btn-warning').click(function(event) {
        event.preventDefault();
        var index = $(this).closest('.card-body').index() + 1;
        removeFormLainnya(index, event);
    });

    function removeFormLainnya(index, event) {
        event.preventDefault();
        if (formCountOthers > 0) {
            const formContainer = document.getElementById(`form-container-bt-lainnya-${index}`);
            if (formContainer) {
                const nominalInput = formContainer.querySelector(`#nominal_bt_lainnya_${index}`);
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(document.querySelector('input[name="total_bt_lainnya"]').value);
                    total -= nominalValue;
                    document.querySelector('input[name="total_bt_lainnya"]').value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-lainnya-${index}`).remove();
                formCountOthers--;
            }
        }
    }

    function clearFormLainnya(index, event) {
        event.preventDefault();
        let nominalValue = cleanNumber(document.querySelector(`#nominal_bt_lainnya_${index}`).value);
        let total = cleanNumber(document.querySelector('input[name="total_bt_lainnya"]').value);
        total -= nominalValue;
        document.querySelector('input[name="total_bt_lainnya"]').value = formatNumber(total);

        // Clear the inputs
        const formContainer = document.getElementById(`form-container-bt-lainnya-${index}`);
        formContainer.querySelectorAll('input[type="text"], input[type="date"]').forEach((input) => {input.value = "";});
        formContainer.querySelector("textarea").value = "";

        // Reset nilai untuk nominal BT Lainnya
        document.querySelector(`#nominal_bt_lainnya_${index}`).value = 0;
        calculateTotalNominalBTTotal();
    }

    function calculateTotalNominalBTLainnya() {
        let total = 0;
        document.querySelectorAll('input[name="nominal_bt_lainnya[]"]').forEach(input => {
            total += cleanNumber(input.value);
        });
        document.getElementById("total_bt_lainnya").value = formatNumber(total);
    }

    function onNominalChange() {
        calculateTotalNominalBTLainnya();
    }

</script>

@if (!empty($caDetail['detail_lainnya']) && $caDetail['detail_lainnya'][0]['tanggal'] !== null)
    <div id="form-container-lainnya">
        @foreach ($caDetail['detail_lainnya'] as $index => $lainnya)
            <div id="form-container-bt-lainnya-{{ $loop->index + 1 }}" class="p-2 mb-2 bg-light card-body rounded-3" >
                <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-lainnya-req-{{ $loop->index + 1 }}" class="card-body bg-white rounded-3 p-2 mb-2">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Other Expenses Request</p>
                    <div class="row">
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th class="label" width="40%">Date</th>
                                    <td class="block">:</td>
                                    <td class="value">{{ $lainnya['tanggal'] }}</td>
                                </tr>
                                <tr>
                                    <th class="label">Type of Others</th>
                                    <td class="block">:</td>
                                    <td class="value">{{ $lainnya['type'] ?? ''  }}</td>
                                </tr>
                                <tr>
                                    <th class="label">Amount</th>
                                    <td class="block">:</td>
                                    <td class="value"> Rp {{ number_format($lainnya['nominal'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="label">Information</th>
                                    <td class="block">:</td>
                                    <td class="value">{{ $lainnya['keterangan'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-bt-lainnya-dec-{{ $loop->index + 1 }}" class="card-body bg-white rounded-3 p-2">
                    <p class="fs-5 text-primary" style="font-weight: bold; ">Other Expenses Declaration</p>
                    @if (isset($declareCa['detail_lainnya'][$index]))
                        @php
                            $lainnya_dec = $declareCa['detail_lainnya'][$index];
                        @endphp
                        <div class="row">
                            <!-- Lainnya Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" value="{{$lainnya_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">  
                                <label class="form-label" for="name">Type of Others</label>  
                                <select class="form-control select2" id="type_bt_lainnya_{{ $loop->index + 1 }}" name="type_bt_lainnya[]">  
                                    <option value="">Select Type...</option>  
                                    <option value="Airport Tax" {{ ($lainnya_dec['type'] ?? '') == 'Airport Tax' ? 'selected' : '' }}>Airport Tax</option>
                                    <option value="Excess Baggage" {{ ($lainnya_dec['type'] ?? '') == 'Excess Baggage' ? 'selected' : '' }}>Excess Baggage</option>
                                    <option value="Laundry" {{ ($lainnya_dec['type'] ?? '') == 'Laundry' ? 'selected' : '' }}>Laundry</option>
                                    <option value="Mandatory Insurance Fees" {{ ($lainnya_dec['type'] ?? '') == 'Mandatory Insurance Fees' ? 'selected' : '' }}>Mandatory Insurance Fees</option>  
                                    <option value="Parking Fees" {{ ($lainnya_dec['type'] ?? '') == 'Parking Fees' ? 'selected' : '' }}>Parking Fees</option>
                                    <option value="Porter Service" {{ ($lainnya_dec['type'] ?? '') == 'Porter Service' ? 'selected' : '' }}>Porter Service (for company luggage only)</option>
                                    <option value="Toll Fees" {{ ($lainnya_dec['type'] ?? '') == 'Toll Fees' ? 'selected' : '' }}>Toll Fees</option>
                                </select>   
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($lainnya_dec['nominal'], 0, ',', '.') }}" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_lainnya[]" class="form-control bg-light" placeholder="Write your information ..." readonly>{{ $lainnya_dec['keterangan'] }}</textarea>
                      
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <!-- Lainnya Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" value="" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">  
                                <label class="form-label" for="name">Type of Others</label>  
                                <select class="form-control select2" id="type_bt_lainnya_{{ $loop->index + 1 }}" name="type_bt_lainnya[]">  
                                    <option value="">Select Type...</option>  
                                    <option value="Airport Tax">Airport Tax</option>
                                    <option value="Excess Baggage">Excess Baggage</option>
                                    <option value="Laundry">Laundry</option>
                                    <option value="Mandatory Insurance Fees">Mandatory Insurance Fees</option>  
                                    <option value="Parking Fees">Parking Fees</option>
                                    <option value="Porter Service">Porter Service (for company luggage only)</option>
                                    <option value="Toll Fees">Toll Fees</option>
                                </select>  
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0" value="" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_lainnya[]" class="form-control bg-light" placeholder="Write your information ..." readonly></textarea>
                    
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya({{ $loop->index + 1 }}, event)">Reset1</button>
                            {{-- <button class="btn btn-warning mr-2" onclick="removeFormLainnya({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCa['detail_lainnya'] as $index => $lainnya_dec)
            @if (!isset($caDetail['detail_lainnya'][$index]))
                <div id="form-container-bt-lainnya-{{ $loop->index + 1 }}" class="p-2 mb-2 card-body bg-light rounded-3 ">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses {{ $loop->index + 1 }}</p>
                    <div class="card-body rounded-3 bg-white p-2">
                        <p class="fs-5 text-primary" style="font-weight: bold; ">Other Expenses Declaration</p>
                        <div class="row">
                            <!-- Lainnya Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" value="{{$lainnya_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">  
                                <label class="form-label" for="name">Type of Others</label>  
                                <select class="form-control select2" id="type_bt_lainnya_{{ $loop->index + 1 }}" name="type_bt_lainnya[]">  
                                    <option value="">Select Type...</option>  
                                    <option value="Airport Tax" {{ ($lainnya_dec['type'] ?? '') == 'Airport Tax' ? 'selected' : '' }}>Airport Tax</option>
                                    <option value="Excess Baggage" {{ ($lainnya_dec['type'] ?? '') == 'Excess Baggage' ? 'selected' : '' }}>Excess Baggage</option>
                                    <option value="Laundry" {{ ($lainnya_dec['type'] ?? '') == 'Laundry' ? 'selected' : '' }}>Laundry</option>
                                    <option value="Mandatory Insurance Fees" {{ ($lainnya_dec['type'] ?? '') == 'Mandatory Insurance Fees' ? 'selected' : '' }}>Mandatory Insurance Fees</option>  
                                    <option value="Parking Fees" {{ ($lainnya_dec['type'] ?? '') == 'Parking Fees' ? 'selected' : '' }}>Parking Fees</option>
                                    <option value="Porter Service" {{ ($lainnya_dec['type'] ?? '') == 'Porter Service' ? 'selected' : '' }}>Porter Service (for company luggage only)</option>
                                    <option value="Toll Fees" {{ ($lainnya_dec['type'] ?? '') == 'Toll Fees' ? 'selected' : '' }}>Toll Fees</option>
                                </select>  
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($lainnya_dec['nominal'], 0, ',', '.') }}" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_lainnya[]" class="form-control bg-light" readonly>{{ $lainnya_dec['keterangan'] }}</textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="removeFormLainnya({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonLainnya" onclick="addMoreFormLainnyaDec(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Other Expenses</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text" min="0" value="{{ number_format(array_sum(array_column($declareCa['detail_lainnya'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@elseif (!empty($declareCa['detail_lainnya']) && $declareCa['detail_lainnya'][0]['nominal'] !== null)
    <div id="form-container-lainnya">
        @foreach ($declareCa['detail_lainnya'] as $index => $lainnya_dec)
            @if (!isset($caDetail['detail_lainnya'][$index]))
                <div id="form-container-bt-lainnya-{{ $loop->index + 1 }}" class="card-body p-2 rounded-3 mb-2 bg-light">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-white p-2 rounded-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Other Expenses Declaration</p>
                        <div class="row">
                            <!-- Lainnya Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" value="{{$lainnya_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">  
                                <label class="form-label" for="name">Type of Others</label>  
                                <select class="form-control select2" id="type_bt_lainnya_{{ $loop->index + 1 }}" name="type_bt_lainnya[]">  
                                    <option value="">Select Type...</option>  
                                    <option value="Airport Tax" {{ ($lainnya_dec['type'] ?? '') == 'Airport Tax' ? 'selected' : '' }}>Airport Tax</option>
                                    <option value="Excess Baggage" {{ ($lainnya_dec['type'] ?? '') == 'Excess Baggage' ? 'selected' : '' }}>Excess Baggage</option>
                                    <option value="Laundry" {{ ($lainnya_dec['type'] ?? '') == 'Laundry' ? 'selected' : '' }}>Laundry</option>
                                    <option value="Mandatory Insurance Fees" {{ ($lainnya_dec['type'] ?? '') == 'Mandatory Insurance Fees' ? 'selected' : '' }}>Mandatory Insurance Fees</option>  
                                    <option value="Parking Fees" {{ ($lainnya_dec['type'] ?? '') == 'Parking Fees' ? 'selected' : '' }}>Parking Fees</option>
                                    <option value="Porter Service" {{ ($lainnya_dec['type'] ?? '') == 'Porter Service' ? 'selected' : '' }}>Porter Service (for company luggage only)</option>
                                    <option value="Toll Fees" {{ ($lainnya_dec['type'] ?? '') == 'Toll Fees' ? 'selected' : '' }}>Toll Fees</option>
                                </select>  
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($lainnya_dec['nominal'], 0, ',', '.') }}" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_lainnya[]" class="form-control">{{ $lainnya_dec['keterangan'] }}</textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="removeFormLainnya({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonLainnya" onclick="addMoreFormLainnyaDec(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Other Expenses</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text" min="0" value="{{ number_format(array_sum(array_column($declareCa['detail_lainnya'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@else
    <div id="form-container-lainnya">
        <div id="form-container-bt-lainnya-1" class="card-body p-2 mb-2 bg-light rounded-3">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Other Expenses 1</p>
            <div class="card-body bg-white rounded-3 p-2">
                <p class="fs-5 text-primary" style="font-weight: bold;">Other Expenses Declaration</p>
                <div class="row">
                    <!-- Lainnya Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label" for="name">Type of Others</label>
                        <select class="form-control select2" id="type_bt_lainnya_1" name="type_bt_lainnya[]">
                            <option value="">Select Type...</option>  
                            <option value="Airport Tax">Airport Tax</option>
                            <option value="Excess Baggage">Excess Baggage</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Mandatory Insurance Fees">Mandatory Insurance Fees</option>  
                            <option value="Parking Fees">Parking Fees</option>
                            <option value="Porter Service">Porter Service (for company luggage only)</option>
                            <option value="Toll Fees">Toll Fees</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_1" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_lainnya[]" class="form-control" placeholder="Write your information ..." ></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormLainnya(1, event)">Reset</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="removeFormLainnya(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButton" onclick="addMoreFormLainnyaDec(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Other Expenses</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text" min="0" value="0" readonly>
        </div>
    </div>
@endif

