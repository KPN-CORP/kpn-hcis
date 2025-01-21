var formCountNBT = 0;
let isCADecNBT;

const routeInfoNonBT = document.getElementById("routeInfo");
if (routeInfoNonBT) {
    isCADecNBT = true;
} else {
    isCADecNBT = false;
}

window.addEventListener("DOMContentLoaded", function () {
    formCountNBT = document.querySelectorAll(
        "#form-container-nonb > div"
    ).length;
});

function addMoreFormNBTReq(event) {
    event.preventDefault();
    formCountNBT++;
    const newForm = document.createElement("div");
    newForm.id = `form-container-nbt-${formCountNBT}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Non Bussiness Trip ${formCountNBT}</p>
            <div id="form-container-nbt-req-${formCountNBT}" class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold; ">Non Bussiness Trip Request</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_nbt[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_${formCountNBT}" type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInputNBT(this)"
                                >
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_nbt[]" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormNBT(${formCountNBT}, event)">Reset</button>
                        <button class="btn btn-warning mr-2" onclick="removeFormNBT(${formCountNBT}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
    document.getElementById("form-container-nonb").appendChild(newForm);

    // Hanya nominal field yang menggunakan event listener
    document
        .querySelector(`#nominal_nbt_${formCountNBT}`)
        .addEventListener("input", function () {
            formatInputNBT(this);
            calculateTotalNominal();
            if (isCADecNBT) {
                calculateTotalNominalBTBalance();
            }
        });

    calculateTotalNominal();
    if (isCADecNBT) {
        calculateTotalNominalBTBalance();
    }
}

function addMoreFormNBTDec(event) {
    event.preventDefault();
    formCountNBT++;
    const newForm = document.createElement("div");
    newForm.id = `form-container-nbt-${formCountNBT}`;
    newForm.className = "card-body p-2 mb-3";
    newForm.style.backgroundColor = "#f8f8f8";
    newForm.innerHTML = `
            <p class="fs-4 text-primary" style="font-weight: bold; ">Non Bussiness Trip ${formCountNBT}</p>
            <div class="card-body bg-light p-2 mb-3">
                <p class="fs-5 text-primary" style="font-weight: bold; ">Non Bussiness Trip Declaration</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="tanggal_nbt[]" class="form-control" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount</label>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control" name="nominal_nbt[]" id="nominal_nbt_${formCountNBT}" type="text" min="0" value="0"
                                onfocus="this.value = this.value === '0' ? '' : this.value;"
                                oninput="formatInputNBT(this)"
                                >
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                        <div class="mb-2">
                            <label class="form-label">Information</label>
                            <textarea name="keterangan_nbt[]" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex justify-start w-100">
                        <button class="btn btn-danger mr-2" style="margin-right: 10px" onclick="clearFormNBT(${formCountNBT}, event)">Reset</button>
                        <button class="btn btn-warning mr-2" onclick="removeFormNBT(${formCountNBT}, event)">Delete</button>
                    </div>
                </div>
            </div>
        `;
    document.getElementById("form-container-nonb").appendChild(newForm);

    // Hanya nominal field yang menggunakan event listener
    document
        .querySelector(`#nominal_nbt_${formCountNBT}`)
        .addEventListener("input", function () {
            formatInputNBT(this);
            calculateTotalNominal();
            // if (isCADecNBT) {
            //     calculateTotalNominalBTBalance();
            // }
        });

    calculateTotalNominal();
    if (isCADecNBT) {
        calculateTotalNominalBTBalance();
    }
}

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormNBT(index, event);
});

function removeFormNBT(index, event) {
    event.preventDefault();
    if (formCountNBT > 0) {
        const formContainer = document.getElementById(
            `form-container-nbt-${index}`
        );
        if (formContainer) {
            const nominalInput = formContainer.querySelector(
                `#nominal_nbt_${index}`
            );
            if (nominalInput) {
                formContainer.querySelector(`#nominal_nbt_${index}`).value = 0;
                calculateTotalNominal();
                if (isCADecNBT) {
                    calculateTotalNominalBTBalance();
                }
            }
            $(`#form-container-nbt-${index}`).remove();
            formCountNBT--;
        }
    }
}

function clearFormNBT(index, event) {
    event.preventDefault();
    const formContainer = document.getElementById(
        `form-container-nbt-${index}`
    );
    if (formContainer) {
        formContainer
            .querySelectorAll('input[type="text"], input[type="date"]')
            .forEach((input) => {
                input.value = "";
            });

        formContainer.querySelectorAll("textarea").forEach((textarea) => {
            textarea.value = "";
        });

        // Reset nominal value to 0
        formContainer.querySelector(`#nominal_nbt_${index}`).value = 0;
        calculateTotalNominal(); // Recalculate total after clearing the form
        if (isCADecNBT) {
            calculateTotalNominalBTBalance();
        }
    }
}

function calculateTotalNominal() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_nbt[]"]')
        .forEach((input) => {
            total += cleanNumber(input.value); // Pastikan hanya menghitung angka
        });
    document.querySelector('input[name="totalca"]').value = formatNumber(total);
}

document.addEventListener("DOMContentLoaded", function () {
    // Attach input event to the existing nominal fields
    document
        .querySelectorAll('input[name="nominal_nbt[]"]')
        .forEach((input) => {
            input.addEventListener("input", function () {
                formatInputNBT(this); // Hanya memformat dan menghitung input nominal
                calculateTotalNominal();
                if (isCADecNBT) {
                    calculateTotalNominalBTBalance();
                }
            });
        });

    calculateTotalNominal(); // Kalkulasi total saat halaman pertama kali dimuat
    if (isCADecNBT) {
        calculateTotalNominalBTBalance();
    }
});

function formatInputNBT(input) {
    let value = input.value.replace(/\./g, "");
    value = parseFloat(value);
    if (!isNaN(value)) {
        input.value = formatNumber(Math.floor(value));
    } else {
        input.value = formatNumber(0);
    }
    calculateTotalNominal();
    if (isCADecNBT) {
        calculateTotalNominalBTBalance();
    }
}
