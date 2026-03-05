<script>
    function calculateTotalReimCA() {
        let total = 0;

        let input = document.querySelector('input[name="total_bt_perdiem"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        input = document.querySelector('input[name="total_bt_transport"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        input = document.querySelector('input[name="total_bt_penginapan"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        input = document.querySelector('input[name="total_bt_lainnya"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        document.querySelectorAll('input[name="totalca"]')
        .forEach((input) => {
            input.value = formatNumber(total);
        });
    }
</script>
