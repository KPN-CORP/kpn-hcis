<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <div style="width: 100%; height: auto; text-align: center;">
        <img src="{{ $logoBase64 }}" 
             alt="Kop Surat" 
             style="height: auto; margin-bottom: 20px; width: 15%;">
    </div>               
    <h2>Declaration Cash Advanced Reminder</h2>

    <p>Dear : Bapak/Ibu <strong>{{ $ca_transaction->employee->fullname }}</strong></p>

    <p>{{ $textNotification }}</p>

    @php
        $detailCA = json_decode($ca_transaction->detail_ca, true);
        $declareCA = json_decode($ca_transaction->declare_ca, true);
    @endphp

    @if ($ca_transaction)
        <table>
            <tr>
                <td>No Dokumen</td>
                <td>:</td>
                <td>{{ $ca_transaction->no_ca }}</td>
            </tr>
            <tr>
                <td>Name</td>
                <td>:</td>
                <td>{{ $ca_transaction->employee->fullname }}</td>
            </tr>
            <tr>
                <td>Start Date</td>
                <td>:</td>
                <td>{{ $ca_transaction->start_date }}</td>
            </tr>
            <tr>
                <td>End Date</td>
                <td>:</td>
                <td>{{ $ca_transaction->end_date }}</td>
            </tr>
            <tr>
                <td>Destination</td>
                <td>:</td>
                <td>{{ $ca_transaction->destination == 'Others' ? $ca_transaction->others_location : $ca_transaction->destination }}</td>
            </tr>
            <tr>
                <td>Purpose</td>
                <td>:</td>
                <td>{{ $ca_transaction->ca_needs }}</td>
            </tr>
            <tr>
                <td>PT</td>
                <td>:</td>
                <td>{{ $ca_transaction->companies->contribution_level }} ({{ $ca_transaction->contribution_level_code }})</td>
            </tr>
            <tr>
                <td>CA Type</td>
                <td>:</td>
                <td>
                    @if($ca_transaction->type_ca === 'entr')
                        Entertaiment
                    @elseif($ca_transaction->type_ca === 'dns')
                        Business Trip
                    @elseif($ca_transaction->type_ca === 'ndns')
                        Non Business Trip
                    @else
                        Unknown Type
                    @endif
                </td>
            </tr>
        </table>
        <br>

        @if($ca_transaction->type_ca === 'entr')
            @if ($declaration == "Declaration")
                <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="5" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Plan</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Declaration</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Detail Entertain</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA['detail_e'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;" colspan="2">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"></td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_real, 0, ',', '.' )}}</td>
                    </tr>
                </table>
            @else
            <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="3" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">>
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Detail Entertain</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}</td>
                    </tr>
                </table>
            @endif
        @elseif($ca_transaction->type_ca === 'dns')
            @if ($declaration == "Declaration")
                @if (count($declareCA['detail_perdiem']) > 0 && !empty($declareCA['detail_perdiem'][0]['company_code']))
                    <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                        <tr>
                            <th colspan="5" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                                <b>Perdiem Plan :</b>
                            </th>
                        </tr>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Start Date</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">End Date</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Office Location</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Company Code</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Total Days</td>
                        </tr>
                    
                        @foreach($declareCA['detail_perdiem'] as $perdiem)
                        <tr style="text-align: center;">
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                {{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                {{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                @if ($perdiem['location'] == 'Others')
                                    Other ({{$perdiem['other_location']}})
                                @else
                                    {{$perdiem['location']}}
                                @endif
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $perdiem['company_code'] }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $perdiem['total_days'] }} Hari</td>
                        </tr>
                        @endforeach
                    
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="4" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                {{ array_sum(array_column($declareCA['detail_perdiem'], 'total_days')) }} Hari
                            </td>
                        </tr>
                    </table>  
                @endif
                <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="5" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Plan</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Declaration</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Perdiem</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Days
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($declareCA['detail_perdiem'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($declareCA['detail_perdiem'], 'total_days')) }} Days
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Transport</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_transport'], 'nominal')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA['detail_transport'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Accomodation</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) }} Night
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($declareCA['detail_penginapan'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($declareCA['detail_penginapan'], 'total_days')) }} Night
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Others</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"></td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format($ca_transaction->total_real, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>                    
            @else
                @if (count($detailCA['detail_perdiem']) > 0 && !empty($detailCA['detail_perdiem'][0]['company_code']))
                    <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                        <tr>
                            <th colspan="5" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                                <b>Perdiem Plan :</b>
                            </th>
                        </tr>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Start Date</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">End Date</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Office Location</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Company Code</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        </tr>
                    
                        @foreach($detailCA['detail_perdiem'] as $perdiem)
                        <tr style="text-align: center;">
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                {{ \Carbon\Carbon::parse($perdiem['start_date'])->format('d-M-y') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                {{ \Carbon\Carbon::parse($perdiem['end_date'])->format('d-M-y') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                @if ($perdiem['location'] == 'Others')
                                    Other ({{$perdiem['other_location']}})
                                @else
                                    {{$perdiem['location']}}
                                @endif
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $perdiem['company_code'] }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">{{ $perdiem['total_days'] }} Hari</td>
                        </tr>
                        @endforeach
                    
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="4" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Hari
                            </td>
                        </tr>
                    </table>  
                    <br><br>                  
                @endif
                <table style="border-collapse: collapse; width: 50%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="3" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Perdiem</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($detailCA['detail_perdiem'], 'total_days')) }} Days
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_perdiem'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Transport</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_transport'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Accomodation</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            @if (array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) <= 0)
                                -
                            @else
                                {{ array_sum(array_column($detailCA['detail_penginapan'], 'total_days')) }} Night
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_penginapan'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Others</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">-</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA['detail_lainnya'], 'nominal')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                            Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}
                        </td>
                    </tr>
                </table> 
                <br>                   
            @endif
        @elseif($ca_transaction->type_ca === 'ndns')
            @if ($declaration == "Declaration")
                <table style="border-collapse: collapse; width: 70%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="5" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <<tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Plan</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Estimate Declaration</td>
                    </tr>
                    <<tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Non Bussiness Trip</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($declareCA, 'nominal_nbt')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;"></td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_real, 0, ',', '.') }}</td>
                    </tr>
                </table>
            @else
            <table style="border-collapse: collapse; width: 50%; margin-top: 8px; font-size: 10px;">
                    <tr>
                        <th colspan="3" style="border: 1px solid #ddd; padding: 4px; background-color: #ab2f2b; color: #ffffff; font-size: 10px; font-weight: bold; white-space: nowrap; text-align: center;">
                            <b>Detail Cash Advanced :</b>
                        </th>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td rowspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Types of Down Payments</td>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Estimate</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Total Days</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Amount</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Non Bussiness Trip</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">
                            {{ $ca_transaction->total_days }} Days
                        </td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">
                            Rp. {{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Total</td>
                        <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Rp. {{ number_format($ca_transaction->total_ca, 0, ',', '.') }}</td>
                    </tr>
                </table>
            @endif
        @endif
        <br>

        <p>If you have any questions, please contact the respective business unit GA. </p>
        <br>
        <p><strong>----------------</strong></p>
        <p>Human Capital - KPN Corp</p>
    @endif
</body>
</html>