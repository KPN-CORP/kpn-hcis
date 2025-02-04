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