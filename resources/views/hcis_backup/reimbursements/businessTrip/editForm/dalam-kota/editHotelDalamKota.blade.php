<div id="hotel_div_dalam_kota">
    <div class="d-flex flex-column gap-1" id="hotel_forms_container_dalam_kota">
        <?php
        $maxForms = 5;
        $hotelCount = count($hotelData); // Use specific data for Dalam Kota

        // Ensure at least one form is shown if no data exists
        if ($hotelCount === 0) {
            $hotelCount = 1;
            $hotelData = [null]; // Set an empty form data
        }

        for ($i = 1; $i <= $hotelCount; $i++) :
            $hotel = $hotelData[$i - 1] ?? null;
        ?>
        <div class="card bg-light shadow-none" id="hotel-form-dalam-kota-<?php echo $i; ?>"
            style="display: <?php echo $i <= $hotelCount ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Hotel <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="nama_htl_dalam_kota[]" type="text"
                                placeholder="ex: Hyatt" value="{{ $hotel['nama_htl'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_htl_dalam_kota[]" type="text"
                                placeholder="ex: Jakarta" value="{{ $hotel['lokasi_htl'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Bed Size</label>
                        <select class="form-select form-select-sm select2" name="bed_htl_dalam_kota[]">
                            <option value="Double Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Double Bed' ? 'selected' : '' }}>
                                Double Bed</option>
                            <option value="Twin Bed"
                                {{ isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Twin Bed' ? 'selected' : '' }}>
                                Twin Bed</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_htl_dalam_kota[]" type="number"
                                min="1" placeholder="ex: 1" value="{{ $hotel['jmlkmr_htl'] ?? '' }}">
                        </div>
                    </div>
                    <div class="sppd-options"
                        style="{{ isset($n->jns_dinas) && $n->jns_dinas === 'dalam kota' && isset($hotel['bed_htl']) && $hotel['bed_htl'] === 'Twin Bed' ? 'display: block;' : 'display: none;' }}">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">No. SPPD for Colleague (If a colleague uses the same
                                    room)</label>
                                <select class="form-select select2 form-select-sm" name="no_sppd_dalam_kota[]">
                                    <option value="-">No Business Trip</option>
                                    @foreach ($bt_sppd as $no_sppd)
                                        <option value="{{ $no_sppd->no_sppd }}"
                                            {{ isset($hotel['no_sppd_htl']) && $hotel['no_sppd_htl'] === $no_sppd->no_sppd ? 'selected' : '' }}>
                                            {{ $no_sppd->no_sppd }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check In Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_masuk_htl_dalam_kota[]"
                            id="check-in-dalam-kota-<?php echo $i; ?>"
                            value="{{ $hotel['tgl_masuk_htl'] ?? '' }}"
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_htl_dalam_kota[]"
                            id="check-out-dalam-kota-<?php echo $i; ?>"
                            value="{{ $hotel['tgl_keluar_htl'] ?? '' }}"
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light"
                            name="total_hari_dalam_kota[]" id="total-days-dalam-kota-<?php echo $i; ?>" readonly
                            value="{{ $hotel['total_hari'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn-dalam-kota"
                        data-form-id="<?php echo $i; ?>">Remove Data</button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary add-hotel-btn-dalam-kota">Add Hotel Data</button>
</div>
