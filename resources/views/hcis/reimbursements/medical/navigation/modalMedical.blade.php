{{-- Excel Health Coverage Limit --}}
<div class="modal fade" id="importExcelHealtCoverage" tabindex="-1" aria-labelledby="importExcelHealtCoverageLabel" aria-hidden="true">
    <div class="modal-dialog" style="wid">
        <form method="POST" action="{{ route('import.medical') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelHealtCoverage">Import Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" name="file" required>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('download-template') }}" class="btn btn-primary">Download Template</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Success --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK',
            });
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: `
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                `,
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK',
            });
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent form from submitting immediately

                const transactionId = button.getAttribute('data-id');
                const form = document.getElementById(`deleteForm_${transactionId}`);
                const noSppd = document.getElementById(`no_sppd_${transactionId}`).value;

                Swal.fire({
                    title: `Do you want to delete this request?\n (${noSppd})`,
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#AB2F2B", // Confirm button color
                    cancelButtonColor: "#CCCCCC", // Cancel button color
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Only submit the form if the user confirms
                    }
                });
            });
        });
    });
</script>
