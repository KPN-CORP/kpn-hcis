<div class="tab-pane fade <?php echo ($entrTab && $dnsTab) || (!$entrTab && $dnsTab) || (!$entrTab && !$dnsTab) ? 'show active' : ''; ?>" id="pills-cashAdvanced" role="tabpanel" aria-labelledby="pills-cashAdvanced-tab">
    <ul class="nav mb-3" id="pills-tab-inner" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-perdiem-tab" data-bs-toggle="pill" data-bs-target="#pills-perdiem"
                type="button" role="tab" aria-controls="pills-perdiem"
                aria-selected="true">{{ $allowance }}</button>
        </li>
        @if ($group_company !== 'KPN Plantations' && $group_company !== 'Plantations')
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-meals-tab" data-bs-toggle="pill" data-bs-target="#pills-meals"
                    type="button" role="tab" aria-controls="pills-meals" aria-selected="false">Meals</button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-transport-tab" data-bs-toggle="pill" data-bs-target="#pills-transport"
                type="button" role="tab" aria-controls="pills-transport" aria-selected="false">Transport</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-accomodation-tab" data-bs-toggle="pill"
                data-bs-target="#pills-accomodation" type="button" role="tab" aria-controls="pills-accomodation"
                aria-selected="false">Accommodation</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-other-tab" data-bs-toggle="pill" data-bs-target="#pills-other"
                type="button" role="tab" aria-controls="pills-other" aria-selected="false">Other</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel" aria-labelledby="pills-perdiem-tab">
            @include('hcis.reimbursements.businessTrip.declaration.caPerdiemDeclare')
        </div>
        <div class="tab-pane fade" id="pills-meals" role="tabpanel" aria-labelledby="pills-meals-tab">
            @include('hcis.reimbursements.businessTrip.declaration.caMealsDeclare')
        </div>
        <div class="tab-pane fade" id="pills-transport" role="tabpanel" aria-labelledby="pills-transport-tab">
            @include('hcis.reimbursements.businessTrip.declaration.caTransportDeclare')
        </div>
        <div class="tab-pane fade" id="pills-accomodation" role="tabpanel" aria-labelledby="pills-accomodation-tab">
            @include('hcis.reimbursements.businessTrip.declaration.caAccommodationDeclare')
        </div>
        <div class="tab-pane fade" id="pills-other" role="tabpanel" aria-labelledby="pills-other-tab">
            @include('hcis.reimbursements.businessTrip.declaration.caOtherDeclare')
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-6 mb-2">
            <label class="form-label">Total Cash Advanced</label>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">Rp</span>
                </div>
                <input class="form-control bg-light" name="totalca_ca" id="totalca_declarasi" type="text"
                    min="0" value="{{ number_format($dnsData->total_ca ?? '0', 0, ',', '.') }}" readonly>
            </div>
        </div>

        <div class="col-md-6 mb-2">
            <label class="form-label">Total Cash Advanced Deklarasi</label>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">Rp</span>
                </div>
                <input class="form-control bg-light" name="totalca_ca_deklarasi" id="totalca_ca_deklarasi"
                    type="text" min="0" value="{{ number_format($dnsData->total_cost ?? '0', 0, ',', '.') }}"
                    readonly>
            </div>
        </div>
    </div>
</div>
