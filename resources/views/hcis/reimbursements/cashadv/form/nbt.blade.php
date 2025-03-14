{{-- <script src="{{ asset('/js/cashAdvanced/nonBusinessTrip.js') }}"></script> --}}
@include('js.hcis.cashAdvanced.nonBusinessTrip')

@if (!empty($detailCA) && $detailCA[0]['tanggal_nbt'] !== null)
    <div id="form-container-nonb">
        @foreach ($detailCA as $item)
            <div id="form-container-nbt-{{ $loop->index + 1 }}" class="card-body p-2 mb-2 bg-light rounded-3" >
                <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel {{ $loop->index + 1 }}</p>
                <div id="form-container-nbt-req-{{ $loop->index + 1 }}" class="card-body bg-white rounded-3 p-2 ">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Non Business Travel Request</p>
                    <div id="form-container-nbt-{{ $loop->index + 1 }}" class="card-body bg-white p-2 rounded-3">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_nbt[]" class="form-control" value="{{ $item['tanggal_nbt'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group ">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($item['nominal_nbt'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" >
                                </div>
                            </div>
                            <div class="col-md-12">

                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_nbt[]" class="form-control">{{ $item['keterangan_nbt'] }}</textarea>

                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-2">
        <button class="btn btn-primary btn-sm" id="addMoreButtonNBT" onclick="addMoreFormNBTReq(event)">Add More</button>
    </div>
@else
    <div id="form-container-nonb">
        <div id="form-container-nbt-1" class="card-body p-2 mb-2 bg-light rounded-3">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel 1</p>
            <div id="form-container-nbt-req-1" class="card-body bg-white rounded-3 p-2 ">
                <p class="fs-5 text-primary" style="font-weight: bold;">Non Business Travel Request</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_nbt[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group ">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_1" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" >
                        </div>
                    </div>
                    <div class="col-md-12">

                            <label class="form-label">Information</label>
                            <textarea name="keterangan_nbt[]" class="form-control"></textarea>

                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT(1, event)">Reset</button>
                        <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-2">
        <button class="btn btn-primary btn-sm" id="addMoreButtonNBT" onclick="addMoreFormNBTReq(event)">Add More</button>
    </div>
@endif
