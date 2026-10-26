<script>
//Medical Form JS
$(document).ready(function () {
    // Handle change event on medical type selection
    $("#date, #medical_type").on("change", function () {
        const selectedDate = $("#date").val();
        const selectedYear = selectedDate
            ? new Date(selectedDate).getFullYear()
            : null;
        const selectedTypes = $("#medical_type").val();
        const balanceContainer = $("#balanceContainer");

        balanceContainer.empty(); // Clear previous balances

        if (selectedYear && selectedTypes && selectedTypes.length > 0) {
            selectedTypes.forEach(function (type) {
                // Fetch the balance based on type and year
                const balance = typeToBalanceMap[type]?.[selectedYear] || 0;
                const balanceGroup = `
                <div class="col-md-3 mb-3">
                    <label for="${type}" class="form-label">${type} Plafond (${selectedYear})</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="medical_plafond_${type}" class="form-control bg-light" value="${formatCurrency(
                    balance
                )}" readonly>
                    </div>
                </div>
                `;
                balanceContainer.append(balanceGroup); // Append the balance input dynamically
            });
        }
    });

    function formatCurrency(value) {
        return new Intl.NumberFormat("id-ID").format(value); // Format number as currency
    }
});
$(document).ready(function () {
    // Partial ini ikut di-include di halaman daftar (medicalAdmin,
    // medicalApproval, medical) yang tidak pernah mendefinisikan
    // medicalTypeData — hanya halaman form yang melakukannya. Tanpa penjagaan
    // ini, blok di bawah melempar "medicalTypeData is not defined" dan
    // menghentikan sisa script di halaman tersebut.
    // Versi lama:
    // var typeToNameMap = {};
    // medicalTypeData.forEach(function (type) {
    //     typeToNameMap[type.medical_type] = type.name;
    // });
    var typeToNameMap = {};
    if (typeof medicalTypeData !== "undefined" && Array.isArray(medicalTypeData)) {
        medicalTypeData.forEach(function (type) {
            typeToNameMap[type.medical_type] = type.name;
        });
    }

    $("#medical_type").on("change", function () {
        var selectedTypes = $(this).val();
        var dynamicForms = $("#dynamicForms");
        dynamicForms.empty();

        if (selectedTypes && selectedTypes.length > 0) {
            selectedTypes.forEach(function (type) {
                var formGroup = `
                <div class="col-md-3 mb-3">
                    <label for="${type}" class="form-label">${type} Claim</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control currency-input" id="${type}" name="medical_costs[${type}]" placeholder="0" required>
                    </div>
                </div>
            `;
                dynamicForms.append(formGroup);
            });

            // Re-initialize currency formatting for new inputs
            initCurrencyFormatting();
        }
    });

    function initCurrencyFormatting() {
        $(".currency-input").on("input", function () {
            var value = $(this).val().replace(/\D/g, "");
            $(this).val(formatCurrency(value));
        });
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat("id-ID").format(value);
    }

    // Initialize currency formatting
    initCurrencyFormatting();
});

function formatCurrency(input) {
    // Your currency formatting logic here
    let value = input.value.replace(/\D/g, ""); // Remove non-digit characters
    if (value) {
        value = (parseInt(value, 10) || 0).toLocaleString("id-ID"); // Format number
        input.value = value;
    }
}

//date medical
const today = new Date();
// Set the date for two weeks ago
const twoWeeksAgo = new Date();
twoWeeksAgo.setDate(today.getDate() - 240); //ubah ke 60 jika mau dikunci 2 bulan

// Format the dates to YYYY-MM-DD
const formattedToday = today.toISOString().split("T")[0];
const formattedTwoWeeksAgo = twoWeeksAgo.toISOString().split("T")[0];

// Set the min attribute for the input to two weeks ago
// Input #date hanya ada di halaman form medical. Di halaman daftar yang juga
// meng-include partial ini, getElementById mengembalikan null sehingga
// setAttribute melempar TypeError dan script sesudahnya tidak jalan.
// Versi lama:
// const dateInput = document.getElementById("date");
// dateInput.setAttribute("min", formattedTwoWeeksAgo);
// dateInput.setAttribute("max", formattedToday); // Optional: To limit selection to today
const dateInput = document.getElementById("date");
if (dateInput) {
    dateInput.setAttribute("min", formattedTwoWeeksAgo);
    dateInput.setAttribute("max", formattedToday); // Optional: To limit selection to today
}
</script>