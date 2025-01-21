    <div id="hotel_div">
        <div class="d-flex flex-column gap-1" id="hotel_forms_container">
            <?php
            $i = 1;
            // for ($i = 1; $i <= 5; $i++) :
            ?>
            <div class="card bg-light shadow-none" id="hotel-form-<?php echo $i; ?>"
                style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
                <div class="card-body">
                    <div class="h5 text-uppercase">
                        <b>Hotel <?php echo $i; ?></b>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Hotel Name</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="nama_htl[]" type="text"
                                    placeholder="ex: Hyatt">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Hotel Location</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="lokasi_htl[]" type="text"
                                    placeholder="ex: Jakarta">
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Bed Size</label>
                            <select class="form-select form-select-sm select2" name="bed_htl[]">
                                <option value="Single Bed">Single Bed</option>
                                <option value="Twin Bed">Twin Bed</option>
                                <option value="King Bed">King Bed</option>
                                <option value="Super King Bed">Super King Bed</option>
                                <option value="Extra Bed">Extra Bed</option>
                                <option value="Baby Cot">Baby Cot</option>
                                <option value="Sofa Bed">Sofa Bed</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Total Room</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="jmlkmr_htl[]" type="number"
                                    min="1" placeholder="ex: 1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Check In Date</label>
                            <input type="date" class="form-control form-control-sm" name="tgl_masuk_htl[]"
                                id="check-in-<?php echo $i; ?>" onchange="calculateTotalDays(<?php echo $i; ?>)">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Check Out Date</label>
                            <input type="date" class="form-control form-control-sm" name="tgl_keluar_htl[]"
                                id="check-out-<?php echo $i; ?>" onchange="calculateTotalDays(<?php echo $i; ?>)">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Nights</label>
                            <input type="number" class="form-control form-control-sm bg-light" name="total_hari[]"
                                id="total-days-<?php echo $i; ?>" readonly>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn">Remove
                            Data</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary add-hotel-btn">Add Hotel
            Data</button>
    </div>
    {{-- </div> --}}
    {{-- </div> --}}
