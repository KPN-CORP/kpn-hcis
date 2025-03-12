<div id="mess_div_dalam_kota">
    <div class="d-flex flex-column gap-1" id="mess_forms_container_dalam_kota">
        <?php
        $maxForms = 5;
        $messCount = count($messData);

        // Ensure at least one form is shown if no data exists
        if ($messCount === 0) {
            $messCount = 1;
            $messData = [null]; // Set an empty form data
        }

        for ($i = 1; $i <= $messCount; $i++) :
            $mess = $messData[$i - 1] ?? null;
        ?>
        <div class="card bg-light shadow-none" id="mess-form-dalam-kota-<?php echo $i; ?>"
            style="display: <?php echo $i <= $messCount ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase text-primary">
                    <b>Mess <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Mess Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_mess_dalam_kota[]" type="text"
                                placeholder="ex: Jakarta" value="{{ $mess['lokasi_mess'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_mess_dalam_kota[]" type="number"
                                min="1" placeholder="ex: 1" value="{{ $mess['jmlkmr_mess'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_mess_dalam_kota[]"
                            id="check-in-mess-dalam-kota-<?php echo $i; ?>"
                            value="{{ $mess['tgl_masuk_mess'] ?? '' }}"
                            onchange="calculateTotalDaysMessDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_mess_dalam_kota[]"
                            id="check-out-mess-dalam-kota-<?php echo $i; ?>"
                            value="{{ $mess['tgl_keluar_mess'] ?? '' }}"
                            onchange="calculateTotalDaysMessDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light"
                            name="total_hari_mess_dalam_kota[]" id="total-days-mess-dalam-kota-<?php echo $i; ?>"
                            readonly value="{{ $mess['total_hari_mess'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-mess-btn-dalam-kota">Delete</button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-sm btn-primary add-mess-btn-dalam-kota">Add More</button>
</div>
