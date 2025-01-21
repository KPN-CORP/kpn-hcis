@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">

    {{-- <style>
        table {
            white-space: nowrap;
        }

        .table-responsive.table-container {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        .table-responsive.table-container table {
            margin-top: 0 !important;
        }

        .table-responsive.table-container thead tr:first-child th {
            border-top: none;
        }
    </style> --}}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('businessTrip.approval') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Declaration Data - {{ $n->no_sppd }}</h4>
                        <a href="{{ route('businessTrip.approval') }}" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="card-body">
                        <form action="/businessTrip/declaration/update/{{ $n->id }}" method="POST" id="btEditForm"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('hcis.reimbursements.businessTrip.modal')
                            <!-- Employee Data Table -->
                            <div class="row">
                                <div class="col-md-6">
                                    <table width="100%" class="">
                                        <tr>
                                            <th width="40%">Employee ID</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Employee Name</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->fullname }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unit</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->unit }}</td>
                                        </tr>
                                        <tr>
                                            <th>Job Level</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->job_level }}</td>
                                        </tr>
                                        <tr>
                                            <th>Designation</th>
                                            <td class="block">:</td>
                                            <td> {{ $employee_data->designation_name }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table width="100%">
                                        <tr>
                                            <th width="40%">Start Date</th>
                                            <td class="block">: </td>
                                            <td width="60%"> {{ date('d M Y', strtotime($n->mulai)) }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td class="block">:</td>
                                            <td> {{ date('d M Y', strtotime($n->kembali)) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Costing Company</th>
                                            <td class="block">:</td>
                                            <td> ({{ $n->bb_perusahaan }})</td>
                                        </tr>
                                        <tr>
                                            <th>Destination</th>
                                            <td class="block">:</td>
                                            <td> {{ $n->tujuan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Purposes</th>
                                            <td class="block">:</td>
                                            <td>{{ $n->keperluan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cash Advance Type</th>
                                            <td class="block">:</td>
                                            <td> Business Trip</td>
                                        </tr>
                                    </table>
                                </div>

                                @php
                                    // Provide default empty arrays if caDetail or sections are not set
                                    $detailPerdiem = $caDetail['detail_perdiem'] ?? [];
                                    $detailTransport = $caDetail['detail_transport'] ?? [];
                                    $detailPenginapan = $caDetail['detail_penginapan'] ?? [];
                                    $detailLainnya = $caDetail['detail_lainnya'] ?? [];
                                @endphp
                                <!-- PERDIEM TABLE -->
                                <div class="bg-light p-3 mt-2 mb-2 rounded">
                                    <h4 class="text-start text-primary mb-3">PERDIEM</h4>
                                    <div class="row mt-2" id="ca_div">
                                        <div class="col-md-6 mb-2">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Perdiem Plan (Request):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($caDetail['detail_perdiem']) && is_array($caDetail['detail_perdiem']) ? (array_sum(array_column($caDetail['detail_perdiem'], 'nominal')) > 0 ? 'perdiemTable' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="8" class="text-center text-white"><b>Perdiem
                                                                    Plan:</b>
                                                            </th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            <th></th>
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
                                                        <?php
                                                        $totalPerdiem = 0;
                                                        $totalDays = 0;
                                                        $hasData = isset($caDetail['detail_perdiem']) && is_array($caDetail['detail_perdiem']);
                                                        $allNominalZero = true; // Flag to check if all nominal values are zero
                                                        ?>

                                                        @if ($hasData)
                                                            @foreach ($caDetail['detail_perdiem'] as $perdiem)
                                                                <?php
                                                                $nominal = floatval($perdiem['nominal'] ?? '0');
                                                                $totalPerdiem += $nominal;
                                                                $totalDays += intval($perdiem['total_days'] ?? '0');

                                                                // Check if any nominal value is not zero
                                                                if ($nominal > 0) {
                                                                    $allNominalZero = false;
                                                                }
                                                                ?>
                                                            @endforeach

                                                            @if ($allNominalZero)
                                                                <tr>
                                                                    <td colspan="8" class="text-center">No data available
                                                                    </td>
                                                                </tr>
                                                            @else
                                                                @foreach ($caDetail['detail_perdiem'] as $perdiem)
                                                                    <tr class="text-center">
                                                                        <td class="text-center"></td>
                                                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($perdiem['start_date']) ? \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ isset($perdiem['end_date']) ? \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>
                                                                            @if (isset($perdiem['location']) && $perdiem['location'] == 'Others')
                                                                                {{ $perdiem['other_location'] ?? '-' }}
                                                                            @else
                                                                                {{ $perdiem['location'] ?? '-' }}
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $perdiem['company_code'] ?? '-' }}</td>
                                                                        <td>{{ $perdiem['total_days'] ?? '-' }} Days</td>
                                                                        <td style="text-align: right">Rp.
                                                                            {{ number_format($perdiem['nominal'] ?? '0', 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="8" class="text-center">No data available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="{{ $hasData && !$allNominalZero ? 4 : 6 }}"
                                                                class="text-right"><b>Total</b></td>
                                                            <td class="text-center"><b>{{ $totalDays }} Days</b></td>
                                                            <td style="text-align: right"><b>Rp.
                                                                    {{ number_format($totalPerdiem, 0, ',', '.') }}</b>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Perdiem Plan (Declaration):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($declareCa['detail_perdiem']) && is_array($declareCa['detail_perdiem']) ? (array_sum(array_column($declareCa['detail_perdiem'], 'nominal')) > 0 ? 'perdiemTableDec' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="8" class="text-center text-white"><b>Perdiem
                                                                    Plan
                                                                    (Declaration):</b></th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            <th></th>
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
                                                        <?php
                                                        $totalPerdiem = 0;
                                                        $totalDays = 0;
                                                        $hasData = isset($declareCa['detail_perdiem']) && is_array($declareCa['detail_perdiem']);
                                                        $allNominalZero = true; // Flag to check if all nominal values are zero
                                                        ?>

                                                        @if ($hasData)
                                                            @foreach ($declareCa['detail_perdiem'] as $perdiem)
                                                                <?php
                                                                $nominal = floatval($perdiem['nominal'] ?? '0');
                                                                $totalPerdiem += $nominal;
                                                                $totalDays += intval($perdiem['total_days'] ?? '0');

                                                                // Check if any nominal value is not zero
                                                                if ($nominal > 0) {
                                                                    $allNominalZero = false;
                                                                }
                                                                ?>
                                                            @endforeach

                                                            @if ($allNominalZero)
                                                                <tr>
                                                                    <td colspan="8" class="text-center">No data available
                                                                    </td>
                                                                </tr>
                                                            @else
                                                                @foreach ($declareCa['detail_perdiem'] as $perdiem)
                                                                    <tr class="text-center">
                                                                        <td class="text-center"></td>
                                                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($perdiem['start_date']) ? \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ isset($perdiem['end_date']) ? \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>
                                                                            @if (isset($perdiem['location']) && $perdiem['location'] == 'Others')
                                                                                {{ $perdiem['other_location'] ?? '-' }}
                                                                            @else
                                                                                {{ $perdiem['location'] ?? '-' }}
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $perdiem['company_code'] ?? '-' }}</td>
                                                                        <td>{{ $perdiem['total_days'] ?? '-' }} Days</td>
                                                                        <td style="text-align: right">Rp.
                                                                            {{ number_format($perdiem['nominal'] ?? '0', 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="8" class="text-center">No data available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="{{ $hasData && !$allNominalZero ? 4 : 6 }}"
                                                                class="text-right"><b>Total</b></td>
                                                            <td class="text-center"><b>{{ $totalDays }} Days</b></td>
                                                            <td style="text-align: right"><b>Rp.
                                                                    {{ number_format($totalPerdiem, 0, ',', '.') }}</b>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- TRANSPORT TABLE --}}

                                <div class="bg-light p-3 mt-2 mb-2 rounded">
                                    <h4 class="text-start text-primary mb-3">TRANSPORT</h4>
                                    <div class="row mb-2">
                                        <div class="col-md-6 mb-2">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Transport Plan (Request):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($caDetail['detail_transport']) && is_array($caDetail['detail_transport']) ? (array_sum(array_column($caDetail['detail_transport'], 'nominal')) > 0 ? 'transportTable' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="5" class="text-center text-white">Transport
                                                                Plan</th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            {{-- <th></th> --}}
                                                            <th>No</th>
                                                            <th>Date</th>
                                                            <th>Information</th>
                                                            <th>Company Code</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php $totalTransport = 0; ?>
                                                        @if (isset($caDetail['detail_transport']) &&
                                                                is_array($caDetail['detail_transport']) &&
                                                                count($caDetail['detail_transport']) > 0)
                                                            @foreach ($caDetail['detail_transport'] as $transport)
                                                                <?php
                                                                $totalTransport += floatval($transport['nominal'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalTransport > 0)
                                                                @foreach ($caDetail['detail_transport'] as $transport)
                                                                    <tr class="text-center">
                                                                        {{-- <td></td> --}}
                                                                        <td class="text-center">{{ $loop->index + 1 }}
                                                                        </td>
                                                                        <td>
                                                                            @if (isset($transport['tanggal']) && $transport['tanggal'])
                                                                                {{ \Carbon\Carbon::parse($transport['tanggal'])->format('d-M-y') }}
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $transport['keterangan'] ?? '-' }}</td>
                                                                        <td>{{ $transport['company_code'] ?? '-' }}</td>
                                                                        <td style="text-align: right">
                                                                            Rp.
                                                                            {{ number_format(floatval($transport['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No data
                                                                        available</td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="5" class="text-center">No data available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="4" class="text-right"><strong>Total</strong>
                                                            </td>
                                                            <td style="text-align: right">
                                                                <strong>Rp.
                                                                    {{ number_format($totalTransport, 0, ',', '.') }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Transport Plan (Declaration):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($declareCa['detail_transport']) && is_array($declareCa['detail_transport']) ? (array_sum(array_column($declareCa['detail_transport'], 'nominal')) > 0 ? 'transportTableDec' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="5" class="text-center text-white">Transport
                                                                Plan
                                                                (Declaration):</th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            <th>No</th>
                                                            <th>Date</th>
                                                            <th>Information</th>
                                                            <th>Company Code</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $totalTransport = 0; ?>
                                                        @if (isset($declareCa['detail_transport']) &&
                                                                is_array($declareCa['detail_transport']) &&
                                                                count($declareCa['detail_transport']) > 0)
                                                            @foreach ($declareCa['detail_transport'] as $transport)
                                                                <?php
                                                                $totalTransport += floatval($transport['nominal'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalTransport > 0)
                                                                @foreach ($declareCa['detail_transport'] as $transport)
                                                                    <tr class="text-center">
                                                                        <td class="text-center">{{ $loop->index + 1 }}
                                                                        </td>
                                                                        <td>
                                                                            @if (isset($transport['tanggal']) && $transport['tanggal'])
                                                                                {{ \Carbon\Carbon::parse($transport['tanggal'])->format('d-M-y') }}
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $transport['keterangan'] ?? '-' }}</td>
                                                                        <td>{{ $transport['company_code'] ?? '-' }}</td>
                                                                        <td style="text-align: right">
                                                                            Rp.
                                                                            {{ number_format(floatval($transport['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No data
                                                                        available</td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="5" class="text-center">No data available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>

                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="4" class="text-right"><strong>Total</strong>
                                                            </td>
                                                            <td style="text-align: right">
                                                                <strong>Rp.
                                                                    {{ number_format($totalTransport, 0, ',', '.') }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ACCOM TABLE --}}
                                <div class="bg-light p-3 mt-2 mb-2 rounded">
                                    <h4 class="text-start text-primary mb-3">ACCOMMODATION</h4>
                                    <div class="row mb-2">
                                        <div class="col-md-6 mb-2">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Accommodation Plan (Request):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap bg-white"
                                                    id="{{ isset($caDetail['detail_penginapan']) && is_array($caDetail['detail_penginapan']) ? (array_sum(array_column($caDetail['detail_penginapan'], 'nominal')) > 0 ? 'penginapanTable' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="7" class="text-center text-white">
                                                                Accommodation
                                                                Plan
                                                            </th>
                                                        </tr> --}}
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
                                                        <?php $totalPenginapan = 0;
                                                        $totalDays = 0; ?>
                                                        @if (isset($caDetail['detail_penginapan']) &&
                                                                is_array($caDetail['detail_penginapan']) &&
                                                                count($caDetail['detail_penginapan']) > 0)
                                                            @foreach ($caDetail['detail_penginapan'] as $penginapan)
                                                                <?php
                                                                $totalPenginapan += floatval($penginapan['nominal'] ?? 0);
                                                                $totalDays += intval($penginapan['total_days'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalPenginapan > 0)
                                                                @foreach ($caDetail['detail_penginapan'] as $penginapan)
                                                                    <tr style="text-align-last: center;">
                                                                        <td>{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($penginapan['start_date']) ? \Carbon\Carbon::parse($penginapan['start_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ isset($penginapan['end_date']) ? \Carbon\Carbon::parse($penginapan['end_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ $penginapan['hotel_name'] ?? '-' }}</td>
                                                                        <td>{{ $penginapan['company_code'] ?? '-' }}
                                                                        </td>
                                                                        <td>{{ $penginapan['total_days'] ?? '-' }} Days
                                                                        </td>
                                                                        <td>Rp.
                                                                            {{ number_format(floatval($penginapan['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="7" class="text-center">No data
                                                                        available
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="7" class="text-center">No data
                                                                    available</td>
                                                            </tr>
                                                        @endif
                                                    <tfoot>
                                                        <td colspan="5" class="text-right"><b>Total</b></td>
                                                        <td class="text-center"><b>{{ $totalDays }} Days</b></td>
                                                        <td class="text-center"><b>Rp.
                                                                {{ number_format($totalPenginapan, 0, ',', '.') }}</b></td>
                                                    </tfoot>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Accommodation Plan (Declaration):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">

                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($declareCa['detail_penginapan']) && is_array($declareCa['detail_penginapan']) ? (array_sum(array_column($declareCa['detail_penginapan'], 'nominal')) > 0 ? 'penginapanTableDec' : '') : '' }}"
                                                    width="100%" cellspacing="0">

                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="7" class="text-center text-white">
                                                                Accommodation Plan
                                                                (Declaration):</th>
                                                        </tr> --}}
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
                                                        <?php $totalPenginapan = 0;
                                                        $totalDays = 0; ?>
                                                        @if (isset($declareCa['detail_penginapan']) &&
                                                                is_array($declareCa['detail_penginapan']) &&
                                                                count($declareCa['detail_penginapan']) > 0)
                                                            @foreach ($declareCa['detail_penginapan'] as $penginapan)
                                                                <?php
                                                                $totalPenginapan += floatval($penginapan['nominal'] ?? 0);
                                                                $totalDays += intval($penginapan['total_days'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalPenginapan > 0)
                                                                @foreach ($declareCa['detail_penginapan'] as $penginapan)
                                                                    <tr style="text-align-last: center;">
                                                                        <td>{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($penginapan['start_date']) ? \Carbon\Carbon::parse($penginapan['start_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ isset($penginapan['end_date']) ? \Carbon\Carbon::parse($penginapan['end_date'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ $penginapan['hotel_name'] ?? '-' }}
                                                                        </td>
                                                                        <td>{{ $penginapan['company_code'] ?? '-' }}
                                                                        </td>
                                                                        <td>{{ $penginapan['total_days'] ?? '-' }}
                                                                            Days
                                                                        </td>
                                                                        <td>Rp.
                                                                            {{ number_format(floatval($penginapan['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="7" class="text-center">No data
                                                                        available</td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="7" class="text-center">No data
                                                                    available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    <tfoot>
                                                        <td colspan="5" class="text-right"><b>Total</b></td>
                                                        <td class="text-center"><b>{{ $totalDays }} Days</b></td>
                                                        <td class="text-center"><b>Rp.
                                                                {{ number_format($totalPenginapan, 0, ',', '.') }}</b></td>
                                                    </tfoot>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- OTHERS TABLE --}}
                                <div class="bg-light p-3 mt-2 mb-2 rounded">
                                    <h4 class="text-start text-primary mb-3">OTHERS</h4>
                                    <div class="row mb-2">
                                        <div class="col-md-6 mb-2">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Others Plan (Request):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($caDetail['detail_lainnya']) && is_array($caDetail['detail_lainnya']) ? (array_sum(array_column($caDetail['detail_lainnya'], 'nominal')) > 0 ? 'otherTable' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="4" class="text-center text-white">Others
                                                                Plan</th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            <th>No</th>
                                                            <th>Date</th>
                                                            <th>Information</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $totalLainnya = 0; ?>
                                                        @if (isset($caDetail['detail_lainnya']) &&
                                                                is_array($caDetail['detail_lainnya']) &&
                                                                count($caDetail['detail_lainnya']) > 0)
                                                            @foreach ($caDetail['detail_lainnya'] as $lainnya)
                                                                <?php
                                                                $totalLainnya += floatval($lainnya['nominal'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalLainnya > 0)
                                                                @foreach ($caDetail['detail_lainnya'] as $lainnya)
                                                                    <tr style="text-align-last: center;">
                                                                        <td>{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($lainnya['tanggal']) ? \Carbon\Carbon::parse($lainnya['tanggal'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ $lainnya['keterangan'] ?? '-' }}</td>
                                                                        <td style="text-align-last: right;">Rp.
                                                                            {{ number_format(floatval($lainnya['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="4" class="text-center">No data
                                                                        available
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="4" class="text-center">No data
                                                                    available</td>
                                                            </tr>
                                                        @endif
                                                    <tfoot>
                                                        <td colspan="3" class="text-right"><b>Total</b></td>
                                                        <td style="text-align: right"><b>Rp.
                                                                {{ number_format($totalLainnya, 0, ',', '.') }}</b></td>
                                                    </tfoot>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                                                Others Plan (Declaration):</h5>
                                            <div class="table-responsive table-container bg-white"
                                                style="height: 300px; overflow-y: auto;">
                                                <table class="table table-hover table-sm nowrap"
                                                    id="{{ isset($declareCa['detail_lainnya']) && is_array($declareCa['detail_lainnya']) ? (array_sum(array_column($declareCa['detail_lainnya'], 'nominal')) > 0 ? 'otherTableDec' : '') : '' }}"
                                                    width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        {{-- <tr class="bg-primary">
                                                            <th colspan="4" class="text-center text-white">
                                                                Others Plan
                                                                (Declaration):</th>
                                                        </tr> --}}
                                                        <tr style="text-align-last: center;">
                                                            <th>No</th>
                                                            <th>Date</th>
                                                            <th>Information</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $totalLainnya = 0; ?>
                                                        @if (isset($declareCa['detail_lainnya']) &&
                                                                is_array($declareCa['detail_lainnya']) &&
                                                                count($declareCa['detail_lainnya']) > 0)
                                                            @foreach ($declareCa['detail_lainnya'] as $lainnya)
                                                                <?php
                                                                $totalLainnya += floatval($lainnya['nominal'] ?? 0);
                                                                ?>
                                                            @endforeach

                                                            @if ($totalLainnya > 0)
                                                                @foreach ($declareCa['detail_lainnya'] as $lainnya)
                                                                    <tr style="text-align-last: center;">
                                                                        <td>{{ $loop->index + 1 }}</td>
                                                                        <td>{{ isset($lainnya['tanggal']) ? \Carbon\Carbon::parse($lainnya['tanggal'])->format('d-M-y') : '-' }}
                                                                        </td>
                                                                        <td>{{ $lainnya['keterangan'] ?? '-' }}
                                                                        </td>
                                                                        <td style="text-align-last: right;">Rp.
                                                                            {{ number_format(floatval($lainnya['nominal'] ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="4" class="text-center">No
                                                                        data
                                                                        available</td>
                                                                </tr>
                                                            @endif
                                                        @else
                                                            <tr>
                                                                <td colspan="4" class="text-center">No data
                                                                    available
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    <tfoot>
                                                        <td colspan="3" class="text-right"><b>Total</b></td>
                                                        <td style="text-align: right"><b>Rp.
                                                                {{ number_format($totalLainnya, 0, ',', '.') }}</b>
                                                        </td>
                                                    </tfoot>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                // Provide default empty arrays if any section is not set
                                $detailPerdiem = $caDetail['detail_perdiem'] ?? [];
                                $detailTransport = $caDetail['detail_transport'] ?? [];
                                $detailPenginapan = $caDetail['detail_penginapan'] ?? [];
                                $detailLainnya = $caDetail['detail_lainnya'] ?? [];

                                $formattedTotalCashAdvanced = number_format($ca->total_ca ?? 0, 0, ',', '.');
                                $formattedTotalReal = number_format($ca->total_real ?? 0, 0, ',', '.');
                                $formattedTotalCost = number_format($ca->total_cost ?? 0, 0, ',', '.');

                            @endphp
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Total Cash Advanced</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input class="form-control bg-light" name="totalca_deklarasi"
                                            id="totalca_deklarasi" type="text" min="0"
                                            value="{{ $formattedTotalCashAdvanced }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Total Declaration</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input class="form-control bg-light" name="totalca_deklarasi"
                                            id="totalca_deklarasi" type="text" min="0"
                                            value="{{ $formattedTotalReal }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Total Cost</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input class="form-control bg-light" name="totalca_deklarasi"
                                            id="totalca_deklarasi" type="text" min="0"
                                            value="{{ $formattedTotalCost }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="no_sppd" value="{{ $n->no_sppd }}">
                            @php
                                $detailPerdiem2 = $declareCa['detail_perdiem'] ?? [];
                                $detailTransport2 = $declareCa['detail_transport'] ?? [];
                                $detailPenginapan2 = $declareCa['detail_penginapan'] ?? [];
                                $detailLainnya2 = $declareCa['detail_lainnya'] ?? [];
                            @endphp
                        </form>
                        @php
                            use Illuminate\Support\Facades\Storage;
                        @endphp
                        <label for="" class="form-label">Uploaded Proof</label>
                        @if (isset($ca->prove_declare) && $ca->prove_declare)
                            @php
                                // Get the file extension
                                $fileExtension = pathinfo($ca->prove_declare, PATHINFO_EXTENSION);
                                // Set the image based on the file type
                                $imageSrc = '';
                                if (in_array($fileExtension, ['pdf'])) {
                                    $imageSrc = 'https://img.icons8.com/color/48/000000/pdf.png'; // Replace with the path to your PDF icon
                                } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $imageSrc = Storage::url($ca->prove_declare); // Image files should display their own thumbnail
                                } else {
                                    $imageSrc = 'https://img.icons8.com/color/48/000000/file.png'; // Replace with the path to your default icon
                                }
                            @endphp
                            <div class="file-preview text-left">
                                <a href="{{ Storage::url($ca->prove_declare) }}" target="_blank"
                                    style="text-decoration: none;">
                                    <img src="{{ $imageSrc }}" alt="{{ $fileExtension }} file" class="file-icon"
                                        style="width: 50px; height: 50px;">
                                    <div style="margin-top: 5px;"><u>View Proof</u></div>
                                </a>
                            @else
                                <div class="text-danger">No proof uploaded</div>
                        @endif
                        <div class="d-flex justify-content-end mt-3">
                            <!-- Decline Form -->
                            <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#rejectReasonModal" style="padding: 0.5rem 1rem; margin-right: 5px">
                                Reject
                            </button>

                            <form method="POST" action="{{ route('confirm.deklarasi', ['id' => $n->id]) }}"
                                style="display: inline-block; margin-right: 5px;" class="status-form"
                                id="approve-form-{{ $n->id }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_approval"
                                    value="{{ Auth::user()->id == $n->manager_l1_id ? 'Pending L2' : 'Declaration Approved' }}">
                                <button type="button" class="btn btn-success rounded-pill approve-button"
                                    style="padding: 0.5rem 1rem;" data-id="{{ $n->id }}">
                                    Approve
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- </div> --}}

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title" id="rejectReasonModalLabel" style="color: #333; font-weight: 600;">
                        Rejection
                        Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="rejectReasonForm" method="POST"
                        action="{{ route('confirm.deklarasi', ['id' => $n->id]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_approval" value="Declaration Rejected">

                        <div class="mb-3">
                            <label for="reject_info" class="form-label" style="color: #555; font-weight: 500;">Please
                                provide a reason for rejection:</label>
                            <textarea class="form-control border-2" name="reject_info" id="reject_info" rows="4" required
                                style="resize: vertical; min-height: 100px;"></textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-primary rounded-pill me-2"
                                data-bs-dismiss="modal" style="min-width: 100px;">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill"
                                style="min-width: 100px;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light rounded-4 border-0 shadow" style="border-radius: 1rem;">
                <div class="modal-body text-center p-5" style="padding: 2rem;">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 100px; color: #AB2F2B !important;"></i>
                    </div>
                    <h4 class="mb-3 fw-bold" style="font-size: 32px; color: #AB2F2B !important;">Success!</h4>
                    <p class="mb-4" id="successModalBody" style="font-size: 20px;">
                        <!-- The success message will be inserted here -->
                    </p>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        //JS TABLE
        $(document).ready(function() {
            var tableIds = [
                '#transportTable',
                '#transportTableDec',
                '#otherTable',
                '#otherTableDec',
                '#penginapanTable',
                '#penginapanTableDec'
            ];

            // Loop through each table ID
            $.each(tableIds, function(index, tableId) {
                // Check if DataTable is already initialized and destroy it
                if ($.fn.dataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy();
                }

                // Initialize DataTable
                $(tableId).DataTable({
                    paging: false,
                    info: false,
                    searching: false,
                    autoWidth: false,
                });
            });
        });


        var tableIdPerdiem = [
            '#perdiemTable',
            '#perdiemTableDec',
        ];
        tableIdPerdiem.forEach(function(id) {

            $(id).DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr',
                    },
                },
                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        responsivePriority: 1,
                        targets: 0,
                        visible: true
                    }, // Ensure the No column is visible
                    {
                        responsivePriority: 2,
                        targets: -1
                    }
                ],
                order: [1, 'asc'],
                info: false,
                paging: false,
                searching: false,
            });
        });
        // $('#otherTableDec').DataTable({
        //     paging: false,
        //     info: false,
        //     searching: false,
        //     ordering: true, // Enable sorting if needed
        //     autoWidth: false, // Prevent automatic column width adjustment
        // });


        // function confirmSubmission(event) {
        //     event.preventDefault(); // Stop the form from submitting immediately

        //     // Display a confirmation alert
        //     const userConfirmed = confirm("Are you sure you want to approve this request?");

        //     if (userConfirmed) {
        //         // If the user confirms, submit the form
        //         event.target.closest('form').submit();
        //     } else {
        //         // If the user cancels, do nothing
        //         alert("Approval cancelled.");
        //     }
        // }
        document.getElementById('rejectReasonForm').addEventListener('submit', function(event) {
            const reason = document.getElementById('reject_info').value.trim();
            if (!reason) {
                alert('Please provide a reason for rejection.');
                event.preventDefault(); // Stop form submission if no reason is provided
            }
        });

        // Add event listener to the decline button to open the modal
        document.getElementById('declineButton').addEventListener('click', function() {
            $('#rejectReasonModal').modal('show');
        });
    </script>
@endsection
