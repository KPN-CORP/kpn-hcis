@if (auth()->check() && (auth()->user()->employee && (strtolower(auth()->user()->employee->group_company) == "downstream")))
    <script>
        function filterTransportHubByType(element) {
            const type = $(element).val();

            const row = $(element).closest('.row');
            if (!row) {
                return;
            }

            const dari = row.find('select[name^="dari_tkt["]');
            const dari_dalam_kota = row.find('select[name^="dari_tkt_dalam_kota["]');
            const ke = row.find('select[name^="ke_tkt["]');
            const ke_dalam_kota = row.find('select[name^="ke_tkt_dalam_kota["]');

            if (dari.length > 0) {
                filterTransportHubLocation(dari, type);
            }

            if (dari_dalam_kota.length > 0) {
                filterTransportHubLocation(dari_dalam_kota, type);
            }

            if (ke.length > 0) {
                filterTransportHubLocation(ke, type);
            }

            if (ke_dalam_kota.length > 0) {
                filterTransportHubLocation(ke_dalam_kota, type);
            }
        }

        function filterTransportHubLocation(select, type) {
            if (!select.data('all-options')) {
                select.data('all-options', select.html());
            }

            const allOptions = select.data('all-options');

            const temp = $('<select></select>').html(allOptions);

            const newOptions = temp.find('option').filter(function () {
                const value = ($(this).val() || '').toLowerCase();

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
            }).clone();

            select.empty().append(newOptions);
            select.val('');
            select.trigger('change');
        }
    </script>
@else
    <script>
        function filterTransportHubByType(element) {
            return;
        }
    </script>
@endif
