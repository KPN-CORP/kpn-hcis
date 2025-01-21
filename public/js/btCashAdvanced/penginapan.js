var formCountPenginapan = 0;

window.addEventListener("DOMContentLoaded", function () {
    formCountPenginapan = document.querySelectorAll(
        "#form-container-penginapan > div"
    ).length;
});

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormPenginapan(index, event);
});

function removeFormPenginapan(index, event) {
    event.preventDefault();
    if (formCountPenginapan > 0) {
        const formContainer = document.getElementById(
            `form-container-bt-penginapan-${index}`
        );
        if (formContainer) {
            const nominalInput = formContainer.querySelector(
                `#nominal_bt_penginapan_${index}`
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_penginapan"]')
                        .value
                );
                total -= nominalValue;
                document.querySelector(
                    'input[name="total_bt_penginapan"]'
                ).value = formatNumber(total);
                calculateTotalNominalBTTotal();
            }
            $(`#form-container-bt-penginapan-${index}`).remove();
            formCountPenginapan--;
        }
    }
}

function removeFormPenginapanDec(index, event) {
    event.preventDefault();
    if (formCountPenginapan > 0) {
        const formContainer = document.getElementById(
            `form-container-bt-penginapan-${index}`
        );
        if (formContainer) {
            const nominalInput = formContainer.querySelector(
                `#nominal_bt_penginapan_${index}`
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_penginapan"]')
                        .value
                );
                total -= nominalValue;
                document.querySelector(
                    'input[name="total_bt_penginapan"]'
                ).value = formatNumber(total);
                calculateTotalNominalBTTotal();
            }
            $(`#form-container-bt-penginapan-dec-${index}`).remove();
            formCountPenginapan--;
        }
    }
}

function clearFormPenginapan(index, event) {
    event.preventDefault();
    let nominalValue = cleanNumber(
        document.querySelector(`#nominal_bt_penginapan_${formCountPenginapan}`)
            .value
    );
    let total = cleanNumber(
        document.querySelector('input[name="total_bt_penginapan"]').value
    );
    total -= nominalValue;
    document.querySelector('input[name="total_bt_penginapan"]').value =
        formatNumber(total);

    let formContainer = document.getElementById(
        `form-container-bt-penginapan-${index}`
    );

    // console.log(formContainer, index);

    formContainer
        .querySelectorAll('input[type="text"], input[type="date"]')
        .forEach((input) => {
            input.value = "";
        });

    formContainer.querySelectorAll('input[type="number"]').forEach((input) => {
        input.value = 0;
    });

    formContainer.querySelectorAll("select").forEach((select) => {
        select.selectedIndex = 0;
    });

    formContainer.querySelectorAll("textarea").forEach((textarea) => {
        textarea.value = "";
    });

    document.querySelector(
        `#nominal_bt_penginapan_${formCountPenginapan}`
    ).value = 0;
    calculateTotalNominalBTTotal();
}

function calculateTotalNominalBTPenginapan() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_bt_penginapan[]"]')
        .forEach((input) => {
            total += cleanNumber(input.value);
        });
    document.querySelector('input[name="total_bt_penginapan"]').value =
        formatNumber(total);
}

function onNominalChange() {
    calculateTotalNominalBTPenginapan();
}

function calculateTotalDaysPenginapan(startInput, endInput, totalDaysInput) {
    const startDate = new Date(startInput.value);
    const endDate = new Date(endInput.value);

    // Set the minimum date for the endDate input
    endInput.min = startInput.value;

    if (startDate && endDate && endDate >= startDate) {
        const timeDiff = endDate - startDate;
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Convert time to days
        totalDaysInput.value = daysDiff > 0 ? daysDiff : 0; // Ensure non-negative
    } else {
        totalDaysInput.value = 0; // Set to 0 if invalid dates
        endInput.value = "";
    }

    if (endDate < startDate) {
        Swal.fire({
            icon: "error",
            title: "End Date cannot be earlier than Start Date",
            text: "Choose another date!",
            timer: 3000,
            confirmButtonColor: "#AB2F2B",
            confirmButtonText: "OK",
        });
    }
}
