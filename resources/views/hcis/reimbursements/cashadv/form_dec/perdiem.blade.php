<script>
    function addMoreFormPerdiemDec(event) {
        event.preventDefault();
        formCountPerdiem++;
        const index = formCountPerdiem;

        const newForm = document.createElement("div");
        newForm.id = `form-container-bt-perdiem-${formCountPerdiem}`;
        newForm.className = "card-body p-2 mb-3";
        newForm.style.backgroundColor = "#f8f8f8";
        newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem ${formCountPerdiem}</p>
            <div id="form-container-bt-perdiem-dec-${formCountPerdiem}" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                <div class="row">
                    <!-- Company Code -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="company_bt_perdiem${formCountPerdiem}">Company Code</label>
                        <select class="form-control" id="company_bt_perdiem_${formCountPerdiem}" name="company_bt_perdiem[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Agency -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="locationFilter">Location Agency</label>
                        <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_${formCountPerdiem}" onchange="toggleOtherLocation(this, ${formCountPerdiem})">
                            <option value="">Select location...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->area }}" @if($location->area == $perdiem['location']) selected @endif>
                                    {{ $location->area." (".$location->company_name.")" }}
                                </option>
                            @endforeach
                            <option value="Others" @if('Others' == $perdiem['location']) selected @endif>Others</option>
                        </select>
                        <div id="other-location-${formCountPerdiem}" class="mt-3" @if($perdiem['location'] != 'Others') style="display: none;" @endif>
                            <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem['other_location'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Start Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Start Perdiem</label>
                        <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- End Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">End Perdiem</label>
                        <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" placeholder="mm/dd/yyyy" onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- Total Days -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Amount -->
                <div class="mb-2">
                    <label class="form-label">Amount</label>
                </div>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control form-control-sm bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_${formCountPerdiem}" type="text" value="0" onchange="onNominalChange()">
                </div>
                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning btn-sm mr-2" style="margin-right: 10px" onclick="clearFormPerdiem(${formCountPerdiem}, event)">Reset</button>
                        <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormPerdiem(${formCountPerdiem}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.getElementById("form-container-perdiem").appendChild(newForm);

        // Inisialisasi select2 setelah elemen baru ditambahkan
        $(`#company_bt_perdiem_${formCountPerdiem}, #location_bt_perdiem_${formCountPerdiem}`).select2({
            theme: "bootstrap-5",
        });

        $(`#company_bt_perdiem_${formCountPerdiem}, #location_bt_perdiem_${formCountPerdiem}`).on('change', function() {
            handleDateChange();
        });

        perdiemData.push({ index: index.toString(), startDate: '', endDate: '' });
        // console.log("Data Perdiem setelah Add More:", perdiemData);

        handleDateChange();
    }
</script>

<script src="{{ asset('/js/cashAdvanced/perdiem.js') }}"></script>

@if (!empty($detailCA['detail_perdiem']) && $detailCA['detail_perdiem'][0]['start_date'] !== null)
    {{-- Form Edit --}}
    <div id="form-container-perdiem">
        @foreach ($detailCA['detail_perdiem'] as $index => $perdiem)
            <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="p-2 mb-2 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Perdiem {{ $loop->index + 1 }}</p>
                <div id="form-container-bt-perdiem-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-2" style="border-radius: 1%;">
                    <div class="row">
                        <!-- Company Code -->
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Request</p>
                        <div class="col-md-6">
                            <table style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Company Code</th>
                                    <td class="colon" style="border: none; width:1%;">:</td>
                                    <td class="value" style="border: none;">{{ $perdiem['company_code'] }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Location Agency</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">
                                        @if($perdiem['location'] == 'Others')
                                            {{$perdiem['other_location']}}
                                        @else
                                            {{$perdiem['location']}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Amount</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">Rp. {{ number_format($perdiem['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Start Perdiem</th>
                                    <td class="colon" style="border: none; width:1%;">:</td>
                                    <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">End Perdiem</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Amount</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{$perdiem['total_days']}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-bt-perdiem-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                    <input type="hidden" value="{{$perdiem['location']}}" name="location_bt_perdiem[]">
                    @if (isset($declareCA['detail_perdiem'][$index]))
                        @php
                            $perdiem_dec = $declareCA['detail_perdiem'][$index];
                        @endphp
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning btn-sm mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_perdiem'] as $index => $perdiem_dec)
            @if (!isset($detailCA['detail_perdiem'][$index]))
                <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="card-body p-2 mb-2" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem {{ $loop->index + 1 }}</p>
                    <div id="form-container-bt-transport-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-2">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormPerdiem({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="{{ number_format(array_sum(array_column($declareCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@elseif (!empty($declareCA['detail_perdiem']) && $declareCA['detail_perdiem'][0]['start_date'] !== null)
    <div id="form-container-perdiem">
        @foreach ($declareCA['detail_perdiem'] as $index => $perdiem_dec)
            @if (!isset($detailCA['detail_perdiem'][$index]))
                <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold;">Perdiem {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-light p-2 mb-3">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                        <div class="row">
                            <!-- Company Code -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="company_bt_perdiem{{ $loop->index + 1 }}">Company Code</label>
                                <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                    <option value="">Select Company...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}"
                                            @if($company->contribution_level_code == $perdiem_dec['company_code']) selected @endif>
                                            {{ $company->contribution_level." (".$company->contribution_level_code.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Agency -->
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="locationFilter">Location Agency</label>
                                <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->area }}" @if($location->area == $perdiem_dec['location']) selected @endif>
                                            {{ $location->area." (".$location->company_name.")" }}
                                        </option>
                                    @endforeach
                                    <option value="Others" @if('Others' == $perdiem_dec['location']) selected @endif>Others</option>
                                </select>
                                <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem_dec['location'] != 'Others') style="display: none;" @endif>
                                    <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem_dec['other_location'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Start Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Start Perdiem</label>
                                <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" value="{{$perdiem_dec['start_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <!-- End Perdiem -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">End Perdiem</label>
                                <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" value="{{$perdiem_dec['end_date']}}" placeholder="mm/dd/yyyy"
                                    onchange="calculateTotalDaysPerdiem(this)">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{$perdiem_dec['total_days']}}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem_dec['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormPerdiem({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary " onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="{{ number_format(array_sum(array_column($declareCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}" readonly>
        </div>
    </div>
@else
    {{-- Form Add --}}
    <div id="form-container-perdiem">
        <div id="form-container-bt-perdiem-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Perdiem 1</p>
            <div id="form-container-bt-perdiem-dec-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Perdiem Declaration</p>
                <div class="row">
                    <!-- Company Code -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="company_bt_perdiem1">Company Code</label>
                        <select class="form-control select2" id="company_bt_perdiem_1" name="company_bt_perdiem[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Agency -->
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="locationFilter">Location Agency</label>
                        <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_1" onchange="toggleOtherLocation(this, 1)">
                            <option value="">Select location...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->area }}" @if($location->area == $perdiem['location']) selected @endif>
                                    {{ $location->area." (".$location->company_name.")" }}
                                </option>
                            @endforeach
                            <option value="Others" @if('Others' == $perdiem['location']) selected @endif>Others</option>
                        </select>
                        <div id="other-location-1" class="mt-3" @if($perdiem['location'] != 'Others') style="display: none;" @endif>
                            <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem['other_location'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Start Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Start Perdiem</label>
                        <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- End Perdiem -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">End Perdiem</label>
                        <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" placeholder="mm/dd/yyyy"
                            onchange="calculateTotalDaysPerdiem(this)">
                    </div>

                    <!-- Total Days -->
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Amount</label>
                </div>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control form-control-sm bg-light" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_1" type="text" value="0" onchange="onNominalChange()" readonly>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormPerdiem(1, event)">Reset</button>
                        <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormPerdiem(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" onclick="addMoreFormPerdiemDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Perdiem</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="0" readonly>
        </div>
    </div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", initializeDateInputs);
</script>
