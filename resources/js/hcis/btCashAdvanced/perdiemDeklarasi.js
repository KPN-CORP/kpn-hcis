var formCountPerdiem = 0;
let perdiemData = [];

window.addEventListener("DOMContentLoaded", function () {
    formCountPerdiem = document.querySelectorAll(
        "#form-container-perdiem > div"
    ).length;
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
            `form-container-bt-perdiem-${index}`
        );
        if (formContainer) {
            // const nominalInput = formContainer.querySelector(`#nominal_bt_perdiem_${index}`);
            const nominalInput = document.querySelector(
                `#nominal_bt_perdiem_${index}`
            );
            if (nominalInput) {
                let nominalValue = cleanNumber(nominalInput.value);
                let total = cleanNumber(
                    document.querySelector('input[name="total_bt_perdiem"]')
                        .value
                );
                total -= nominalValue;
                document.querySelector('input[name="total_bt_perdiem"]').value =
                    formatNumber(total);
                calculateTotalNominalBTTotal();
            }
            formContainer.remove();

            perdiemData = perdiemData.filter(
                (data) => data.index !== index.toString()
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
            `#nominal_bt_perdiem_${index}`
        );
        if (nominalInput) {
            let nominalValue = cleanNumber(nominalInput.value);
            let total = cleanNumber(
                document.querySelector('input[name="total_bt_perdiem"]').value
            );
            total -= nominalValue;
            document.querySelector('input[name="total_bt_perdiem"]').value =
                formatNumber(total);
            nominalInput.value = 0;
            calculateTotalNominalBTTotal();
        }

        const formContainer = document.getElementById(
            `form-container-bt-perdiem-${index}`
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
                `#company_bt_perdiem_${index}`
            );
            if (companyCodeSelect) {
                companyCodeSelect.selectedIndex = 0; // Reset the select element to the default option
                var event = new Event("change");
                companyCodeSelect.dispatchEvent(event); // Trigger the change event to update the select2 component
            }

            const locationSelect = formContainer.querySelector(
                `#location_bt_perdiem_${index}`
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
        }

        perdiemData = perdiemData.filter(
            (data) => data.index !== index.toString()
        );
    }
}

function calculateTotalDaysPerdiem(input) {
    const formGroup = input.closest(".row").parentElement;
    const startDateInput = formGroup.querySelector("input.start-perdiem");
    const endDateInput = formGroup.querySelector("input.end-perdiem");
    const totalDaysInput = formGroup.querySelector("input.total-days-perdiem");
    const perdiemInput = document.getElementById("perdiem");
    const groupCompany = document.getElementById("group_company");
    const allowanceInput = formGroup.querySelector(
        'input[name="nominal_bt_perdiem[]"]'
    );

    const formIndex = formGroup.getAttribute("id").match(/\d+/)[0];
    // Cek apakah tanggal sudah digunakan di form lain
    if (isDateUsed(startDateInput.value, endDateInput.value, formIndex)) {
        Swal.fire({
            icon: "error",
            title: "Date has been used",
            text: "Please choose another date!",
            timer: 2000,
            confirmButtonColor: "#AB2F2B",
            confirmButtonText: "OK",
        });
        startDateInput.value = "";
        endDateInput.value = "";
        return;
    }

    if (startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
            const diffTime = Math.abs(endDate - startDate);
            const totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalDaysInput.value = totalDays;

            const perdiem = parseFloat(perdiemInput.value) || 0;
            let allowance = totalDays * perdiem;

            const locationSelect = formGroup.querySelector(
                'select[name="location_bt_perdiem[]"]'
            );
            const otherLocationInput = formGroup.querySelector(
                'input[name="other_location_bt_perdiem[]"]'
            );

            if (groupCompany.value !== "Plantations"){
                allowance *= 1;
            }else if (locationSelect.value === "Others" || otherLocationInput.value.trim() !== "") {
                allowance *= 1;
            } else {
                allowance *= 0.5;
            }

            allowanceInput.value = formatNumberPerdiem(allowance);
            calculateTotalNominalBTPerdiem();
        } else {
            totalDaysInput.value = 0;
            allowanceInput.value = 0;
        }
    } else {
        totalDaysInput.value = 0;
        allowanceInput.value = 0;
    }

    // Cek apakah data Perdiem untuk index ini sudah ada, jika ada update, jika belum tambahkan
    const existingPerdiemIndex = perdiemData.findIndex(
        (data) => data.index === formIndex
    );

    if (existingPerdiemIndex !== -1) {
        // Jika ada, perbarui data di array
        perdiemData[existingPerdiemIndex].startDate = startDateInput.value;
        perdiemData[existingPerdiemIndex].endDate = endDateInput.value;
    } else {
        perdiemData.push({
            index: formIndex,
            startDate: startDateInput.value,
            endDate: endDateInput.value,
        });
    }
}

function calculateTotalNominalBTPerdiem() {
    let total = 0;
    document
        .querySelectorAll('input[name="nominal_bt_perdiem[]"]')
        .forEach((input) => {
            total += cleanNumber(input.value);
        });
    document.querySelector('input[name="total_bt_perdiem"]').value =
        formatNumber(total);
    calculateTotalNominalBTTotal();
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

    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);

    // Set the min attribute of the end_date input to the selected start_date
    endDateInput.min = startDateInput.value;

    // Validate dates
    if (endDate < startDate) {
        alert("End Date cannot be earlier than Start Date");
        endDateInput.value = "";
    }

    // Update min and max values for all dynamic perdiem date fields
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

    document
        .querySelectorAll('input[name="total_days_bt_perdiem[]"]')
        .forEach(function (input) {
            calculateTotalDaysPerdiem(input);
        });
}
document.addEventListener("DOMContentLoaded", initializeDateInputs);
