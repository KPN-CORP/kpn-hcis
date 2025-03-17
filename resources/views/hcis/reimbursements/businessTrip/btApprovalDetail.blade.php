@extends('layouts_.vertical', ['page_title' => 'Business Travel'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">

    <style>
        .nav-link {
            color: black;
            border-bottom: 2px solid transparent;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-link.active {
            color: #AB2F2B;
            /* Primary color */
            border-bottom: 2px solid #AB2F2B;
            font-weight: bold;
            /* Underline with primary color */
        }

        .nav-link:hover {
            color: #AB2F2B;
            /* Change color on hover */
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('businessTrip.approval') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex bg-primary text-white justify-content-between">
                        <h4 class="mb-0">Detail Data - {{ $n->no_sppd }}</h4>
                        <a href="/businessTrip/approval" type="button" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="card-body">
                        @include('hcis.reimbursements.businessTrip.modal')

                        <form action="{{ route('confirm.status', ['id' => $n->id]) }}" method="POST" id="btEditForm">
                            @csrf
                            @method('PUT')
                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="nama" class="form-label">Name</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="nama"
                                        name="nama" style="cursor:not-allowed;" value="{{ $employee_data->fullname }}"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="divisi" class="form-label">Unit</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="divisi"
                                        name="divisi" style="cursor:not-allowed;" value="{{ $employee_data->unit }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 mb-2">
                                    <label for="norek_krywn" class="form-label">Employee Account Number</label>
                                    <input type="number" class="form-control form-control-sm bg-light" id="norek_krywn"
                                        name="norek_krywn" value="{{ $employee_data->bank_account_number }}" readonly>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="nama_pemilik_rek" class="form-label">Name of Account Owner</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="nama_pemilik_rek" name="nama_pemilik_rek"
                                        value="{{ $employee_data->bank_account_name }}" readonly>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="nama_bank" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="nama_bank"
                                        name="nama_bank" value="{{ $employee_data->bank_name }}" placeholder="ex. BCA"
                                        readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 mb-2">
                                    <label for="mulai" class="form-label">Start Date Business Travel</label>
                                    <input type="date" class="form-control form-control-sm bg-light" id="mulai"
                                        name="mulai" placeholder="Tanggal Mulai" value="{{ $n->mulai }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label for="kembali" class="form-label">End Date Business Travel</label>
                                    <input type="date" class="form-control form-control-sm bg-light" id="kembali"
                                        name="kembali" placeholder="Tanggal Kembali" value="{{ $n->kembali }}" readonly>
                                </div>

                                <input class="form-control" id="perdiem" name="perdiem" type="hidden"
                                    value="{{ $perdiem->amount ?? 0 }}" readonly>

                                <div class="col-md-4 mb-2">
                                    <label for="tujuan" class="form-label">Destination</label>
                                    <select class="form-select form-select-sm select2 bg-light" name="tujuan"
                                        id="tujuan" onchange="BTtoggleOthers()" disabled>
                                        <option value="">--- Choose Destination ---</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->area }}"
                                                {{ $n->tujuan === $location->area ? 'selected' : '' }}>
                                                {{ $location->area . ' (' . $location->city . ')' }}
                                            </option>
                                        @endforeach
                                        <option value="Others"
                                            {{ !in_array($n->tujuan, $locations->pluck('area')->toArray()) ? 'selected' : '' }}>
                                            Others</option>
                                    </select>

                                    <br>
                                    <input type="text" name="others_location" id="others_location"
                                        class="form-control form-control-sm bg-light" placeholder="Other Location"
                                        value="{{ !in_array($n->tujuan, $locations->pluck('area')->toArray()) ? $n->tujuan : '' }}"
                                        style="{{ !in_array($n->tujuan, $locations->pluck('area')->toArray()) ? '' : 'display: none;' }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="keperluan" class="form-label">Need (To be filled in according to visit
                                    service)</label>
                                <textarea class="form-control form-control-sm" id="keperluan" name="keperluan" rows="3"
                                    placeholder="Fill your need" disabled>{{ $n->keperluan }}</textarea>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label for="bb_perusahaan" class="form-label">
                                        Company Cost Expenses (PT Service Needs / Not PT Payroll)
                                    </label>
                                    <select class="form-select form-select-sm bg-light" id="bb_perusahaan"
                                        name="bb_perusahaan" disabled>
                                        <option value="">--- Choose PT ---</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->contribution_level_code }}"
                                                {{ $company->contribution_level_code == $n->bb_perusahaan ? 'selected' : '' }}>
                                                {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="jns_dinas" class="form-label">Type of Service</label>
                                    <select class="form-select form-select-sm" id="jns_dinas" name="jns_dinas" disabled
                                        onchange="toggleAdditionalFields()">
                                        <option value="" selected disabled>-- Choose Type of Service --</option>
                                        <option value="dalam kota" {{ $n->jns_dinas == 'dalam kota' ? 'selected' : '' }}>
                                            Dinas
                                            Dalam Kota</option>
                                        <option value="luar kota" {{ $n->jns_dinas == 'luar kota' ? 'selected' : '' }}>
                                            Dinas
                                            Luar Kota</option>
                                    </select>
                                </div>
                            </div>
                            @php
                                // Provide default empty arrays if caDetail or sections are not set
                                $detailPerdiem = $caDetail['detail_perdiem'] ?? [];
                                $detailTransport = $caDetail['detail_transport'] ?? [];
                                $detailPenginapan = $caDetail['detail_penginapan'] ?? [];
                                $detailLainnya = $caDetail['detail_lainnya'] ?? [];

                                // Calculate totals with default values
                                $totalPerdiem = array_reduce(
                                    $detailPerdiem,
                                    function ($carry, $item) {
                                        return $carry + (int) ($item['nominal'] ?? 0);
                                    },
                                    0,
                                );

                                $totalTransport = array_reduce(
                                    $detailTransport,
                                    function ($carry, $item) {
                                        return $carry + (int) ($item['nominal'] ?? 0);
                                    },
                                    0,
                                );

                                $totalPenginapan = array_reduce(
                                    $detailPenginapan,
                                    function ($carry, $item) {
                                        return $carry + (int) ($item['nominal'] ?? 0);
                                    },
                                    0,
                                );

                                $totalLainnya = array_reduce(
                                    $detailLainnya,
                                    function ($carry, $item) {
                                        return $carry + (int) ($item['nominal'] ?? 0);
                                    },
                                    0,
                                );

                                // Total Cash Advanced
                                $totalCashAdvanced = $totalPerdiem + $totalTransport + $totalPenginapan + $totalLainnya;
                            @endphp

                            @php
                                // $detailCA = isset($ca) && $ca->detail_ca ? json_decode($ca->detail_ca, true) : [];

                                // $showPerdiem = !empty($detailCA['detail_perdiem']);

                                // Check if any of Transport, Penginapan, or Lainnya has data
                                $showCashAdvanced =
                                    !empty($caDetail['detail_perdiem']) ||
                                    !empty($caDetail['detail_transport']) ||
                                    !empty($caDetail['detail_meals']) ||
                                    !empty($caDetail['detail_penginapan']) ||
                                    !empty($caDetail['detail_lainnya']);

                                $showEntertain = !empty($caDetail['detail_e']) || !empty($caDetail['relation_e']);

                            @endphp
                            <div id="additional-fields-dalam" class="row mb-3" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="hidden" name="hotel_dalam_kota" value="Tidak">
                                            <input class="form-check-input" type="checkbox" id="hotelCheckboxDalamKota"
                                                name="hotel_dalam_kota" value="Ya"
                                                <?= $n->jns_dinas === 'dalam kota' && $n->hotel === 'Ya' ? 'checked' : '' ?>
                                                disabled>
                                            <label class="form-check-label" for="hotelCheckboxDalamKota">
                                                Hotel
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="hidden" name="mess_dalam_kota" value="Tidak">
                                            <input class="form-check-input" type="checkbox" id="messCheckboxDalamKota"
                                                name="mess_dalam_kota" value="Ya"
                                                <?= $n->jns_dinas === 'dalam kota' && $n->mess === 'Ya' ? 'checked' : '' ?>
                                                disabled>
                                            <label class="form-check-label" for="messCheckboxDalamKota">
                                                Mess
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="hidden" name="tiket_dalam_kota" value="Tidak">
                                            <input class="form-check-input" type="checkbox" id="ticketCheckboxDalamKota"
                                                name="tiket_dalam_kota" value="Ya"
                                                <?= $n->jns_dinas === 'dalam kota' && $n->tiket === 'Ya' ? 'checked' : '' ?>
                                                disabled>
                                            <label class="form-check-label" for="ticketCheckboxDalamKota">
                                                Ticket
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="hidden" name="taksi_dalam_kota" value="Tidak">
                                            <input class="form-check-input" type="checkbox" id="taksiCheckboxDalamKota"
                                                name="taksi_dalam_kota" value="Ya"
                                                <?= $n->jns_dinas === 'dalam kota' && $n->taksi === 'Ya' ? 'checked' : '' ?>
                                                disabled>
                                            <label class="form-check-label" for="taksiCheckboxDalamKota">
                                                Taxi/Grab
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <ul class="nav nav-tabs nav-pills mb-2" id="dalam-kota-pills-tab" role="tablist">
                                            <!-- Hotel Tab -->
                                            <li class="nav-item" role="presentation" id="nav-hotel-dalam-kota"
                                                style="display: <?= $n->jns_dinas === 'dalam kota' && $n->hotel == 'Ya' ? 'block' : 'none' ?>;">
                                                <button class="nav-link" id="pills-hotel-dalam-kota-tab"
                                                    data-bs-toggle="pill" data-bs-target="#pills-hotel-dalam-kota"
                                                    type="button" role="tab" aria-controls="pills-hotel-dalam-kota"
                                                    aria-selected="false">Hotel</button>
                                            </li>
                                            <!-- Mess Tab -->
                                            <li class="nav-item" role="presentation" id="nav-mess-dalam-kota"
                                                style="display: <?= $n->jns_dinas === 'dalam kota' && $n->mess == 'Ya' ? 'block' : 'none' ?>;">
                                                <button class="nav-link" id="pills-mess-dalam-kota-tab"
                                                    data-bs-toggle="pill" data-bs-target="#pills-mess-dalam-kota"
                                                    type="button" role="tab" aria-controls="pills-mess-dalam-kota"
                                                    aria-selected="false">Mess</button>
                                            </li>
                                            <!-- Ticket Tab -->
                                            <li class="nav-item" role="presentation" id="nav-ticket-dalam-kota"
                                                style="display: <?= $n->jns_dinas === 'dalam kota' && $n->tiket == 'Ya' ? 'block' : 'none' ?>;">
                                                <button class="nav-link" id="pills-ticket-dalam-kota-tab"
                                                    data-bs-toggle="pill" data-bs-target="#pills-ticket-dalam-kota"
                                                    type="button" role="tab" aria-controls="pills-ticket-dalam-kota"
                                                    aria-selected="false">Ticket</button>
                                            </li>
                                            <!-- Taxi Tab -->
                                            <li class="nav-item" role="presentation" id="nav-taksi-dalam-kota"
                                                style="display: <?= $n->jns_dinas === 'dalam kota' && $n->taksi == 'Ya' ? 'block' : 'none' ?>;">
                                                <button class="nav-link" id="pills-taksi-dalam-kota-tab"
                                                    data-bs-toggle="pill" data-bs-target="#pills-taksi-dalam-kota"
                                                    type="button" role="tab" aria-controls="pills-taksi-dalam-kota"
                                                    aria-selected="false">Taxi/Grab</button>
                                            </li>
                                        </ul>


                                        <div id="dalam-kota-pills-tabContent" class="tab-content">
                                            <!-- Hotel Content -->
                                            <div class="tab-pane fade" id="pills-hotel-dalam-kota" role="tabpanel"
                                                aria-labelledby="pills-hotel-dalam-kota-tab">
                                                {{-- Hotel content --}}
                                                @include('hcis.reimbursements.businessTrip.approval.dalam-kota.approvalHotelDalamKota')
                                            </div>

                                            <!-- Mess Content -->
                                            <div class="tab-pane fade" id="pills-mess-dalam-kota" role="tabpanel"
                                                aria-labelledby="pills-mess-dalam-kota-tab">
                                                {{-- mess content --}}
                                                @include('hcis.reimbursements.businessTrip.approval.dalam-kota.approvalMessDalamKota')
                                            </div>

                                            <!-- Ticket Content -->
                                            <div class="tab-pane fade" id="pills-ticket-dalam-kota" role="tabpanel"
                                                aria-labelledby="pills-ticket-dalam-kota-tab">
                                                {{-- Ticket content --}}
                                                @include('hcis.reimbursements.businessTrip.approval.dalam-kota.approvalTicketDalamKota')
                                            </div>

                                            <!-- Taxi Content -->
                                            <div class="tab-pane fade" id="pills-taksi-dalam-kota" role="tabpanel"
                                                aria-labelledby="pills-taksi-dalam-kota-tab">
                                                {{-- Taxi content --}}
                                                @include('hcis.reimbursements.businessTrip.approval.dalam-kota.approvalTaxiDalamKota')
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                {{-- </div> --}}

                                <div id="additional-fields" class="row mb-3" style="display: none;">
                                    <div class="col-md-12">
                                        <label for="additional-fields-title" class="mb-3">Business Travel Needs</label>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="cashAdvancedCheckbox" value="Ya"
                                                        onchange="updateCAValue()" @checked($showCashAdvanced) disabled>
                                                    <label class="form-check-label" for="cashAdvancedCheckbox">Cash
                                                        Advanced</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="hidden" name="ent" id="caEntertainCheckbox"
                                                        value="{{ $showEntertain ? 'Ya' : 'Tidak' }}">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="caEntertainCheckbox" name="ent" value="Ya"
                                                        onchange="updateCAValue()" @checked($showEntertain) disabled>
                                                    <label class="form-check-label" for="caEntertainCheckbox">CA
                                                        Entertain</label>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="hidden" name="hotel" value="Tidak">
                                                    <input class="form-check-input" type="checkbox" id="hotelCheckbox"
                                                        name="hotel" value="Ya"
                                                        <?= $n->jns_dinas === 'luar kota' && $n->hotel == 'Ya' ? 'checked' : '' ?>
                                                        disabled>
                                                    <label class="form-check-label" for="hotelCheckbox">
                                                        Hotel
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="hidden" name="mess" value="Tidak">
                                                    <input class="form-check-input" type="checkbox" id="messCheckbox"
                                                        name="mess" value="Ya"
                                                        <?= $n->jns_dinas === 'luar kota' && $n->mess == 'Ya' ? 'checked' : '' ?>
                                                        disabled>
                                                    <label class="form-check-label" for="messCheckbox">
                                                        Mess
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="hidden" name="tiket" value="Tidak">
                                                    <input class="form-check-input" type="checkbox" id="ticketCheckbox"
                                                        name="tiket" value="Ya"
                                                        <?= $n->jns_dinas === 'luar kota' && $n->tiket == 'Ya' ? 'checked' : '' ?>
                                                        disabled>
                                                    <label class="form-check-label" for="ticketCheckbox">
                                                        Ticket
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="hidden" name="taksi" value="Tidak">
                                                    <input class="form-check-input" type="checkbox" id="taksiCheckbox"
                                                        name="taksi" value="Ya"
                                                        <?= $n->jns_dinas === 'luar kota' && $n->taksi == 'Ya' ? 'checked' : '' ?>
                                                        disabled>
                                                    <label class="form-check-label" for="taksiCheckbox">
                                                        Taxi/Grab
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <ul class="nav nav-tabs nav-pills mb-2" id="pills-tab" role="tablist">

                                                    <li class="nav-item" role="presentation" id="nav-cashAdvanced"
                                                        style="display: {{ $showCashAdvanced ? 'block' : 'none' }};">
                                                        <button class="nav-link" id="pills-cashAdvanced-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-cashAdvanced"
                                                            type="button" role="tab"
                                                            aria-controls="pills-cashAdvanced" aria-selected="false">Cash
                                                            Advanced</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation"
                                                        id="nav-cashAdvancedEntertain"
                                                        style="display: {{ $showEntertain ? 'block' : 'none' }};">
                                                        <button class="nav-link" id="pills-cashAdvancedEntertain-tab"
                                                            data-bs-toggle="pill"
                                                            data-bs-target="#pills-cashAdvancedEntertain" type="button"
                                                            role="tab" aria-controls="pills-cashAdvancedEntertain"
                                                            aria-selected="false">CA
                                                            Entertain</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation" id="nav-hotel"
                                                        style="display: <?= $n->jns_dinas === 'luar kota' && $n->hotel == 'Ya' ? 'block' : 'none' ?>;">
                                                        <button class="nav-link" id="pills-hotel-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-hotel"
                                                            type="button" role="tab" aria-controls="pills-hotel"
                                                            aria-selected="false">Hotel</button>
                                                    </li>
                                                    <!-- Mess Tab -->
                                                    <li class="nav-item" role="presentation" id="nav-mess"
                                                        style="display: <?= $n->jns_dinas === 'luar kota' && $n->mess == 'Ya' ? 'block' : 'none' ?>;">
                                                        <button class="nav-link" id="pills-mess-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-mess"
                                                            type="button" role="tab" aria-controls="pills-mess"
                                                            aria-selected="false">Mess</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation" id="nav-ticket"
                                                        style="display: <?= $n->jns_dinas === 'luar kota' && $n->tiket == 'Ya' ? 'block' : 'none' ?>;">
                                                        <button class="nav-link" id="pills-ticket-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-ticket"
                                                            type="button" role="tab" aria-controls="pills-ticket"
                                                            aria-selected="false">Ticket</button>
                                                    </li>

                                                    <li class="nav-item" role="presentation" id="nav-taksi"
                                                        style="display: <?= $n->jns_dinas === 'luar kota' && $n->taksi == 'Ya' ? 'block' : 'none' ?>;">
                                                        <button class="nav-link" id="pills-taksi-tab"
                                                            data-bs-toggle="pill" data-bs-target="#pills-taksi"
                                                            type="button" role="tab" aria-controls="pills-taksi"
                                                            aria-selected="false">Taxi/Grab</button>
                                                    </li>
                                                </ul>

                                                <div class="tab-content" id="pills-tabContent">
                                                    <div class="tab-pane fade" id="pills-cashAdvanced" role="tabpanel"
                                                        aria-labelledby="pills-cashAdvanced-tab">
                                                        {{-- Cash Advanced content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.btCaApproval')
                                                    </div>
                                                    <div class="tab-pane fade" id="pills-cashAdvancedEntertain"
                                                        role="tabpanel" aria-labelledby="pills-cashAdvancedEntertain-tab">
                                                        {{-- Cash Advanced content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.btEntApproval')
                                                    </div>
                                                    <div class="tab-pane fade" id="pills-hotel" role="tabpanel"
                                                        aria-labelledby="pills-hotel-tab">
                                                        {{-- Hotel content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.approvalHotel')
                                                    </div>
                                                    <div class="tab-pane fade" id="pills-mess" role="tabpanel"
                                                        aria-labelledby="pills-mess-tab">
                                                        {{-- Mess content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.approvalMess')
                                                    </div>
                                                    <div class="tab-pane fade" id="pills-ticket" role="tabpanel"
                                                        aria-labelledby="pills-ticket-tab">
                                                        {{-- Ticket content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.approvalTicket')
                                                    </div>

                                                    <div class="tab-pane fade" id="pills-taksi" role="tabpanel"
                                                        aria-labelledby="pills-taksi-tab">
                                                        {{-- Taxi content --}}
                                                        @include('hcis.reimbursements.businessTrip.approval.approvalTaxi')
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </form>
                        <input type="hidden" id="no_sppd" value="{{ $n->no_sppd }}">
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#rejectReasonModal" style="padding: 0.5rem 1rem; margin-right: 5px">
                                Reject
                            </button>

                            <button type="button" class="btn btn-outline-info rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#revisiReasonModal" style="padding: 0.5rem 1rem; margin-right: 5px">
                                Revisi
                            </button>

                            <form method="POST" action="{{ route('confirm.status', ['id' => $n->id]) }}"
                                style="display: inline-block; margin-right: 5px;" class="status-form"
                                id="approve-form-{{ $n->id }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_approval"
                                    value="{{ Auth::user()->id == $n->manager_l1_id ? 'Pending L2' : 'Approved' }}">
                                <button type="button" class="btn btn-success rounded-pill approve-button"
                                    style="padding: 0.5rem 1rem;" data-id="{{ $n->id }}">
                                    Approve
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- </div>
    </div> --}}


    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title" id="rejectReasonModalLabel" style="color: #333; font-weight: 600;">Rejection
                        Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="rejectReasonForm" method="POST" action="{{ route('confirm.status', ['id' => $n->id]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_approval" value="Rejected">

                        <div class="mb-3">
                            <label for="reject_info" class="form-label" style="color: #555; font-weight: 500;">Please
                                provide a reason for rejection:</label>
                            <textarea class="form-control border-2" name="reject_info" id="reject_info" rows="4" required
                                style="resize: vertical; min-height: 100px;"></textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-primary rounded-pill me-2"
                                data-bs-dismiss="modal" style="min-width: 100px;">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill"
                                style="min-width: 100px;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Revisi Reason Modal -->
    <div class="modal fade" id="revisiReasonModal" tabindex="-1" aria-labelledby="revisiReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title" id="revisiReasonModalLabel" style="color: #333; font-weight: 600;">Revision
                        Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="revisiReasonForm" method="POST" action="{{ route('confirm.status', ['id' => $n->id]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_approval" value="Request Revision">

                        <div class="mb-3">
                            <label for="revisi_info" class="form-label" style="color: #555; font-weight: 500;">Please
                                provide a reason for Revision:</label>
                            <textarea class="form-control border-2" name="revisi_info" id="revisi_info" rows="4" required
                                style="resize: vertical; min-height: 100px;"></textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-primary rounded-pill me-2"
                                data-bs-dismiss="modal" style="min-width: 100px;">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill"
                                style="min-width: 100px;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light rounded-4 border-0 shadow" style="border-radius: 1rem;">
                <div class="modal-body text-center p-5" style="padding: 2rem;">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 100px; color: #AB2F2B !important;"></i>
                    </div>
                    <h4 class="mb-3 fw-bold" style="font-size: 32px; color: #AB2F2B !important;">Success!</h4>
                    <p class="mb-4" id="successModalBody" style="font-size: 20px;">
                        <!-- The success message will be inserted here -->
                    </p>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Part -->
    {{-- <script src="{{ asset('/js/editBusinessTrip.js') }}"></script> --}}
    @include('js.hcis.editBusinessTrip')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // function confirmSubmission(event) {
        //     event.preventDefault(); // Stop the form from submitting immediately

        //     // Display a confirmation alert
        //     const userConfirmed = confirm("Are you sure you want to approve this request?");

        //     if (userConfirmed) {
        //         // If the user confirms, submit the form
        //         event.target.closest('form').submit();
        //     } else {
        //         // If the user cancels, do nothing
        //         alert("Approval cancelled.");
        //     }
        // }
        document.getElementById('rejectReasonForm').addEventListener('submit', function(event) {
            const reason = document.getElementById('reject_info').value.trim();
            if (!reason) {
                alert('Please provide a reason for rejection.');
                event.preventDefault(); // Stop form submission if no reason is provided
            }
        });

        document.getElementById('revisionReasonForm').addEventListener('submit', function(event) {
            const reason = document.getElementById('revisi_info').value.trim();
            if (!reason) {
                alert('Please provide a reason for Revision.');
                event.preventDefault(); // Stop form submission if no reason is provided
            }
        });

        // Add event listener to the decline button to open the modal
        document.getElementById('declineButton').addEventListener('click', function() {
            $('#rejectReasonModal').modal('show');
        });

        // document.addEventListener('DOMContentLoaded', function() {
        //     const forms = document.querySelectorAll('.status-form');

        //     forms.forEach(form => {
        //         form.addEventListener('submit', function(e) {
        //             e.preventDefault();

        //             const action = this.querySelector('input[name="status_approval"]').value;
        //             const confirmMessage = action === 'Rejected' ?
        //                 'Are you sure you want to reject this?' :
        //                 'Are you sure you want to approve this?';

        //             if (action === 'Approved') {
        //                 // Show the approval confirmation modal
        //                 document.getElementById('confirmationMessage').textContent = confirmMessage;
        //                 var approvalConfirmationModal = new bootstrap.Modal(document.getElementById(
        //                     'approvalConfirmationModal'));
        //                 approvalConfirmationModal.show();

        //                 // Handle confirmation
        //                 document.getElementById('confirmApproveButton').addEventListener('click',
        //                     () => {
        //                         const formData = new FormData(this);
        //                         fetch(this.action, {
        //                                 method: 'POST',
        //                                 body: formData,
        //                                 headers: {
        //                                     'X-Requested-With': 'XMLHttpRequest'
        //                                 }
        //                             })
        //                             .then(response => response.json())
        //                             .then(data => {
        //                                 if (data.success) {
        //                                     // Redirect after successful approval
        //                                     window.location.href = '/businessTrip/approval';
        //                                 } else {
        //                                     alert('An error occurred. Please try again.');
        //                                 }
        //                             })
        //                             .catch(error => {
        //                                 console.error('Error:', error);
        //                                 alert('An error occurred. Please try again.');
        //                             });
        //                     });

        //             } else if (action === 'Rejected') {
        //                 // Handle rejection directly
        //                 if (confirm(confirmMessage)) {
        //                     const formData = new FormData(this);
        //                     fetch(this.action, {
        //                             method: 'POST',
        //                             body: formData,
        //                             headers: {
        //                                 'X-Requested-With': 'XMLHttpRequest'
        //                             }
        //                         })
        //                         .then(response => response.json())
        //                         .then(data => {
        //                             if (data.success) {
        //                                 alert('The request has been successfully Rejected.');
        //                                 window.location.href = '/businessTrip/approval';
        //                             } else {
        //                                 alert('An error occurred. Please try again.');
        //                             }
        //                         })
        //                         .catch(error => {
        //                             console.error('Error:', error);
        //                             alert('An error occurred. Please try again.');
        //                         });
        //                 }
        //             }
        //         });
        //     });
        // });

        function formatCurrency(input) {
            var cursorPos = input.selectionStart;
            var value = input.value.replace(/[^\d]/g, ''); // Remove everything that is not a digit
            var formattedValue = '';

            // Format the value with dots
            if (value.length > 3) {
                formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            } else {
                formattedValue = value;
            }

            input.value = formattedValue;

            // Adjust the cursor position after formatting
            cursorPos += (formattedValue.length - value.length);
            input.setSelectionRange(cursorPos, cursorPos);
        }

        document.getElementById('btEditForm').addEventListener('submit', function(event) {
            // Unformat the voucher fields before submission
            var nominalField = document.getElementById('nominal_vt');
            var keeperField = document.getElementById('keeper_vt');

            // Remove dots from the formatted value to keep the number intact
            nominalField.value = nominalField.value.replace(/\./g, '');
            keeperField.value = keeperField.value.replace(/\./g, '');
        });


        // document.addEventListener('DOMContentLoaded', function() {
        //     document.getElementById('save-draft').addEventListener('click', function(event) {
        //         event.preventDefault();

        //         // Remove the existing status input
        //         const existingStatus = document.getElementById('status');
        //         if (existingStatus) {
        //             existingStatus.remove();
        //         }

        //         // Create a new hidden input for "Draft"
        //         const draftInput = document.createElement('input');
        //         draftInput.type = 'hidden';
        //         draftInput.name = 'status';
        //         draftInput.value = 'Draft';
        //         draftInput.id = 'status';

        //         // Append the draft input to the form
        //         this.closest('form').appendChild(draftInput);

        //         // Submit the form
        //         this.closest('form').submit();
        //     });
        // });


        function calculateTotalDays(index) {
            const checkInInput = document.querySelector(`#hotel-form-${index} input[name="tgl_masuk_htl[]"]`);
            const checkOutInput = document.querySelector(`#hotel-form-${index} input[name="tgl_keluar_htl[]"]`);
            const totalDaysInput = document.querySelector(`#hotel-form-${index} input[name="total_hari[]"]`);

            // Get Start Date and End Date from the main form
            const mulaiInput = document.getElementById('mulai');
            const kembaliInput = document.getElementById('kembali');

            if (!checkInInput || !checkOutInput || !mulaiInput || !kembaliInput) {
                return; // Ensure elements are present before proceeding
            }

            // Parse the dates
            const checkInDate = new Date(checkInInput.value);
            const checkOutDate = new Date(checkOutInput.value);
            const mulaiDate = new Date(mulaiInput.value);
            const kembaliDate = new Date(kembaliInput.value);

            // Validate Check In Date
            if (checkInDate < mulaiDate) {
                alert('Check In date cannot be earlier than Start date.');
                checkInInput.value = ''; // Reset the Check In field
                totalDaysInput.value = ''; // Clear total days
                return;
            }

            // Ensure Check Out Date is not earlier than Check In Date
            if (checkOutDate < checkInDate) {
                alert('Check Out date cannot be earlier than Check In date.');
                checkOutInput.value = ''; // Reset the Check Out field
                totalDaysInput.value = ''; // Clear total days
                return;
            }

            // Calculate the total days if all validations pass
            if (checkInDate && checkOutDate) {
                const diffTime = Math.abs(checkOutDate - checkInDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDaysInput.value = diffDays;
            } else {
                totalDaysInput.value = '';
            }
        }

        // Attach event listeners to the hotel forms
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.hotel-form').forEach((form, index) => {
                const i = index + 1; // Adjust for 1-based index

                form.querySelector('input[name="tgl_masuk_htl[]"]').addEventListener('change', () =>
                    calculateTotalDays(i));
                form.querySelector('input[name="tgl_keluar_htl[]"]').addEventListener('change', () =>
                    calculateTotalDays(i));
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            var jnsDinasSelect = document.getElementById('jns_dinas');
            var additionalFields = document.getElementById('additional-fields');

            function showAdditionalFields() {
                if (jnsDinasSelect.value === 'luar kota') {
                    additionalFields.style.display = 'block';
                } else {
                    additionalFields.style.display = 'none';
                }
            }

            // Show additional fields on page load if 'luar kota' is selected
            showAdditionalFields();

            jnsDinasSelect.addEventListener('change', function() {
                showAdditionalFields();
                if (this.value !== 'luar kota') {
                    // Reset all fields to "Tidak" if not 'luar kota'
                    document.getElementById('ca').value = 'Tidak';
                    document.getElementById('tiket').value = 'Tidak';
                    document.getElementById('hotel').value = 'Tidak';
                    document.getElementById('taksi').value = 'Tidak';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const caSelect = document.getElementById('ca');
            const caNbtDiv = document.getElementById('ca_div');

            const hotelSelect = document.getElementById('hotel');
            const hotelDiv = document.getElementById('hotel_div');

            const taksiSelect = document.getElementById('taksi');
            const taksiDiv = document.getElementById('taksi_div');

            const tiketSelect = document.getElementById('tiket');
            const tiketDiv = document.getElementById('tiket_div');


            function toggleDisplay(selectElement, targetDiv) {
                if (selectElement.value === 'Ya') {
                    targetDiv.style.display = 'block';
                } else {
                    targetDiv.style.display = 'none';
                }
            }

            caSelect.addEventListener('change', function() {
                toggleDisplay(caSelect, caNbtDiv);
            });

            hotelSelect.addEventListener('change', function() {
                toggleDisplay(hotelSelect, hotelDiv);
            });

            taksiSelect.addEventListener('change', function() {
                toggleDisplay(taksiSelect, taksiDiv);
            });

            tiketSelect.addEventListener('change', function() {
                toggleDisplay(tiketSelect, tiketDiv);
            });

        });
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the caSelect and caDiv elements
            const caSelect = document.getElementById('ca'); // Make sure this matches your HTML ID
            const caDiv = document.getElementById('ca_div');

            // Check if elements exist
            if (!caSelect || !caDiv) {
                console.error('caSelect or caDiv element not found.');
                return;
            }

            // Function to handle display of ca_div based on caSelect value
            function handleCaDisplay() {
                // Ensure caSelect has a value
                if (caSelect.value === 'Ya') {
                    caDiv.style.display = 'block';
                } else {
                    caDiv.style.display = 'none';
                }
            }

            // Initial check on page load
            handleCaDisplay();

            // Add event listener to handle changes in caSelect
            caSelect.addEventListener('change', function() {
                handleCaDisplay();
            });

        });

        document.addEventListener('DOMContentLoaded', function() {
            // Ticket form handling
            const ticketSelect = document.getElementById('tiket');
            const ticketDiv = document.getElementById('tiket_div');

            ticketSelect.addEventListener('change', function() {
                if (this.value === 'Ya') {
                    ticketDiv.style.display = 'block';
                } else {
                    ticketDiv.style.display = 'none';
                    // Reset all input fields within the ticketDiv when 'Tidak' is selected
                    resetTicketFields(ticketDiv);
                }
            });

            function resetTicketFields(container) {
                const inputs = container.querySelectorAll('input[type="text"], input[type="number"], textarea');
                inputs.forEach(input => {
                    input.value = '';
                });
                const selects = container.querySelectorAll('select');
                selects.forEach(select => {
                    select.selectedIndex = 0;
                });
            }

            for (let i = 1; i <= 4; i++) {
                const yesRadio = document.getElementById(`more_tkt_yes_${i}`);
                const noRadio = document.getElementById(`more_tkt_no_${i}`);
                const nextForm = document.getElementById(`ticket-form-${i + 1}`);

                yesRadio.addEventListener('change', function() {
                    if (this.checked) {
                        nextForm.style.display = 'block';
                    }
                });

                noRadio.addEventListener('change', function() {
                    if (this.checked) {
                        nextForm.style.display = 'none';
                        // Hide all subsequent forms
                        for (let j = i + 1; j <= 5; j++) {
                            const form = document.getElementById(`ticket-form-${j}`);
                            if (form) {
                                form.style.display = 'none';
                                // Reset the form when it is hidden
                                resetTicketFields(form);
                            }
                        }
                        // Reset radio buttons for subsequent forms
                        for (let j = i + 1; j <= 4; j++) {
                            const noRadioButton = document.getElementById(`more_tkt_no_${j}`);
                            if (noRadioButton) {
                                noRadioButton.checked = true;
                            }
                        }
                    }
                });
            }

            // Handle Round Trip options
            const ticketTypes = document.querySelectorAll('select[name="type_tkt[]"]');
            ticketTypes.forEach((select, index) => {
                select.addEventListener('change', function() {
                    const roundTripOptions = this.closest('.card-body').querySelector(
                        '.round-trip-options');
                    if (this.value === 'Round Trip') {
                        roundTripOptions.style.display = 'block';
                    } else {
                        roundTripOptions.style.display = 'none';
                    }
                });
            });
            // Handle hotel forms
            for (let i = 1; i <= 4; i++) {
                const yesRadio = document.getElementById(`more_htl_yes_${i}`);
                const noRadio = document.getElementById(`more_htl_no_${i}`);
                const nextForm = document.getElementById(`hotel-form-${i + 1}`);

                if (yesRadio) {
                    yesRadio.addEventListener('change', function() {
                        if (this.checked) {
                            nextForm.style.display = 'block';
                        }
                    });
                }

                if (noRadio) {
                    noRadio.addEventListener('change', function() {
                        if (this.checked) {
                            nextForm.style.display = 'none';
                            // Hide all subsequent forms
                            for (let j = i + 1; j <= 5; j++) {
                                const form = document.getElementById(`hotel-form-${j}`);
                                if (form) {
                                    form.style.display = 'none';
                                    // Reset the form when it is hidden
                                    resetHotelFields(form);
                                }
                            }
                            // Reset radio buttons for subsequent forms
                            for (let j = i + 1; j <= 4; j++) {
                                const noRadioButton = document.getElementById(`more_htl_no_${j}`);
                                if (noRadioButton) {
                                    noRadioButton.checked = true;
                                }
                            }
                        }
                    });
                }
            }

            // Function to reset hotel fields
            function resetHotelFields(container) {
                const inputs = container.querySelectorAll('input[type="text"], input[type="number"], textarea');
                inputs.forEach(input => {
                    input.value = '';
                });
                const selects = container.querySelectorAll('select');
                selects.forEach(select => {
                    select.selectedIndex = 0;
                });
            }

            // Calculate total days for each hotel form
            function calculateTotalDays(index) {
                const checkIn = document.querySelector(`#hotel-form-${index} input[name="tgl_masuk_htl[]"]`);
                const checkOut = document.querySelector(`#hotel-form-${index} input[name="tgl_keluar_htl[]"]`);
                const totalDays = document.querySelector(`#hotel-form-${index} input[name="total_hari[]"]`);

                if (checkIn && checkOut && totalDays) {
                    const start = new Date(checkIn.value);
                    const end = new Date(checkOut.value);

                    if (checkIn.value && checkOut.value) {
                        // Calculate difference in milliseconds and convert to days, excluding the same day
                        const difference = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                        if (difference < 0) {
                            alert("Check out date cannot be earlier than check in date.");
                            checkOut.value = ''; // Clear the check-out date if invalid
                            totalDays.value = ''; // Clear the total days if check-out date is reset
                        } else {
                            totalDays.value = difference >= 0 ? difference : 0;
                        }
                    } else {
                        totalDays.value = ''; // Clear total days if dates are not set
                    }
                } else {
                    console.error("Elements not found. Check selectors.");
                }
            }

            // Add event listeners for date inputs in hotel forms
            for (let i = 1; i <= 5; i++) {
                const checkIn = document.querySelector(`#hotel-form-${i} input[name="tgl_masuk_htl[]"]`);
                const checkOut = document.querySelector(`#hotel-form-${i} input[name="tgl_keluar_htl[]"]`);

                if (checkIn && checkOut) {
                    checkIn.addEventListener('change', () => calculateTotalDays(i));
                    checkOut.addEventListener('change', () => calculateTotalDays(i));
                }
            }

            // Handle date validation for the return date
            document.getElementById('kembali').addEventListener('change', function() {
                const mulaiDate = document.getElementById('mulai').value;
                const kembaliDate = this.value;

                if (kembaliDate < mulaiDate) {
                    alert('Return date cannot be earlier than Start date.');
                    this.value = ''; // Reset the kembali field
                }
            });
        });



        document.getElementById('tgl_keluar_htl').addEventListener('change', function() {
            var masukHtl = document.getElementById('tgl_masuk_htl').value;
            var keluarDate = this.value;

            if (masukHtl && keluarDate) {
                var checkInDate = new Date(masukHtl);
                var checkOutDate = new Date(keluarDate);

                if (checkOutDate < checkInDate) {
                    alert("Check out date cannot be earlier than check in date.");
                    this.value = ''; // Reset the check out date field
                }
            }
        });

        document.getElementById('type_tkt').addEventListener('change', function() {
            var roundTripOptions = document.getElementById('roundTripOptions');
            if (this.value === 'Round Trip') {
                roundTripOptions.style.display = 'block';
            } else {
                roundTripOptions.style.display = 'none';
            }
        });


        function toggleOthers() {
            var locationFilter = document.getElementById("tujuan");
            var others_location = document.getElementById("others_location");
            var selectedValue = locationFilter.value;
            var options = Array.from(locationFilter.options).map(option => option.value);

            // Check if the selected value is "Others" or not in the list
            if (selectedValue === "Others" || !options.includes(selectedValue)) {
                others_location.style.display = "block";

                if (!options.includes(selectedValue)) {
                    locationFilter.value = "Others"; // Select "Others"
                    others_location.value = selectedValue; // Show the unlisted value in the text field
                }
            } else {
                others_location.style.display = "none";
                others_location.value = ""; // Clear the input field
            }
        }

        // Call the function on page load to handle any pre-filled values
        window.onload = toggleOthers;

        function validateDates(index) {
            // Get the departure and return date inputs for the given form index
            const departureDate = document.querySelector(`#tgl_brkt_tkt_${index}`);
            const returnDate = document.querySelector(`#tgl_plg_tkt_${index}`);

            // Get the departure and return time inputs for the given form index
            const departureTime = document.querySelector(`#jam_brkt_tkt_${index}`);
            const returnTime = document.querySelector(`#jam_plg_tkt_${index}`);

            if (departureDate && returnDate) {
                const depDate = new Date(departureDate.value);
                const retDate = new Date(returnDate.value);

                // Check if both dates are valid
                if (depDate && retDate) {
                    // Validate if return date is earlier than departure date
                    if (retDate < depDate) {
                        alert("Return date cannot be earlier than departure date.");
                        returnDate.value = ''; // Reset the return date field
                    } else if (retDate.getTime() === depDate.getTime() && departureTime && returnTime) {
                        // If dates are the same, validate time
                        const depTime = departureTime.value;
                        const retTime = returnTime.value;

                        // Check if both times are set and validate
                        if (depTime && retTime) {
                            const depDateTime = new Date(`1970-01-01T${depTime}:00`);
                            const retDateTime = new Date(`1970-01-01T${retTime}:00`);

                            if (retDateTime < depDateTime) {
                                alert("Return time cannot be earlier than departure time on the same day.");
                                returnTime.value = ''; // Reset the return time field
                            }
                        }
                    }
                }
            }
        }




        document.getElementById('nik').addEventListener('change', function() {
            var nik = this.value;

            fetch('/get-employee-data?nik=' + nik)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('jk_tkt').value = data.jk_tkt;
                        document.getElementById('tlp_tkt').value = data.tlp_tkt;
                    } else {
                        alert('Employee data not found!');
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        //CA JS
        //CA JS
        function toggleDivs() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");
            var div_bisnis_numb = document.getElementById("div_bisnis_numb");
            var bisnis_numb = document.getElementById("bisnis_numb");
            var div_allowance = document.getElementById("div_allowance");

            if (ca_type.value === "dns") {
                ca_bt.style.display = "block";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb.style.display = "block";
                div_allowance.style.display = "block";
            } else if (ca_type.value === "ndns") {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "block";
                ca_e.style.display = "none";
                div_bisnis_numb.style.display = "none";
                bisnis_numb.style.value = "";
                div_allowance.style.display = "none";
            } else if (ca_type.value === "entr") {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "block";
                div_bisnis_numb.style.display = "block";
            } else {
                ca_bt.style.display = "none";
                ca_nbt.style.display = "none";
                ca_e.style.display = "none";
                div_bisnis_numb.style.display = "none";
                bisnis_numb.style.value = "";
            }
        }

        function toggleOthers() {
            // ca_type ca_nbt ca_e
            var locationFilter = document.getElementById("locationFilter");
            var others_location = document.getElementById("others_location");

            if (locationFilter.value === "Others") {
                others_location.style.display = "block";
            } else {
                others_location.style.display = "none";
                others_location.value = "";
            }
        }

        function validateInput(input) {
            //input.value = input.value.replace(/[^0-9,]/g, '');
            input.value = input.value.replace(/[^0-9]/g, '');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const totalDaysInput = document.getElementById('totaldays');
            const perdiemInput = document.getElementById('perdiem');
            const allowanceInput = document.getElementById('allowance');
            const othersLocationInput = document.getElementById('others_location');
            const transportInput = document.getElementById('transport');
            const accommodationInput = document.getElementById('accommodation');
            const otherInput = document.getElementById('other');
            const totalcaInput = document.getElementById('totalca');
            const nominal_1Input = document.getElementById('nominal_1');
            const nominal_2Input = document.getElementById('nominal_2');
            const nominal_3Input = document.getElementById('nominal_3');
            const nominal_4Input = document.getElementById('nominal_4');
            const nominal_5Input = document.getElementById('nominal_5');
            const caTypeInput = document.getElementById('ca_type');

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, '')) || 0;
            }

            function calculateTotalDays() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                if (startDate && endDate && !isNaN(startDate) && !isNaN(endDate)) {
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                    const totalDays = daysDiff > 0 ? daysDiff + 1 : 0 + 1;
                    totalDaysInput.value = totalDays;

                    const perdiem = parseFloat(perdiemInput.value) || 0;
                    let allowance = totalDays * perdiem;

                    if (othersLocationInput.value.trim() !== '') {
                        allowance *= 1; // allowance * 50%
                    } else {
                        allowance *= 0.5;
                    }

                    allowanceInput.value = formatNumber(Math.floor(allowance));
                } else {
                    totalDaysInput.value = 0;
                    allowanceInput.value = 0;
                }
                calculateTotalCA();
            }

            function formatInput(input) {
                let value = input.value.replace(/\./g, '');
                value = parseFloat(value);
                if (!isNaN(value)) {
                    // input.value = formatNumber(value);
                    input.value = formatNumber(Math.floor(value));
                } else {
                    input.value = formatNumber(0);
                }

                calculateTotalCA();
            }

            function calculateTotalCA() {
                const allowance = parseNumber(allowanceInput.value);
                const transport = parseNumber(transportInput.value);
                const accommodation = parseNumber(accommodationInput.value);
                const other = parseNumber(otherInput.value);
                const nominal_1 = parseNumber(nominal_1Input.value);
                const nominal_2 = parseNumber(nominal_2Input.value);
                const nominal_3 = parseNumber(nominal_3Input.value);
                const nominal_4 = parseNumber(nominal_4Input.value);
                const nominal_5 = parseNumber(nominal_5Input.value);

                // Perbaiki penulisan caTypeInput.value
                const ca_type = caTypeInput.value;

                let totalca = 0;
                if (ca_type === 'dns') {
                    totalca = allowance + transport + accommodation + other;
                } else if (ca_type === 'ndns') {
                    totalca = transport + accommodation + other;
                    allowanceInput.value = 0;
                } else if (ca_type === 'entr') {
                    totalca = nominal_1 + nominal_2 + nominal_3 + nominal_4 + nominal_5;
                    allowanceInput.value = 0;
                }

                // totalcaInput.value = formatNumber(totalca.toFixed(2));
                totalcaInput.value = formatNumber(Math.floor(totalca));
            }

            startDateInput.addEventListener('change', calculateTotalDays);
            endDateInput.addEventListener('change', calculateTotalDays);
            othersLocationInput.addEventListener('input', calculateTotalDays);
            caTypeInput.addEventListener('change', calculateTotalDays);
            [transportInput, accommodationInput, otherInput, allowanceInput, nominal_1, nominal_2, nominal_3,
                nominal_4, nominal_5
            ].forEach(input => {
                input.addEventListener('input', () => formatInput(input));
            });
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = new Date(this.value);
            const declarationEstimateDate = new Date(endDate);
            declarationEstimateDate.setDate(declarationEstimateDate.getDate() + 3);

            const year = declarationEstimateDate.getFullYear();
            const month = String(declarationEstimateDate.getMonth() + 1).padStart(2, '0');
            const day = String(declarationEstimateDate.getDate()).padStart(2, '0');

            document.getElementById('ca_decla').value = `${year}-${month}-${day}`;
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: "bootstrap-5",

            });
        });

        $(document).ready(function() {
            function toggleCard(buttonId, cardId) {
                var $button = $(buttonId);
                var $card = $(cardId);
                var isVisible = $card.is(':visible');

                $card.slideToggle('fast', function() {
                    if (isVisible) {
                        // Clear form inputs
                        $card.find('input[type="text"], input[type="date"], textarea').val('');
                        $card.find('select').prop('selectedIndex', 0);
                        $card.find('input[readonly]').val(0);
                        $card.find('input[type="number"]').val(0);

                        // Set button text and icon
                        var buttonText = $button.data('text') || $button.text();
                        $button.html('<i class="bi bi-plus-circle"></i> ' + buttonText);
                        $button.data('state', 'false');
                    } else {
                        // Set button text and icon
                        var buttonText = $button.data('text') || $button.text();
                        $button.html('<i class="bi bi-dash-circle"></i> ' + buttonText);
                        $button.data('state', 'true');
                    }
                });
            }

            // Store the original button text
            $('#toggle-bt-perdiem, #toggle-bt-transport, #toggle-bt-penginapan, #toggle-bt-lainnya, #toggle-e-detail, #toggle-e-relation')
                .each(function() {
                    $(this).data('text', $(this).text().trim());
                });

            $('#toggle-bt-perdiem').click(function() {
                toggleCard('#toggle-bt-perdiem', '#perdiem-card');
            });

            $('#toggle-bt-transport').click(function() {
                toggleCard('#toggle-bt-transport', '#transport-card');
            });

            $('#toggle-bt-penginapan').click(function() {
                toggleCard('#toggle-bt-penginapan', '#penginapan-card');
            });

            $('#toggle-bt-lainnya').click(function() {
                toggleCard('#toggle-bt-lainnya', '#lainnya-card');
            });

            $('#toggle-e-detail').click(function() {
                toggleCard('#toggle-e-detail', '#entertain-card');
            });

            $('#toggle-e-relation').click(function() {
                toggleCard('#toggle-e-relation', '#relation-card');
            });
            var caType = $('input[name="ca"]').val();
            var caType = $('#ca').val(); // Get the value of the <select> element

            // Check if the value of 'ca' is 'Ya'
            if (caType === 'Ya') {
                $('#toggle-bt-perdiem').click();
                $('#toggle-bt-transport').click();
                $('#toggle-bt-penginapan').click();
                $('#toggle-bt-lainnya').click();
            }

        });


        document.addEventListener('DOMContentLoaded', function() {
            const formContainerBTPerdiem = document.getElementById('form-container-bt-perdiem');
            const formContainerBTTransport = document.getElementById('form-container-bt-transport');
            const formContainerBTPenginapan = document.getElementById('form-container-bt-penginapan');
            const formContainerBTLainnya = document.getElementById('form-container-bt-lainnya');

            function toggleOthersBT(selectElement) {
                const formGroup = selectElement.closest('.mb-2').parentElement;
                const othersInput = formGroup.querySelector('input[name="other_location_bt_perdiem[]"]');

                if (selectElement.value === "Others") {
                    othersInput.style.display = 'block';
                    othersInput.required = true;
                } else {
                    othersInput.style.display = 'none';
                    othersInput.required = false;
                    othersInput.value = "";
                }
            }

            document.querySelectorAll('.location-select').forEach(function(selectElement) {
                selectElement.addEventListener('change', function() {
                    toggleOthersBT(this);
                });
            });

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, '')) || 0;
            }

            function formatInput(input) {
                let value = input.value.replace(/\./g, '');
                value = parseFloat(value);
                if (!isNaN(value)) {
                    input.value = formatNumber(Math.floor(value));
                } else {
                    input.value = formatNumber(0);
                }
                calculateTotalNominalBTPerdiem();
                calculateTotalNominalBTTransport();
                calculateTotalNominalBTPenginapan();
                calculateTotalNominalBTLainnya();
                calculateTotalNominalBTTotal();
            }

            function calculateTotalNominalBTPerdiem() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_bt_perdiem[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="total_bt_perdiem[]"]').value = formatNumber(total);
            }

            function calculateTotalNominalBTTransport() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_bt_transport[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="total_bt_transport[]"]').value = formatNumber(total);
            }

            function calculateTotalNominalBTPenginapan() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_bt_penginapan[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="total_bt_penginapan[]"]').value = formatNumber(total);
            }

            function calculateTotalNominalBTLainnya() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_bt_lainnya[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="total_bt_lainnya[]"]').value = formatNumber(total);
            }

            function calculateTotalNominalBTTotal() {
                let total = 0;
                document.querySelectorAll('input[name="total_bt_perdiem[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelectorAll('input[name="total_bt_transport[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelectorAll('input[name="total_bt_penginapan[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelectorAll('input[name="total_bt_lainnya[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="totalca"]').value = formatNumber(total);
            }

            function calculateTotalDaysPerdiem(input) {
                const formGroup = input.closest('.mb-2').parentElement;
                const startDateInput = formGroup.querySelector('input[name="start_bt_perdiem[]"]');
                const endDateInput = formGroup.querySelector('input[name="end_bt_perdiem[]"]');

                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (!isNaN(startDate) && !isNaN(endDate)) {
                    if (startDate > endDate) {
                        alert('End date cannot be earlier than start date.');
                        endDateInput.value = ''; // Clear the end date field
                        formGroup.querySelector('input[name="total_days_bt_perdiem[]"]').value = 0;
                        return; // Exit the function to prevent further calculation
                    }

                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    formGroup.querySelector('input[name="total_days_bt_perdiem[]"]').value = diffDays;
                } else {
                    formGroup.querySelector('input[name="total_days_bt_perdiem[]"]').value = 0;
                }
            }


            function calculateTotalDaysPenginapan(input) {
                const formGroup = input.closest('.mb-2').parentElement;
                const startDateInput = formGroup.querySelector('input[name="start_bt_penginapan[]"]');
                const endDateInput = formGroup.querySelector('input[name="end_bt_penginapan[]"]');

                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (!isNaN(startDate) && !isNaN(endDate)) {
                    if (startDate > endDate) {
                        alert('End date cannot be earlier than start date.');
                        endDateInput.value = ''; // Clear the end date field
                        formGroup.querySelector('input[name="total_days_bt_penginapan[]"]').value = 0;
                        return; // Exit the function to prevent further calculation
                    }

                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    formGroup.querySelector('input[name="total_days_bt_penginapan[]"]').value = diffDays;
                } else {
                    formGroup.querySelector('input[name="total_days_bt_penginapan[]"]').value = 0;
                }
            }

            function addNewPerdiemForm() {
                const newFormBTPerdiem = document.createElement('div');
                newFormBTPerdiem.classList.add('mb-2');

                newFormBTPerdiem.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Start Perdiem</label>
                        <input type="date" name="start_bt_perdiem[]" class="form-control start-perdiem" placeholder="mm/dd/yyyy" >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Perdiem</label>
                        <input type="date" name="end_bt_perdiem[]" class="form-control end-perdiem" placeholder="mm/dd/yyyy" >
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="start">Total Days</label>
                        <div class="input-group">
                            <input class="form-control bg-light total-days-perdiem" id="total_days_bt_perdiem[]" name="total_days_bt_perdiem[]" type="text" min="0" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                     <div class="mb-2">
                        <label class="form-label" for="name">Location Agency</label>
                        <select class="form-control select2 location-select" name="location_bt_perdiem[]">
                            <option value="">Select location...</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->area }}">{{ $location->area . ' (' . $location->company_name . ')' }}</option>
                            @endforeach
                            <option value="Others">Others</option>
                        </select>
                        <br>
                        <input type="text" name="other_location_bt_perdiem[]" class="form-control other-location" placeholder="Other Location" value="" style="display: none;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="name">Company Code</label>
                        <select class="form-control select2" name="company_bt_perdiem[]" >
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">{{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_bt_perdiem[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                document.getElementById('form-container-bt-perdiem').appendChild(newFormBTPerdiem);

                formContainerBTPerdiem.appendChild(newFormBTPerdiem);

                newFormBTPerdiem.querySelector('.location-select').addEventListener('change', function() {
                    toggleOthersBT(this);
                });


                // Attach input event to the newly added nominal field
                newFormBTPerdiem.querySelector('input[name="nominal_bt_perdiem[]"]').addEventListener('input',
                    function() {
                        formatInput(this);
                    });

                // Attach change event to the date fields to calculate total days
                newFormBTPerdiem.querySelector('input[name="start_bt_perdiem[]"]').addEventListener('change',
                    function() {
                        calculateTotalDaysPerdiem(this);
                    });

                newFormBTPerdiem.querySelector('input[name="end_bt_perdiem[]"]').addEventListener('change',
                    function() {
                        calculateTotalDaysPerdiem(this);
                    });

                // Attach click event to the remove button
                newFormBTPerdiem.querySelector('.remove-form').addEventListener('click', function() {
                    newFormBTPerdiem.remove();
                    calculateTotalNominalBTPerdiem();
                    calculateTotalNominalBTTotal();
                });

                // Update the date constraints for the new 'start_bt_perdiem[]' and 'end_bt_perdiem[]' input fields
                const startDateInput = document.getElementById('start_date').value;
                const endDateInput = document.getElementById('end_date').value;

                newFormBTPerdiem.querySelectorAll('input[name="start_bt_perdiem[]"]').forEach(function(input) {
                    input.min = startDateInput;
                    input.max = endDateInput;
                });

                newFormBTPerdiem.querySelectorAll('input[name="end_bt_perdiem[]"]').forEach(function(input) {
                    input.min = startDateInput;
                    input.max = endDateInput;
                });
            }

            function addNewTransportForm() {
                const newFormBTTransport = document.createElement('div');
                newFormBTTransport.classList.add('mb-2');

                newFormBTTransport.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Transport Date</label>
                        <input type="date" name="tanggal_bt_transport[]" class="form-control" placeholder="mm/dd/yyyy" >
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="name">Company Code</label>
                        <select class="form-control select2" name="company_bt_transport[]" >
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">{{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Information</label>
                        <textarea name="keterangan_bt_transport[]" class="form-control"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_bt_transport[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainerBTTransport.appendChild(newFormBTTransport);

                // Attach input event to the newly added nominal field
                newFormBTTransport.querySelector('input[name="nominal_bt_transport[]"]').addEventListener('input',
                    function() {
                        formatInput(this);
                    });

                // Attach click event to the remove button
                newFormBTTransport.querySelector('.remove-form').addEventListener('click', function() {
                    newFormBTTransport.remove();
                    calculateTotalNominalBTTransport();
                    calculateTotalNominalBTTotal();
                });
            }

            function addNewPenginapanForm() {
                const newFormBTPenginapan = document.createElement('div');
                newFormBTPenginapan.classList.add('mb-2');

                newFormBTPenginapan.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Start Penginapan</label>
                        <input type="date" name="start_bt_penginapan[]" class="form-control start-penginapan" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Penginapan</label>
                        <input type="date" name="end_bt_penginapan[]" class="form-control end-penginapan" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="start">Total Days</label>
                        <div class="input-group">
                            <input class="form-control bg-light total-days-penginapan" id="total_days_bt_penginapan[]" name="total_days_bt_penginapan[]" type="text" min="0" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="name">Hotel Name</label>
                        <input type="text" name="hotel_name_bt_penginapan[]" class="form-control" placeholder="Hotel">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="name">Company Code</label>
                        <select class="form-control select2" id="companyFilter" name="company_bt_penginapan[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">{{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_bt_penginapan[]" id="nominal_bt_penginapan[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainerBTPenginapan.appendChild(newFormBTPenginapan);

                // Attach input event to the newly added nominal field
                newFormBTPenginapan.querySelector('input[name="nominal_bt_penginapan[]"]').addEventListener('input',
                    function() {
                        formatInput(this);
                    });

                // Attach change event to the date fields to calculate total days
                newFormBTPenginapan.querySelector('input[name="start_bt_penginapan[]"]').addEventListener('change',
                    function() {
                        calculateTotalDaysPenginapan(this);
                    });

                newFormBTPenginapan.querySelector('input[name="end_bt_penginapan[]"]').addEventListener('change',
                    function() {
                        calculateTotalDaysPenginapan(this);
                    });

                // Attach click event to the remove button
                newFormBTPenginapan.querySelector('.remove-form').addEventListener('click', function() {
                    newFormBTPenginapan.remove();
                    calculateTotalNominalBTPenginapan();
                    calculateTotalNominalBTTotal();
                });
            }

            function addNewLainnyaForm() {
                const newFormBTLainnya = document.createElement('div');
                newFormBTLainnya.classList.add('mb-2');

                newFormBTLainnya.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal_bt_lainnya[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan_bt_lainnya[]" class="form-control"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Accommodation</label>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_bt_lainnya[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainerBTLainnya.appendChild(newFormBTLainnya);

                // Attach input event to the newly added nominal field
                newFormBTLainnya.querySelector('input[name="nominal_bt_lainnya[]"]').addEventListener('input',
                    function() {
                        formatInput(this);
                    });

                // Attach click event to the remove button
                newFormBTLainnya.querySelector('.remove-form').addEventListener('click', function() {
                    newFormBTLainnya.remove();
                    calculateTotalNominalBTLainnya();
                    calculateTotalNominalBTTotal();
                });
            }

            document.getElementById('add-more-bt-perdiem').addEventListener('click', addNewPerdiemForm);
            document.getElementById('add-more-bt-transport').addEventListener('click', addNewTransportForm);
            document.getElementById('add-more-bt-penginapan').addEventListener('click', addNewPenginapanForm);
            document.getElementById('add-more-bt-lainnya').addEventListener('click', addNewLainnyaForm);

            // Attach input event to the existing nominal fields
            document.querySelectorAll('input[name="nominal_bt_perdiem[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            document.querySelectorAll('input[name="nominal_bt_transport[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            document.querySelectorAll('input[name="nominal_bt_penginapan[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            // Attach change event to the existing start and end date fields to calculate total days
            document.querySelectorAll('input[name="start_bt_perdiem[]"], input[name="end_bt_perdiem[]"]').forEach(
                input => {
                    input.addEventListener('change', function() {
                        calculateTotalDaysPerdiem(this);
                    });
                });

            document.querySelectorAll('input[name="start_bt_penginapan[]"], input[name="end_bt_penginapan[]"]')
                .forEach(input => {
                    input.addEventListener('change', function() {
                        calculateTotalDaysPenginapan(this);
                    });
                });

            document.querySelectorAll('input[name="nominal_bt_lainnya[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            // Initial calculation for the total nominal
            calculateTotalNominalBTPerdiem();
            calculateTotalNominalBTTransport();
            calculateTotalNominalBTPenginapan();
            calculateTotalNominalBTLainnya();
            calculateTotalNominalBTTotal();

            document.getElementById('start_date').addEventListener('change', handleDateChange);
            document.getElementById('end_date').addEventListener('change', handleDateChange);

            function handleDateChange() {
                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');

                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                // Set the min attribute of the end_date input to the selected start_date
                endDateInput.min = startDateInput.value;

                // Validate dates
                if (endDate < startDate) {
                    alert("End Date cannot be earlier than Start Date");
                    endDateInput.value = "";
                }

                // Update min and max values for all dynamic perdiem date fields
                document.querySelectorAll('input[name="start_bt_perdiem[]"]').forEach(function(input) {
                    input.min = startDateInput.value;
                    input.max = endDateInput.value;
                });

                document.querySelectorAll('input[name="end_bt_perdiem[]"]').forEach(function(input) {
                    input.min = startDateInput.value;
                    input.max = endDateInput.value;
                });

                document.querySelectorAll('input[name="total_days_bt_perdiem[]"]').forEach(function(input) {
                    calculateTotalDaysPerdiem(input);
                });
            }



            // Attach click event to the remove button for existing forms
            document.querySelectorAll('.remove-form').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.mb-2').remove();
                    calculateTotalNominalBTPerdiem();
                    calculateTotalNominalBTTransport();
                    calculateTotalNominalBTPenginapan();
                    calculateTotalNominalBTLainnya();
                    calculateTotalNominalBTTotal();
                });
            });
        });



        //

        document.addEventListener('DOMContentLoaded', function() {
            const formContainer = document.getElementById('form-container');

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, '')) || 0;
            }

            function formatInput(input) {
                let value = input.value.replace(/\./g, '');
                value = parseFloat(value);
                if (!isNaN(value)) {
                    input.value = formatNumber(Math.floor(value));
                } else {
                    input.value = formatNumber(0);
                }
                calculateTotalNominal();
            }

            function calculateTotalNominal() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_nbt[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.getElementById('totalca').value = formatNumber(total);
            }

            document.getElementById('add-more').addEventListener('click', function() {
                const newForm = document.createElement('div');
                newForm.classList.add('mb-2', 'form-group');

                newForm.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal_nbt[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan_nbt[]" class="form-control"></textarea>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_nbt[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainer.appendChild(newForm);

                // Attach input event to the newly added nominal field
                newForm.querySelector('input[name="nominal_nbt[]"]').addEventListener('input', function() {
                    formatInput(this);
                });

                // Attach click event to the remove button
                newForm.querySelector('.remove-form').addEventListener('click', function() {
                    newForm.remove();
                    calculateTotalNominal();
                });

                // Update the date constraints for the new 'tanggal_nbt[]' input fields
                const startDateInput = document.getElementById('start_date').value;
                const endDateInput = document.getElementById('end_date').value;

                newForm.querySelectorAll('input[name="tanggal_nbt[]"]').forEach(function(input) {
                    input.min = startDateInput;
                    input.max = endDateInput;
                });
            });

            // Attach input event to the existing nominal fields
            document.querySelectorAll('input[name="nominal_nbt[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            // Initial calculation for the total nominal
            calculateTotalNominal();

        });

        document.addEventListener('DOMContentLoaded', function() {
            const formContainerEDetail = document.getElementById('form-container-e-detail');
            const formContainerERelation = document.getElementById('form-container-e-relation');

            // Function to update checkboxes visibility based on selected options
            function updateCheckboxVisibility() {
                // Gather all selected options from enter_type_e_detail
                const selectedOptions = Array.from(document.querySelectorAll(
                        'select[name="enter_type_e_detail[]"]'))
                    .map(select => select.value)
                    .filter(value => value !== "");

                // Update visibility for each checkbox in enter_type_e_relation
                formContainerERelation.querySelectorAll('.form-check').forEach(checkDiv => {
                    const checkbox = checkDiv.querySelector('input.form-check-input');
                    const checkboxValue = checkbox.value.toLowerCase().replace(/\s/g, "_");
                    if (selectedOptions.includes(checkboxValue)) {
                        checkDiv.style.display = 'block';
                    } else {
                        checkDiv.style.display = 'none';
                    }
                });
            }

            // Function to format number with thousands separator
            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Function to parse number from formatted string
            function parseNumber(value) {
                return parseFloat(value.replace(/\./g, '')) || 0;
            }

            // Function to format input fields
            function formatInput(input) {
                let value = input.value.replace(/\./g, '');
                value = parseFloat(value);
                if (!isNaN(value)) {
                    input.value = formatNumber(Math.floor(value));
                } else {
                    input.value = formatNumber(0);
                }
                calculateTotalNominalEDetail();
            }

            // Function to calculate the total nominal value for EDetail
            function calculateTotalNominalEDetail() {
                let total = 0;
                document.querySelectorAll('input[name="nominal_e_detail[]"]').forEach(input => {
                    total += parseNumber(input.value);
                });
                document.querySelector('input[name="total_e_detail[]"]').value = formatNumber(total);
                document.getElementById('totalca').value = formatNumber(total);
            }

            // Function to add new EDetail form
            function addNewEDetailForm() {
                const newFormEDetail = document.createElement('div');
                newFormEDetail.classList.add('mb-2');

                newFormEDetail.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Entertainment Type</label>
                        <select name="enter_type_e_detail[]" class="form-select">
                            <option value="">-</option>
                            <option value="food_cost">Food/Beverages/Souvenir</option>
                            <option value="transport">Transport</option>
                            <option value="accommodation">Accommodation</option>
                            <option value="gift">Gift</option>
                            <option value="fund">Fund</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Entertainment Fee Detail</label>
                        <textarea name="enter_fee_e_detail[]" class="form-control"></textarea>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input class="form-control" name="nominal_e_detail[]" type="text" min="0" value="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-form-e-detail">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainerEDetail.appendChild(newFormEDetail);

                // Attach input event to the newly added nominal field
                newFormEDetail.querySelector('input[name="nominal_e_detail[]"]').addEventListener('input',
                    function() {
                        formatInput(this);
                    });

                // Attach change event to update checkbox visibility
                newFormEDetail.querySelector('select[name="enter_type_e_detail[]"]').addEventListener('change',
                    updateCheckboxVisibility);

                // Attach click event to the remove button
                newFormEDetail.querySelector('.remove-form-e-detail').addEventListener('click', function() {
                    newFormEDetail.remove();
                    updateCheckboxVisibility();
                    calculateTotalNominalEDetail();
                });
            }

            // Function to add new ERelation form
            function addNewERelationForm() {
                const newFormERelation = document.createElement('div');
                newFormERelation.classList.add('mb-2');

                newFormERelation.innerHTML = `
                    <div class="mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="food_cost_e_relation[]" value="food_cost">
                            <label class="form-check-label" for="food_cost_e_relation[]">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="transport_e_relation[]" value="transport">
                            <label class="form-check-label" for="transport_e_relation[]">Transport</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accommodation_e_relation[]" value="accommodation">
                            <label class="form-check-label" for="accommodation_e_relation[]">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="gift_e_relation[]" value="gift">
                            <label class="form-check-label" for="gift_e_relation[]">Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="fund_e_relation[]" value="fund">
                            <label class="form-check-label" for="fund_e_relation[]">Fund</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" name="rname_e_relation[]" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Position</label>
                        <input type="text" name="rposition_e_relation[]" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Company</label>
                        <input type="text" name="rcompany_e_relation[]" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="rpurpose_e_relation[]" class="form-control">
                    </div>
                    <button type="button" class="btn btn-danger remove-form-e-relation">Remove</button>
                    <hr class="border border-primary border-1 opacity-50">
                `;

                formContainerERelation.appendChild(newFormERelation);

                // Initial update of checkbox visibility
                updateCheckboxVisibility();

                // Attach click event to the remove button
                newFormERelation.querySelector('.remove-form-e-relation').addEventListener('click', function() {
                    newFormERelation.remove();
                    updateCheckboxVisibility();
                });
            }

            document.getElementById('add-more-e-detail').addEventListener('click', addNewEDetailForm);
            document.getElementById('add-more-e-relation').addEventListener('click', addNewERelationForm);

            // Attach input event to the existing nominal fields
            document.querySelectorAll('input[name="nominal_e_detail[]"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatInput(this);
                });
            });

            // Attach change event to existing select fields for checkbox visibility
            document.querySelectorAll('select[name="enter_type_e_detail[]"]').forEach(select => {
                select.addEventListener('change', updateCheckboxVisibility);
            });

            calculateTotalNominalEDetail();
            updateCheckboxVisibility();
        });
    </script>
@endsection
