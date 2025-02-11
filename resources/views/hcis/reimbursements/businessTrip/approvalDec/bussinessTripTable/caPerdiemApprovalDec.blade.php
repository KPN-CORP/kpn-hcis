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