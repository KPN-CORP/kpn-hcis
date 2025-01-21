<script src="{{ asset('/js/cashAdvanced/detail.js') }}"></script>

@if (!empty($detailCA['detail_e']) && $detailCA['detail_e'][0]['type'] !== null)
    <div id="form-container-detail">
        @foreach ($detailCA['detail_e'] as $index => $detail)
            <div id="form-container-e-detail-{{ $loop->index + 1 }}" class="p-2 mb-4 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment {{ $loop->index + 1 }}</p>
                <div id="form-container-e-detail-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Request</p>
                    <div class="row">
                        <!-- Company Code -->
                        <div class="col-md-6">
                            <table class="table" style="border: none; border-collapse: collapse; padding: 1%;">
                                <tr>
                                    <th class="label" style="border: none; width:40%;">Entertainment Type</th>
                                    <td class="colon" style="border: none; width:1%;">:</td>
                                    <td class="value" style="border: none;">
                                        @php
                                            $typeMap = [
                                                'accommodation' => 'Accommodation',
                                                'food' => 'Food/Beverages/Souvenir',
                                                'fund' => 'Fund',
                                                'gift' => 'Gift',
                                                'transport' => 'Transport',
                                            ];
                                        @endphp
                                        {{ $typeMap[$detail['type']] ?? $detail['type'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Amount</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{ number_format($detail['nominal'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="label" style="border: none;">Entertainment Fee Detail</th>
                                    <td class="colon" style="border: none;">:</td>
                                    <td class="value" style="border: none;">{{ $detail['fee_detail'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-e-detail-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Declaration</p>
                    @if (isset($declareCA['detail_e'][$index]))
                        @php
                            $detail_dec = $declareCA['detail_e'][$index];
                        @endphp
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Entertainment Type</label>
                                <select name="enter_type_e_detail[]" id="enter_type_e_detail[]" class="form-select">
                                    <option value="">-</option>
                                    <option value="accommodation" {{ $detail_dec['type'] == 'accommodation' ? 'selected' : '' }}>Accommodation</option>
                                    <option value="food" {{ $detail_dec['type'] == 'food' ? 'selected' : '' }}>Food/Beverages/Souvenir</option>
                                    <option value="fund" {{ $detail_dec['type'] == 'fund' ? 'selected' : '' }}>Fund</option>
                                    <option value="gift" {{ $detail_dec['type'] == 'gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="transport" {{ $detail_dec['type'] == 'transport' ? 'selected' : '' }}>Transport</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_e_detail[]"
                                        id="nominal_e_detail_{{ $loop->index + 1 }}"
                                        type="text" min="0" value="{{ number_format($detail_dec['nominal'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;"
                                        oninput="formatInputENT(this)">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Entertainment Fee Detail</label>
                                <textarea name="enter_fee_e_detail[]" class="form-control">{{ $detail_dec['fee_detail'] }}</textarea>
                            </div>
                        </div>
                    @endif
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail({{ $loop->index + 1 }}, event)">Reset</button>
                            {{-- <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormDetailDec({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['detail_e'] as $index => $detail_dec)
            @if (!isset($detailCA['detail_e'][$index]))
                <div id="form-container-e-detail-{{ $loop->index + 1 }}" class="p-2 mb-4 rounded-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Entertainment Type</label>
                            <select name="enter_type_e_detail[]" id="enter_type_e_detail_{{ $loop->index + 1 }}" class="form-select">
                                <option value="">-</option>
                                <option value="food" {{ $detail_dec['type'] == 'food' ? 'selected' : '' }}>Food/Beverages/Souvenir</option>
                                <option value="transport" {{ $detail_dec['type'] == 'transport' ? 'selected' : '' }}>Transport</option>
                                <option value="accommodation" {{ $detail_dec['type'] == 'accommodation' ? 'selected' : '' }}>Accommodation</option>
                                <option value="gift" {{ $detail_dec['type'] == 'gift' ? 'selected' : '' }}>Gift</option>
                                <option value="fund" {{ $detail_dec['type'] == 'fund' ? 'selected' : '' }}>Fund</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_e_detail[]"
                                    id="nominal_e_detail_{{ $loop->index + 1 }}"
                                    type="text" min="0" value="{{ number_format($detail_dec['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInputENT(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Entertainment Fee Detail</label>
                            <textarea name="enter_fee_e_detail[]" class="form-control">{{ $detail_dec['fee_detail'] }}</textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormDetail({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonDetail" onclick="addMoreFormDetailDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Entertain</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_e_detail" id="total_e_detail"
                type="text" min="0" value="0"
                readonly>
        </div>
    </div>
@elseif (!empty($declareCA['detail_e']) && $declareCA['detail_e'][0]['nominal'] !== null)
    <div id="form-container-detail">
        @foreach ($declareCA['detail_e'] as $index => $detail_dec)
            @if (!isset($detailCA['detail_e'][$index]))
                <div id="form-container-e-detail-{{ $loop->index + 1 }}" class="p-2 mb-4 rounded-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Entertainment Type</label>
                            <select name="enter_type_e_detail[]" id="enter_type_e_detail_{{ $loop->index + 1 }}" class="form-select">
                                <option value="">-</option>
                                <option value="accommodation" {{ $detail_dec['type'] == 'accommodation' ? 'selected' : '' }}>Accommodation</option>
                                <option value="food" {{ $detail_dec['type'] == 'food' ? 'selected' : '' }}>Food/Beverages/Souvenir</option>
                                <option value="fund" {{ $detail_dec['type'] == 'fund' ? 'selected' : '' }}>Fund</option>
                                <option value="gift" {{ $detail_dec['type'] == 'gift' ? 'selected' : '' }}>Gift</option>
                                <option value="transport" {{ $detail_dec['type'] == 'transport' ? 'selected' : '' }}>Transport</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control" name="nominal_e_detail[]"
                                    id="nominal_e_detail_{{ $loop->index + 1 }}"
                                    type="text" min="0" value="{{ number_format($detail_dec['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInputENT(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Entertainment Fee Detail</label>
                            <textarea name="enter_fee_e_detail[]" class="form-control">{{ $detail_dec['fee_detail'] }}</textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormDetail({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonDetail" onclick="addMoreFormDetailDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Entertain</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_e_detail" id="total_e_detail"
                type="text" min="0" value="0"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-detail">
        <div id="form-container-e-detail-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment 1</p>
            <div id="form-container-e-detail-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Declaration</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Entertainment Type</label>
                        <select name="enter_type_e_detail[]" id="enter_type_e_detail_1" class="form-select">
                            <option value="">-</option>
                            <option value="accommodation">Accommodation</option>
                            <option value="food">Food/Beverages/Souvenir</option>
                            <option value="fund">Fund</option>
                            <option value="gift">Gift</option>
                            <option value="transport">Transport</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_e_detail[]"
                                id="nominal_e_detail_1"
                                type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInputENT(this)">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Entertainment Fee Detail</label>
                        <textarea name="enter_fee_e_detail[]" class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail(1, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormDetail(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonDetail" onclick="addMoreFormDetailDec(event)">Add More</button>
    </div>

    <div class="mt-2">
        <label class="form-label">Total Entertain</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_e_detail" id="total_e_detail"
                type="text" min="0" value="0"
                readonly>
        </div>
    </div>
@endif
