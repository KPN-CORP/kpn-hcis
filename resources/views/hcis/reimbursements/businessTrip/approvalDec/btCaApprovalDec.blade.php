<div class="tab-pane fade <?php echo ($entrTab && !$dnsTab) ? 'show active' : (($dnsTab && $entrTab) ? 'show active' : ''); ?>" id="pills-cashAdvanced"
    role="tabpanel" aria-labelledby="pills-cashAdvanced-tab">
    @if ($dns == true)
        @php
            $formattedTotalCashAdvanced = number_format($dns->total_ca ?? 0, 0, ',', '.');
            $formattedTotalRealCashAdvanced = number_format($dns->total_real ?? 0, 0, ',', '.');
            $formattedTotalCostCashAdvanced = number_format($dns->total_cost ?? 0, 0, ',', '.');
        @endphp
    @else
        @php
            $formattedTotalCashAdvanced = 0;
            $formattedTotalRealCashAdvanced = 0;
            $formattedTotalCostCashAdvanced = 0;
        @endphp
    @endif
    {{-- PERDIEM TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.bussinessTripTable.caPerdiemApprovalDec')

    {{-- TRANSPORT TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.bussinessTripTable.caMealsApprovalDec')
    
    {{-- TRANSPORT TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.bussinessTripTable.caTransportApprovalDec')

    {{-- ACCOM TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.bussinessTripTable.caAccommodationApprovalDec')

    {{-- OTHERS TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.bussinessTripTable.caOtherApprovalDec')
    

    <div class="row mb-2">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Cash Advanced</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control bg-light" name="totalca_deklarasi"
                        id="totalca_deklarasi" type="text" min="0"
                        value="{{ $formattedTotalCashAdvanced }}" readonly>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Declaration</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control bg-light" name="totalca_deklarasi"
                        id="totalca_deklarasi" type="text" min="0"
                        value="{{ $formattedTotalRealCashAdvanced }}" readonly>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Cost</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control bg-light" name="totalca_deklarasi"
                        id="totalca_deklarasi" type="text" min="0"
                        value="{{ $formattedTotalCostCashAdvanced }}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>