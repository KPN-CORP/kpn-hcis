<div class="d-sm-flex align-items-center justify-content-center">
    <div class="card col-md-8">
        <div class="card-header d-flex bg-primary text-white justify-content-between">
            <h4 class="modal-title" id="viewFormEmployeeLabel">Edit Data {{ $transactions->no_tkt }}</h4>
            <a href="{{ route('ticket') }}" type="button" class="btn btn-close btn-close-white"></a>
        </div>
        <div class="card-body" @style('overflow-y: auto;')>
            <div class="container-fluid">
                <form id="scheduleForm" method="post"
                    action="{{ route('ticket.update', encrypt($transactions->id)) }}">@csrf
                    <div class="row my-2">
                        <div class="col-md-5">
                            <div class="mb-2">
                                <label class="form-label" for="start">Name</label>
                                <input type="text" name="name" id="name"
                                    value="{{ $employee_data->fullname }}" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="mb-2">
                                <label class="form-label" for="start">Unit</label>
                                <input type="text" name="unit" id="unit"
                                    value="{{ $employee_data->unit }}" class="form-control bg-light" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-2">
                                <label class="form-label" for="start">Grade</label>
                                <input type="text" name="grade" id="grade"
                                    value="{{ $employee_data->job_level }}" class="form-control bg-light" readonly>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="col-md-12">
                        <div class="mb-2">
                            <label class="form-label" for="name">Business Trip Number</label>
                            <select class="form-control select2" id="bisnis_numb" name="bisnis_numb">
                                <option value="-">No Business Trip</option>
                                @foreach ($no_sppds as $no_sppd)
                                    <option value="{{ $no_sppd->no_sppd }}">{{ $no_sppd->no_sppd }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row my-2">
                            <label class="form-label" for="jk_tkt">Passengers Name (No KTP)</label>
                            <div class="col-md-2">
                                <div class="mb-2 mr-0">
                                    <select class="form-control" id="jk_tkt" name="jk_tkt" required>
                                        <option value="">-</option>
                                        <option value="Mr"
                                            {{ $transactions->jk_tkt == 'Male' ? 'selected' : '' }}>Mr
                                        </option>
                                        <option value="Mrs"
                                            {{ $transactions->jk_tkt == 'Female' ? 'selected' : '' }}>
                                            Mrs</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <input type="text" name="np_tkt" id="np_tkt" class="form-control"
                                        value="{{ $transactions->np_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <input type="number" name="noktp_tkt" id="noktp_tkt" class="form-control"
                                        value="{{ $transactions->noktp_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label" for="tlp_tkt">Phone Number</label>
                                    <input type="number" name="tlp_tkt" id="tlp_tkt" class="form-control"
                                        maxlength="12" value="{{ $transactions->tlp_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label" for="jenis_tkt">Transportation Type</label>
                                    <select class="form-select" name="jenis_tkt" required>
                                        <option value="Pesawat"
                                            {{ $transactions->jenis_tkt == 'Pesawat' ? 'selected' : '' }}>Pesawat
                                        </option>
                                        <option value="Kereta"
                                            {{ $transactions->jenis_tkt == 'Kereta' ? 'selected' : '' }}>Kereta
                                        </option>
                                        <option value="Bus"
                                            {{ $transactions->jenis_tkt == 'Bus' ? 'selected' : '' }}>Bus</option>
                                        <option value="Ferry"
                                            {{ $transactions->jenis_tkt == 'Ferry' ? 'selected' : '' }}>Ferry
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label" for="dari_tkt">Depature City</label>
                                    <input type="text" name="dari_tkt" id="dari_tkt" class="form-control"
                                        value="{{ $transactions->dari_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label" for="start">Arrival City</label>
                                    <input type="text" name="ke_tkt" id="ke_tkt" class="form-control"
                                        value="{{ $transactions->ke_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <label class="form-label" for="tgl_brkt_tkt">Depature Date</label>
                                    <input type="date" name="tgl_brkt_tkt" id="tgl_brkt_tkt"
                                        class="form-control" value="{{ $transactions->tgl_brkt_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <label class="form-label" for="jam_brkt_tkt">Depature Time</label>
                                    <input type="time" name="jam_brkt_tkt" id="jam_brkt_tkt"
                                        class="form-control" value="{{ $transactions->jam_brkt_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label" for="type_tkt">Ticket Type</label>
                                    <select name="type_tkt" id="type_tkt" class="form-select"
                                        onchange="toggleDivs()">
                                        <option value="One-Way"
                                            {{ $transactions->type_tkt == 'One-Way' ? 'selected' : '' }}>One-Way
                                        </option>
                                        <option value="Round-Trip"
                                            {{ $transactions->type_tkt == 'Round-Trip' ? 'selected' : '' }}>
                                            Round-Trip
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row my-2" id="div_ticket">
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <label class="form-label" for="tgl_plg_tkt">Return Date</label>
                                    <input type="date" name="tgl_plg_tkt" id="tgl_plg_tkt"
                                        class="form-control" value="{{ $transactions->tgl_plg_tkt }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <label class="form-label" for="jam_plg_tkt">Return Time</label>
                                    <input type="time" name="jam_plg_tkt" id="jam_plg_tkt"
                                        class="form-control" value="{{ $transactions->jam_plg_tkt }}">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md d-md-flex justify-content-end text-center">
                                <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">

                                <button type="submit"
                                    class="btn btn-primary rounded-pill shadow px-4">Submit</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
