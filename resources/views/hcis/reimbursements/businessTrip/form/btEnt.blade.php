<div class="row" id="ca_entr" style="">
    <div class="col-md-12 mb-2">
        <div class="table-responsive-sm">
            <div class="row mb-2">
                <div class="col-md-6 mb-2">
                    <label for="date_required" class="form-label">CA Withdrawal Date</label>
                    <input type="date" class="form-control form-control-sm" id="date_required_1" name="date_required_1"
                        placeholder="Date Required" value="{{ $date->date_required ?? 0 }}" onchange="syncDateRequired(this)">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="ca_decla">Declaration Estimate</label>
                    <input type="date" name="ca_decla" id="ca_decla_3" class="form-control form-control-sm bg-light"
                        placeholder="mm/dd/yyyy" readonly>
                </div>
            </div>
            <div class="alert alert-info col-md-12" role="alert">
                *In accordance with the company's policy on <strong>Long-Distance Business Travel</strong>, any unused cash advance must be returned to the company's cash/account no later than <strong>2 (two) days</strong> after the <strong>Business Travel Accountability Declaration</strong> is verified by the <strong>GA department</strong>. If the employee fails to return the unused cash advance within the specified time, the cash advance will be <strong>deducted from the employee's salary</strong>.
            </div>
            <div class="d-flex flex-column p-2 rounded-3" style="background-color: #f8f8f8">
                <ul class="nav mb-2" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-detail-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-detail" type="button" role="tab" aria-controls="pills-detail"
                            aria-selected="true">Detail Entertainment</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-relation-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-relation" type="button" role="tab"
                            aria-controls="pills-relation" aria-selected="false">Detail Receiver</button>
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
        </div>
    </div>

    <div class="col-md-12 mb-2" id="total_bt_ent_2" style="display:none;">
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
