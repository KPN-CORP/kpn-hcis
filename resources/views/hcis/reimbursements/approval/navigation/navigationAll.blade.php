<div class="col-md-12">
    <ul class="nav nav-pills nav-tabs" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation" id="nav-perdiem">
            <a href="{{ route('approval.cashadvanced') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('approval.cashadvanced') ? 'active' : '' }}" id="pills-perdiem-tab"
                role="tab" aria-controls="pills-perdiem" aria-selected="false">Cash Advanced
                @if ($totalPendingCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-10 start-10 mx-2 translate-middle rounded-circle">{{ $totalPendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item" role="presentation" id="nav-cashAdvanced">
            <a href="{{ route('businessTrip.approval') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('businessTrip.approval') ? 'active' : '' }}"
                id="pills-cashAdvanced-tab" role="tab" aria-controls="pills-cashAdvanced" aria-selected="false">
                Bussiness Trip
                @if ($totalBTCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-10 start-10 mx-2 translate-middle rounded-circle">{{ $totalBTCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item" role="presentation" id="nav-ticket">
            <a href="{{ route('ticket.approval') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('ticket.approval') ? 'active' : '' }}" id="pills-ticket-tab"
                role="tab" aria-controls="pills-ticket" aria-selected="false">Ticket
                @if ($totalTKTCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-10 start-10 mx-2 translate-middle rounded-circle">{{ $totalTKTCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item" role="presentation" id="nav-hotel">
            <a href="{{ route('hotel.approval') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('hotel.approval') ? 'active' : '' }}" id="pills-hotel-tab"
                role="tab" aria-controls="pills-hotel" aria-selected="false">Hotel
                @if ($totalHTLCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-10 start-10 mx-2 translate-middle rounded-circle">{{ $totalHTLCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item" role="presentation" id="nav-medical">
            <a href="{{ route('medical.approval') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('medical.approval') ? 'active' : '' }}" id="pills-medical-tab"
                role="tab" aria-controls="pills-medical" aria-selected="false">Medical
                @if ($totalMDCCount >= 1)
                    <span
                        class="badge bg-danger position-absolute top-10 start-10 mx-2 translate-middle rounded-circle">{{ $totalMDCCount }}</span>
                @endif
            </a>
        </li>
        {{-- <li class="nav-item" role="presentation" id="nav-homeTrip">
            <a href="{{ route('home-trip.approval') }}" style="border-radius: 10px 10px 0px 0px"
                class="nav-link {{ request()->routeIs('home-trip.approval') ? 'active' : '' }}" id="pills-homeTrip-tab" role="tab"
                aria-controls="pills-homeTrip" aria-selected="false">Home Trip </a>
        </li> --}}
    </ul>
</div>
