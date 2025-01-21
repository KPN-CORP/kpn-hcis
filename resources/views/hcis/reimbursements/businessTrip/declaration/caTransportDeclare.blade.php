<script src="{{ asset('/js/btCashAdvanced/transport.js') }}"></script>
<script>
    var formCountTransport = 0;

    window.addEventListener("DOMContentLoaded", function () {
        formCountTransport = document.querySelectorAll(
            "#form-container-transport > div"
        ).length;
    });

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
                            <div class="input-group mb-3">
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
                        <div class="col-md-12 mb-2">
                            <div class="mb-2">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport(${formCountTransport}, event)">Reset</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="removeFormTransport(${formCountTransport}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        document.getElementById("form-container-transport").appendChild(newForm);

        $(`#company_bt_transport_${formCountTransport}`).select2({
            theme: "bootstrap-5",
        });
    }

    function addMoreFormTransportReq(event) {
        event.preventDefault();
        formCountTransport++;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-transport-${formCountTransport}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
                <p class="fs-4 text-primary" style="font-weight: bold; ">Transport ${formCountTransport}</p>
                <div class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Request Declaration</p>
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
                            <div class="input-group mb-3">
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
                        <div class="col-md-12 mb-2">
                            <div class="mb-2">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport(${formCountTransport}, event)">Reset</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="removeFormTransport(${formCountTransport}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        document.getElementById("form-container-transport").appendChild(newForm);

        $(`#company_bt_transport_${formCountTransport}`).select2({
            theme: "bootstrap-5",
        });
    }

    $(".btn-warning").click(function (event) {
        event.preventDefault();
        var index = $(this).closest(".card-body").index() + 1;
        removeFormTransport(index, event);
    });

    function removeFormTransport(index, event) {
        event.preventDefault();
        if (formCountTransport > 0) {
            const formContainer = document.getElementById(
                `form-container-bt-transport-${index}`
            );
            if (formContainer) {
                const nominalInput = formContainer.querySelector(
                    `#nominal_bt_transport_${index}`
                );
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(
                        document.querySelector('input[name="total_bt_transport"]')
                            .value
                    );
                    total -= nominalValue;
                    document.querySelector(
                        'input[name="total_bt_transport"]'
                    ).value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-transport-${index}`).remove();
                formCountTransport--;
            }
        }
    }

    function removeFormTransportDec(index, event) {
        event.preventDefault();
        if (formCountTransport > 0) {
            const formContainer = document.getElementById(
                `form-container-bt-transport-dec-${index}`
            );
            if (formContainer) {
                const nominalInput = formContainer.querySelector(
                    `#nominal_bt_transport_${index}`
                );
                if (nominalInput) {
                    let nominalValue = cleanNumber(nominalInput.value);
                    let total = cleanNumber(
                        document.querySelector('input[name="total_bt_transport"]')
                            .value
                    );
                    total -= nominalValue;
                    document.querySelector(
                        'input[name="total_bt_transport"]'
                    ).value = formatNumber(total);
                    calculateTotalNominalBTTotal();
                }
                $(`#form-container-bt-transport-dec-${index}`).remove();
                formCountTransport--;
            }
        }
    }

    function clearFormTransport(index, event) {
        event.preventDefault();
        if (formCountTransport > 0) {
            let nominalValue = cleanNumber(
                document.querySelector(`#nominal_bt_transport_${index}`).value
            );

            let total = cleanNumber(
                document.querySelector('input[name="total_bt_transport"]').value
            );
            total -= nominalValue;
            document.querySelector('input[name="total_bt_transport"]').value =
                formatNumber(total);

            let formContainer = document.getElementById(
                `form-container-bt-transport-${index}`
            );

            formContainer
                .querySelectorAll('input[type="text"], input[type="date"]')
                .forEach((input) => {
                    input.value = "";
                });

            formContainer
                .querySelectorAll('input[type="number"]')
                .forEach((input) => {
                    input.value = 0;
                });

            const companyCodeSelect = formContainer.querySelector(
                `#company_bt_transport_${index}`
            );
            if (companyCodeSelect) {
                companyCodeSelect.selectedIndex = 0; // Reset the select element to the default option
                var event = new Event("change");
                companyCodeSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
            }

            formContainer.querySelectorAll("select").forEach((select) => {
                select.selectedIndex = 0;
            });

            formContainer.querySelectorAll("textarea").forEach((textarea) => {
                textarea.value = "";
            });

            document.querySelector(`#nominal_bt_transport_${index}`).value = 0;
            calculateTotalNominalBTTotal();
        }
    }

    function calculateTotalNominalBTTransport() {
        let total = 0;
        document
            .querySelectorAll('input[name="nominal_bt_transport[]"]')
            .forEach((input) => {
                total += cleanNumber(input.value); // Gunakan cleanNumber untuk parsing
            });
        document.querySelector('input[name="total_bt_transport"]').value =
            formatNumber(total); // Tampilkan dengan format
    }

    function onNominalChange() {
        calculateTotalNominalBTTransport();
    }
</script>

@if (!empty($detailCA['detail_transport']) && $detailCA['detail_transport'][0]['tanggal'] !== null)
    <div id="form-container-transport">
        @foreach ($detailCA['detail_transport'] as $index => $transport)
            <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="p-2 mb-3 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Transport {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-transport-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
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
                                <div class="input-group mb-3">
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
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ...">{{$transport_dec['keterangan']}}</textarea>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                            {{-- <button class="btn btn-warning mr-2" onclick="removeFormTransportDec({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_transport'] as $index => $transport_dec)
            @if (!isset($detailCA['detail_transport'][$index]))
                <div id="form-container-bt-transport-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
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
                            <div class="input-group mb-3">
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
                        <div class="col-md-12 mb-2">
                            <div class="mb-2">
                                <label class="form-label">Information</label>
                                <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ...">{{$transport_dec['keterangan']}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="removeFormTransport({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-1">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
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
@elseif (!empty($declareCA['detail_transport']) && $declareCA['detail_transport'][0]['tanggal'] !== null && $declareCA['detail_transport'][0]['tanggal'] !== null)
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
                                <div class="input-group mb-3">
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
                            <div class="col-md-12 mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ...">{{$transport_dec['keterangan']}}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="removeFormTransport({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
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
                        <div class="input-group mb-3">
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
                    <div class="col-md-12 mb-2">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_bt_transport[]" class="form-control" placeholder="Write your information ..."></textarea>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm" style="margin-right: 10px" onclick="clearFormTransport(1, event)">Reset</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="removeFormTransport(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonTransport" onclick="addMoreFormTransportDec(event)">Add More</button>
    </div>

    <div class="mt-2 mb-2">
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

