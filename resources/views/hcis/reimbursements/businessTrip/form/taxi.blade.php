<div class="card-body bg-light rounded shadow-none" id="taksi_div">
    <div class="h5 text-uppercase">
        <b>Taxi Voucher</b>
    </div>
    <div class="row">
        <div class="col-md-6 mb-2" id="taksi_div">
            <label class="form-label">Total Ticket</label>
            <div class="input-group input-group-sm">
                <input class="form-control" name="no_vt" id="no_vt" type="number" min="0"
                    placeholder="ex: 2">
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label">Voucher Nominal</label>
            <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                <input class="form-control" name="nominal_vt" id="nominal_vt" type="text" placeholder="ex. 12.000"
                    oninput="formatCurrency(this)">
            </div>
        </div>
        {{-- <div class="col-md-4 mb-2">
            <label class="form-label">Voucher Keeper</label>
            <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                <input class="form-control" name="keeper_vt" id="keeper_vt" type="text" placeholder="ex. 12.000"
                    oninput="formatCurrency(this)">
            </div>
        </div> --}}
    </div>
</div>
</div>

