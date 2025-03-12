<div id="tiket_div_dalam_kota">
    <div class="d-flex flex-column gap-1" id="ticket_forms_container_dalam_kota">
        <?php
        $i = 1;
        ?>
        <div class="card bg-light shadow-none" id="ticket-form-dalam-kota-<?php echo $i; ?>"
            style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase text-primary">
                    <b>TICKET <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Employee Name</label>
                        <select class="form-select form-select-sm select2" id="noktp_tkt_dalam_kota_<?php echo $i; ?>"
                            name="noktp_tkt_dalam_kota[]">
                            <option value="" selected>Please Select</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->ktp }}">
                                    {{ $employee->ktp . ' - ' . $employee->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">From</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="dari_tkt_dalam_kota[]" type="text"
                                placeholder="ex. Yogyakarta (YIA)">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">To</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="ke_tkt_dalam_kota[]" type="text"
                                placeholder="ex. Jakarta (CGK)">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label" for="jenis_tkt_dalam_kota_<?php echo $i; ?>">Transportation
                            Type</label>
                        <div class="input-group">
                            <select class="form-select form-select-sm" name="jenis_tkt_dalam_kota[]"
                                id="jenis_tkt_dalam_kota_<?php echo $i; ?>">
                                <option value="">Select Transportation Type</option>
                                <option value="Train">Train</option>
                                <option value="Ferry">Ferry</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="type_tkt_dalam_kota_<?php echo $i; ?>" class="form-label">Ticket Type</label>
                        <select class="form-select form-select-sm" name="type_tkt_dalam_kota[]">
                            <option value="One Way" selected>One Way</option>
                            <option value="Round Trip">Round Trip</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" id="tgl_brkt_tkt_dalam_kota_<?php echo $i; ?>"
                                name="tgl_brkt_tkt_dalam_kota[]" type="date"
                                onchange="validateDatesDalamKota(<?php echo $i; ?>)">
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Time</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm"
                                id="jam_brkt_tkt_dalam_kota_<?php echo $i; ?>" name="jam_brkt_tkt_dalam_kota[]"
                                type="time" onchange="validateDatesDalamKota(<?php echo $i; ?>)">
                        </div>
                    </div>
                </div>
                <div class="round-trip-options" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Return Date</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="tgl_plg_tkt_dalam_kota[]"
                                    type="date" id="tgl_plg_tkt_dalam_kota_<?php echo $i; ?>"
                                    onchange="validateDatesDalamKota(<?php echo $i; ?>)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Return Time</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm"
                                    id="jam_plg_tkt_dalam_kota_<?php echo $i; ?>" name="jam_plg_tkt_dalam_kota[]"
                                    type="time" onchange="validateDatesDalamKota(<?php echo $i; ?>)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label for="ket_tkt_dalam_kota_<?php echo $i; ?>" class="form-label">Information</label>
                        <textarea class="form-control" id="ket_tkt_dalam_kota_<?php echo $i; ?>" name="ket_tkt_dalam_kota[]" rows="3"
                            placeholder="This field is for adding ticket details, e.g., Citilink, Garuda Indonesia, etc."></textarea>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-ticket-btn-dalam-kota"
                        id="remove-ticket-btn-dalam-kota-<?php echo $i; ?>">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-primary add-ticket-btn-dalam-kota"
        id="add-ticket-btn-dalam-kota">Add More</button>
</div>

{{-- </div> --}}
{{-- </div> --}}
<script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('vendor/select2/dist/js/select2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $(".select2-special").each(function() {
            $(this).select2({
                theme: "bootstrap-5",
                width: "100%",
                minimumInputLength: 1, // Trigger search when at least 1 character is entered
                ajax: {
                    url: "/search/name", // URL to your search endpoint
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            searchTerm: params.term // Send search term to server
                        };
                    },
                    processResults: function(data) {
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
    });
</script>
