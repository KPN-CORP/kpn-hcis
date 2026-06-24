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

    </div>

</div>
@endsection

@push('scripts')
@endpush
