<div id="mess_div">
    <div class="d-flex flex-column gap-1" id="mess_forms_container">
        <?php
        $i = 1;
        ?>
        <div class="card bg-light shadow-none" id="mess-form-<?php echo $i; ?>" style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Mess <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Mess Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_mess[]" type="text"
                                placeholder="ex: Jakarta">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_mess[]" type="number"
                                min="1" placeholder="ex: 1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_mess[]"
                            id="check-in-<?php echo $i; ?>" onchange="calculateTotalDays(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_mess[]"
                            id="check-out-<?php echo $i; ?>" onchange="calculateTotalDays(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light" name="total_hari[]"
                            id="total-days-<?php echo $i; ?>" readonly>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-mess-btn">Remove
                        Data</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary add-mess-btn">Add Mess
        Data</button>
</div>
