   <div class="row mt-2" id="hotel_div" style="display: none;">
       <div class="col-md-12">
           <div class="table-responsive-sm">
               <div class="d-flex flex-column gap-2" id="hotel_forms_container">
                   <?php for ($i = 1; $i <= 5; $i++) : ?>
                   <div class="hotel-form" id="hotel-form-<?php echo $i; ?>" style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
                       <div class="text-bg-primary p-2" style="text-align:center; border-radius:4px;">
                           Hotel <?php echo $i; ?>
                       </div>
                       <div class="card">
                           <div class="card-body">
                               <div class="row mb-2">
                                   <div class="col-md-6 mb-2">
                                       <label class="form-label">Hotel
                                           Name</label>
                                       <div class="input-group">
                                           <input class="form-control bg-white" name="nama_htl[]" type="text"
                                               placeholder="ex: Hyatt">
                                       </div>
                                   </div>
                                   <div class="col-md-6 mb-2">
                                       <label class="form-label">Hotel
                                           Location</label>
                                       <div class="input-group">
                                           <input class="form-control bg-white" name="lokasi_htl[]" type="text"
                                               placeholder="ex: Jakarta">
                                       </div>
                                   </div>
                               </div>
                               <div class="row mb-2">
                                   <div class="col-md-9 mb-2">
                                       <label class="form-label">Bed Size</label>
                                       <select class="form-select" name="bed_htl[]" required>
                                           <option value="Single Bed">Single Bed
                                           </option>
                                           <option value="Twin Bed">Twin Bed
                                           </option>
                                           <option value="King Bed">King Bed
                                           </option>
                                           <option value="Super King Bed">Super
                                               King
                                               Bed
                                           </option>
                                           <option value="Extra Bed">Extra Bed
                                           </option>
                                           <option value="Baby Cot">Baby Cot
                                           </option>
                                           <option value="Sofa Bed">Sofa Bed
                                           </option>
                                       </select>
                                   </div>
                                   <div class="col-md-3 mb-2">
                                       <label class="form-label">Total
                                           Room</label>
                                       <div class="input-group">
                                           <input class="form-control bg-white" name="jmlkmr_htl[]" type="number"
                                               min="1" placeholder="ex: 1">
                                       </div>
                                   </div>
                               </div>
                               <div class="row mb-2">
                                   <div class="col-md-5 mb-2">
                                       <label class="form-label">Check In
                                           Date</label>
                                       <input type="date" class="form-control datepicker" name="tgl_masuk_htl[]"
                                           onchange="calculateTotalDays(<?php echo $i; ?>)">
                                   </div>
                                   <div class="col-md-5 mb-2">
                                       <label class="form-label">Check Out
                                           Date</label>
                                       <input type="date" class="form-control datepicker" name="tgl_keluar_htl[]"
                                           onchange="calculateTotalDays(<?php echo $i; ?>)">
                                   </div>
                                   <div class="col-md-2 mb-2">
                                       <label class="form-label">Total
                                           Days</label>
                                       <input type="number" class="form-control datepicker bg-light"
                                           name="total_hari[]" readonly>
                                   </div>
                               </div>
                               <?php if ($i < 5) : ?>
                               <div class="mt-3">
                                   <label class="form-label">Add more
                                       hotel</label>
                                   <div class="form-check">
                                       <input class="form-check-input" type="radio"
                                           id="more_htl_no_<?php echo $i; ?>" name="more_htl_<?php echo $i; ?>"
                                           value="Tidak" checked>
                                       <label class="form-check-label"
                                           for="more_htl_no_<?php echo $i; ?>">Tidak</label>
                                   </div>
                                   <div class="form-check">
                                       <input class="form-check-input" type="radio"
                                           id="more_htl_yes_<?php echo $i; ?>" name="more_htl_<?php echo $i; ?>"
                                           value="Ya">
                                       <label class="form-check-label"
                                           for="more_htl_yes_<?php echo $i; ?>">Ya</label>
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
   {{-- </div> --}}
