<script>
var formCountMeals = 0;

window.addEventListener("DOMContentLoaded", function () {
    formCountMeals = document.querySelectorAll(
        "#form-container-meals > div"
    ).length;
});

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormMeals(index, event);
});

function removeFormMeals(index, event) {
    event.preventDefault();
    if (formCountMeals > 0) {
        const formContainer = document.getElementById(
            `form-container-bt-meals-${index}`
        );
        if (formContainer) {
            const nominalInput = formContainer.querySelector(
                `#nominal_bt_meals_${index}`
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_meals"]').value
                );
                total -= nominalValue;
                document.querySelector('input[name="total_bt_meals"]').value =
                    formatNumber(total);
                calculateTotalNominalBTTotal();
            }
            $(`#form-container-bt-meals-${index}`).remove();
            formCountMeals--;
        }
    }
}

function clearFormMeals(index, event) {
    event.preventDefault();
    let nominalValue = cleanNumber(
        document.querySelector(`#nominal_bt_meals_${index}`).value
    );
    let total = cleanNumber(
        document.querySelector('input[name="total_bt_meals"]').value
    );
    total -= nominalValue;
    document.querySelector('input[name="total_bt_meals"]').value =
        formatNumber(total);

    // Clear the inputs
    const formContainer = document.getElementById(
        `form-container-bt-meals-${index}`
    );
    formContainer
        .querySelectorAll('input[type="text"], input[type="date"]')
        .forEach((input) => {
            input.value = "";
        });
    formContainer.querySelector("textarea").value = "";

    // Reset nilai untuk nominal BT Meals
    document.querySelector(`#nominal_bt_meals_${index}`).value = 0;
    calculateTotalNominalBTTotal();
}

function calculateTotalNominalBTMeals() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_bt_meals[]"]')
        .forEach((input) => {
            total += cleanNumber(input.value);
        });
    document.getElementById("total_bt_meals").value = formatNumber(total);
}

function onNominalChange() {
    calculateTotalNominalBTMeals();
}

function calculateTotalDaysMeals(startInput, endInput, totalDaysInput) {
    const startDate = new Date(startInput.value);
    const endDate = new Date(endInput.value);

    // Set the minimum date for the endDate input
    endInput.min = startInput.value;

    if (startDate && endDate && endDate >= startDate) {
        const timeDiff = endDate - startDate;
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Convert time to days
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
</script>