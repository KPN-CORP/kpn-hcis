<script>
    function calculateTotalReq() {
        let total = 0;

        let input = document.querySelector('input[name="totalca"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        input = document.querySelector('input[name="total_ent_detail"]');
        if (input && input.value) {
            total += parseNumber(input.value);
        }

        document.querySelectorAll('input[name="totalreq"]')
        .forEach((input) => {
            input.value = formatNumber(total);
        });
        document.querySelectorAll('input[name="totalreq2"]')
        .forEach((input) => {
            input.value = formatNumber(total);
        });
    }
</script>
