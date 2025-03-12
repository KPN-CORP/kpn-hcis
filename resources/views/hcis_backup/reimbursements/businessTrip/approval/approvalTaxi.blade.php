<div class="card-body bg-light rounded shadow-none" id="taksi_div">
    <div class="h5 text-uppercase">
        <b>Taxi/Grab</b>
    </div>
    <div class="row">
        <div class="col-md-12 mb-2" id="taksi_div">
            <label class="form-label">Total Ticket</label>
            <div class="input-group input-group-sm">
                <input class="form-control bg-light" name="no_vt" id="no_vt" type="number" min="0"
                    placeholder="ex: 2" value="{{ $taksiData->no_vt ?? '' }}" readonly>
            </div>
        </div>
        <div class="col-md-12 mb-2">
            <label class="form-label">Detail Information</label>
            <div class="input-group input-group-sm">
                <textarea class="form-control form-control-sm bg-light" id="vt_detail" name="vt_detail" rows="3"
                    placeholder="Fill your need" readonly>{{ $taksiData->vt_detail ?? '' }}</textarea>
            </div>
        </div>
        {{-- <div class="col-md-4 mb-2">
            <label class="form-label">Voucher Keeper</label>
            <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                <input class="form-control bg-light" name="keeper_vt" id="keeper_vt" type="text" placeholder="ex. 12.000"
                    oninput="formatCurrency(this)" value="{{ $taksiData->keeper_vt ?? '' }}" readonly>
            </div>
        </div> --}}
    </div>
</div>
</div>
