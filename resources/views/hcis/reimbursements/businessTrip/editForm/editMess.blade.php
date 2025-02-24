<div id="mess_div">
    <div class="d-flex flex-column gap-1" id="mess_forms_container">
        <?php
        $maxForms = 5; // Optional: limit the number of forms, adjust as needed
        $messCount = count($messData);

        // Ensure at least one form is shown if no data exists
        if ($messCount === 0) {
            $messCount = 1;
            $messData = [null]; // Set an empty form data
        }

        for ($i = 1; $i <= $messCount; $i++) :
            $mess = $messData[$i - 1] ?? null;
        ?>
        <div class="card bg-light shadow-none" id="mess-form-<?php echo $i; ?>" style="display: <?php echo $i <= $messCount ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Mess <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Mess Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_mess[]" type="text"
                                placeholder="ex: Jakarta" value="<?php echo $mess['lokasi_mess'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_mess[]" type="number"
                                min="1" placeholder="ex: 1" value="<?php echo $mess['jmlkmr_mess'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_mess[]"
                            id="check-in-mess-<?php echo $i; ?>" value="<?php echo $mess['tgl_masuk_mess'] ?? ''; ?>"
                            onchange="calculateTotalDaysMess(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_mess[]"
                            id="check-out-mess-<?php echo $i; ?>" value="<?php echo $mess['tgl_keluar_mess'] ?? ''; ?>"
                            onchange="calculateTotalDaysMess(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light" name="total_hari_mess[]"
                            id="total-days-mess-<?php echo $i; ?>" readonly value="<?php echo $mess['total_hari_mess'] ?? ''; ?>">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-mess-btn">Remove
                        Data</button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary add-mess-btn">Add Mess
        Data</button>
</div>
