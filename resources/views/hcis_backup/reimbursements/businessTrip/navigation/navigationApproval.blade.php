<div class="row g-2 justify-content-start">
    {{-- <ul class="nav nav-pills" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="active btn btn-outline-primary mb-2 rounded-pill shadow w-100 position-relative" id="pills-perdiem-tab"
                data-bs-toggle="pill" data-bs-target="#pills-all" type="button"
                role="tab" aria-controls="pills-all"
                aria-selected="true">All
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="btn btn-outline-primary mb-2 rounded-pill shadow position-relative mx-2" id="pills-transport-tab" data-bs-toggle="pill"
                data-bs-target="#pills-request" type="button" role="tab"
                aria-controls="pills-request" aria-selected="false">Request
                @if ($requestCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-circle">{{ $requestCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="btn btn-outline-primary mb-2 rounded-pill shadow position-relative mx-0" id="pills-accomodation-tab"
                data-bs-toggle="pill" data-bs-target="#pills-declaration"
                type="button" role="tab" aria-controls="pills-declaration"
                aria-selected="false">Declaration
                @if ($declarationCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-circle">{{ $declarationCount }}</span>
                @endif
            </button>
        </li>
    </ul> --}}

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-all" role="tabpanel"
            aria-labelledby="pills-all-tab">
            @include('hcis.reimbursements.businessTrip.navigation.table.allTabel')
        </div>
        <div class="tab-pane fade" id="pills-request" role="tabpanel"
            aria-labelledby="pills-request-tab">
            @include('hcis.reimbursements.businessTrip.navigation.table.requestTabel')
        </div>
        <div class="tab-pane fade" id="pills-declaration" role="tabpanel"
            aria-labelledby="pills-declaration-tab">
            @include('hcis.reimbursements.businessTrip.navigation.table.deklarasiTabel')
        </div>
    </div>
</div>
