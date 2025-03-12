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