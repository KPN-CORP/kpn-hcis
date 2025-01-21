@extends('layouts_.vertical', ['page_title' => 'Ticket'])

@section('css')
    <!-- Sertakan CSS Bootstrap jika diperlukan -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/css/bootstrap.min.css">
@endsection

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('ticket.approval') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="d-sm-flex align-items-center justify-content-center">
            <div class="card col-md-12">
                <div class="card-header d-flex bg-primary text-white justify-content-between">
                    <h4 class="modal-title" id="viewFormEmployeeLabel">Detail Ticket - {{ $ticket->no_tkt }}</h4>
                    <a href="{{ route('ticket.approval') }}" type="button" class="btn btn-close btn-close-white"></a>
                </div>
                <div class="card-body" @style('overflow-y: auto;')>
                    <div class="container-fluid">
                        <form id="btEditForm" method="POST"
                            action="{{ route('change.status.ticket', ['id' => $ticket->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Name</label>
                                        <input type="text" name="name" id="name"
                                            value="{{ $employee_data->fullname }}"
                                            class="form-control bg-light form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Unit</label>
                                        <input type="text" name="unit" id="unit"
                                            value="{{ $employee_data->unit }}" class="form-control bg-light form-control-sm"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <label class="form-label" for="start">Grade</label>
                                        <input type="text" name="grade" id="grade"
                                            value="{{ $employee_data->job_level }}"
                                            class="form-control bg-light form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                            @include('hcis.reimbursements.businessTrip.modal')
                            <!-- Business Trip Number Selection -->
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="bisnis_numb">Business Trip Number</label>
                                        <select class="form-control select2 form-select-sm" id="bisnis_numb"
                                            name="bisnis_numb" disabled>
                                            <option value="-" {{ $ticket->no_sppd === '-' ? 'selected' : '' }}>No
                                                Business
                                                Trip</option>
                                            @foreach ($no_sppds as $no_sppd)
                                                <option value="{{ $no_sppd->no_sppd }}"
                                                    {{ $ticket->no_sppd == $no_sppd->no_sppd ? 'selected' : '' }}>
                                                    {{ $no_sppd->no_sppd }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label" for="contribution_level_code">Costing Company</label>
                                        <select class="form-control select2 form-select-sm" id="contribution_level_code"
                                            name="contribution_level_code" disabled>
                                            <option value="" selected disabled>Select Costing Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->contribution_level_code }}"
                                                    {{ $ticket->contribution_level_code == $company->contribution_level_code ? 'selected' : '' }}>
                                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <label class="form-label" for="jns_dinas_tkt">Service Type</label>
                                        <input type="text" class="form-control bg-light form-control-sm"
                                            name="jns_dinas_tkt" id="jns_dinas_tkt" value="{{ $ticket->jns_dinas_tkt }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                            <div id="tiket_div">
                                <div class="d-flex flex-column gap-1" id="ticket_forms_container">
                                    <?php
                                        $maxForms = 5;
                                        $ticketCount = count($ticketData);

                                        if ($ticketCount === 0) {
                                            $ticketCount = 1;
                                            $ticketData = [null]; // Set an empty form data
                                        }

                                        for ($i = 0; $i < $ticketCount; $i++) :
                                            $ticket = $ticketData[$i];
                                            $displayNumber = $i + 1;
                                        ?>

                                    <div class="card bg-light shadow-none" id="ticket-form-<?php echo $i; ?>"
                                        style="display: <?php echo $i <= $ticketCount ? 'block' : 'none'; ?>;">
                                        <div class="card-body">
                                            <div class="h5 text-uppercase">
                                                <b>TICKET <?php echo $displayNumber; ?></b>
                                            </div>
                                            <div class="row">
                                                <label class="form-label" for="jk_tkt">Passengers Name (No KTP)</label>
                                                <div class="col-md-2">
                                                    <div class="mb-2 mt-1 gap-1">
                                                        <div class="form-group" id="jk_tkt_<?php echo $i; ?>">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    id="male_<?php echo $i; ?>"
                                                                    name="jk_tkt[<?php echo $i; ?>]" value="Male"
                                                                    disabled <?php echo isset($ticket['jk_tkt']) && $ticket['jk_tkt'] == 'Male' ? 'checked' : ''; ?>>
                                                                <label class="form-check-label"
                                                                    for="male_<?php echo $i; ?>">Mr</label>
                                                            </div>
                                                            <div class="form-check form-check-inline ms-2">
                                                                <input class="form-check-input" type="radio"
                                                                    id="female_<?php echo $i; ?>"
                                                                    name="jk_tkt[<?php echo $i; ?>]" value="Female"
                                                                    disabled <?php echo isset($ticket['jk_tkt']) && $ticket['jk_tkt'] == 'Female' ? 'checked' : ''; ?>>
                                                                <label class="form-check-label"
                                                                    for="female_<?php echo $i; ?>">Mrs</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="mb-2">
                                                        <input type="text" name="np_tkt[]"
                                                            id="np_tkt_<?php echo $i; ?>"
                                                            class="form-control form-control-sm bg-light" readonly
                                                            placeholder="Passengers Name"
                                                            value="{{ $ticket['np_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="mb-2">
                                                        <input type="number" name="noktp_tkt[]"
                                                            id="noktp_tkt_<?php echo $i; ?>"
                                                            class="form-control form-control-sm bg-light" readonly
                                                            placeholder="No KTP"
                                                            value="{{ $ticket['noktp_tkt'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-2">
                                                        <label class="form-label" for="tlp_tkt_<?php echo $i; ?>">Phone
                                                            Number</label>
                                                        <input type="number" name="tlp_tkt[]"
                                                            id="tlp_tkt_<?php echo $i; ?>"
                                                            class="form-control form-control-sm bg-light" maxlength="12"
                                                            value="{{ $ticket['tlp_tkt'] ?? '' }}"
                                                            placeholder="ex: 08123123123" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">From</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm bg-light"
                                                            name="dari_tkt[]" type="text"
                                                            placeholder="ex. Yogyakarta (YIA)"
                                                            value="{{ $ticket['dari_tkt'] ?? '' }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">To</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm bg-light"
                                                            name="ke_tkt[]" type="text"
                                                            placeholder="ex. Jakarta (CGK)"
                                                            value="{{ $ticket['ke_tkt'] ?? '' }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Transportation Type</label>
                                                    <div class="input-group">
                                                        <select class="form-select form-select-sm" name="jenis_tkt[]"
                                                            disabled>
                                                            <option value="">Select Transportation Type</option>
                                                            <option value="Train"
                                                                {{ $ticket && $ticket['jenis_tkt'] == 'Train' ? 'selected' : '' }}>
                                                                Train</option>
                                                            <option value="Bus"
                                                                {{ $ticket && $ticket['jenis_tkt'] == 'Bus' ? 'selected' : '' }}>
                                                                Bus</option>
                                                            <option value="Airplane"
                                                                {{ $ticket && $ticket['jenis_tkt'] == 'Airplane' ? 'selected' : '' }}>
                                                                Airplane
                                                            </option>
                                                            <option value="Car"
                                                                {{ $ticket && $ticket['jenis_tkt'] == 'Car' ? 'selected' : '' }}>
                                                                Car</option>
                                                            <option value="Ferry"
                                                                {{ $ticket && $ticket['jenis_tkt'] == 'Ferry' ? 'selected' : '' }}>
                                                                Ferry</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Ticket Type</label>
                                                    <select class="form-select form-select-sm" name="type_tkt[]" disabled>
                                                        <option value="One Way"
                                                            {{ $ticket && $ticket['type_tkt'] == 'One Way' ? 'selected' : '' }}>
                                                            One Way</option>
                                                        <option value="Round Trip"
                                                            {{ $ticket && $ticket['type_tkt'] == 'Round Trip' ? 'selected' : '' }}>
                                                            Round Trip
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Date</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm bg-light"
                                                            name="tgl_brkt_tkt[]" type="date"
                                                            id="tgl_brkt_tkt_<?php echo $i; ?>"
                                                            value="{{ $ticket['tgl_brkt_tkt'] ?? '' }}"
                                                            onchange="validateDates(<?php echo $i; ?>)" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Time</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm bg-light"
                                                            name="jam_brkt_tkt[]" type="time"
                                                            id="jam_brkt_tkt_<?php echo $i; ?>"
                                                            value="{{ $ticket['jam_brkt_tkt'] ?? '' }}"
                                                            onchange="validateDates(<?php echo $i; ?>)" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="round-trip-options"
                                                style="display: {{ isset($ticket['type_tkt']) && $ticket['type_tkt'] == 'Round Trip' ? 'block' : 'none' }};">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Return Date</label>
                                                        <div class="input-group">
                                                            <input class="form-control form-control-sm bg-light"
                                                                name="tgl_plg_tkt[]" type="date"
                                                                id="tgl_plg_tkt_<?php echo $i; ?>"
                                                                onchange="validateDates(<?php echo $i; ?>)"
                                                                value="{{ $ticket['tgl_plg_tkt'] ?? '' }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Return Time</label>
                                                        <div class="input-group">
                                                            <input class="form-control form-control-sm bg-light"
                                                                id="jam_plg_tkt_<?php echo $i; ?>" name="jam_plg_tkt[]"
                                                                type="time"
                                                                onchange="validateDates(<?php echo $i; ?>)"
                                                                value="{{ $ticket['jam_plg_tkt'] ?? '' }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-2">
                                                    <label class="form-label">Information</label>
                                                    <textarea class="form-control bg-light" name="ket_tkt[]" rows="3" placeholder="Add ticket details" readonly>{{ $ticket['ket_tkt'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="ticket_ids[]" value="{{ $ticket['id'] }}">

                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                    </div>
                    </form>
                    <div class="d-flex justify-content-end">
                        <!-- Decline Form -->
                        <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#rejectReasonModal" style="padding: 0.5rem 1rem; margin-right: 5px">
                            Reject
                        </button>
                        <input type="hidden" id="no_sppd" value="{{ $ticket['no_tkt'] }}">
                        <!-- Approve Form -->
                        <form method="POST" action="{{ route('change.status.ticket', ['id' => $ticket['id']]) }}"
                            style="display: inline-block; margin-right: 5px;" class="status-form"
                            id="approve-form-{{ $ticket['id'] }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status_approval"
                                value="{{ Auth::user()->id == $ticketOwnerEmployee->manager_l1_id ? 'Pending L2' : 'Approved' }}">
                            <button type="submit" class="btn btn-success rounded-pill approve-button"
                                style="padding: 0.5rem 1rem;" data-id="{{ $ticket['id'] }}">
                                Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

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
                    <form id="rejectReasonForm" method="POST"
                        action="{{ route('change.status.ticket', ['id' => $ticket['id']]) }}">
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
@endsection
