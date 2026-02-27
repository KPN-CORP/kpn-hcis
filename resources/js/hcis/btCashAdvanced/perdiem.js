var formCountPerdiem = 0;
let perdiemData = [];
var perdiemData = typeof perdiemData !== "undefined" ? perdiemData : [];
window.addEventListener("DOMContentLoaded", function () {
    // 1. Cek container
    const container = document.getElementById("form-container-perdiem");
    if (container) {
        formCountPerdiem = container.querySelectorAll(".perdiem-item").length;
    }

    // 2. TRIGGER MANUAL (SOLUSI AMOUNT 0)
    // Kita paksa browser "merasa" inputnya baru diubah
    setTimeout(function () {
        document
            .querySelectorAll("input.start-perdiem")
            .forEach(function (input) {
                // Cek apakah input ada isinya secara JS
                if (input.value) {
                    // Panggil fungsi hitung
                    window.calculateTotalDaysPerdiem(input);
                }
            });
    }, 500); // Kasih delay 0.5 detik biar HTML render dulu sempurna

    // 3. Listener Global lain
    const startInput = document.getElementById("mulai");
    const endInput = document.getElementById("kembali");
    if (startInput)
        startInput.addEventListener("change", window.handleDateChange);
    if (endInput) endInput.addEventListener("change", window.handleDateChange);
});

function calculateTotalNominalBTTotal() {
    let total = 0;
    document
        .querySelectorAll('input[name="total_bt_perdiem"]')
        .forEach((input) => {
            total += parseNumber(input.value);
        });
    document
        .querySelectorAll('input[name="total_bt_transport"]')
        .forEach((input) => {
            total += parseNumber(input.value);
        });
    document
        .querySelectorAll('input[name="total_bt_penginapan"]')
        .forEach((input) => {
            total += parseNumber(input.value);
        });
    document
        .querySelectorAll('input[name="total_bt_lainnya"]')
        .forEach((input) => {
            total += parseNumber(input.value);
        });
    document.querySelector('input[name="totalca"]').value = formatNumber(total);
}

// Run the function on page load
document.addEventListener("DOMContentLoaded", function () {
    calculateTotalNominalBTTotal(); // Calculate the total immediately when the page loads
    calculateTotalNominalBTENTTotal();
});

function isDateInRange(date, startDate, endDate) {
    const targetDate = new Date(date).setHours(0, 0, 0, 0);
    const start = new Date(startDate).setHours(0, 0, 0, 0);
    const end = new Date(endDate).setHours(0, 0, 0, 0);
    return targetDate >= start && targetDate <= end;
}

function isDateUsed(startDate, endDate, index) {
    // Cek apakah tanggal sudah digunakan di form lain
    return perdiemData.some((data) => {
        if (data.index !== index) {
            // Cek untuk index yang berbeda
            // Cek apakah range tanggal bentrok dengan form lain
            return (
                isDateInRange(startDate, data.startDate, data.endDate) ||
                isDateInRange(endDate, data.startDate, data.endDate) ||
                isDateInRange(data.startDate, startDate, endDate) ||
                isDateInRange(data.endDate, startDate, endDate)
            );
        }
        return false;
    });
}

$(".btn-warning").click(function (event) {
    event.preventDefault();
    var index = $(this).closest(".card-body").index() + 1;
    removeFormPerdiem(index, event);
});

function removeFormPerdiem(index, event) {
    event.preventDefault();
    if (formCountPerdiem > 0) {
        const formContainer = document.getElementById(
            `form-container-bt-perdiem-${index}`,
        );
        if (formContainer) {
            // const nominalInput = formContainer.querySelector(`#nominal_bt_perdiem_${index}`);
            const nominalInput = document.querySelector(
                `#nominal_bt_perdiem_${index}`,
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_perdiem"]')
                        .value,
                );
                total -= nominalValue;
                document.querySelector('input[name="total_bt_perdiem"]').value =
                    formatNumber(total);
                calculateTotalNominalBTTotal();
                calculateTotalNominalBTENTTotal();
            }
            formContainer.remove();

            perdiemData = perdiemData.filter(
                (data) => data.index !== index.toString(),
            );
            console.log("Data setelah dihapus:", perdiemData); // Cek di console

            calculateTotalNominalBTPerdiem();
        }
    }
}

function clearFormPerdiem(index, event) {
    event.preventDefault();
    if (formCountPerdiem > 0) {
        const nominalInput = document.querySelector(
            `#nominal_bt_perdiem_${index}`,
        );
        if (nominalInput) {
            let nominalValue = cleanNumber(nominalInput.value);
            let total = cleanNumber(
                document.querySelector('input[name="total_bt_perdiem"]').value,
            );
            total -= nominalValue;
            document.querySelector('input[name="total_bt_perdiem"]').value =
                formatNumber(total);
            nominalInput.value = 0;
            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        const formContainer = document.getElementById(
            `form-container-bt-perdiem-${index}`,
        );
        if (formContainer) {
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

            const companyCodeSelect = formContainer.querySelector(
                `#company_bt_perdiem_${index}`,
            );
            if (companyCodeSelect) {
                companyCodeSelect.selectedIndex = 0; // Reset the select element to the default option
                var event = new Event("change");
                companyCodeSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
            }

            const locationSelect = formContainer.querySelector(
                `#location_bt_perdiem_${index}`,
            );
            if (locationSelect) {
                locationSelect.selectedIndex = 0; // Reset the select element to the default option
                var event = new Event("change");
                locationSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
            }

            formContainer.querySelectorAll("select").forEach((select) => {
                select.selectedIndex = 0;
            });

            formContainer.querySelectorAll("textarea").forEach((textarea) => {
                textarea.value = "";
            });

            calculateTotalNominalBTTotal();
            calculateTotalNominalBTENTTotal();
        }

        perdiemData = perdiemData.filter(
            (data) => data.index !== index.toString(),
        );
    }
}

function calculateTotalNominalBTPerdiem() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_bt_perdiem[]"]')
        .forEach((input) => {
            let val = input.value;
            if (typeof cleanNumber === "function") {
                total += cleanNumber(val);
            } else if (typeof parseNumber === "function") {
                total += parseNumber(val);
            } else {
                total += parseFloat(val.replace(/[^0-9.-]+/g, "")) || 0;
            }
        });

    const totalPerdiemInput = document.querySelector(
        'input[name="total_bt_perdiem"]',
    );
    if (totalPerdiemInput) {
        totalPerdiemInput.value = formatNumber(total);
    }

    calculateTotalNominalBTTotal();

    if (typeof calculateTotalNominalBTENTTotal === "function") {
        calculateTotalNominalBTENTTotal();
    }
}

function onNominalChange() {
    calculateTotalNominalBTPerdiem();
}

function toggleOtherLocation(selectElement, index) {
    const otherLocationDiv = document.getElementById("other-location-" + index);

    if (selectElement.value === "Others") {
        otherLocationDiv.style.display = "block";
    } else {
        otherLocationDiv.style.display = "none";
    }
}

// Optionally, if you want to trigger this on page load
document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll('[id^="location_bt_perdiem_"]');
    selects.forEach((select) => {
        const index = select.id.split("_").pop();
        toggleOtherLocation(select, index);
    });
});

function initializeDateInputs() {
    const startDateInput = document.getElementById("mulai");
    const endDateInput = document.getElementById("kembali");

    // If there are existing values, set the min attribute and handle initial validation
    if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
    }
    handleDateChange(); // Initial call to update related fields
}

document.getElementById("mulai").addEventListener("change", handleDateChange);
document.getElementById("kembali").addEventListener("change", handleDateChange);

function handleDateChange() {
    const startDateInput = document.getElementById("mulai");
    const endDateInput = document.getElementById("kembali");

    if (!startDateInput || !endDateInput) return;

    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);

    // Set min date
    endDateInput.min = startDateInput.value;

    if (endDate < startDate) {
        alert("End Date cannot be earlier than Start Date");
        endDateInput.value = "";
    }

    // Update min dan max attribute saja, JANGAN trigger kalkulasi ulang di sini
    document
        .querySelectorAll('input[name="start_bt_perdiem[]"]')
        .forEach(function (input) {
            input.min = startDateInput.value;
            input.max = endDateInput.value;
        });

    document
        .querySelectorAll('input[name="end_bt_perdiem[]"]')
        .forEach(function (input) {
            input.min = startDateInput.value;
            input.max = endDateInput.value;
        });
}
document.addEventListener("DOMContentLoaded", initializeDateInputs);
