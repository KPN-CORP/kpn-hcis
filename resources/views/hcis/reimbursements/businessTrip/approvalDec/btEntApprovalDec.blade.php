<div class="tab-pane fade <?php echo (!$dnsTab && $entrTab) ? 'show active' : ''; ?>" id="pills-caEntertain"
    role="tabpanel" aria-labelledby="pills-caEntertain-tab">
    @if ($entr == true)
        @php
            $formattedTotalEntertain = number_format($entr->total_ca ?? 0, 0, ',', '.');
            $formattedTotalRealEntertain = number_format($entr->total_real ?? 0, 0, ',', '.');
            $formattedTotalCostEntertain = number_format($entr->total_cost ?? 0, 0, ',', '.');
        @endphp
    @else
        @php
            $formattedTotalEntertain = 0;
            $formattedTotalRealEntertain = 0;
            $formattedTotalCostEntertain = 0;
        @endphp
    @endif
    {{-- PERDIEM TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.entertainTable.entDetailApprovalDec')

    {{-- TRANSPORT TABLE --}}
    @include('hcis.reimbursements.businessTrip.approvalDec.entertainTable.entRelationApprovalDec')

    <div class="row mb-2">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Total Entertain</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input class="form-control bg-light" name="totalca_deklarasi"
                        id="totalca_deklarasi" type="text" min="0"
                        value="{{ $formattedTotalEntertain }}" readonly>
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
                        value="{{ $formattedTotalRealEntertain }}" readonly>
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
                        value="{{ $formattedTotalCostEntertain }}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>