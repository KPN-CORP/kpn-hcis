@extends('layouts_.vertical', ['page_title' => 'Reimbursements'])

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
        font-size: 14px;
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

        @if($access_ca=='Y')
        <div class="col">
            <a href="{{ route('cashadvanced') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/cashadv.png') }}" alt="Cash Advanced" class="menu-icon mb-3">
                    <span class="menu-text">Cash Advanced</span>
                </div>
            </a>
        </div>
        @endif

        <div class="col">
            <a href="{{ route('medical') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/md.png') }}" alt="Medical" class="menu-icon mb-3">
                    <span class="menu-text">Medical</span>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('parking') }}" class="text-decoration-none">
                <div class="menu-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                    <img src="{{ asset('images/menu/logo-parking.png') }}" alt="Parking" class="menu-icon mb-3">
                    <span class="menu-text">Parking</span>
                </div>
            </a>
        </div>

    </div>

    @if (auth()->check() && (auth()->user()->can('reportca_hcis') || auth()->user()->can('report_hcis_md')))
    <div class="admin-panel w-100">

        <div class="admin-panel-title">
            <i class="ri-shield-user-line fs-4"></i>
            Menu Admin
        </div>

        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3">

            @if (auth()->check() && $access_ca=='Y')
                @can('reportca_hcis')
                <div class="col">
                    <a href="{{ route('cashadvanced.admin') }}" class="text-decoration-none">
                        <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                            <img src="{{ asset('images/menu/report.png') }}" alt="Report" class="menu-icon mb-3">
                            <span class="menu-text">Cash Advanced<br>(Admin)</span>
                        </div>
                    </a>
                </div>
                @endcan
            @endif

            @if (auth()->check())
                @can('report_hcis_md')
                <div class="col">
                    <a href="{{ route('medical.admin') }}" class="text-decoration-none">
                        <div class="admin-inner-card d-flex flex-column align-items-center justify-content-center p-3 text-center">
                            <img src="{{ asset('images/menu/report.png') }}" alt="Report" class="menu-icon mb-3">
                            <span class="menu-text">Medical<br>(Admin)</span>
                        </div>
                    </a>
                </div>
                @endcan
            @endif

        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
@endpush
