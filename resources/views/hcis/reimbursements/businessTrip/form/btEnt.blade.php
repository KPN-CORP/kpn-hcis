<div class="row" id="ca_entr" style="">
    <div class="col-md-12 mb-2">
        <div class="table-responsive-sm">
            <div class="row mb-2">
                <div class="col-md-6 mb-2">
                    <label for="date_required" class="form-label">Date Required</label>
                    <input type="date" class="form-control form-control-sm" id="date_required_1" name="date_required"
                        placeholder="Date Required" onchange="syncDateRequired(this)">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="ca_decla">Declaration Estimate</label>
                    <input type="date" name="ca_decla" id="ca_decla_3" class="form-control form-control-sm bg-light"
                        placeholder="mm/dd/yyyy" readonly>
                </div>
            </div>
            <div class="d-flex flex-column">
                <ul class="nav mb-2" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-detail-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-detail" type="button" role="tab" aria-controls="pills-detail"
                            aria-selected="true">Detail Entertain</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-relation-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-relation" type="button" role="tab"
                            aria-controls="pills-relation" aria-selected="false">Relation Entertain</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-detail" role="tabpanel"
                        aria-labelledby="pills-detail-tab">
                        {{-- ca detail content --}}
                        @include('hcis.reimbursements.businessTrip.form.entertainForm.entDetail')
                    </div>
                    <div class="tab-pane fade" id="pills-relation" role="tabpanel"
                        aria-labelledby="pills-relation-tab">
                        {{-- ca relation content --}}
                        @include('hcis.reimbursements.businessTrip.form.entertainForm.entRelation')
                    </div>
                </div>
                <br>
            </div>

            <div class="mt-2">
                <label class="form-label">Total Entertain</label>
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
        </div>
    </div>

    <div class="col-md-12 mb-2" id="total_bt_ent_2" style="display:none">
        <label class="form-label">Total Request</label>
        <div class="input-group">
            <div class="input-group-append">
                <span class="input-group-text">Rp</span>
            </div>
            <input class="form-control bg-light" name="totalreq2" id="totalreq2" type="text"
                min="0" value="0" readonly>
        </div>
    </div>
</div>
