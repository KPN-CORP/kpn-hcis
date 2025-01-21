<div class="row g-2 mb-3 justify-content-start">
    <div class=" col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('approval.cashadvanced') }}"
                class="btn {{ request()->routeIs('approval.cashadvanced') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Cash Advanced
                @if ( $pendingCACount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingCACount }}</span>
                @endif
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('approval.cashadvancedDeklarasi') }}"
                class="btn {{ request()->routeIs('approval.cashadvancedDeklarasi') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Deklarasi Cash Advanced
                @if ( $pendingDECCount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingDECCount }}</span>
                @endif
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('approval.cashadvancedExtend') }}"
                class="btn {{ request()->routeIs('approval.cashadvancedExtend') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Extend Cash Advanced
                @if ( $pendingEXCount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingEXCount }}</span>
                @endif
            </a>
        </div>
    </div>
    {{-- <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvanced.form') }}" class="btn btn-outline-primary rounded-pill shadow w-100 position-relative">
                Medical
                @if ( $pendingHTLCount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingHTLCount }}</span>
                @else

                @endif
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto" style="display:none">
        <div class="mb-2">
            <a href="{{ route('cashadvanced.form') }}" class="btn btn-outline-primary rounded-pill shadow w-100 position-relative">
                Hometrip
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">99</span>
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto" style="display:none">
        <div class="mb-2">
            <a href="{{ route('cashadvanced.form') }}" class="btn btn-outline-primary rounded-pill shadow w-100 position-relative">
                Assessment
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"></span>
            </a>
        </div>
    </div> --}}
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
