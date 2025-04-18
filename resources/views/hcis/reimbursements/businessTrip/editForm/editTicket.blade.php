<div id="tiket_div">
    <div class="d-flex flex-column gap-1" id="ticket_forms_container">
        <?php
        $maxForms = 5;
        $ticketCount = count($ticketData);

        if ($ticketCount === 0) {
            $ticketCount = 1;
            $ticketData = [null]; // Set an empty form data
        }

        for ($i = 1; $i <= $ticketCount; $i++) :
            $ticket = $ticketData[$i - 1];
        ?>
        <div class="card bg-light shadow-none" id="ticket-form-<?php echo $i; ?>" style="display: <?php echo $i <= $ticketCount ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase text-primary">
                    <b>TICKET <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Employee Name</label>
                        <select class="form-select form-select-sm select2" id="noktp_tkt_<?php echo $i; ?>"
                            name="noktp_tkt[]">
                            <option value="" selected>Please Select</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->ktp }}"
                                    {{ $ticket && $ticket['noktp_tkt'] == $employee->ktp ? 'selected' : '' }}>
                                    {{ $employee->employee_id . ' - ' . $employee->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">From</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="dari_tkt[]" type="text"
                                placeholder="ex. Yogyakarta (YIA)" value="{{ $ticket['dari_tkt'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">To</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="ke_tkt[]" type="text"
                                placeholder="ex. Jakarta (CGK)" value="{{ $ticket['ke_tkt'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Transportation Type</label>
                        <div class="input-group">
                            <select class="form-select form-select-sm" name="jenis_tkt[]">
                                <option value="">Select Transportation Type</option>
                                <option value="Train"
                                    {{ $ticket && $ticket['jenis_tkt'] == 'Train' ? 'selected' : '' }}>Train</option>
                                <option value="Airplane"
                                    {{ $ticket && $ticket['jenis_tkt'] == 'Airplane' ? 'selected' : '' }}>Airplane
                                </option>
                                <option value="Ferry"
                                    {{ $ticket && $ticket['jenis_tkt'] == 'Ferry' ? 'selected' : '' }}>Ferry</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Ticket Type</label>
                        <select class="form-select form-select-sm" name="type_tkt[]">
                            <option value="One Way" {{ $ticket && $ticket['type_tkt'] == 'One Way' ? 'selected' : '' }}>
                                One Way</option>
                            <option value="Round Trip"
                                {{ $ticket && $ticket['type_tkt'] == 'Round Trip' ? 'selected' : '' }}>Round Trip
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="tgl_brkt_tkt[]" type="date"
                                id="tgl_brkt_tkt_<?php echo $i; ?>" value="{{ $ticket['tgl_brkt_tkt'] ?? '' }}"
                                onchange="validateDates(<?php echo $i; ?>)">
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Time</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jam_brkt_tkt[]" type="time"
                                id="jam_brkt_tkt_<?php echo $i; ?>" value="{{ $ticket['jam_brkt_tkt'] ?? '' }}"
                                onchange="validateDates(<?php echo $i; ?>)">
                        </div>
                    </div>
                </div>
                <div class="round-trip-options"
                    style="display: {{ isset($ticket['type_tkt']) && $ticket['type_tkt'] == 'Round Trip' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Return Date</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="tgl_plg_tkt[]" type="date"
                                    id="tgl_plg_tkt_<?php echo $i; ?>" onchange="validateDates(<?php echo $i; ?>)"
                                    value="{{ $ticket['tgl_plg_tkt'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Return Time</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" id="jam_plg_tkt_<?php echo $i; ?>"
                                    name="jam_plg_tkt[]" type="time" onchange="validateDates(<?php echo $i; ?>)"
                                    value="{{ $ticket['jam_plg_tkt'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Information</label>
                        <textarea class="form-control" name="ket_tkt[]" rows="3" placeholder="Add ticket details">{{ $ticket['ket_tkt'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-ticket-btn"
                        id="remove-ticket-btn" data-form-id="<?php echo $i; ?>">Delete</button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-sm btn-primary add-ticket-btn" id="add-ticket-btn">Add More</button>
</div>

{{-- </div> --}}
{{-- </div> --}}
<script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('vendor/select2/dist/js/select2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $(".selection2").select2({
            theme: "bootstrap-5",
            width: "100%",
            minimumInputLength: 1,
            ajax: {
                url: "/search/name", // Ensure this matches your route
                dataType: "json",
                delay: 250,
                data: function(params) {
                    console.log("Requesting data with term:", params.term); // Log search term
                    return {
                        searchTerm: params.term
                    };
                },
                processResults: function(data) {
                    console.log("Received data:", data); // Log received data
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.ktp,
                                text: item.fullname + " - " + item.ktp
                            };
                        })
                    };
                },
                cache: true
            }
        });
    });
</script>
