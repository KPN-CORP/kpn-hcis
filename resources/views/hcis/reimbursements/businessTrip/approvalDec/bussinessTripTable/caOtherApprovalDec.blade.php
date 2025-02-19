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
                            <th>Type of Others</th>
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
                                        <td>{{ $lainnya['type'] ?? '-' }}</td>
                                        <td>{{ $lainnya['keterangan'] ?? '-' }}</td>
                                        <td style="text-align-last: right;">Rp.
                                            {{ number_format(floatval($lainnya['nominal'] ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No data
                                        available
                                    </td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="5" class="text-center">No data
                                    available</td>
                            </tr>
                        @endif
                    <tfoot>
                        <td colspan="4" class="text-right"><b>Total</b></td>
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
                            <th>Type of Others</th>
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
                                        <td>{{ $lainnya['type'] ?? '-' }}</td>
                                        <td>{{ $lainnya['keterangan'] ?? '-' }}
                                        </td>
                                        <td style="text-align-last: right;">Rp.
                                            {{ number_format(floatval($lainnya['nominal'] ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No
                                        data
                                        available</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="5" class="text-center">No data
                                    available
                                </td>
                            </tr>
                        @endif
                    <tfoot>
                        <td colspan="4" class="text-right"><b>Total</b></td>
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