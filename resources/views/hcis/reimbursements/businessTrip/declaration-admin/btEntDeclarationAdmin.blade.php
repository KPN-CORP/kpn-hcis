<div class="tab-pane fade <?php echo (!$dnsTab && $entrTab) ? 'show active' : ''; ?>" id="pills-caEntertain" role="tabpanel"
    aria-labelledby="pills-caEntertain-tab">
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
                aria-controls="pills-relation" aria-selected="false">Relation Entertain</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pills-detail" role="tabpanel"
            aria-labelledby="pills-detail-tab">
            {{-- ca detail content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin/entDeclarationFormAdmin/entDetailDeclareAdmin')
        </div>
        <div class="tab-pane fade" id="pills-relation" role="tabpanel"
            aria-labelledby="pills-relation-tab">
            {{-- ca relation content --}}
            @include('hcis.reimbursements.businessTrip.declaration-admin/entDeclarationFormAdmin/entRelationDeclareAdmin')
    </div>
</div>