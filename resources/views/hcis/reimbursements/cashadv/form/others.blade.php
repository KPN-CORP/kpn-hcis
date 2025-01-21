<script src="{{ asset('/js/cashAdvanced/others.js') }}"></script>

<script>
    function addMoreFormLainnyaReq(event) {
        event.preventDefault();
        formCountOthers++;
        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-lainnya-${formCountOthers}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                    <p class="fs-4 text-primary" style="font-weight: bold;">Others ${formCountOthers}</p>
                    <div id="form-container-bt-lainnya-req-${formCountOthers}" class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Others Request</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
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
                                <textarea name="keterangan_bt_lainnya[]" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormLainnya(${formCountOthers}, event)">Reset</button>
                                <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormLainnya(${formCountOthers}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
        document.getElementById("form-container-lainnya").appendChild(newForm);
    }

</script>

@if (!empty($detailCA['detail_lainnya']) && $detailCA['detail_lainnya'][0]['tanggal'] !== null)
    <div id="form-container-lainnya">
        @foreach ($detailCA['detail_lainnya'] as $lainnya)
            <div id="form-container-bt-lainnya-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Others {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-lainnya-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Others Request</p>
                    <div class="row">
                        <!-- Lainnya Date -->
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="tanggal_bt_lainnya[]" class="form-control" value="{{$lainnya['tanggal']}}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($lainnya['nominal'], 0, ',', '.') }}" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="col-md-12">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_lainnya[]" class="form-control">{{ $lainnya['keterangan'] }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormLainnya({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormLainnya({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonLainnya" onclick="addMoreFormLainnyaReq(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Others</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text" min="0" value="{{ number_format(array_sum(array_column($detailCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}" readonly>
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
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group ">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_bt_lainnya[]" id="nominal_bt_lainnya_1" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInput(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12 ">
                        <label class="form-label">Information</label>
                        <textarea name="keterangan_bt_lainnya[]" class="form-control"></textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormLainnya(1, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormLainnya(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" id="addMoreButton" onclick="addMoreFormLainnyaReq(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Others</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_lainnya" id="total_bt_lainnya" type="text" min="0" value="0" readonly>
        </div>
    </div>
@endif

