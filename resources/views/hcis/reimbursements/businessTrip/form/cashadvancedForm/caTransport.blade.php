{{-- <script src="{{ asset('/js/btCashAdvanced/transport.js') }}"></script> --}}
@include('js.hcis.btCashAdvanced.transport')

<script>
    function addMoreFormTransportReq(event) {
        event.preventDefault();
        formCountTransport++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-transport-${formCountTransport}`;
        newForm.className = "bg-light rounded-3 card-body p-2 mb-2";
        // newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Transport ${formCountTransport}</p>
                     <label for="additional-fields-title" class="mb-2">
                        <span class="text-info fst-italic">* Transport only for Bus, Train and Speedboat</span>
                    </label>
                    <div class="card-body bg-white rounded-3 p-2">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Request Transport</p>
                        <div class="row">
                            <!-- Transport Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Transport Date</label>
                                <input type="date" name="tanggal_bt_transport[]" class="form-control" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="name">Company Code</label>
                                <select class="form-control select2" id="company_bt_transport_${formCountTransport}" name="company_bt_transport[]">
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
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control"
                                            name="nominal_bt_transport[]"
                                            id="nominal_bt_transport_${formCountTransport}"
                                            type="text"
                                            min="0"
                                            value="0"
                                            onfocus="this.value = this.value === '0' ? '' : this.value;"
                                            oninput="formatInput(this)"
                                            onblur="formatOnBlur(this)" onchange="calculateTotalNominalBTTransport()">
                                </div>
                            </div>
                            <!-- Information -->
                            <div class="col-md-12">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information here ..."></textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormTransport(${formCountTransport}, event)">Reset</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFormTransport(${formCountTransport}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
        document.getElementById("form-container-transport").appendChild(newForm);

        $(`#company_bt_transport_${formCountTransport}`).select2({
            theme: "bootstrap-5",
        });
    }
</script>

@if (!empty($caDetail['detail_transport']) && $caDetail['detail_transport'][0]['tanggal'] !== null)
    <div id="form-container-transport">
        @foreach ($caDetail['detail_transport'] as $transport)
            <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="bg-light rounded-3 card-body p-2 mb-2">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Transport {{ $loop->index + 1 }}</p>
                <label for="additional-fields-title" class="mb-2">
                    <span class="text-info fst-italic">* Transport only for Bus, Train and Speedboat</span>
                </label>
                <div id="form-container-bt-transport-req-{{ $loop->index + 1 }}" class="card-body rounded-3 bg-white p-2">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Transport Request</p>
                    <div class="row">
                        <!-- Transport Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Transport Date</label>
                            <input type="date" name="tanggal_bt_transport[]" class="form-control"
                                value="{{ $transport['tanggal'] }}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="name">Company Code</label>
                            <select class="form-control select2" id="company_bt_transport_{{ $loop->index + 1 }}"
                                name="company_bt_transport[]">
                                <option value="">Select Company...</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}"
                                        @if ($company->contribution_level_code == $transport['company_code']) selected @endif>
                                        {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_bt_transport[]"
                                    id="nominal_bt_transport_{{ $loop->index + 1 }}" type="text" min="0"
                                    value="{{ number_format($transport['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)" onblur="formatOnBlur(this)">
                            </div>
                        </div>
                        <!-- Information -->
                        <div class="col-md-12">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information here ...">{{ $transport['keterangan'] }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                                onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="removeFormTransport({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport" onclick="addMoreFormTransportReq(event)">Add
            More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Transport</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_transport" id="total_bt_transport" type="text"
                min="0"
                value="{{ number_format(array_sum(array_column($caDetail['detail_transport'], 'nominal')), 0, ',', '.') }}"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-transport">
        <div id="form-container-bt-transport-1" class="card-body p-2 mb-2 bg-light rounded-3">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Transport 1</p>
            <label for="additional-fields-title" class="mb-2">
                <span class="text-info fst-italic">* Transport only for Bus, Train and Speedboat</span>
            </label>
            <div id="form-container-bt-transport-req-1" class="card-body bg-white rounded-3 p-2">
                <p class="fs-5 text-primary" style="font-weight: bold;">Transport Request</p>
                <div class="row">
                    <!-- Transport Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Transport Date</label>
                        <input type="date" name="tanggal_bt_transport[]" class="form-control"
                            placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label" for="name">Company Code</label>
                        <select class="form-control select2" id="company_bt_transport_1" name="company_bt_transport[]">
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
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_bt_transport[]" id="nominal_bt_transport_1"
                                type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInput(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>
                    <!-- Information -->
                    <div class="col-md-12">
                        <label class="form-label">Information</label>
                        <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information here ..."></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px"
                            onclick="clearFormTransport(1, event)">Reset</button>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="removeFormTransport(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport"
            onclick="addMoreFormTransportReq(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Transport</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="total_bt_transport" id="total_bt_transport" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
