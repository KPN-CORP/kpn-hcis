<div class="alert alert-info col-md-12" role="alert">
    *In accordance with the company's policy on <strong>Long-Distance Business Travel</strong>, any unused cash advance must be returned to the company's cash/account no later than <strong>2 (two) days</strong> after the <strong>Business Trip Accountability Declaration</strong> is verified by the <strong>GA department</strong>. If the employee fails to return the unused cash advance within the specified time, the cash advance will be <strong>deducted from the employee's salary</strong>.  
          </div>
<div style="background-color:#f8f8f8;" class="tab-pane fade p-2 rounded-3  <?php echo ($entrTab && $dnsTab) || (!$entrTab && $dnsTab) || (!$entrTab && !$dnsTab) ? 'show active' : ''; ?>" id="pills-cashAdvanced" role="tabpanel"
    aria-labelledby="pills-cashAdvanced-tab">
    <ul class="nav mb-2" id="pills-tab-inner" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-perdiem-tab" data-bs-toggle="pill" data-bs-target="#pills-perdiem"
                type="button" role="tab" aria-controls="pills-perdiem"
                aria-selected="true">{{ $allowance }}</button>
        </li>
        @if (($group_company !== 'KPN Plantations' && $group_company !== 'Plantations') || $job_level_number >= 8)
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
                type="button" role="tab" aria-controls="pills-other" aria-selected="false">Other Expenses</button>
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
</div>
