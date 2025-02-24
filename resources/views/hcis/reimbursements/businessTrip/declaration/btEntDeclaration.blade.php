<div class="tab-pane fade <?php echo (!$dnsTab && $entrTab) ? 'show active' : ''; ?>" id="pills-caEntertain" role="tabpanel"
    aria-labelledby="pills-caEntertain-tab">
    <label for="additional-fields-title" class="mb-2">  
        <span class="text-info fst-italic">  
            * In accordance with the company's policy on <strong>Long-Distance Business Travel</strong>, any unused cash advance must be returned to the company's cash/account no later than <strong>2 (two) days</strong> after the <strong>Business Trip Accountability Declaration</strong> is verified by the <strong>GA department</strong>. If the employee fails to return the unused cash advance within the specified time, the cash advance will be <strong>deducted from the employee's salary</strong>.  
        </span>  
    </label>  
    <!-- Duplicate content for now -->
    <ul class="nav mb-3" id="pills-tab-inner" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-detail-tab" data-bs-toggle="pill"
                data-bs-target="#pills-detail" type="button" role="tab" aria-controls="pills-detail"
                aria-selected="true">Detail Entertain</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-relation-tab" data-bs-toggle="pill"
                data-bs-target="#pills-relation" type="button" role="tab"
                aria-controls="pills-relation" aria-selected="false">Detail Receiver</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pills-detail" role="tabpanel"
            aria-labelledby="pills-detail-tab">
            {{-- ca detail content --}}
            @include('hcis.reimbursements.businessTrip.declaration.entDetailDeclare')
        </div>
        <div class="tab-pane fade" id="pills-relation" role="tabpanel"
            aria-labelledby="pills-relation-tab">
            {{-- ca relation content --}}
            @include('hcis.reimbursements.businessTrip.declaration.entRelationDeclare')
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-6 mb-2">
            <label class="form-label">Total Entertain</label>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">Rp</span>
                </div>
                <input class="form-control bg-light" name="totalca_ent"
                    id="totalca_ent" type="text" min="0"
                    value="{{ number_format($entrData->total_ca ?? '0', 0, ',', '.') }}"
                    readonly>
            </div>
        </div>

        <div class="col-md-6 mb-2">
            <label class="form-label">Total Cash Advanced Deklarasi</label>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">Rp</span>
                </div>
                <input class="form-control bg-light" name="totalca"
                    id="totalca" type="text" min="0"
                    value="{{ number_format($entrData->total_cost ?? '0', 0, ',', '.') }}"
                    readonly>
            </div>
        </div>
    </div>
</div>
