<div style="background-color:#f8f8f8;" class="p-2 rounded-3 tab-pane fade <?php echo !$entrTab && $dnsTab ? 'show active' : ($dnsTab && $entrTab ? 'show active' : ''); ?>" id="pills-cashAdvanced" role="tabpanel"
    aria-labelledby="pills-cashAdvanced-tab">
    <ul class="nav mb-2" id="pills-tab-inner" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-perdiem-tab" data-bs-toggle="pill" data-bs-target="#pills-perdiem"
                type="button" role="tab" aria-controls="pills-perdiem"
                aria-selected="true">{{ $allowance }}</button>
        </li>
        @if ($group_company !== 'KPN Plantations' && $group_company !== 'Plantations' || $job_level_number >= 8)
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
    {{-- <div class="card"> --}}
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel" aria-labelledby="pills-perdiem-tab">
            {{-- ca perdiem content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin.caDeclarationFormAdmin.caPerdiemDeclare')
        </div>
        <div class="tab-pane fade show" id="pills-meals" role="tabpanel" aria-labelledby="pills-meals-tab">
            {{-- ca meals content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin.caDeclarationFormAdmin.caMealsDeclareAdmin')
        </div>
        <div class="tab-pane fade" id="pills-transport" role="tabpanel" aria-labelledby="pills-transport-tab">
            {{-- ca transport content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin.caDeclarationFormAdmin.caTransportDeclareAdmin')
        </div>
        <div class="tab-pane fade" id="pills-accomodation" role="tabpanel" aria-labelledby="pills-accomodation-tab">
            {{-- ca accommodatioon content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin.caDeclarationFormAdmin.caAccommodationDeclare')</div>
        <div class="tab-pane fade" id="pills-other" role="tabpanel" aria-labelledby="pills-other-tab">
            {{-- ca others content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin.caDeclarationFormAdmin.caOtherDeclareAdmin')
        </div>
    </div>
</div>
