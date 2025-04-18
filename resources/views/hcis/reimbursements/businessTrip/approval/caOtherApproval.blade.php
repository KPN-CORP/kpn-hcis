{{-- <script src="{{ asset('/js/btCashAdvanced/others.js') }}"></script> --}}
@include('js.hcis.btCashAdvanced.others')

<script>
    function addMoreFormLainnyaReq(event) {
        event.preventDefault();
        formCountOthers++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-lainnya-${formCountOthers}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Others ${formCountOthers}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Others Request</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="name">Type of Others</label>
                                <select class="form-control select2" id="type_bt_lainnya_${formCountOthers}" name="type_bt_lainnya[]" disabled>
                                    <option value="">Select Type...</option>
                                    <option value="Laundry">Laundry</option>
                                    <option value="Airport Tax">Airport Tax</option>
                                    <option value="Porter">Porter</option>
                                    <option value="Excess Baggage">Excess Baggage</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_${formCountOthers}" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_lainnya[]" class="form-control" placeholder="Write your information here ..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormLainnya(${formCountOthers}, event)">Reset</button>
                                <button class="btn btn-sm btn-outline-primary" onclick="removeFormLainnya(${formCountOthers}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
        document.getElementById("form-container-lainnya").appendChild(newForm);
    }
</script>

@if (!empty($caDetail['detail_lainnya']) && $caDetail['detail_lainnya'][0]['tanggal'] !== null)
    <div id="form-container-lainnya">
        @foreach ($caDetail['detail_lainnya'] as $lainnya)
            <div id="form-container-bt-lainnya-{{ $loop->index + 1 }}" class="card-body p-2 mb-3"
                style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Others {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-lainnya-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Others Request</p>
                    <div class="row">
                        <!-- Lainnya Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="tanggal_bt_lainnya[]" class="form-control bg-light"
                                value="{{ $lainnya['tanggal'] }}" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">  
                            <label class="form-label" for="name">Type of Others</label>  
                            <select class="form-control select2" id="type_bt_lainnya_{{ $loop->index + 1 }}" name="type_bt_lainnya[]" disabled>  
                                <option value="">Select Type...</option>  
                                <option value="Laundry" {{ $lainnya['type'] == 'Laundry' ? 'selected' : '' }}>Laundry</option>  
                                <option value="Airport Tax" {{ $lainnya['type'] == 'Airport Tax' ? 'selected' : '' }}>Airport Tax</option>  
                                <option value="Porter" {{ $lainnya['type'] == 'Porter' ? 'selected' : '' }}>Porter</option>  
                                <option value="Excess Baggage" {{ $lainnya['type'] == 'Excess Baggage' ? 'selected' : '' }}>Excess Baggage</option>  
                            </select>  
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control bg-light" name="nominal_bt_lainnya[]"
                                    id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0"
                                    value="{{ number_format($lainnya['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)" onblur="formatOnBlur(this)" readonly>
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="col-md-12 mb-2">
                            <div class="mb-2">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_lainnya[]" class="form-control bg-light" placeholder="Write your information here ..."
                                    readonly>{{ $lainnya['keterangan'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-2">
        <label class="form-label">Total Others</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text"
                min="0"
                value="{{ number_format(array_sum(array_column($caDetail['detail_lainnya'], 'nominal')), 0, ',', '.') }}"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-lainnya">
        <div id="form-container-bt-lainnya-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Others 1</p>
            <div id="form-container-bt-lainnya-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Others Request</p>
                <div class="row">
                    <!-- Lainnya Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_bt_lainnya[]" class="form-control bg-light"
                            placeholder="mm/dd/yyyy" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label" for="name">Type of Others</label>
                        <select class="form-control select2" id="type_bt_lainnya_1" name="type_bt_lainnya[]" disabled>
                            <option value="">Select Type...</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Airport Tax">Airport Tax</option>
                            <option value="Porter">Porter</option>
                            <option value="Excess Baggage">Excess Baggage</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control bg-light" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_1"
                                type="text" min="0" value="0" oninput="formatInput(this)"
                                onblur="formatOnBlur(this)" readonly>
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12 mb-2">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_lainnya[]" class="form-control bg-light" placeholder="Write your information here ..."
                                readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Others</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
