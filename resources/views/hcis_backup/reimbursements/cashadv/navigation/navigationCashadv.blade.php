<div class="row g-2 mt-1 mb-2 justify-content-start">
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvanced') }}"
            class="btn {{ request()->routeIs('cashadvanced') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Request
            </a>
        </div>
    </div>
    {{-- <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvancedRequest') }}"
            class="btn {{ request()->routeIs('cashadvancedRequest') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Request
            </a>
        </div>
    </div> --}}
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvancedDeklarasi') }}"
            class="btn {{ request()->routeIs('cashadvancedDeklarasi') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Declaration
                @if ( $deklarasiCACount >= 1 )
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $deklarasiCACount }}</span>
                @endif
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvancedDone') }}"
            class="btn {{ request()->routeIs('cashadvancedDone') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Done
            </a>
        </div>
    </div>
    <div class="col-6 col-sm-auto">
        <div class="mb-2">
            <a href="{{ route('cashadvancedReject') }}"
            class="btn {{ request()->routeIs('cashadvancedReject') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill shadow w-100 position-relative">
                Rejected
            </a>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="modalExtend" tabindex="-1" aria-labelledby="modalExtendLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-center fs-5" id="modalExtendLabel">Extending End Date - <label id="ext_no_ca">3123333123</label></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('cashadvanced.extend') }}">@csrf
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
                            <input type="date" name="ext_end_date" id="ext_end_date" class="form-control" placeholder="mm/dd/yyyy" required>
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
                            <textarea name="ext_reason" id="ext_reason" class="form-control" required></textarea>
                        </div>
                        <input type="hidden" name="no_id" id="no_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="extendButton">Extending</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
