<script>
    function calculateTotalBTPerdiem() {
        let total = 0;
        let pageIdentifierName = document.getElementById("page-identifier-name");
        let nominalBTPerdiemInputs = document.querySelectorAll('input[name="nominal_bt_perdiem[]"]');

        if (pageIdentifierName && pageIdentifierName.value == "businessTripCAPerdiemForm") {
            // TODO: THIS IS ONLY FOR CURRENT DUPLICATE ELEMENT ISSUE.
            // IF THE DUPLICATE ISSUE FIXED, CHANGE THIS BEHAVIOUR
            let nominalBTPerdiemDivider = Math.floor(nominalBTPerdiemInputs.length / 2);
            Array.from(nominalBTPerdiemInputs)
            .slice(nominalBTPerdiemDivider)
            .forEach((input) => {
                total += cleanNumber(input.value);
            });
        } else {
            nominalBTPerdiemInputs
            .forEach((input) => {
                total += cleanNumber(input.value);
            });
        }

        document.querySelectorAll('input[name="total_bt_perdiem"]')
        .forEach((input) => {
            input.value = formatNumber(total);
        });

        if (typeof calculateTotalReimCA === "function") {
            calculateTotalReimCA();
        } else if (typeof calculateTotalBTCA === "function") {
            calculateTotalBTCA();
        }

        calculateTotalReq();

        if (typeof isCADecPerdiem !== "undefined" && isCADecPerdiem) {
            calculateTotalNominalBTBalance();
        }
    }

    function calculateTotalDaysPerdiem(input) {
        const formGroup = input.closest(".row").parentElement;
        const startDateInput = formGroup.querySelector("input.start-perdiem");
        const endDateInput = formGroup.querySelector("input.end-perdiem");
        const totalDaysInput = formGroup.querySelector("input.total-days-perdiem");
        const perdiemInput = document.getElementById("perdiem");
        const groupCompany = document.getElementById("group_company");
        const isOverseas = document.getElementById('is_overseas');
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

            // console.log("Group Company:", groupCompany.value);

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

                if (groupCompany && groupCompany.value !== "Plantations") {
                    allowance *= 1;
                } else if (
                    locationSelect.value === "Others" ||
                    otherLocationInput.value.trim() !== ""
                ) {
                    allowance *= 1;
                } else {
                    allowance *= 0.5;
                }

                if (totalDays >= 30) {
                    allowance *= 0.75;
                }

                if(isOverseas && isOverseas.checked){

                }else{
                    allowanceInput.value = formatNumberPerdiem(allowance);
                }

                calculateTotalBTPerdiem();

                if (typeof isCADecPerdiem !== "undefined" && isCADecPerdiem) {
                    calculateTotalNominalBTBalance();
                }
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

    function removeFormSubmitBTPerdiemDuplicateElement(form) {
        // TODO: THIS IS ONLY FOR CURRENT DUPLICATE ELEMENT ISSUE.
        // IF THE DUPLICATE ISSUE FIXED, REMOVE THIS

        let inputs = form.querySelectorAll('input[name="nominal_bt_perdiem[]"]');
        let divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('select[name="company_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('select[name="location_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('input[name="other_location_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('input[name="start_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('input[name="end_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });

        inputs = form.querySelectorAll('input[name="total_days_bt_perdiem[]"]');
        divider = Math.round(inputs.length / 2);
        Array.from(inputs)
        .slice(0, divider)
        .forEach((input) => {
            input.disabled = true;
        });
    }
</script>
