<script src="{{ asset('/js/cashAdvanced/relation.js') }}"></script>

@if (!empty($detailCA['relation_e']) && $detailCA['relation_e'][0]['name'] !== null)
    <div id="form-container-relation">
        @foreach($detailCA['relation_e'] as $index => $relation)
            @php
                $initialCount = count($detailCA['relation_e']);
            @endphp
            <div id="form-container-e-relation-{{ $loop->index + 1 }}" class="p-2 mb-4 rounded-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment {{ $loop->index + 1 }}</p>
                <div id="form-container-e-relation-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <div class="row">
                        <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Request</p>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th width="40%">Relation Type</th>
                                    <td class="block">:</td>
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
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td class="block">:</td>
                                    <td>{{$relation['name']}}</td>
                                </tr>
                                <tr>
                                    <th>Position</th>
                                    <td class="block">:</td>
                                    <td>{{$relation['position']}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table width="100%">
                                <tr>
                                    <th>Company</th>
                                    <td class="block">:</td>
                                    <td>{{$relation['company']}}</td>
                                </tr>
                                <tr>
                                    <th>Purpose</th>
                                    <td class="block">:</td>
                                    <td>{{$relation['purpose']}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="form-container-e-relation-dec-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Declaration</p>
                    @if (isset($declareCA['relation_e'][$index]))
                        @php
                            $relation_dec = $declareCA['relation_e'][$index];
                        @endphp
                        <div class="row">
                            <!-- Relation Date -->
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Relation Type</label>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="accommodation_e_relation[{{ $loop->index }}]"
                                        id="accommodation_e_relation_{{ $loop->index + 1 }}"
                                        value="accommodation" {{ isset($relation['relation_type']['Accommodation']) && $relation['relation_type']['Accommodation'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="accommodation_e_relation_{{ $loop->index + 1 }}">Accommodation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="food_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="food_e_relation_{{ $loop->index + 1 }}"
                                        value="food" {{ isset($relation['relation_type']['Food']) && $relation['relation_type']['Food'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="food_e_relation_{{ $loop->index + 1 }}">Food/Beverages/Souvenir</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="fund_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="fund_e_relation_{{ $loop->index + 1 }}"
                                        value="fund" {{ isset($relation['relation_type']['Fund']) && $relation['relation_type']['Fund'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="fund_e_relation_{{ $loop->index + 1 }}">Fund</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="gift_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="gift_e_relation_{{ $loop->index + 1 }}"
                                        value="gift" {{ isset($relation['relation_type']['Gift']) && $relation['relation_type']['Gift'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="gift_e_relation_{{ $loop->index + 1 }}">Gift</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="transport_e_relation[{{ $loop->index }}]"
                                        type="checkbox"
                                        id="transport_e_relation_{{ $loop->index + 1 }}"
                                        value="transport" {{ isset($relation['relation_type']['Transport']) && $relation['relation_type']['Transport'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="transport_e_relation_{{ $loop->index + 1 }}">Transport</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label"
                                    for="name">Name</label>
                                <input type="text"
                                    name="rname_e_relation[]"
                                    id="rname_e_relation_{{ $loop->index + 1 }}"
                                    value="{{ $relation_dec['name'] }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label"
                                    for="position">Position</label>
                                <input type="text"
                                    name="rposition_e_relation[]"
                                    id="rposition_e_relation_{{ $loop->index + 1 }}"
                                    value="{{ $relation_dec['position'] }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label"
                                    for="company">Company</label>
                                <input type="text"
                                    name="rcompany_e_relation[]"
                                    id="rcompany_e_relation_{{ $loop->index + 1 }}"
                                    value="{{ $relation_dec['company'] }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"
                                    for="purpose">Purpose</label>
                                <textarea name="rpurpose_e_relation[]"
                                    id="rpurpose_e_relation_{{ $loop->index + 1 }}"
                                    class="form-control">{{ $relation_dec['purpose'] }}</textarea>
                            </div>
                        </div>
                        <br>
                        <div class="row mt-3">
                            <div class="d-flex justify-start w-100">
                                <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation({{ $loop->index + 1 }}, event)">Reset</button>
                                {{-- <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation({{ $loop->index + 1 }}, event)">Delete</button> --}}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        @foreach ($declareCA['relation_e'] as $index => $relation_dec)
            @if (!isset($detailCA['relation_e'][$index]))
                <div id="form-container-e-relation-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <!-- Relation Date -->
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Relation Type</label>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="accommodation_e_relation[{{ $loop->index }}]"
                                    id="accommodation_e_relation_{{ $loop->index + 1 }}"
                                    value="accommodation" {{ isset($relation['relation_type']['Accommodation']) && $relation['relation_type']['Accommodation'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="accommodation_e_relation_{{ $loop->index + 1 }}">Accommodation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="transport_e_relation[{{ $loop->index }}]"
                                    type="checkbox"
                                    id="transport_e_relation_{{ $loop->index + 1 }}"
                                    value="transport" {{ isset($relation['relation_type']['Transport']) && $relation['relation_type']['Transport'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="transport_e_relation_{{ $loop->index + 1 }}">Transport</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="gift_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="gift_e_relation_{{ $loop->index + 1 }}"
                                    value="gift" {{ isset($relation['relation_type']['Gift']) && $relation['relation_type']['Gift'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="gift_e_relation_{{ $loop->index + 1 }}">Gift</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="fund_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="fund_e_relation_{{ $loop->index + 1 }}"
                                    value="fund" {{ isset($relation['relation_type']['Fund']) && $relation['relation_type']['Fund'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="fund_e_relation_{{ $loop->index + 1 }}">Fund</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="food_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="food_e_relation_{{ $loop->index + 1 }}"
                                    value="food" {{ isset($relation['relation_type']['Food']) && $relation['relation_type']['Food'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="food_e_relation_{{ $loop->index + 1 }}">Food/Beverages/Souvenir</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="name">Name</label>
                            <input type="text"
                                name="rname_e_relation[]"
                                id="rname_e_relation_1"
                                value="{{ $relation_dec['name'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="position">Position</label>
                            <input type="text"
                                name="rposition_e_relation[]"
                                id="rposition_e_relation_1"
                                value="{{ $relation_dec['position'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="company">Company</label>
                            <input type="text"
                                name="rcompany_e_relation[]"
                                id="rcompany_e_relation_1"
                                value="{{ $relation_dec['company'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"
                                for="purpose">Purpose</label>
                            <textarea name="rpurpose_e_relation[]"
                                id="rpurpose_e_relation_1"
                                class="form-control">{{ $relation_dec['purpose'] }}</textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        let checkboxCount = {{ $initialCount }} - 1;
    </script>    

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonRelation" onclick="addMoreFormRelationDec(event)">Add More</button>
    </div>
@elseif (!empty($declareCA['relation_e']) && $declareCA['relation_e'][0]['name'] !== null)
    <div id="form-container-relation">
        @foreach ($declareCA['relation_e'] as $index => $relation_dec)
            @if (!isset($detailCA['relation_e'][$index]))
                @php
                    $initialCount = count($detailCA['relation_e']);
                @endphp
                <div id="form-container-e-relation-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                    <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment {{ $loop->index + 1 }}</p>
                    <div class="row">
                        <!-- Relation Date -->
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Relation Type</label>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="accommodation_e_relation[{{ $loop->index }}]"
                                    id="accommodation_e_relation_{{ $loop->index + 1 }}"
                                    value="accommodation" {{ isset($relation['relation_type']['Accommodation']) && $relation['relation_type']['Accommodation'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="accommodation_e_relation_{{ $loop->index + 1 }}">Accommodation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="food_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="food_e_relation_{{ $loop->index + 1 }}"
                                    value="food" {{ isset($relation['relation_type']['Food']) && $relation['relation_type']['Food'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="food_e_relation_{{ $loop->index + 1 }}">Food/Beverages/Souvenir</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="fund_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="fund_e_relation_{{ $loop->index + 1 }}"
                                    value="fund" {{ isset($relation['relation_type']['Fund']) && $relation['relation_type']['Fund'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="fund_e_relation_{{ $loop->index + 1 }}">Fund</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="gift_e_relation[{{ $loop->index }}]" type="checkbox"
                                    id="gift_e_relation_{{ $loop->index + 1 }}"
                                    value="gift" {{ isset($relation['relation_type']['Gift']) && $relation['relation_type']['Gift'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="gift_e_relation_{{ $loop->index + 1 }}">Gift</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                    name="transport_e_relation[{{ $loop->index }}]"
                                    type="checkbox"
                                    id="transport_e_relation_{{ $loop->index + 1 }}"
                                    value="transport" {{ isset($relation['relation_type']['Transport']) && $relation['relation_type']['Transport'] ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="transport_e_relation_{{ $loop->index + 1 }}">Transport</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="name">Name</label>
                            <input type="text"
                                name="rname_e_relation[]"
                                id="rname_e_relation_1"
                                value="{{ $relation_dec['name'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="position">Position</label>
                            <input type="text"
                                name="rposition_e_relation[]"
                                id="rposition_e_relation_1"
                                value="{{ $relation_dec['position'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label"
                                for="company">Company</label>
                            <input type="text"
                                name="rcompany_e_relation[]"
                                id="rcompany_e_relation_1"
                                value="{{ $relation_dec['company'] }}"
                                class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"
                                for="purpose">Purpose</label>
                            <textarea name="rpurpose_e_relation[]"
                                id="rpurpose_e_relation_1"
                                class="form-control">{{ $relation_dec['purpose'] }}</textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row mt-3">
                        <div class="d-flex justify-start w-100">
                            <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation({{ $loop->index + 1 }}, event)">Reset</button>
                            <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation({{ $loop->index + 1 }}, event)">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        let checkboxCount = {{ $initialCount }} - 1;
    </script>  

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonRelation" onclick="addMoreFormRelationDec(event)">Add More</button>
    </div>
@else
    <div id="form-container-relation">
        <div id="form-container-e-relation-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment 1</p>
            <div id="form-container-e-relation-dec-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Declaration</p>
                <div class="row">
                    <!-- Relation Date -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="accommodation_e_relation[0]"
                                id="accommodation_e_relation_0"
                                value="accommodation">
                            <label class="form-check-label"
                                for="accommodation_e_relation_0">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="food_e_relation[0]" type="checkbox"
                                id="food_e_relation_0" value="food">
                            <label class="form-check-label"
                                for="food_e_relation_0">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="fund_e_relation[0]" type="checkbox"
                                id="fund_e_relation_0" value="fund">
                            <label class="form-check-label"
                                for="fund_e_relation_0">Fund</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="gift_e_relation[0]" type="checkbox"
                                id="gift_e_relation_0" value="gift">
                            <label class="form-check-label"
                                for="gift_e_relation_0">Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="transport_e_relation[0]"
                                type="checkbox"
                                id="transport_e_relation_0"
                                value="transport">
                            <label class="form-check-label"
                                for="transport_e_relation_0">Transport</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="name">Name</label>
                        <input type="text" name="rname_e_relation[]"
                            id="rname_e_relation_1" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="position">Position</label>
                        <input type="text"
                            name="rposition_e_relation[]"
                            id="rposition_e_relation_1"
                            class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="company">Company</label>
                        <input type="text" name="rcompany_e_relation[]"
                            id="rcompany_e_relation_1"
                            class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"
                            for="purpose">Purpose</label>
                        <textarea name="rpurpose_e_relation[]"
                            id="rpurpose_e_relation_1"
                            class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation(1, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation(1, event)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let checkboxCount = 0;
    </script> 

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonRelation" onclick="addMoreFormRelationDec(event)">Add More</button>
    </div>
@endif
