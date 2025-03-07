<div id="hotel_div_dalam_kota">
    <div class="d-flex flex-column gap-1" id="hotel_forms_container_dalam_kota">
        <?php
        $i = 1;
        ?>
        <div class="card bg-light shadow-none" id="hotel-form-dalam-kota-<?php echo $i; ?>"
            style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <div class="h5 text-uppercase">
                    <b>Hotel <?php echo $i; ?></b>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Name</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="nama_htl_dalam_kota[]" type="text"
                                placeholder="ex: Hyatt">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Hotel Location</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="lokasi_htl_dalam_kota[]" type="text"
                                placeholder="ex: Jakarta">
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Bed Size</label>
                        <select class="form-select form-select-sm select2" name="bed_htl_dalam_kota[]"
                            id="bed_size_select_dalam_kota">
                            <option value="Single Bed">Double Bed</option>
                            <option value="Twin Bed">Twin Bed</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">Total Room</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm" name="jmlkmr_htl_dalam_kota[]" type="number"
                                min="1" placeholder="ex: 1">
                        </div>
                    </div>
                    <div class="sppd-options" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-12">
                               <label class="form-label">No. SPPD for Colleague (If a colleague uses the same room)</label>
                                <select class="form-select select2 form-select-sm" name="no_sppd_dalam_kota[]">
                                    <option value="-">No Business Trip</option>
                                    @foreach ($bt_sppd as $no_sppd)
                                        <option value="{{ $no_sppd->no_sppd }}">{{ $no_sppd->no_sppd }}</option>
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
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Check Out Date</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_keluar_htl_dalam_kota[]"
                            id="check-out-dalam-kota-<?php echo $i; ?>"
                            onchange="calculateTotalDaysDalamKota(<?php echo $i; ?>)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Nights</label>
                        <input type="number" class="form-control form-control-sm bg-light"
                            name="total_hari_dalam_kota[]" id="total-days-dalam-kota-<?php echo $i; ?>" readonly>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn-dalam-kota"
                        id="remove-hotel-btn-dalam-kota-<?php echo $i; ?>">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-primary add-hotel-btn-dalam-kota"
        id="add-hotel-btn-dalam-kota">Add More</button>
</div>
