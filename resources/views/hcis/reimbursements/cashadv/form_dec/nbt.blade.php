{{-- <script src="{{ asset('/js/cashAdvanced/nonBusinessTrip.js') }}"></script> --}}
@include('js.hcis.cashAdvanced.nonBusinessTrip')

@if (!empty($detailCA) && $detailCA[0]['nominal_nbt'] !== null)
    <div id="form-container-nonb">
        @foreach ($detailCA as $index => $item)
            <div id="form-container-nbt-{{ $loop->index + 1 }}" class="p-2 mb-2 rounded-3 bg-light">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel {{ $loop->index + 1 }}</p>
                <div id="form-container-nbt-req-{{ $loop->index + 1 }}" class="card-body bg-white p-2 mb-2 rounded-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Non Business Travel Request</p>
                    <div class="row">
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Date</th>
                                    <td class="block">:</td>
                                    <td>{{ $item['tanggal_nbt'] }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td class="block">:</td>
                                    <td> Rp {{ number_format($item['nominal_nbt'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Information</th>
                                    <td class="block">:</td>
                                    <td>{{ $item['keterangan_nbt'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-nbt-dec-{{ $loop->index + 1 }}" class="card-body bg-white p-2 mb-2 rounded-3">
                    <p class="fs-5 text-primary" style="font-weight: bold; ">Non Business Travel Declaration</p>
                    @if (isset($declareCA[$index]))
                        @php
                            $item_dec = $declareCA[$index];
                        @endphp
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_nbt[]" class="form-control" value="{{ $item_dec['tanggal_nbt'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($item_dec['nominal_nbt'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_nbt[]" class="form-control">{{ $item_dec['keterangan_nbt'] }}</textarea>
                                </div>
                        </div>
                    @endif
                    <div class="row mt-2">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT({{ $loop->index + 1 }}, event)">Reset</button>
                            {{-- <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT({{ $loop->index + 1 }}, event)">Delete</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($declareCA  as $index => $item_dec)
            @if (!isset($detailCA [$index]))
                <div id="form-container-nbt-{{ $loop->index + 1 }}" class="card-body p-2 mb-2 rounded-3 bg-light">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-white rounded-3 p-2 mb-2">
                        <p class="fs-5 text-primary" style="font-weight: bold; ">Non Business Travel Declaration</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_nbt[]" class="form-control" value="{{ $item_dec['tanggal_nbt'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($item_dec['nominal_nbt'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_nbt[]" class="form-control">{{ $item_dec['keterangan_nbt'] }}</textarea>
                                </div>
                        </div>
                        <div class="row mt-2">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-2">
        <button class="btn btn-primary btn-sm" id="addMoreButtonNBT" onclick="addMoreFormNBTDec(event)">Add More</button>
    </div>
@elseif (!empty($declareCA) && $declareCA[0]['nominal_nbt'] !== null)
    <div id="form-container-nonb">
        @foreach ($declareCA  as $index => $item_dec)
            @if (!isset($detailCA [$index]))
                <div id="form-container-nbt-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel {{ $loop->index + 1 }}</p>
                    <div class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                        <p class="fs-5 text-primary" style="font-weight: bold; ">Non Business Travel Declaration</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="tanggal_nbt[]" class="form-control" value="{{ $item_dec['tanggal_nbt'] }}" placeholder="mm/dd/yyyy">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_{{ $loop->index + 1 }}" type="text" min="0" value="{{ number_format($item_dec['nominal_nbt'], 0, ',', '.') }}"
                                        onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" onblur="formatOnBlur(this)">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                    <label class="form-label">Information</label>
                                    <textarea name="keterangan_nbt[]" class="form-control">{{ $item_dec['keterangan_nbt'] }}</textarea>

                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT({{ $loop->index + 1 }}, event)">Reset</button>
                                <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT({{ $loop->index + 1 }}, event)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-2">
        <button class="btn btn-primary btn-sm" id="addMoreButtonNBT" onclick="addMoreFormNBTDec(event)">Add More</button>
    </div>
@else
    <div id="form-container-nonb">
        <div id="form-container-nbt-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Non Business Travel 1</p>
            <div id="form-container-nbt-dec-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Non Business Travel Declaration</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_nbt[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_1" type="text" min="0" value="0" onfocus="this.value = this.value === '0' ? '' : this.value;" oninput="formatInputNBT(this)" onblur="formatOnBlur(this)">
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_nbt[]" class="form-control"></textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormNBT(1, event)">Reset</button>
                        <button class="btn btn-outline-danger mr-2 btn-sm" onclick="removeFormNBT(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-1">
        <button class="btn btn-primary btn-sm" id="addMoreButtonNBT" onclick="addMoreFormNBTDec(event)">Add More</button>
    </div>
@endif
