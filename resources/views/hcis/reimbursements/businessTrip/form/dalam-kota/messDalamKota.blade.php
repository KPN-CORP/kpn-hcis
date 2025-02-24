<div id="mess_div_dalam_kota">
    <div class="d-flex flex-column gap-1" id="mess_forms_container_dalam_kota">
        <?php
        $i = 1;
        ?>
        <div class="card bg-light shadow-none" id="mess-form-dalam-kota-<?php echo $i; ?>"
            style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Mess <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Mess Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_mess_dalam_kota[]" type="text"
                                placeholder="ex: Jakarta">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_mess_dalam_kota[]" type="number"
                                min="1" placeholder="ex: 1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_mess_dalam_kota[]"
                            id="check-in-dalam-kota-<?php echo $i; ?>"
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_mess_dalam_kota[]"
                            id="check-out-dalam-kota-<?php echo $i; ?>"
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light"
                            name="total_hari_mess_dalam_kota[]" id="total-days-dalam-kota-<?php echo $i; ?>" readonly>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-mess-btn-dalam-kota">Remove
                        Data</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary add-mess-btn-dalam-kota">Add Mess Data</button>
</div>
