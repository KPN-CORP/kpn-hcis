<div class="row g-2 mb-2 justify-content-start">
    <ul class="nav nav-pills mb-0" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="active btn btn-outline-primary mb-2 rounded-pill shadow w-100 position-relative" id="pills-perdiem-tab"
                data-bs-toggle="pill" data-bs-target="#pills-perdiem" type="button"
                role="tab" aria-controls="pills-perdiem"
                aria-selected="true">Cash Advanced
                @if ( $pendingCACount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingCACount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="btn btn-outline-primary mb-2 rounded-pill shadow w-100 position-relative mx-3" id="pills-transport-tab" data-bs-toggle="pill"
                data-bs-target="#pills-transport" type="button" role="tab"
                aria-controls="pills-transport" aria-selected="false">Deklarasi Cash Advanced
                @if ( $pendingDECCount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingDECCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="btn btn-outline-primary mb-2 rounded-pill shadow w-100 position-relative mx-4" id="pills-accomodation-tab"
                data-bs-toggle="pill" data-bs-target="#pills-accomodation"
                type="button" role="tab" aria-controls="pills-accomodation"
                aria-selected="false">Extend Cash Advanced
                @if ( $pendingEXCount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingEXCount }}</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-perdiem" role="tabpanel"
            aria-labelledby="pills-perdiem-tab">
            @include('hcis.reimbursements.approval.navigation.table.requestTabel')
        </div>
        <div class="tab-pane fade" id="pills-transport" role="tabpanel"
            aria-labelledby="pills-transport-tab">
            @include('hcis.reimbursements.approval.navigation.table.deklarasiTabel')
        </div>
        <div class="tab-pane fade" id="pills-accomodation" role="tabpanel"
            aria-labelledby="pills-accomodation-tab">
            @include('hcis.reimbursements.approval.navigation.table.extendTabel')
        </div>
    </div>
</div>

<div class="modal fade" id="modalExtend" tabindex="-1" aria-labelledby="modalExtendLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-center fs-5" id="modalExtendLabel">Extending End Date - <label id="ext_no_ca">3123333123</label></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('approval.cashadvancedExtended') }}">@csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="start">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="end">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="total">Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light" id="totaldays" name="totaldays" type="text" min="0" value="0" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <p class="text-center mt-2">--<b>Changing too</b>--</p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_start">Start Date</label>
                            <input type="date" name="ext_start_date" id="ext_start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_end">New End Date</label>
                            <input type="date" name="ext_end_date" id="ext_end_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="new_total">New Total Days</label>
                            <div class="input-group">
                                <input class="form-control bg-light" id="ext_totaldays" name="ext_totaldays" type="text" min="0" value="0" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="reason">Reason</label>
                            <textarea name="ext_reason" id="ext_reason" class="form-control bg-light" readonly></textarea>
                        </div>
                        <input type="hidden" name="no_id" id="no_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="action_ca_reject" value="Reject" class="btn btn-primary" id="extendButton">Reject</button>
                    <button type="submit" name="action_ca_approve" value="Pending" class="btn btn-primary" id="extendButton">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
