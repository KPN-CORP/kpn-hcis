<script src="{{ asset('/js/btCashAdvanced/detail.js') }}"></script>

@if (!empty($caDetail['relation_e']) && $caDetail['relation_e'][0]['name'] !== null)
    <div id="form-container-detail">
        @foreach ($caDetail['detail_e'] as $detail)
            <div id="form-container-e-detail-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment {{ $loop->index + 1 }}</p>
                <div id="form-container-e-detail-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Request</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Entertainment Type</label>
                            <select name="enter_type_e_detail[]" id="enter_type_e_detail[]" class="form-select bg-light" disabled>
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
                                <input class="form-control bg-light" disabled name="nominal_e_detail[]"
                                    id="nominal_e_detail_{{ $loop->index + 1 }}"
                                    type="text" min="0" value="{{ number_format($detail['nominal'], 0, ',', '.') }}"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatInputENT(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Entertainment Fee Detail</label>
                            <textarea name="enter_fee_e_detail[]" class="form-control bg-light" disabled>{{ $detail['fee_detail'] }}</textarea>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div id="form-container-relation">
        <div id="form-container-e-relation-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment 1</p>
            <div id="form-container-e-relation-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Request</p>
                <div class="row">
                    <!-- Relation Date -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="accommodation_e_relation[0]"
                                id="accommodation_e_relation_0"
                                value="accommodation">
                            <label class="form-check-label"
                                for="accommodation_e_relation_0">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="food_e_relation[0]" type="checkbox"
                                id="food_e_relation_0" value="food">
                            <label class="form-check-label"
                                for="food_e_relation_0">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="fund_e_relation[0]" type="checkbox"
                                id="fund_e_relation_0" value="fund">
                            <label class="form-check-label"
                                for="fund_e_relation_0">Fund</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="gift_e_relation[0]" type="checkbox"
                                id="gift_e_relation_0" value="gift">
                            <label class="form-check-label"
                                for="gift_e_relation_0">Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="transport_e_relation[0]"
                                type="checkbox"
                                id="transport_e_relation_0"
                                value="transport">
                            <label class="form-check-label"
                                for="transport_e_relation_0">Transport</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="name">Name</label>
                        <input type="text" name="rname_e_relation[]"
                            id="rname_e_relation_1" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="position">Position</label>
                        <input type="text"
                            name="rposition_e_relation[]"
                            id="rposition_e_relation_1"
                            class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="company">Company</label>
                        <input type="text" name="rcompany_e_relation[]"
                            id="rcompany_e_relation_1"
                            class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"
                            for="purpose">Purpose</label>
                        <textarea name="rpurpose_e_relation[]"
                            id="rpurpose_e_relation_1"
                            class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation(1, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif