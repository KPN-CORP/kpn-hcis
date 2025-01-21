<div id="hotel_div">
    <div class="d-flex flex-column gap-1" id="hotel_forms_container">
        <?php
        $maxForms = 5;
        $hotelCount = count($hotelData); // Assuming $hotelData contains hotel data from the controller

        // Ensure at least one form is shown if no data exists
        if ($hotelCount === 0) {
            $hotelCount = 1;
            $hotelData = [null]; // Set an empty form data
        }

        for ($i = 1; $i <= $hotelCount; $i++) :
            $hotel = $hotelData[$i - 1] ?? null;
        ?>
        <div class="card bg-light shadow-none" id="hotel-form-<?php echo $i; ?>" style="display: <?php echo $i <= $hotelCount ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Hotel <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="nama_htl[]" type="text"
                                placeholder="ex: Hyatt" value="{{ $hotel['nama_htl'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_htl[]" type="text"
                                placeholder="ex: Jakarta" value="{{ $hotel['lokasi_htl'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Bed Size</label>
                        <select class="form-select form-select-sm select2" name="bed_htl[]">
                            <option value="Single Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Single Bed' ? 'selected' : '' }}>
                                Single Bed</option>
                            <option value="Twin Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Twin Bed' ? 'selected' : '' }}>
                                Twin Bed</option>
                            <option value="King Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'King Bed' ? 'selected' : '' }}>
                                King Bed</option>
                            <option value="Super King Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Super King Bed' ? 'selected' : '' }}>
                                Super King Bed</option>
                            <option value="Extra Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Extra Bed' ? 'selected' : '' }}>
                                Extra Bed</option>
                            <option value="Baby Cot"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Baby Cot' ? 'selected' : '' }}>
                                Baby Cot</option>
                            <option value="Sofa Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Sofa Bed' ? 'selected' : '' }}>
                                Sofa Bed</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_htl[]" type="number"
                                min="1" placeholder="ex: 1" value="{{ $hotel['jmlkmr_htl'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_htl[]"
                            id="check-in-<?php echo $i; ?>" value="{{ $hotel['tgl_masuk_htl'] ?? '' }}"
                            onchange="calculateTotalDays(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_htl[]"
                            id="check-out-<?php echo $i; ?>" value="{{ $hotel['tgl_keluar_htl'] ?? '' }}"
                            onchange="calculateTotalDays(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light" name="total_hari[]"
                            id="total-days-<?php echo $i; ?>" readonly value="{{ $hotel['total_hari'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn"
                        data-form-id="<?php echo $i; ?>">Remove Data</button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary add-hotel-btn">Add Hotel Data</button>
</div>
