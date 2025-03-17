<div class="row" id="ca_bt" style="">
    <div class="col-md-12">
        <div class="table-responsive-sm">
            <div class="row mb-2">
                <div class="col-md-6 mb-2">
                    <label for="date_required" class="form-label">CA Withdrawal Date</label>
                    <input type="date" class="form-control form-control-sm" id="date_required_2"
                        name="date_required_2" placeholder="Date Required" onchange="syncDateRequired(this)"
                        value="{{ $date->date_required ?? 0 }}">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="ca_decla">Declaration Estimate</label>
                    <input type="date" name="ca_decla" id="ca_decla_2" class="form-control form-control-sm bg-light"
                        placeholder="mm/dd/yyyy" value="{{ $date->declare_estimate ?? 0 }}" readonly>
                </div>
            </div>
           <div class="alert alert-info" role="alert">
                    *In accordance with the company's policy on <strong>Long-Distance Business Travel</strong>, any unused cash advance must be returned to the company's cash/account no later than <strong>2 (two) days</strong> after the <strong>Business Travel Accountability Declaration</strong> is verified by the <strong>GA department</strong>. If the employee fails to return the unused cash advance within the specified time, the cash advance will be <strong>deducted from the employee's salary</strong>.  </div>
            <div class="d-flex flex-column rounded-3 p-2" style="background-color:#f8f8f8;">
                <ul class="nav mb-2" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation" id="perdiem-tab-li">
                        <button class="nav-link active" id="pills-perdiem-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-perdiem" type="button" role="tab" aria-controls="pills-perdiem"
                            aria-selected="true">{{ $allowance }}</button>
                    </li>
                    @if (($group_company !== 'KPN Plantations' && $group_company !== 'Plantations') || $job_level_number >= 8)
                        <li class="nav-item" role="presentation" id="meals-tab-li">
                            <button class="nav-link" id="pills-meals-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-meals" type="button" role="tab" aria-controls="pills-meals"
                                aria-selected="false">Meals</button>
                        </li>
                    @endif
                    <li class="nav-item" role="presentation" id="transport-tab-li">
                        <button class="nav-link" id="pills-transport-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-transport" type="button" role="tab"
                            aria-controls="pills-transport" aria-selected="false">Transport</button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-accomodation-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-accomodation" type="button" role="tab"
                            aria-controls="pills-accomodation" aria-selected="false">Accomodation</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-other-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-other" type="button" role="tab" aria-controls="pills-other"
                            aria-selected="false">Other Expenses</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel"
                        aria-labelledby="pills-perdiem-tab">
                        {{-- ca perdiem content --}}
                        @include('hcis.reimbursements.businessTrip.form.cashadvancedForm.caPerdiem')
                    </div>
                    @if (($group_company !== 'KPN Plantations' && $group_company !== 'Plantations') || $job_level_number >= 8)
                        <div class="tab-pane fade" id="pills-meals" role="tabpanel" aria-labelledby="pills-meals-tab">
                            {{-- ca transport content --}}
                            @include('hcis.reimbursements.businessTrip.form.cashadvancedForm.caMeals')
                        </div>
                    @endif
                    <div class="tab-pane fade" id="pills-transport" role="tabpanel"
                        aria-labelledby="pills-transport-tab">
                        {{-- ca transport content --}}
                        @include('hcis.reimbursements.businessTrip.form.cashadvancedForm.caTransport')
                    </div>
                    <div class="tab-pane fade" id="pills-accomodation" role="tabpanel"
                        aria-labelledby="pills-accomodation-tab">
                        {{-- ca accommodatioon content --}}
                        @include('hcis.reimbursements.businessTrip.form.cashadvancedForm.caAccommodation')</div>
                    <div class="tab-pane fade" id="pills-other" role="tabpanel" aria-labelledby="pills-other-tab">
                        {{-- ca others content --}}
                        @include('hcis.reimbursements.businessTrip.form.cashadvancedForm.caOther')
                    </div>
                </div>

            </div>
             <div class="col-md-12 mt-3">
                    <label class="form-label">Total Cash Advanced</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control bg-light" name="totalca" id="totalca" type="text"
                            min="0" value="0" readonly>
                    </div>
                </div>
                <div class="col-md-12 mb-2" id="total_bt_ent" style="display:none">
                    <label class="form-label">Total Request</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control bg-light" name="totalreq" id="totalreq" type="text"
                            min="0" value="0" readonly>
                    </div>
                </div>
        </div>
    </div>
</div>
