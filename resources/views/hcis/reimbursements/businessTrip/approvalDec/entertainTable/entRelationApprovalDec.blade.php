<div class="bg-light p-3 mt-2 mb-2 rounded">
    <h4 class="text-start text-primary mb-3">RELATION ENTERTAIN</h4>
    <div class="row mb-2">
        <div class="col-md-6 mb-2">
            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                Relation Entertain Plan (Request):</h5>
            <div class="table-responsive table-container bg-white"
                style="height: 300px; overflow-y: auto;">
                <table class="table table-hover table-sm nowrap"
                    id="{{ isset($caDetail['relation_e']) && is_array($caDetail['relation_e']) ? (array_sum(array_column($caDetail['relation_e'], 'nominal')) > 0 ? 'transportTable' : '') : '' }}"
                    width="100%" cellspacing="0">
                    <thead class="thead-light">
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
                        @if (isset($caDetail['relation_e']) &&
                                is_array($caDetail['relation_e']) &&
                                count($caDetail['relation_e']) > 0)
                            @foreach ($caDetail['relation_e'] as $relation)
                                <tr class="text-center">
                                    {{-- <td></td> --}}
                                    <td class="text-center">{{ $loop->index + 1 }}</td>
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
                        @else
                            <tr>
                                <td colspan="6" class="text-center">No data available
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <h5 class="bg-primary text-white text-center p-2" style="margin-bottom: 0;">
                Relation Entertain (Declaration):</h5>
            <div class="table-responsive table-container bg-white"
                style="height: 300px; overflow-y: auto;">
                <table class="table table-hover table-sm nowrap"
                    id="{{ isset($declareCa['relation_e']) && is_array($declareCa['relation_e']) ? (array_sum(array_column($declareCa['relation_e'], 'nominal')) > 0 ? 'transportTableDec' : '') : '' }}"
                    width="100%" cellspacing="0">
                    <thead class="thead-light">
                        {{-- <tr class="bg-primary">
                            <th colspan="6" class="text-center text-white">Transport
                                Plan
                                (Declaration):</th>
                        </tr> --}}
                        <tr style="text-align-last: center;">
                            <tr style="text-align-last: center;">
                                <th>No</th>
                                <th>Relation Type</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Purpose</th>
                            </tr>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalDetail = 0; ?>
                        @if (isset($declareCa['relation_e']) &&
                                is_array($declareCa['relation_e']) &&
                                count($declareCa['relation_e']) > 0)
                            @foreach ($declareCA['relation_e'] as $relation)
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
                        @else
                            <tr>
                                <td colspan="6" class="text-center">No data available
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>