<div class="row mt-2" id="tiket_div" style="display: none;">
    <div class="col-md-12">
        <div class="table-responsive-sm">
            <div class="d-flex flex-column gap-2" id="ticket_forms_container">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                <div class="ticket-form" id="ticket-form-<?php echo $i; ?>" style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
                    <div class="text-bg-primary p-2" style="text-align:center; border-radius:4px;">Ticket
                        <?php echo $i; ?></div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">NIK</label>
                                    <div class="input-group">
                                        <input class="form-control" name="noktp_tkt[]" type="number"
                                            placeholder="ex: 3521XXXXXXXXXXXX">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">From</label>
                                    <div class="input-group">
                                        <input class="form-control bg-white" name="dari_tkt[]" type="text"
                                            placeholder="ex. Yogyakarta (YIA)">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">To</label>
                                    <div class="input-group">
                                        <input class="form-control bg-white" name="ke_tkt[]" type="text"
                                            placeholder="ex. Jakarta (CGK)">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <label class="form-label">Date</label>
                                    <div class="input-group">
                                        <input class="form-control bg-white" id="tgl_brkt_tkt_<?php echo $i; ?>"
                                            name="tgl_brkt_tkt[]" type="date"
                                            onchange="validateDates(<?php echo $i; ?>)">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time</label>
                                    <div class="input-group">
                                        <input class="form-control bg-white" id="jam_brkt_tkt_<?php echo $i; ?>"
                                            name="jam_brkt_tkt[]" type="time"
                                            onchange="validateDates(<?php echo $i; ?>)">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="ket_tkt" class="form-label">Information</label>
                                <textarea class="form-control" id="ket_tkt_<?php echo $i; ?>" name="ket_tkt[]" rows="3"
                                    placeholder="This field is for adding ticket details, e.g., Citilink, Garuda Indonesia, etc."></textarea>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <label class="form-label" for="jenis_tkt_<?php echo $i; ?>">Transportation
                                        Type</label>
                                    <div class="input-group">
                                        <select class="form-select" name="jenis_tkt[]" id="jenis_tkt">
                                            <option value="">Select
                                                Transportation
                                                Type</option>
                                            <option value="Train">Train
                                            </option>
                                            <option value="Bus">Bus</option>
                                            <option value="Airplane">Airplane
                                            </option>
                                            <option value="Car">Car</option>
                                            <option value="Ferry">Ferry
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="type_tkt_<?php echo $i; ?>" class="form-label">Ticket Type</label>
                                    <select class="form-select" name="type_tkt[]" required>
                                        <option value="One Way">One Way
                                        </option>
                                        <option value="Round Trip">Round Trip
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="round-trip-options" style="display: none;">
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <label class="form-label">Return
                                            Date</label>
                                        <div class="input-group">
                                            <input class="form-control bg-white" name="tgl_plg_tkt[]" type="date"
                                                id="tgl_plg_tkt_<?php echo $i; ?>"
                                                onchange="validateDates(<?php echo $i; ?>)">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Return
                                            Time</label>
                                        <div class="input-group">
                                            <input class="form-control bg-white" id="jam_plg_tkt_<?php echo $i; ?>"
                                                name="jam_plg_tkt[]" type="time"
                                                onchange="validateDates(<?php echo $i; ?>)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($i < 5) : ?>
                            <div class="mt-3">
                                <label class="form-label">Add more
                                    ticket</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                        id="more_tkt_no_<?php echo $i; ?>" name="more_tkt_<?php echo $i; ?>"
                                        value="Tidak" checked>
                                    <label class="form-check-label"
                                        for="more_tkt_no_<?php echo $i; ?>">Tidak</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                        id="more_tkt_yes_<?php echo $i; ?>" name="more_tkt_<?php echo $i; ?>"
                                        value="Ya">
                                    <label class="form-check-label" for="more_tkt_yes_<?php echo $i; ?>">Ya</label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
</div>
</div>
