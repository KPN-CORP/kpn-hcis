<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cash Advanced Notification</title>
</head>
    <body>
        <div style="width: 100%; height: auto; text-align: center;">
            <img src="{{ $logoBase64 }}" 
                 alt="Kop Surat" 
                 style="height: auto; margin-bottom: 20px; width: 15%;">
        </div>               
        <h2>Reimburse Cash Advanced Notification</h2>
        @if ($nextApproval)
        {{-- {{dd($nextApproval)}} --}}
            <p>Dear : Bapak/Ibu <strong>{{ $nextApproval->employee->fullname }}</strong></p>
            <br>
        @endif

        <p>{{ $textNotification }}</p>

        @php
            $detailCA = json_decode($caTransaction->detail_ca, true);
            $declareCA = json_decode($caTransaction->declare_ca, true);
        @endphp

        @if ($caTransaction)
            <table>
                <tr>
                    <td><b>No Dokumen</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->no_ca }}</td>
                </tr>
                <tr>
                    <td><b>Name</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->employee->fullname }}</td>
                </tr>
                <tr>
                    <td><b>Start Date</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->start_date }}</td>
                </tr>
                <tr>
                    <td><b>End Date</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->end_date }}</td>
                </tr>
                <tr>
                    <td><b>Destination</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->destination == 'Others' ? $caTransaction->others_location : $caTransaction->destination }}</td>
                </tr>
                <tr>
                    <td><b>Purpose</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->ca_needs }}</td>
                </tr>
                <tr>
                    <td><b>PT</b></td>
                    <td>:</td>
                    <td>{{ $caTransaction->companies->contribution_level }} ({{ $caTransaction->contribution_level_code }})</td>
                </tr>
                <tr>
                    <td><b>CA Type</b></td>
                    <td>:</td>
                    <td>
                        @if($caTransaction->type_ca === 'entr')
                            Entertaiment
                        @elseif($caTransaction->type_ca === 'dns')
                            Business Trip
                        @elseif($caTransaction->type_ca === 'ndns')
                            Non Business Trip
                        @else
                            Unknown Type
                        @endif
                    </td>
                </tr>
            </table>
            <br>

            @if($caTransaction->type_ca === 'entr')
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
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($declareCA['detail_e'], 'nominal')), 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;" colspan="2">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"></td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">Rp. {{ number_format($caTransaction->total_real, 0, ',', '.' )}}</td>
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
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($detailCA['detail_e'], 'nominal')), 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif
            @elseif($caTransaction->type_ca === 'dns')
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
                                Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;"></td>
                            <td style="border: 1px solid #ddd; padding: 4px; vertical-align: top;">
                                Rp. {{ number_format($caTransaction->total_real, 0, ',', '.') }}
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
                                Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table> 
                    <br>                   
                @endif
            @elseif($caTransaction->type_ca === 'ndns')
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
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($declareCA, 'nominal_nbt')), 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;"></td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; vertical-align: top;">Rp. {{ number_format($caTransaction->total_real, 0, ',', '.') }}</td>
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
                                {{ $caTransaction->total_days }} Days
                            </td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">
                                Rp. {{ number_format(array_sum(array_column($detailCA, 'nominal_nbt')), 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Total</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;">Rp. {{ number_format($caTransaction->total_ca, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif
            @endif
            <br>

            @if ($linkApprove)
                <p>To Approve or Reject the Cash Advance, please select the following link :</p>
                <p>
                    <a href="{{ $linkApprove }}" style="font-size: 20px;">Approve</a>    /     
                    <a href="{{ $linkReject }}" style="font-size: 20px;">Reject</a>
                </p>                
            @else
                <p>Cash Advanced that has been approved can be seen in the attachment</p>
            @endif
            <br>

            <p>If you have any questions, please contact the respective business unit GA. </p>
            <br>
            <p><strong>----------------</strong></p>
            <p>Human Capital - KPN Corp</p>
        @endif
    </body>
</html>
