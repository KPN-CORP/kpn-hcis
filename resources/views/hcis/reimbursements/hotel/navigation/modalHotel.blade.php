<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="rejectReasonModalLabel">Rejection Information</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Rejected by</strong>
                    </div>
                    <div class="col-md-8">
                        <span id="rejectedBy"></span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>Rejection reason</strong>
                    </div>
                    <div class="col-md-8">
                        <span id="rejectionReason"></span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>Rejection date</strong>
                    </div>
                    <div class="col-md-8">
                        <span id="rejectionDate"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary rounded-pill"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="detailModalLabel">Detail Information</h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <h6 id="detailTypeHeader" class="mb-3"></h6>
                <div id="detailContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary rounded-pill"
                    data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h1 class="modal-title fs-5 text-white" id="bookingModalLabel">Booking Detail - <label id="book_no_htl" style="font-weight: bold"></label></h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="bookingForm">@csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="booking_code">Booking Code</label>
                            <input name="booking_code" id="booking_code" class="form-control" placeholder="Write Booking Code ..." required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="booking_price">Hotel Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input class="form-control form-control-sm" name="booking_price" id="booking_price" type="text" value="0"
                                    onfocus="this.value = this.value === '0' ? '' : this.value;"
                                    oninput="formatNumber(this)"
                                    onblur="removeFormatting(this)">
                            </div>
                        </div>
                        <input type="hidden" name="book_no_id" id="book_no_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="action_htl_book" value="Submit" class=" btn btn-primary btn-pill px-4 me-2">Submit</button>
                </div>
            </form>
        </div>
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
                confirmButtonText: 'OK'
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
                confirmButtonText: 'Ok'
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

    function showPendingAlert() {
        Swal.fire({
            title: 'Cannot Add Data!',
            text: 'You still have 2 Outstanding CA Please Check Your Request or Declaration',
            icon: 'warning',
            confirmButtonColor: "#9a2a27",
            confirmButtonText: 'Ok'
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const bookingModal = document.getElementById('bookingModal');
        bookingModal.addEventListener('show.bs.modal', function (event) {
            // Tombol yang memicu modal
            const button = event.relatedTarget;
            const noId = button.getAttribute('data-no-id');
            const noHtl = button.getAttribute('data-no-htl');

            // Isi form modal dengan nilai dari tombol
            const bookNoHtlLabel = document.getElementById('book_no_htl');
            const bookNoIdInput = document.getElementById('book_no_id');
            const bookingForm = document.getElementById('bookingForm');

            bookNoHtlLabel.textContent = noHtl;
            bookNoIdInput.value = noId;

            // Set action form dengan ID yang dinamis
            bookingForm.action = `/hotel/admin/booking/${noId}`;
        });
    });


    const bookingModal = document.getElementById('bookingModal');
    bookingModal.addEventListener('hidden.bs.modal', function () {
        // Mengatur nilai default input kembali seperti semula
        document.getElementById('bookingForm').reset();
        document.getElementById('booking_price').value = '0';
    });

    function formatNumber(element) {
        let num = element.value.replace(/\./g, '');

        element.value = num.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function removeFormatting(element) {
        element.value = element.value.replace(/\./g, '');
    }

</script>
