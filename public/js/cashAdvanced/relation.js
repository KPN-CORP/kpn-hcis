var formCountRelation = 0;

window.addEventListener("DOMContentLoaded", function () {
    formCountRelation = document.querySelectorAll(
        "#form-container-relation > div"
    ).length;
});

function addMoreFormRelationDec(event) {
    event.preventDefault();
    formCountRelation++;

    const newForm = document.createElement("div");
    newForm.id = `form-container-e-relation-${formCountRelation}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment ${formCountRelation}</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Relation Declaration</p>
                <div class="row">
                    <!-- Relation Date -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="accommodation_e_relation[]"
                                id="accommodation_e_relation_${formCountRelation}"
                                value="accommodation">
                            <label class="form-check-label"
                                for="accommodation_e_relation_${formCountRelation}">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="food_e_relation[]" type="checkbox"
                                id="food_e_relation_${formCountRelation}" value="food">
                            <label class="form-check-label"
                                for="food_e_relation_${formCountRelation}">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="fund_e_relation[]" type="checkbox"
                                id="fund_e_relation_${formCountRelation}" value="fund">
                            <label class="form-check-label"
                                for="fund_e_relation_${formCountRelation}">Fund</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="gift_e_relation[]" type="checkbox"
                                id="gift_e_relation_${formCountRelation}" value="gift">
                            <label class="form-check-label"
                                for="gift_e_relation_${formCountRelation}">Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="transport_e_relation[]"
                                type="checkbox"
                                id="transport_e_relation_${formCountRelation}"
                                value="transport">
                            <label class="form-check-label"
                                for="transport_e_relation_${formCountRelation}">Transport</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="name">Name</label>
                        <input type="text" name="rname_e_relation[]"
                            id="rname_e_relation_${formCountRelation}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="position">Position</label>
                        <input type="text"
                            name="rposition_e_relation[]"
                            id="rposition_e_relation_${formCountRelation}"
                            class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="company">Company</label>
                        <input type="text" name="rcompany_e_relation[]"
                            id="rcompany_e_relation_${formCountRelation}"
                            class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"
                            for="purpose">Purpose</label>
                        <textarea name="rpurpose_e_relation[]"
                            id="rpurpose_e_relation_${formCountRelation}"
                            class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation(${formCountRelation}, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation(${formCountRelation}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
    document.getElementById("form-container-relation").appendChild(newForm);
    updateCheckboxVisibility();
}

function addMoreFormRelationReq(event) {
    event.preventDefault();
    formCountRelation++;
    checkboxCount++;

    const newForm = document.createElement("div");
    newForm.id = `form-container-e-relation-${formCountRelation}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Relation Entertainment ${formCountRelation}</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Relation Request</p>
                <div class="row">
                    <!-- Relation Date -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Relation Type</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="accommodation_e_relation[${checkboxCount}]"
                                id="accommodation_e_relation_${formCountRelation}"
                                value="accommodation">
                            <label class="form-check-label"
                                for="accommodation_e_relation_${formCountRelation}">Accommodation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="food_e_relation[${checkboxCount}]" type="checkbox"
                                id="food_e_relation_${formCountRelation}" value="food">
                            <label class="form-check-label"
                                for="food_e_relation_${formCountRelation}">Food/Beverages/Souvenir</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="fund_e_relation[${checkboxCount}]" type="checkbox"
                                id="fund_e_relation_${formCountRelation}" value="fund">
                            <label class="form-check-label"
                                for="fund_e_relation_${formCountRelation}">Fund</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="gift_e_relation[${checkboxCount}]" type="checkbox"
                                id="gift_e_relation_${formCountRelation}" value="gift">
                            <label class="form-check-label"
                                for="gift_e_relation_${formCountRelation}">Gift</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                name="transport_e_relation[${checkboxCount}]"
                                type="checkbox"
                                id="transport_e_relation_${formCountRelation}"
                                value="transport">
                            <label class="form-check-label"
                                for="transport_e_relation_${formCountRelation}">Transport</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="name">Name</label>
                        <input type="text" name="rname_e_relation[]"
                            id="rname_e_relation_${formCountRelation}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="position">Position</label>
                        <input type="text"
                            name="rposition_e_relation[]"
                            id="rposition_e_relation_${formCountRelation}"
                            class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label"
                            for="company">Company</label>
                        <input type="text" name="rcompany_e_relation[]"
                            id="rcompany_e_relation_${formCountRelation}"
                            class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"
                            for="purpose">Purpose</label>
                        <textarea name="rpurpose_e_relation[]"
                            id="rpurpose_e_relation_${formCountRelation}"
                            class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormRelation(${formCountRelation}, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormRelation(${formCountRelation}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
    document.getElementById("form-container-relation").appendChild(newForm);
    updateCheckboxVisibility();
}

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormRelation(index, event);
});

function removeFormRelation(index, event) {
    event.preventDefault();
    if (formCountRelation > 0) {
        const formContainer = document.getElementById(
            `form-container-e-relation-${index}`
        );
        if (formContainer) {
            $(`#form-container-e-relation-${index}`).remove();
            formCountRelation--;
        }
    }
}

function clearFormRelation(index, event) {
    event.preventDefault();
    let form = document.getElementById(`form-container-e-relation-${index}`);

    form.querySelectorAll('input[type="text"], textarea').forEach((input) => {
        input.value = "";
    });

    form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.checked = false;
    });
}
