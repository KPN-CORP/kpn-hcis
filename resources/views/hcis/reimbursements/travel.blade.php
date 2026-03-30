@extends('layouts_.vertical', ['page_title' => 'Travel'])

@section('css')
<style>
    .menu-card, .admin-inner-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
        height: 100%;
        min-height: 190px;
    }
    .menu-card:hover, .admin-inner-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }
    .admin-inner-card:hover {
        border-color: #AB2F2B;
    }
    .menu-icon {
        width: 80px;
        height: 80px;
        object-fit: contain;
    }
    .menu-text {
        font-size: 16px;
        font-weight: 600;
        color: #313a46;
        line-height: 1.3;
    }
    .admin-panel {
        background: #f8f9fa;
        border-radius: 16px;
        border: 1px solid #eef2f7;
        padding: 24px;
        margin-top: 30px;
    }
    .admin-panel-title {
        font-size: 16px;
        font-weight: 700;
        color: #AB2F2B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3">

        <div class="col">
            <a href="{{ route('businessTrip.approval') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/approval.png') }}" alt="Approval" class="menu-icon mb-3">
                    <span class="menu-text">Approval</span>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('businessTrip') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/bt.png') }}" alt="Business Travel" class="menu-icon mb-3">
                    <span class="menu-text">Business Travel</span>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('ticket') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/tkt.png') }}" alt="Ticket" class="menu-icon mb-3">
                    <span class="menu-text">Ticket</span>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('hotel') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/ht.png') }}" alt="Hotel" class="menu-icon mb-3">
                    <span class="menu-text">Hotel</span>
                </div>
            </a>
        </div>

        @if(!empty(trim(Auth::user()->employee->homebase ?? '')) && (preg_match('/^[4-9]/', $jobLevel)))
        <div class="col">
            <a href="{{ route('home-trip') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/home-trip.png') }}" alt="Home Trip" class="menu-icon mb-3 rounded-circle">
                    <span class="menu-text">Home Trip</span>
                </div>
            </a>
        </div>
        @endif

    </div>

    @if (auth()->check() && (auth()->user()->can('report_hcis_bt') || auth()->user()->can('report_hcis_ht') || auth()->user()->can('report_hcis_tkt') || auth()->user()->can('report_hcis_htl')))
    <div class="admin-panel w-100">

        <div class="admin-panel-title">
            <i class="ri-shield-user-line fs-4"></i>
            Menu Admin
        </div>

        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3">

            @can('report_hcis_bt')
            <div class="col">
                <a href="{{ route('businessTrip.admin') }}" class="text-decoration-none">
                    <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                        <img src="/images/menu/report.png" alt="Report" class="menu-icon mb-3">
                        <span class="menu-text">Business Travel<br>(Admin)</span>
                    </div>
                </a>
            </div>
            @endcan

            @can('report_hcis_ht')
            <div class="col">
                <a href="{{ route('home-trip.admin') }}" class="text-decoration-none">
                    <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                        <img src="{{ asset('images/menu/report.png') }}" alt="Report" class="menu-icon mb-3 rounded-circle">
                        <span class="menu-text">Home Trip<br>(Admin)</span>
                    </div>
                </a>
            </div>
            @endcan

            @can('report_hcis_tkt')
            <div class="col">
                <a href="{{ route('ticket.admin') }}" class="text-decoration-none">
                    <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                        <img src="{{ asset('images/menu/report.png') }}" alt="Report" class="menu-icon mb-3">
                        <span class="menu-text">Ticket<br>(Admin)</span>
                    </div>
                </a>
            </div>
            @endcan

            @can('report_hcis_htl')
            <div class="col">
                <a href="{{ route('hotel.admin') }}" class="text-decoration-none">
                    <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                        <img src="{{ asset('images/menu/report.png') }}" alt="Report" class="menu-icon mb-3">
                        <span class="menu-text">Hotel<br>(Admin)</span>
                    </div>
                </a>
            </div>
            @endcan

        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
@endpush
