<div class="bg-light p-3 mt-2 mb-2 rounded">
    <h4 class="text-start text-primary mb-3">DETAIL ENTERTAIN</h4>
    <div class="row mb-2">
        <div class="col-md-6 mb-2">
            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                Detail Entertain Plan (Request):</h5>
            <div class="table-responsive table-container bg-white"
                style="height: 300px; overflow-y: auto;">
                <table class="table table-hover table-sm nowrap"
                    id="{{ isset($caDetail['detail_e']) && is_array($caDetail['detail_e']) ? (array_sum(array_column($caDetail['detail_e'], 'nominal')) > 0 ? 'transportTable' : '') : '' }}"
                    width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr style="text-align-last: center;">
                            <th>No</th>
                            <th>Entertainment Type</th>
                            <th>Entertainment Fee Detail</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php $totalDetail = 0; ?>
                        @if (isset($caDetail['detail_e']) &&
                                is_array($caDetail['detail_e']) &&
                                count($caDetail['detail_e']) > 0)
                            @foreach ($caDetail['detail_e'] as $detail)
                                <?php
                                $totalDetail += floatval($detail['nominal'] ?? 0);
                                ?>
                            @endforeach

                            @if ($totalDetail > 0)
                                @foreach ($caDetail['detail_e'] as $detail)
                                    <tr class="text-center">
                                        {{-- <td></td> --}}
                                        <td class="text-center">{{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            @php
                                                $typeMap = [
                                                    'accommodation' => 'Accommodation',
                                                    'food' => 'Food/Beverages/Souvenir',
                                                    'fund' => 'Fund',
                                                    'transport' => 'Transport',
                                                    'gift' => 'Gift',
                                                ];
                                            @endphp
                                            {{ $typeMap[$detail['type']] ?? $detail['type'] }}
                                        </td>
                                        <td>{{ $detail['fee_detail'] ?? '-' }}</td>
                                        <td style="text-align: right">
                                            Rp.
                                            {{ number_format(floatval($detail['nominal'] ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">No data
                                        available</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="3" class="text-center">No data available
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total</strong>
                            </td>
                            <td style="text-align: right">
                                <strong>Rp.
                                    {{ number_format($totalDetail, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                Detail Entertain (Declaration):</h5>
            <div class="table-responsive table-container bg-white"
                style="height: 300px; overflow-y: auto;">
                <table class="table table-hover table-sm nowrap"
                    id="{{ isset($declareCa['detail_e']) && is_array($declareCa['detail_e']) ? (array_sum(array_column($declareCa['detail_e'], 'nominal')) > 0 ? 'transportTableDec' : '') : '' }}"
                    width="100%" cellspacing="0">
                    <thead class="thead-light">
                        {{-- <tr class="bg-primary">
                            <th colspan="3" class="text-center text-white">Transport
                                Plan
                                (Declaration):</th>
                        </tr> --}}
                        <tr style="text-align-last: center;">
                            <tr style="text-align-last: center;">
                                <th>No</th>
                                <th>Entertainment Type</th>
                                <th>Entertainment Fee Detail</th>
                                <th>Amount</th>
                            </tr>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalDetail = 0; ?>
                        @if (isset($declareCa['detail_e']) &&
                                is_array($declareCa['detail_e']) &&
                                count($declareCa['detail_e']) > 0)
                            @foreach ($declareCa['detail_e'] as $detail)
                                <?php
                                $totalDetail += floatval($detail['nominal'] ?? 0);
                                ?>
                            @endforeach

                            @if ($totalDetail > 0)
                                @foreach ($declareCa['detail_e'] as $detail)
                                    <tr class="text-center">
                                        <td class="text-center">{{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            @php
                                                $typeMap = [
                                                    'accommodation' => 'Accommodation',
                                                    'food' => 'Food/Beverages/Souvenir',
                                                    'fund' => 'Fund',
                                                    'transport' => 'Transport',
                                                    'gift' => 'Gift',
                                                ];
                                            @endphp
                                            {{ $typeMap[$detail['type']] ?? $detail['type'] }}
                                        </td>
                                        <td>{{ $detail['fee_detail'] ?? '-' }}</td>
                                        <td style="text-align: right">
                                            Rp.
                                            {{ number_format(floatval($detail['nominal'] ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">No data
                                        available</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="3" class="text-center">No data available
                                </td>
                            </tr>
                        @endif
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total</strong>
                            </td>
                            <td style="text-align: right">
                                <strong>Rp.
                                    {{ number_format($totalDetail, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>