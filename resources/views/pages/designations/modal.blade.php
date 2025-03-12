@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "Warning!",
                text: "{{ session('error') }}",
                icon: "error",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.update-button').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent form from submitting immediately

                const transactionId = button.getAttribute('data-id');
                const form = this.closest('form');
                const formId = form.id;

                let designationId;
                if (formId.startsWith('dept-head-update-form-')) {
                    designationId = document.getElementById(
                        `dept-designation-id-${transactionId}`).value;
                } else if (formId.startsWith('director-update-form-')) {
                    designationId = document.getElementById(
                        `director-designation-id-${transactionId}`).value;
                }

                Swal.fire({
                    title: `Do you want to change this data ?\n (${designationId})`,
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#4BB543", // Primary color
                    cancelButtonColor: "#CCCCC", // Darker shade for cancel button
                    confirmButtonText: "Yes, Change it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
