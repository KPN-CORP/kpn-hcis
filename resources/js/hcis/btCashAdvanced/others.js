var formCountOthers = 0;

window.addEventListener("DOMContentLoaded", function () {
    formCountOthers = document.querySelectorAll(
        "#form-container-lainnya > div"
    ).length;
});

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormLainnya(index, event);
});

function removeFormLainnya(index, event) {
    event.preventDefault();
    if (formCountOthers > 0) {
        const formContainer = document.getElementById(
            `form-container-bt-lainnya-${index}`
        );
        if (formContainer) {
            const nominalInput = formContainer.querySelector(
                `#nominal_bt_lainnya_${index}`
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_lainnya"]')
                        .value
                );
                total -= nominalValue;
                document.querySelector('input[name="total_bt_lainnya"]').value =
                    formatNumber(total);
                calculateTotalNominalBTTotal();
            }
            $(`#form-container-bt-lainnya-${index}`).remove();
            formCountOthers--;
        }
    }
}

function clearFormLainnya(index, event) {
    event.preventDefault();
    let nominalValue = cleanNumber(
        document.querySelector(`#nominal_bt_lainnya_${index}`).value
    );
    let total = cleanNumber(
        document.querySelector('input[name="total_bt_lainnya"]').value
    );
    total -= nominalValue;
    document.querySelector('input[name="total_bt_lainnya"]').value =
        formatNumber(total);

    // Clear the inputs
    const formContainer = document.getElementById(
        `form-container-bt-lainnya-${index}`
    );
    formContainer
        .querySelectorAll('input[type="text"], input[type="date"]')
        .forEach((input) => {
            input.value = "";
        });
    formContainer.querySelector("textarea").value = "";

    // Reset nilai untuk nominal BT Lainnya
    document.querySelector(`#nominal_bt_lainnya_${index}`).value = 0;
    calculateTotalNominalBTTotal();
}

function calculateTotalNominalBTLainnya() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_bt_lainnya[]"]')
        .forEach((input) => {
            total += cleanNumber(input.value);
        });
    document.getElementById("total_bt_lainnya").value = formatNumber(total);
}

function onNominalChange() {
    calculateTotalNominalBTLainnya();
}
