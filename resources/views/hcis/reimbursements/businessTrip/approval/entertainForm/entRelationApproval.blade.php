{{-- <script src="{{ asset('/js/cashAdvanced/relation.js') }}"></script> --}}
@include('js.hcis.cashAdvanced.relation')

@if (!empty($caDetail['relation_e']) && $caDetail['relation_e'][0]['name'] !== null)
    <div id="form-container-relation">
        @foreach($caDetail['relation_e'] as $relation)
            @php
                $initialCount = count($caDetail['relation_e']);
            @endphp
            <div id="form-container-e-relation-{{ $loop->index + 1 }}" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
                <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment {{ $loop->index + 1 }}</p>
                <div id="form-container-e-relation-req-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3">
                    <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Request</p>
                    <div id="fordm-container-e-relation-{{ $loop->index + 1 }}" class="card-body bg-light p-2 mb-3" style="border-radius: 1%;">
                        <div class="row">
                            <!-- Relation Date -->
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Relation Type</label>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="accommodation_e_relation[{{ $loop->index }}]"
                                        id="accommodation_e_relation_{{ $loop->index + 1 }}"
                                        value="accommodation" {{ isset($relation['relation_type']['Accommodation']) && $relation['relation_type']['Accommodation'] ? 'checked' : '' }} disabled>
                                    <label class="form-check-label"
                                        for="accommodation_e_relation_{{ $loop->index + 1 }}">Accommodation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="food_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="food_e_relation_{{ $loop->index + 1 }}"
                                        value="food" {{ isset($relation['relation_type']['Food']) && $relation['relation_type']['Food'] ? 'checked' : '' }} disabled>
                                    <label class="form-check-label"
                                        for="food_e_relation_{{ $loop->index + 1 }}">Food/Beverages/Souvenir</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="fund_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="fund_e_relation_{{ $loop->index + 1 }}"
                                        value="fund" {{ isset($relation['relation_type']['Fund']) && $relation['relation_type']['Fund'] ? 'checked' : '' }} disabled>
                                    <label class="form-check-label"
                                        for="fund_e_relation_{{ $loop->index + 1 }}">Fund</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="gift_e_relation[{{ $loop->index }}]" type="checkbox"
                                        id="gift_e_relation_{{ $loop->index + 1 }}"
                                        value="gift" {{ isset($relation['relation_type']['Gift']) && $relation['relation_type']['Gift'] ? 'checked' : '' }} disabled>
                                    <label class="form-check-label"
                                        for="gift_e_relation_{{ $loop->index + 1 }}">Gift</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                        name="transport_e_relation[{{ $loop->index }}]"
                                        type="checkbox"
                                        id="transport_e_relation_{{ $loop->index + 1 }}"
                                        value="transport" {{ isset($relation['relation_type']['Transport']) && $relation['relation_type']['Transport'] ? 'checked' : '' }} disabled>
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
                                    value="{{ $relation['name'] }}"
                                    class="form-control bg-light" disabled>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label"
                                    for="position">Position</label>
                                <input type="text"
                                    name="rposition_e_relation[]"
                                    id="rposition_e_relation_{{ $loop->index + 1 }}"
                                    value="{{ $relation['position'] }}"
                                    class="form-control bg-light" disabled>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label"
                                    for="company">Company</label>
                                <input type="text"
                                    name="rcompany_e_relation[]"
                                    id="rcompany_e_relation_{{ $loop->index + 1 }}"
                                    value="{{ $relation['company'] }}"
                                    class="form-control bg-light" disabled>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"
                                    for="purpose">Purpose</label>
                                <textarea name="rpurpose_e_relation[]"
                                    id="rpurpose_e_relation_{{ $loop->index + 1 }}"
                                    class="form-control bg-light" disabled>{{ $relation['purpose'] }}</textarea>
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <script>
        let checkboxCount = {{ $initialCount }} - 1;
    </script>    

    <div class="mt-3">
        <button class="btn btn-primary btn-sm" id="addMoreButtonRelation" onclick="addMoreFormRelationReq(event)">Add More</button>
    </div>
@else
    <div id="form-container-relation">
        <div id="form-container-e-relation-1" class="card-body p-2 mb-3" style="background-color: #f8f8f8">
            <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment 1</p>
            <div id="form-container-e-relation-req-1" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Relation Entertainment Request</p>
                <div class="row">
                    <!-- Relation Date -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="accommodation_e_relation[0]"
                                id="accommodation_e_relation_0"
                                value="accommodation" disabled>
                            <label class="form-check-label"
                                for="accommodation_e_relation_0">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="food_e_relation[0]" type="checkbox"
                                id="food_e_relation_0" value="food" disabled>
                            <label class="form-check-label"
                                for="food_e_relation_0">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="fund_e_relation[0]" type="checkbox"
                                id="fund_e_relation_0" value="fund" disabled>
                            <label class="form-check-label"
                                for="fund_e_relation_0" disabled>Fund</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="gift_e_relation[0]" type="checkbox"
                                id="gift_e_relation_0" value="gift" disabled>
                            <label class="form-check-label"
                                for="gift_e_relation_0" disabled>Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="transport_e_relation[0]"
                                type="checkbox"
                                id="transport_e_relation_0"
                                value="transport" disabled>
                            <label class="form-check-label"
                                for="transport_e_relation_0" disabled>Transport</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="name">Name</label>
                        <input type="text" name="rname_e_relation[]"
                            id="rname_e_relation_1" class="form-control bg-light" disabled>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="position">Position</label>
                        <input type="text"
                            name="rposition_e_relation[]"
                            id="rposition_e_relation_1"
                            class="form-control bg-light" disabled>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="company">Company</label>
                        <input type="text" name="rcompany_e_relation[]"
                            id="rcompany_e_relation_1"
                            class="form-control bg-light" disabled>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"
                            for="purpose">Purpose</label>
                        <textarea name="rpurpose_e_relation[]"
                            id="rpurpose_e_relation_1"
                            class="form-control bg-light" disabled></textarea>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>

    <script>
        let checkboxCount = 0;
    </script> 
@endif
