{{-- <script src="{{ asset('/js/btCashAdvanced/detail.js') }}"></script> --}}
@include('js.hcis.btCashAdvanced.detail')

@if (!empty($caDetail['detail_e']) && $caDetail['detail_e'][0]['type'] !== null)
    <div id="form-container-detail">
        @foreach ($caDetail['detail_e'] as $detail)
            <div id="form-container-e-detail-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-2">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment {{ $loop->index + 1 }}</p>
                <div id="form-container-e-detail-req-{{ $loop->index + 1 }}" class="card-body bg-white p-2">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Request</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Entertainment Type</label>
                            <select name="enter_type_e_detail[]" id="enter_type_e_detail[]" class="form-select">
                                <option value="">-</option>
                                <option value="accommodation" {{ $detail['type'] == 'accommodation' ? 'selected' : '' }}>Accommodation</option>
                                <option value="food" {{ $detail['type'] == 'food' ? 'selected' : '' }}>Food/Beverages/Souvenir</option>
                                <option value="fund" {{ $detail['type'] == 'fund' ? 'selected' : '' }}>Fund</option>
                                <option value="gift" {{ $detail['type'] == 'gift' ? 'selected' : '' }}>Gift</option>
                                <option value="transport" {{ $detail['type'] == 'transport' ? 'selected' : '' }}>Transport</option>
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
                                    type="text" min="0" value="{{ number_format($detail['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInputENT(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Entertainment Fee Detail</label>
                            <textarea name="enter_fee_e_detail[]" class="form-control" placeholder="Write more details ...">{{ $detail['fee_detail'] }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormDetail({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonDetail" onclick="addMoreFormDetailReq(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Entertainment</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_ent_detail" id="total_ent_detail"
                type="text" min="0" value="0"
                readonly>
        </div>
    </div>
@else
    <div id="form-container-detail">
        <div id="form-container-e-detail-1" class="card-body p-2 mb-2 rounded-3 bg-light">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment 1</p>
            <div id="form-container-e-detail-req-1" class="card-body bg-white rounded-3 p-2 ">
                <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Request</p>
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
                        <textarea name="enter_fee_e_detail[]" class="form-control" placeholder="Write more details ..."></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail(1, event)">Reset</button>
                        <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormDetail(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonDetail" onclick="addMoreFormDetailReq(event)">Add More</button>
    </div>
    <hr/>
    <div>
        <label class="form-label">Total Entertainment</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light"
                name="total_ent_detail" id="total_ent_detail"
                type="text" min="0" value="0"
                readonly>
        </div>
    </div>
@endif
