@extends('layouts_.vertical', ['page_title' => 'Approval Cash Advanced'])

@section('css')
    <!-- Sertakan CSS Bootstrap jika diperlukan -->
    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/css/bootstrap.min.css"> --}}
@endsection

@section('content')
    <style>
        .table > :not(caption) > * > * {
            padding: 0.4rem 0.4rem; /* Sesuaikan padding di sini */
        }
    </style>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('approval.cashadvanced') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="d-sm-flex align-items-center justify-content-center">
            <div class="card col-md-12">
                <div class="card-header d-flex bg-white justify-content-between">
                    <p></p>
                    <h4 class="modal-title" id="viewFormEmployeeLabel">Approval Cash Advance -
                        <b>"{{ $transactions->no_ca }}"</b></h4>
                    <a href="{{ route('approval.cashadvanced') }}" type="button" class="btn btn-close"></a>
                </div>
                <div class="card-body" @style('overflow-y: auto;')>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <table class="table" style="border: none; border-collapse: collapse; padding: 1%;">
                                    <tr>
                                        <td colspan="3" style="border: none;" class="bg-info"><h4>Employee Data:</h4></td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="width: 20%; border: none;">Name</th>
                                        <td class="colon" style="width: 3%; border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->fullname }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">NIK</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->employee_id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Email</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->email }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Account Details</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->bank_name }} - {{ $transactions->employee->bank_account_number }} - {{ $transactions->employee->bank_account_name}}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Division/Dept</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->unit }} / {{ $transactions->employee->designation_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">PT/Location</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->employee->company_name }} / {{ $transactions->employee->office_area }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-12 mb-2">
                                <table class="table" style="border: none; border-collapse: collapse;">
                                    <tr>
                                        <td colspan="3" style="border: none;"><h4>CA Detail:</h4></td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="width: 20%; border: none;">Costing Company</th>
                                        <td class="colon" style="width: 3%; border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->companies->contribution_level }} ({{ $transactions->contribution_level_code }})</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="width: 20%; border: none;">Destination Location</th>
                                        <td class="colon" style="width: 3%; border: none;">:</td>
                                        <td class="value" style="border: none;">
                                            {{ $transactions->destination == 'Others' ? $transactions->others_location : $transactions->destination }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Date</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($transactions->start_date)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($transactions->end_date)->format('d-M-y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Total Date</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->total_days }} Days</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Date CA Required</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($transactions->date_required)->format('d-M-y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Declaration Estimate</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ \Carbon\Carbon::parse($transactions->declare_estimate)->format('d-M-y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="label" style="border: none;">Purposes</th>
                                        <td class="colon" style="border: none;">:</td>
                                        <td class="value" style="border: none;">{{ $transactions->ca_needs }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <form enctype="multipart/form-data" id="approveForm" method="post" action="{{ route('approval.cashadvancedApproved', $transactions->id) }}">
                            @csrf
                            <div class="row" style="display: none">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" for="type">CA Type</label>
                                    <select name="ca_type_disabled" id="ca_type" class="form-control bg-light" disabled>
                                        <option value="">-</option>
                                        <option value="dns" {{ $transactions->type_ca == 'dns' ? 'selected' : '' }}>
                                            Business Trip
                                        </option>
                                        <option value="ndns" {{ $transactions->type_ca == 'ndns' ? 'selected' : '' }}>
                                            Non Business Trip
                                        </option>
                                        <option value="entr" {{ $transactions->type_ca == 'entr' ? 'selected' : '' }}>
                                            Entertainment
                                        </option>
                                    </select>

                                    <input type="hidden" name="ca_type" value="{{ $transactions->type_ca }}">
                                </div>
                            </div>
                            @php
                                $detailCA = json_decode($transactions->detail_ca, true) ?? [];
                                // $detailCA = json_decode($transactions->declare_ca, true) ?? [];
                            @endphp
                            <script>
                            </script>
                            <br>
                            <div class="row" id="ca_bt" style="display: none;">
                                @if ($transactions->type_ca == 'dns')
                                    <div class="col-md-12">
                                        <div class="table-responsive-sm">
                                            <div class="d-flex flex-row gap-2">
                                                <div class="col-md-12">
                                                    @if (!empty($detailCA['detail_perdiem']) && $detailCA['detail_perdiem'][0]['start_date'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="perdiemTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="7" class="text-center text-white"><b>Perdiem Plan :</b></th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Start Date</th>
                                                                        <th>End Date</th>
                                                                        <th>Location</th>
                                                                        <th>Company Code</th>
                                                                        <th>Total Days</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalPerdiem = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA['detail_perdiem'] as $perdiem)
                                                                        <tr class="text-center">
                                                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}</td>
                                                                            <td>
                                                                                @if ($perdiem['location']=='Others')
                                                                                    {{$perdiem['other_location']}}
                                                                                @else
                                                                                    {{$perdiem['location']}}
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $perdiem['company_code'] }}</td>
                                                                            <td>{{ $perdiem['total_days'] }} Days</td>
                                                                            <td style="text-align: right">Rp. {{ number_format($perdiem['nominal'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalPerdiem += $perdiem['nominal'];
                                                                            $totalDays += $perdiem['total_days'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="5" class="text-right">Total</td>
                                                                            <td class="text-center">{{$totalDays}} Days</td>
                                                                            <td style="text-align: right"> Rp. {{ number_format($totalPerdiem, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @if (!empty($detailCA['detail_transport']) && $detailCA['detail_transport'][0]['tanggal'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="transportTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="5" class="text-center text-white">Transport Plan</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Date</th>
                                                                        <th>Information</th>
                                                                        <th>Company Code</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalTransport = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA['detail_transport'] as $transport)
                                                                        <tr class="text-center">
                                                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($transport['tanggal'])->format('d-M-y') }}</td>
                                                                            <td>
                                                                                {{$transport['keterangan']}}
                                                                            </td>
                                                                            <td>{{ $transport['company_code'] }}</td>
                                                                            <td style="text-align: right">Rp. {{ number_format($transport['nominal'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalTransport += $transport['nominal'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="4" class="text-right">Total</td>
                                                                            <td style="text-align: right"> Rp. {{ number_format($totalTransport, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @if (!empty($detailCA['detail_penginapan']) && $detailCA['detail_penginapan'][0]['start_date'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="penginapanTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="7" class="text-center text-white">Accommodation Plan:</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Start Date</th>
                                                                        <th>End Date</th>
                                                                        <th>Hotel Name</th>
                                                                        <th>Company Code</th>
                                                                        <th>Total Days</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalPenginapan = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA['detail_penginapan'] as $penginapan)
                                                                        <tr style="text-align-last: center;">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($penginapan['start_date'])->format('d-M-y') }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($penginapan['end_date'])->format('d-M-y') }}</td>
                                                                            <td>{{$penginapan['hotel_name']}}</td>
                                                                            <td>{{ $penginapan['company_code'] }}</td>
                                                                            <td>{{$penginapan['total_days']}}</td>
                                                                            <td>Rp. {{ number_format($penginapan['nominal'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalPenginapan += $penginapan['nominal'];
                                                                            $totalDays += $penginapan['total_days'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="5" class="text-right">Total</td>
                                                                            <td class="text-center">{{ $totalDays }}</td>
                                                                            <td class="text-center"> Rp. {{ number_format($totalPenginapan, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @if (!empty($detailCA['detail_lainnya']) && $detailCA['detail_lainnya'][0]['tanggal'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="lainnyaTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="4" class="text-center text-white">Others Plan</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Date</th>
                                                                        <th>Information</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalLainnya = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA['detail_lainnya'] as $lainnya)
                                                                        <tr style="text-align-last: center;">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($lainnya['tanggal'])->format('d-M-y') }}</td>
                                                                            <td>{{$lainnya['keterangan']}}</td>
                                                                            <td style="text-align-last: right;">Rp. {{ number_format($lainnya['nominal'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalLainnya += $lainnya['nominal'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3" class="text-right">Total</td>
                                                                            <td style="text-align: right"> Rp. {{ number_format($totalLainnya, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row" id="ca_nbt" style="display: none;">
                                @if ($transactions->type_ca == 'ndns')
                                    <div class="col-md-12">
                                        <div class="table-responsive-sm">
                                            <div class="d-flex flex-row gap-2">
                                                <div class="col-md-12">
                                                    @if (!empty($detailCA) && $detailCA[0]['tanggal_nbt'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="lainnyaTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="4" class="text-center text-white">Non Bussiness Plan</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Date</th>
                                                                        <th>Information</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalBNT = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA as $lainnya)
                                                                        <tr style="text-align-last: center;">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($lainnya['tanggal_nbt'])->format('d-M-y') }}</td>
                                                                            <td>{{$lainnya['keterangan_nbt']}}</td>
                                                                            <td style="text-align-last: right;">Rp. {{ number_format($lainnya['nominal_nbt'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalBNT += $lainnya['nominal_nbt'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3" class="text-right">Total</td>
                                                                            <td style="text-align: right"> Rp. {{ number_format($totalBNT, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row" id="ca_e" style="display: none;">
                                @if ($transactions->type_ca == 'entr')
                                    <div class="col-md-12">
                                        <div class="table-responsive-sm">
                                            <div class="d-flex flex-row gap-2">
                                                <div class="col-md-12">
                                                    @if (!empty($detailCA['detail_e']) && $detailCA['detail_e'][0]['type'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="lainnyaTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="4" class="text-center text-white">Detail Entertainment Plan</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Entertainment Type</th>
                                                                        <th>Entertainment Fee Detail</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $totalDetail = 0; $totalDays = 0; ?>
                                                                    @foreach ($detailCA['detail_e'] as $detail)
                                                                        <tr style="text-align-last: center;">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>
                                                                                @php
                                                                                    $typeMap = [
                                                                                        'accommodation' => 'Accommodation',
                                                                                        'food' => 'Food/Beverages/Souvenir',
                                                                                        'fund' => 'Fund',
                                                                                        'gift' => 'Gift',
                                                                                        'transport' => 'Transport',
                                                                                    ];
                                                                                @endphp
                                                                                {{ $typeMap[$detail['type']] ?? $detail['type'] }}
                                                                            </td>
                                                                            <td>{{$detail['fee_detail']}}</td>
                                                                            <td style="text-align-last: right;">Rp. {{ number_format($detail['nominal'], 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <?php
                                                                            $totalDetail += $detail['nominal'];
                                                                        ?>
                                                                    @endforeach
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3" class="text-right">Total</td>
                                                                            <td style="text-align: right"> Rp. {{ number_format($totalDetail, 0, ',', '.') }} </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @if (!empty($detailCA['relation_e']) && $detailCA['relation_e'][0]['name'] !== null)
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-sm nowrap" id="penginapanTable" width="100%" cellspacing="0">
                                                                <thead class="thead-light">
                                                                    <tr class="bg-primary">
                                                                        <th colspan="6" class="text-center text-white">Relation Entertainment Plan</th>
                                                                    </tr>
                                                                    <tr style="text-align-last: center;">
                                                                        <th>No</th>
                                                                        <th>Relation Type</th>
                                                                        <th>Name</th>
                                                                        <th>Position</th>
                                                                        <th>Company</th>
                                                                        <th>Purpose</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($detailCA['relation_e'] as $relation)
                                                                        <tr style="text-align-last: center;">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>
                                                                                @php
                                                                                    $relationTypes = [];
                                                                                    $typeMap = [
                                                                                        'Accommodation' => 'Accommodation',
                                                                                        'Food' => 'Food/Beverages/Souvenir',
                                                                                        'Fund' => 'Fund',
                                                                                        'Gift' => 'Gift',
                                                                                        'Transport' => 'Transport',
                                                                                    ];

                                                                                    // Mengumpulkan semua tipe relasi yang berstatus true
                                                                                    foreach($relation['relation_type'] as $type => $status) {
                                                                                        if ($status && isset($typeMap[$type])) {
                                                                                            $relationTypes[] = $typeMap[$type]; // Menggunakan pemetaan untuk mendapatkan deskripsi
                                                                                        }
                                                                                    }
                                                                                @endphp

                                                                                {{ implode(', ', $relationTypes) }} {{-- Menggabungkan tipe relasi yang relevan menjadi string --}}
                                                                            </td>
                                                                            <td>{{ $relation['name'] }}</td>
                                                                            <td>{{$relation['position']}}</td>
                                                                            <td>{{ $relation['company'] }}</td>
                                                                            <td>{{$relation['purpose']}}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                    <tbody>
                                                                        {{-- <tr>
                                                                            <td colspan="5" class="text-right">Total</td>
                                                                            <td class="text-center">{{ $totalDays }}</td>
                                                                            <td class="text-center"> Rp. {{ number_format($totalDetail, 0, ',', '.') }} </td>
                                                                        </tr> --}}
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label">Total Cash Advanced</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input class="form-control bg-light" name="totalca" id="totalca_declarasi"
                                            type="text" min="0" value="{{ number_format($transactions->total_ca, 0, ',', '.') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2" style="display: none">
                                    <label class="form-label">Persetujuan</label>
                                    <select name="approval_status" id="approval_status" class="form-select">
                                        <option value="">-</option>
                                        <option value="Rejected">Rejected</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </div>
                                {{-- {{ dd($transactions) }} --}}
                            </div>
                            </div>
                            <input type="hidden" name="no_id" id="no_id" value="{{ $transactions->id }}"
                                class="form-control bg-light" readonly>
                            <input type="hidden" name="no_ca" id="no_ca" value="{{ $transactions->no_ca }}"
                                class="form-control bg-light" readonly>
                            <input type="hidden" name="bisnis_numb" id="bisnis_numb" value="{{ $transactions->no_sppd }}"
                                class="form-control bg-light" readonly>
                            <br>
                            <div class="row">
                                <div class="p-4 col-md d-md-flex justify-content-end text-center">
                                    <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                                    <a href="{{ route('approval.cashadvanced') }}" type="button"
                                        class="btn mb-2 btn-outline-secondary px-4 me-2">Cancel</a>
                                    <button id="rejectButton" type="button" class="btn mb-2 btn-primary btn-pill px-4 me-2" data-bs-toggle="modal" data-bs-target="#modalReject"
                                            data-no-id="{{ $transactions->id }}"
                                            data-no-ca="{{ $transactions->no_ca }}"
                                            data-start-date="{{ $transactions->start_date }}"
                                            data-end-date="{{ $transactions->end_date }}"
                                            data-total-days="{{ $transactions->total_days }}">
                                            Reject
                                    </button>

                                    <button type="submit" name="action_ca_approve" value="Approve"
                                        class="btn mb-2 btn-success btn-pill px-4 me-2 approve-button"
                                        data-no-id="{{ $transactions->id }}"
                                        data-no-ca="{{ $transactions->no_ca }}"
                                        >
                                        Approve
                                    </button>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('hcis.reimbursements.cashadv.navigation.modalCashadv')
@endsection
<!-- Tambahkan script JavaScript untuk mengumpulkan nilai repeat_days[] -->
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ca_type ca_nbt ca_e
            var ca_type = document.getElementById("ca_type");
            var ca_nbt = document.getElementById("ca_nbt");
            var ca_e = document.getElementById("ca_e");

            function toggleDivs() {
                if (ca_type.value === "dns") {
                    ca_bt.style.display = "block";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                } else if (ca_type.value === "ndns"){
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "block";
                    ca_e.style.display = "none";
                } else if (ca_type.value === "entr"){
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "block";
                } else{
                    ca_bt.style.display = "none";
                    ca_nbt.style.display = "none";
                    ca_e.style.display = "none";
                }
            }

            toggleDivs();
            ca_type.addEventListener("change", toggleDivs);
        });
    </script>
    <script>

        $(document).ready(function() {
            // Check if DataTable is already initialized and destroy it
            if ($.fn.dataTable.isDataTable('#perdiemTable')) {
                $('#perdiemTable').DataTable().destroy();
            }
            if ($.fn.dataTable.isDataTable('#transportTable')) {
                $('#transportTable').DataTable().destroy();
            }
            if ($.fn.dataTable.isDataTable('#penginapanTable')) {
                $('#penginapanTable').DataTable().destroy();
            }
            if ($.fn.dataTable.isDataTable('#lainnyaTable')) {
                $('#lainnyaTable').DataTable().destroy();
            }

            // Initialize DataTable
            $('#perdiemTable').DataTable({
                paging: false,
                info: false,
                searching: false
            });

            $('#transportTable').DataTable({
                paging: false,
                info: false,
                searching: false
            });

            $('#penginapanTable').DataTable({
                paging: false,
                info: false,
                searching: false
            });

            $('#lainnyaTable').DataTable({
                paging: false,
                info: false,
                searching: false
            });
        });

        function previewFile() {
            const fileInput = document.getElementById('prove_declare');
            const file = fileInput.files[0];
            const preview = document.getElementById('existing-file-preview');
            preview.innerHTML = ''; // Kosongkan preview sebelumnya

            if (file) {
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    const img = document.createElement('img');
                    img.style.maxWidth = '200px';
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                } else if (fileExtension === 'pdf') {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(file);
                    link.target = '_blank';
                    const icon = document.createElement('img');
                    icon.src = "https://img.icons8.com/color/48/000000/pdf.png";
                    icon.style.maxWidth = '48px';
                    link.appendChild(icon);
                    const text = document.createElement('p');
                    text.textContent = "Click to view PDF";
                    preview.appendChild(link);
                    preview.appendChild(text);
                } else {
                    preview.textContent = 'File type not supported.';
                }
            }
        }

        const editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const caNumber = this.getAttribute('data-no-ca');
                const idNumber = this.getAttribute('data-no-id');

                document.getElementById('reject_no_ca').textContent = caNumber;
                document.getElementById('reject_no_id').value = idNumber; // Mengisi input no_id
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoOpen') === 'reject') {
                const rejectButton = document.getElementById('rejectButton');
                if (rejectButton) {
                    rejectButton.click();
                }
            }
        });

    </script>
    {{-- <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/popper.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script> --}}
@endpush
