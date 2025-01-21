<script src="{{ asset('/js/cashAdvanced/transport.js') }}"></script>

<script>
    function addMoreFormTransportDec(event) {
        event.preventDefault();
        formCountTransport++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-transport-${formCountTransport}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Transport ${formCountTransport}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Transport Declaration</p>
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
                                <textarea name="keterangan_bt_transport[]" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormTransport(${formCountTransport}, event)">Reset</button>
                                <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormTransport(${formCountTransport}, event)">Delete</button>
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

@if (!empty($detailCA['detail_transport']) && $detailCA['detail_transport'][0]['tanggal'] !== null)
    <div id="form-container-transport">
        @foreach ($detailCA['detail_transport'] as $index => $transport)
            <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="p-2 mb-2 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Transport {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-transport-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-2" style="border-radius: 1%;">
                    <div class="row">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Transport Request</p>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Company Code</th>
                                    <td class="block">:</td>
                                    <td>{{ $transport['company_code'] }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td class="block">:</td>
                                    <td> Rp {{ number_format($transport['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Date</th>
                                    <td class="block">:</td>
                                    <td> {{ date('d M Y', strtotime($transport['tanggal'])) }} </td>
                                </tr>
                                <tr>
                                    <th>End Date</th>
                                    <td class="block">:</td>
                                    <td>{{number_format($transport['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-bt-transport-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold; ">Transport Declaration</p>
                    @if (isset($declareCA['detail_transport'][$index]))
                        @php
                            $transport_dec = $declareCA['detail_transport'][$index];
                        @endphp
                        <div class="row">
                            <!-- Transport Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Transport Date</label>
                                <input type="date" name="tanggal_bt_transport[]" class="form-control" value="{{$transport_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="name">Company Code</label>
                                <select class="form-control select2" id="company_bt_transport_{{ $loop->index + 1 }}" name="company_bt_transport[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $transport_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group ">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control"
                                            name="nominal_bt_transport[]"
                                            id="nominal_bt_transport_{{ $loop->index + 1 }}"
                                            type="text"
                                            min="0"
                                            value="{{number_format($transport_dec['nominal'], 0, ',', '.') }}"
                                            onfocus="this.value = this.value === '0' ? '' : this.value;"
                                            oninput="formatInput(this)"
                                            onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_transport[]" class="form-control">{{$transport_dec['keterangan']}}</textarea>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_transport'] as $index => $transport_dec)
            @if (!isset($detailCA['detail_transport'][$index]))
                <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-2" style="border-radius: 1%;">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Transport {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <!-- Transport Date -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Transport Date</label>
                            <input type="date" name="tanggal_bt_transport[]" class="form-control" value="{{$transport_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="name">Company Code</label>
                            <select class="form-control select2" id="company_bt_transport_{{ $loop->index + 1 }}" name="company_bt_transport[]">
                                <option value="">Select Company...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}"
                                        @if($company->contribution_level_code == $transport_dec['company_code']) selected @endif>
                                        {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
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
                                        id="nominal_bt_transport_{{ $loop->index + 1 }}"
                                        type="text"
                                        min="0"
                                        value="{{number_format($transport_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInput(this)"
                                        onblur="formatOnBlur(this)">
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="col-md-12">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_transport[]" class="form-control">{{$transport_dec['keterangan']}}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormTransport({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary " id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Transport</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_transport"
                id="total_bt_transport" type="text"
                min="0" value="{{ number_format(array_sum(array_column($declareCA['detail_transport'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@elseif (!empty($declareCA['detail_transport']) && $declareCA['detail_transport'][0]['tanggal'] !== null)
    <div id="form-container-transport">
        @foreach ($declareCA['detail_transport'] as $index => $transport_dec)
            @if (!isset($detailCA['detail_transport'][$index]))
                <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Transport {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Transport Declaration</p>
                        <div class="row">
                            <!-- Transport Date -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Transport Date</label>
                                <input type="date" name="tanggal_bt_transport[]" class="form-control" value="{{$transport_dec['tanggal']}}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="name">Company Code</label>
                                <select class="form-control select2" id="company_bt_transport_{{ $loop->index + 1 }}" name="company_bt_transport[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $transport_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
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
                                            id="nominal_bt_transport_{{ $loop->index + 1 }}"
                                            type="text"
                                            min="0"
                                            value="{{number_format($transport_dec['nominal'], 0, ',', '.') }}"
                                            onfocus="this.value = this.value === '0' ? '' : this.value;"
                                            oninput="formatInput(this)"
                                            onblur="formatOnBlur(this)">
                                </div>
                            </div>

                            <!-- Information -->
                            <div class="col-md-12">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_transport[]" class="form-control">{{$transport_dec['keterangan']}}</textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormTransport({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Transport</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_transport"
                id="total_bt_transport" type="text"
                min="0" value="{{ number_format(array_sum(array_column($declareCA['detail_transport'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@else
    <div id="form-container-transport">
        <div id="form-container-bt-transport-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Transport 1</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Transport Declaration</p>
                <div class="row">
                    <!-- Transport Date -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Transport Date</label>
                        <input type="date" name="tanggal_bt_transport[]" class="form-control" placeholder="mm/dd/yyyy">
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
                            <input class="form-control"
                                    name="nominal_bt_transport[]"
                                    id="nominal_bt_transport_1"
                                    type="text"
                                    min="0"
                                    value="0"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInput(this)"
                                    onblur="formatOnBlur(this)">
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="col-md-12">
                        <label class="form-label">Information</label>
                        <textarea name="keterangan_bt_transport[]" class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormTransport(1, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormTransport(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Transport</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_bt_transport"
                id="total_bt_transport" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
@endif
