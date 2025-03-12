<div class="row mt-2" id="taksi_div" style="display: none;">
    <div class="col-md-12">
        <div class="table-responsive-sm">
            <div class="d-flex flex-column gap-2">
                <div class="text-bg-primary p-2 r-3"
                    style="text-align:center; border-radius:4px;">Taxi
                    Voucher
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="mb-2" id="taksi_div">
                            <label class="form-label">Total
                                Ticket</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                </div>
                                <input class="form-control bg-white"
                                    name="no_vt" id="no_vt"
                                    type="number" min="0"
                                    placeholder="ex: 2">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Voucher
                                Nominal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span
                                        class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control"
                                    name="nominal_vt" id="nominal_vt"
                                    type="text"
                                    placeholder="ex. 12.000"
                                    oninput="formatCurrency(this)">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Voucher
                                Keeper</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span
                                        class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control"
                                    name="keeper_vt" id="keeper_vt"
                                    type="text"
                                    placeholder="ex. 12.000"
                                    oninput="formatCurrency(this)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
