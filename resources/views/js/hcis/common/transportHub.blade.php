<script>
    function filterTransportHubByType(element) {
        const type = $(element).val();

        const row = $(element).closest('.row');

        const dari = row.find('select[name="dari_tkt[]"]');
        const ke = row.find('select[name="ke_tkt[]"]');

        filterTransportLocation(dari, type);
        filterTransportLocation(ke, type);
    }

    function filterTransportHubLocation(select, type) {
        if (!select.data('all-options')) {
            select.data('all-options', select.html());
        }

        const allOptions = select.data('all-options');

        if (!type) {
            select.html(allOptions);
            select.val('');
            select.trigger('change.select2');
            return;
        }

        const temp = $('<select>' + allOptions + '</select>');
        const newOptions = temp.find('option').filter(function () {
            const value = $(this).val().toLowerCase();

            if (value === '') {
                return true;
            }

            if (value === 'others') {
                return true;
            }

            if (type === 'Train') {
                return value.includes('stasiun');
            }

            if (type === 'Airplane') {
                return value.includes('bandara');
            }

            if (type === 'Ferry') {
                return value.includes('pelabuhan');
            }

            return true;
        });

        select.html(newOptions);
        select.val('');
        select.trigger('change.select2');
    }
</script>
