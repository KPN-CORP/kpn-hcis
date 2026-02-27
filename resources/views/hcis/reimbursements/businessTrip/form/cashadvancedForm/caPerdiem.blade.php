<input type="hidden" id="perdiem" value="{{ $perdiem->amount ?? 0 }}">
<input type="hidden" id="group_company" value="{{ $group_company ?? '' }}">
@include('js.hcis.btCashAdvanced.perdiem')



<div id="form-container-perdiem">
    <input id="page-identifier-name" name="page-identifier-name" type="hidden" value="businessTripCAPerdiemForm" disabled>
    @if (!empty($caDetail['detail_perdiem']) && $caDetail['detail_perdiem'][0]['start_date'] !== null)
        @foreach ($caDetail['detail_perdiem'] as $perdiem)
            <div id="form-container-bt-perdiem-{{ $loop->index + 1 }}" class="bg-light rounded-3 card-body p-2 mb-2 perdiem-item">
                <p class="fs-4 text-primary" style="font-weight: bold;">{{ $allowance }} <span class="form-index">{{ $loop->index + 1 }}</span></p>
                <div id="form-container-bt-perdiem-req-{{ $loop->index + 1 }}" class="card-body rounded-3 p-2 bg-white">
                    <p class="fs-5 text-primary" style="font-weight: bold;">{{ $allowance }} Request</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Company Codeeeee</label>
                            <select class="form-control select2" id="company_bt_perdiem_{{ $loop->index + 1 }}" name="company_bt_perdiem[]">
                                <option value="">Select Company...</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->contribution_level_code }}" @if ($company->contribution_level_code == $perdiem['company_code']) selected @endif>
                                        {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Location</label>
                            <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_{{ $loop->index + 1 }}" onchange="toggleOtherLocation(this, {{ $loop->index + 1 }})">
                                <option value="">Select location...</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->area }}" @if($location->area == $perdiem['location']) selected @endif>
                                        {{ $location->area." (".$location->company_name.")" }}
                                    </option>
                                @endforeach
                                <option value="Others" @if('Others' == $perdiem['location']) selected @endif>Others</option>
                            </select>
                            <div id="other-location-{{ $loop->index + 1 }}" class="mt-3" @if($perdiem['location'] != 'Others') style="display: none;" @endif>
                                <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location" value="{{ $perdiem['other_location'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Start {{ $allowance }}</label>
                            <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" value="{{ $perdiem['start_date'] }}" onchange="calculateTotalDaysPerdiem(this)">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">End {{ $allowance }}</label>
                            <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" value="{{ $perdiem['end_date'] }}" onchange="calculateTotalDaysPerdiem(this)">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Days</label>
                            <div class="input-group">
                                <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="{{ $perdiem['total_days'] }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control form-control-sm bg-light per-diem-input" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_{{ $loop->index + 1 }}" type="text" value="{{ number_format($perdiem['nominal'], 0, ',', '.') }}" onchange="onNominalChange()" oninput="formatInput(this)" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormPerdiem({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFormPerdiem({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div id="form-container-bt-perdiem-1" class="bg-light rounded-3 card-body p-2 mb-2 perdiem-item">
            <p class="fs-4 text-primary" style="font-weight: bold;">{{ $allowance }} <span class="form-index">1</span></p>
            <div id="form-container-bt-perdiem-req-1" class="card-body rounded-3 p-2 bg-white">
                <p class="fs-5 text-primary" style="font-weight: bold;">{{ $allowance }} Request</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Company Code</label>
                        <select class="form-control select2" id="company_bt_perdiem_1" name="company_bt_perdiem[]">
                            <option value="">Select Company...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->contribution_level_code }}">
                                    {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Location</label>
                        <select class="form-control select2" name="location_bt_perdiem[]" id="location_bt_perdiem_1" onchange="toggleOtherLocation(this, 1)">
                            <option value="">Select Location...</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->area }}">
                                    {{ $location->area . ' (' . $location->company_name . ')' }}
                                </option>
                            @endforeach
                            <option value="Others">Others</option>
                        </select>
                        <div id="other-location-1" class="mt-3" style="display: none;">
                            <input type="text" name="other_location_bt_perdiem[]" class="form-control" placeholder="Other Location">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Start {{ $allowance }}</label>
                        <input type="date" name="start_bt_perdiem[]" class="form-control form-control-sm start-perdiem" onchange="calculateTotalDaysPerdiem(this)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">End {{ $allowance }}</label>
                        <input type="date" name="end_bt_perdiem[]" class="form-control form-control-sm end-perdiem" onchange="calculateTotalDaysPerdiem(this)">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Total Days</label>
                        <div class="input-group">
                            <input class="form-control form-control-sm bg-light total-days-perdiem" name="total_days_bt_perdiem[]" type="number" value="0" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control form-control-sm bg-light per-diem-input" name="nominal_bt_perdiem[]" id="nominal_bt_perdiem_1" type="text" value="0" onchange="onNominalChange()" oninput="formatInput(this)" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-sm btn-outline-warning" style="margin-right: 10px" onclick="clearFormPerdiem(1, event)">Reset</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFormPerdiem(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="mt-3">
    <button type="button" class="btn btn-primary btn-sm" onclick="window.addMoreFormPerdiemReq(event)">Add More</button>
</div>

<hr/>
<div>
    <label class="form-label">Total {{ $allowance }}</label>
    <div class="input-group">
        <div class="input-group-append">
            <span class="input-group-text">Rp.</span>
        </div>
        <input class="form-control form-control-sm bg-light" name="total_bt_perdiem" id="total_bt_perdiem" type="text" value="{{ isset($caDetail['detail_perdiem']) ? number_format(array_sum(array_column($caDetail['detail_perdiem'], 'nominal')), 0, ',', '.') : '0' }}" readonly>
    </div>
</div>

<script>
window.addMoreFormPerdiemReq = function(event) {
    if(event) event.preventDefault();

    var wrapper = document.getElementById("form-container-perdiem");

    if (!wrapper) {
        alert("Wrapper tidak ditemukan!");
        return;
    }

    var children = wrapper.querySelectorAll(".perdiem-item");
    if(children.length === 0) {
        alert("Form awal tidak ditemukan!");
        return;
    }

    var lastForm = children[children.length - 1];
    var parts = lastForm.id.split("-");
    var lastIndex = parseInt(parts[parts.length - 1]);
    var newIndex = lastIndex + 1;

    var newForm = lastForm.cloneNode(true);

    newForm.id = "form-container-bt-perdiem-" + newIndex;

    var titleSpan = newForm.querySelector(".form-index");
    if(titleSpan) titleSpan.innerText = newIndex;

    var html = newForm.innerHTML;
    var regexId = new RegExp("_" + lastIndex, "g");
    var regexHyphen = new RegExp("-" + lastIndex, "g");
    var regexFunc = new RegExp("\\(" + lastIndex + ",", "g");

    html = html.replace(regexId, "_" + newIndex);
    html = html.replace(regexHyphen, "-" + newIndex);
    html = html.replace(regexFunc, "(" + newIndex + ",");

    newForm.innerHTML = html;

    var inputs = newForm.querySelectorAll("input");
    inputs.forEach(input => {
        if(input.name && input.name.includes("nominal")) {
            input.value = 0;
        } else {
            input.value = "";
        }
    });

    newForm.querySelectorAll(".select2-container").forEach(el => el.remove());

    newForm.querySelectorAll("select").forEach(sel => {
        sel.classList.remove("select2-hidden-accessible");
        sel.removeAttribute("data-select2-id");
        sel.removeAttribute("aria-hidden");
        sel.removeAttribute("tabindex");
        sel.style.display = "block";
        sel.selectedIndex = 0;
    });

    wrapper.appendChild(newForm);

    setTimeout(function() {
        try {
            $(`#company_bt_perdiem_${newIndex}`).select2({ theme: "bootstrap-5" });
            $(`#location_bt_perdiem_${newIndex}`).select2({ theme: "bootstrap-5" });

            var otherLoc = newForm.querySelector(`#other-location-${newIndex}`);
            if(otherLoc) otherLoc.style.display = 'none';

            var locSel = newForm.querySelector(`#location_bt_perdiem_${newIndex}`);
            if(locSel) locSel.setAttribute('onchange', `toggleOtherLocation(this, ${newIndex})`);

        } catch(e) {}
    }, 100);

    if (typeof window.perdiemData === 'undefined') window.perdiemData = [];
    window.perdiemData.push({ index: newIndex.toString(), startDate: "", endDate: "" });

    newForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
};
</script>
