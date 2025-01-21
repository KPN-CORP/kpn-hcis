var formCountDetail = 0;
let isCADecDetail;

const routeInfoDetail = document.getElementById("routeInfo");
if (routeInfoDetail) {
    isCADecDetail = true;
} else {
    isCADecDetail = false;
}

window.addEventListener("DOMContentLoaded", function () {
    formCountDetail = document.querySelectorAll(
        "#form-container-detail > div"
    ).length;
    updateCheckboxVisibility();
});

function addMoreFormDetailDec(event) {
    event.preventDefault();
    formCountDetail++;

    const newForm = document.createElement("div");
    newForm.id = `form-container-e-detail-${formCountDetail}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment ${formCountDetail}</p>
            <div id="form-container-e-detail-dec-${formCountDetail}" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Declaration</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Entertainment Type</label>
                        <select name="enter_type_e_detail[]" id="enter_type_e_detail_${formCountDetail}" class="form-select">
                            <option value="">-</option>
                            <option value="accommodation">Accommodation</option>
                            <option value="food">Food/Beverages/Souvenir</option>
                            <option value="fund">Fund</option>
                            <option value="gift">Gift</option>
                            <option value="transport">Transport</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_e_detail[]"
                                id="nominal_e_detail_${formCountDetail}"
                                type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInputENT(this)">
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Entertainment Fee Detail</label>
                        <textarea name="enter_fee_e_detail[]" class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px" onclick="clearFormDetail(${formCountDetail}, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm" onclick="removeFormDetail(${formCountDetail}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;

    document.getElementById("form-container-detail").appendChild(newForm);

    // Menambahkan listener untuk select dan input baru
    newForm
        .querySelector('select[name="enter_type_e_detail[]"]')
        .addEventListener("change", updateCheckboxVisibility);

    newForm
        .querySelector('input[name="nominal_e_detail[]"]')
        .addEventListener("input", function () {
            formatInputENT(this);
            calculateTotalNominalEDetail();
            if (isCADecDetail) {
                calculateTotalNominalBTBalance();
            }
        });

    calculateTotalNominalEDetail(); // Hitung total secara otomatis.
    if (isCADecDetail) {
        calculateTotalNominalBTBalance();
    }
    updateCheckboxVisibility(); // Memperbarui visibilitas checkbox.
}

function addMoreFormDetailReq(event) {
    event.preventDefault();
    formCountDetail++;

    const newForm = document.createElement("div");
    newForm.id = `form-container-e-detail-${formCountDetail}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Detail Entertainment ${formCountDetail}</p>
            <div id="form-container-e-detail-req-${formCountDetail}" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold;">Detail Entertainment Request</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Entertainment Type</label>
                        <select name="enter_type_e_detail[]" id="enter_type_e_detail_${formCountDetail}" class="form-select">
                            <option value="">-</option>
                            <option value="accommodation">Accommodation</option>
                            <option value="food">Food/Beverages/Souvenir</option>
                            <option value="fund">Fund</option>
                            <option value="gift">Gift</option>
                            <option value="transport">Transport</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_e_detail[]"
                                id="nominal_e_detail_${formCountDetail}"
                                type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInputENT(this)">
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Entertainment Fee Detail</label>
                        <textarea name="enter_fee_e_detail[]" class="form-control"></textarea>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-outline-warning mr-2 btn-sm" style="margin-right: 10px"
                            onclick="clearFormDetail(${formCountDetail}, event)">Reset</button>
                        <button class="btn btn-outline-primary mr-2 btn-sm"
                            onclick="removeFormDetail(${formCountDetail}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;

    document.getElementById("form-container-detail").appendChild(newForm);

    // Menambahkan listener untuk select dan input baru
    newForm
        .querySelector('select[name="enter_type_e_detail[]"]')
        .addEventListener("change", updateCheckboxVisibility);

    newForm
        .querySelector('input[name="nominal_e_detail[]"]')
        .addEventListener("input", function () {
            formatInputENT(this);
            calculateTotalNominalEDetail();
            if (isCADecDetail) {
                calculateTotalNominalBTBalance();
            }
        });

    calculateTotalNominalEDetail(); // Hitung total secara otomatis.
    if (isCADecDetail) {
        calculateTotalNominalBTBalance();
    }
    updateCheckboxVisibility(); // Memperbarui visibilitas checkbox.
}

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormDetail(index, event);
});

function removeFormDetail(index, event) {
    event.preventDefault();
    if (formCountDetail > 0) {
        let nominalValue = cleanNumber(
            document.querySelector(`#nominal_e_detail_${index}`).value
        );
        let totalCA =
            cleanNumber(
                document.querySelector('input[name="totalca"]').value
            ) || 0;
        totalCA -= nominalValue;
        document.querySelector('input[name="total_e_detail"]').value =
            formatNumber(totalCA);
        document.querySelector('input[name="totalca"]').value =
            formatNumber(totalCA);

        // Hide the form to be removed
        let formContainer = document.getElementById(
            `form-container-e-detail-${index}`
        );
        formContainer.remove();
        formCountDetail--;

        updateCheckboxVisibility();
    }
}

function removeFormDetailDec(index, event) {
    event.preventDefault();
    if (formCountDetail > 0) {
        let nominalValue = cleanNumber(
            document.querySelector(`#nominal_e_detail_${index}`).value
        );
        let totalCA =
            cleanNumber(
                document.querySelector('input[name="totalca"]').value
            ) || 0;
        totalCA -= nominalValue;
        document.querySelector('input[name="total_e_detail"]').value =
            formatNumber(totalCA);
        document.querySelector('input[name="totalca"]').value =
            formatNumber(totalCA);

        // Hide the form to be removed
        let formContainer = document.getElementById(
            `form-container-e-detail-dec-${index}`
        );
        formContainer.remove();
        formCountDetail--;

        updateCheckboxVisibility();
    }
}

function clearFormDetail(index, event) {
    event.preventDefault();
    if (formCountDetail > 0) {
        let nominalValue = cleanNumber(
            document.querySelector(`#nominal_e_detail_${index}`).value
        );
        let totalCA =
            cleanNumber(
                document.querySelector('input[name="totalca"]').value
            ) || 0;
        totalCA -= nominalValue;
        document.querySelector('input[name="total_e_detail"]').value =
            formatNumber(totalCA);
        document.querySelector('input[name="totalca"]').value =
            formatNumber(totalCA);

        let formContainer = document.getElementById(
            `form-container-e-detail-${index}`
        );

        formContainer
            .querySelectorAll('input[type="text"], input[type="date"]')
            .forEach((input) => {
                input.value = "";
            });

        formContainer
            .querySelectorAll('input[type="number"]')
            .forEach((input) => {
                input.value = 0;
            });

        formContainer.querySelectorAll("select").forEach((select) => {
            select.selectedIndex = 0;
        });

        formContainer.querySelectorAll("textarea").forEach((textarea) => {
            textarea.value = "";
        });

        document.querySelector(`#nominal_e_detail_${index}`).value = 0;

        // Call this function to update visibility of relation fields
        updateCheckboxVisibility();
    }
}

function calculateTotalNominalEDetail() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_e_detail[]"]')
        .forEach((input) => {
            total += parseNumber(input.value);
        });
    document.querySelector('input[name="total_e_detail"]').value =
        formatNumber(total);
    document.getElementById("totalca").value = formatNumber(total);
}

function updateCheckboxVisibility() {
    const selectedOptions = Array.from(
        document.querySelectorAll('select[name="enter_type_e_detail[]"]')
    )
        .map((select) => select.value)
        .filter((value) => value !== "");

    const formContainerERelation = document.querySelectorAll(
        '[id^="form-container-e-relation-"]'
    );
    const formContainerERelationDec = document.querySelectorAll(
        '[id^="form-container-e-relation-dec-"]'
    );

    formContainerERelation.forEach((container) => {
        container.querySelectorAll(".form-check").forEach((checkDiv) => {
            const checkbox = checkDiv.querySelector("input.form-check-input");
            const checkboxValue = checkbox.value
                .toLowerCase()
                .replace(/\s/g, "_");
            if (selectedOptions.includes(checkboxValue)) {
                checkDiv.style.display = "block"; // Show the checkbox
            } else {
                checkDiv.style.display = "none"; // Hide the checkbox
                checkbox.checked = false; // Uncheck the hidden checkbox
            }
        });
    });

    formContainerERelationDec.forEach((container) => {
        container.querySelectorAll(".form-check").forEach((checkDiv) => {
            const checkbox = checkDiv.querySelector("input.form-check-input");
            const checkboxValue = checkbox.value
                .toLowerCase()
                .replace(/\s/g, "_");
            if (selectedOptions.includes(checkboxValue)) {
                checkDiv.style.display = "block"; // Show the checkbox
            } else {
                checkDiv.style.display = "none"; // Hide the checkbox
                checkbox.checked = false; // Uncheck the hidden checkbox
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    document
        .querySelectorAll('input[name="nominal_e_detail[]"]')
        .forEach((input) => {
            input.addEventListener("input", function () {
                formatInputENT(this);
                calculateTotalNominalEDetail(); // Ensure we calculate total here
                if (isCADecDetail) {
                    calculateTotalNominalBTBalance();
                }
            });
        });

    // Call the function after the existing select elements are processed
    document
        .querySelectorAll('select[name="enter_type_e_detail[]"]')
        .forEach((select) => {
            select.addEventListener("change", updateCheckboxVisibility);
        });

    calculateTotalNominalEDetail();
    if (isCADecDetail) {
        calculateTotalNominalBTBalance();
    }
    updateCheckboxVisibility();
});

function formatInputENT(input) {
    let value = input.value.replace(/\./g, "");
    value = parseFloat(value);
    if (!isNaN(value)) {
        input.value = formatNumber(Math.floor(value));
    } else {
        input.value = formatNumber(0);
    }
    calculateTotalNominalEDetail();
    if (isCADecDetail) {
        calculateTotalNominalBTBalance();
    }
}
