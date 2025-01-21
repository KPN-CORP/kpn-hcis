@if (request()->routeIs('cashadvancedDeklarasi'))
    <div class="modal fade" id="modalExtend" tabindex="-1" aria-labelledby="modalExtendLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalExtendLabel">Extending End Date - <label id="ext_no_ca">3123333123</label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('cashadvanced.extend') }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="start">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="end">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="total">Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light" id="totaldays" name="totaldays" type="text" min="0" value="0" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <p class="text-center mt-2">--<b>Changing too</b>--</p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="new_start">Start Date</label>
                                <input type="date" name="ext_start_date" id="ext_start_date" class="form-control bg-light" placeholder="mm/dd/yyyy" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="new_end">New End Date</label>
                                <input type="date" name="ext_end_date" id="ext_end_date" class="form-control" placeholder="mm/dd/yyyy" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label" for="new_total">New Total Days</label>
                                <div class="input-group">
                                    <input class="form-control bg-light" id="ext_totaldays" name="ext_totaldays" type="text" min="0" value="0" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reason</label>
                                <textarea name="ext_reason" id="ext_reason" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="no_id" id="no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="extendButton">Extending</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif (request()->routeIs('approval.cashadvancedForm'))
    <div class="modal fade" id="modalReject" tabindex="-1" aria-labelledby="modalRejectLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalRejectLabel">Reject - <label id="reject_no_ca"></label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('approval.cashadvancedApproved',$transactions->id) }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reasons</label>
                                <textarea name="reject_info" id="reject_info" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="reject_no_id" id="reject_no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_reject" value="Reject" class=" btn btn-primary btn-pill px-4 me-2">Reject</button>
                        {{-- <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="rejectButton">Extending</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif (request()->routeIs('approval.cashadvancedFormDeklarasi'))
    <div class="modal fade" id="modalRejectDec" tabindex="-1" aria-labelledby="modalRejectDecLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalRejectDecLabel">Rejection Reason - <label id="rejectDec_no_ca"></label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('approval.cashadvancedDeclare',$transactions->id) }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reasons for Refusal</label>
                                <textarea name="reject_reason" id="reject_reason" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="rejectDec_no_id" id="rejectDec_no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_reject" value="Reject" class=" btn btn-primary btn-pill px-4 me-2">Reject</button>
                        {{-- <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="rejectButton">Extending</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif (request()->routeIs('cashadvanced.admin'))
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Cash Advanced</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('cashadvanced.adupdate', ':id') }}" method="POST">@csrf
                    <div class="modal-body">
                            {{-- <label for="transaction-id-display" class="col-form-label">Transaction ID: </label> --}}
                            {{-- <input type="text" class="form-control" id="transaction-id-display" readonly> --}}
                            <input type="hidden" name="transaction_id" id="transaction_id">
                        <div class="mb-3">
                            <label for="date_required">CA Date Required :</label>
                            <input type="date" id="date_required" name="date_required" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="ca_paid_date">CA Paid Date :</label>
                            <input type="date" id="ca_paid_date" name="ca_paid_date" class="form-control">
                        </div>
                        <div class="mb-3" id="status-container" style="display: none;">
                            <label for="recipient-name" class="col-form-label">Status :</label>
                            <select class="form-select" name="ca_status" id="ca_status">
                                <option value="On Progress">On Progress</option>
                                <option value="Refund">Refund</option>
                                <option value="Done">Done</option>
                            </select>
                        </div>
                        <div class="mb-3" id="paid-date-container" style="display: none;">
                            <label for="paid_date">Declaration Paid Date :</label>
                            <input type="date" id="paid_date" name="paid_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Cash Advanced</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="transaction_id">
                    <div class="row">
                        <div class="declare-request" style="display: flex;">
                            <div class="col-md-7 mb-3 text-center">
                                <label for="export-request" class="col-form-label">Export PDF Request:</label>
                            </div>
                            <div class="col-md-5 mb-3 text-center">
                                <a href="{{ route('cashadvanced.download', ':id') }}" id="downloadLink" target="_blank" class="btn btn-outline-primary" title="Download PDF">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <div class="declare-section" style="display: none;">
                            <div class="col-md-7 mb-3 text-center">
                                <label for="export-deklarasi" class="col-form-label">Export PDF Deklarasi:</label>
                            </div>
                            <div class="col-md-5 mb-3 text-center">
                                <a href="{{ route('cashadvanced.downloadDeclare', ':id') }}" id="declareLink" target="_blank" class="btn btn-outline-primary" title="Download PDF Deklarasi">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Approval Cash Advanced Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm" action="{{ route('approvalAdmin.cashadvancedApprovedAdmin', ':id') }}" method="POST">@csrf
                    <div class="modal-body">
                        <input type="hidden" name="no_id" id="no_id">
                        <input type="hidden" name="ca_type" id="ca_type">
                        <input type="hidden" name="totalca" id="totalca">
                        <input type="hidden" name="no_ca" id="no_ca">
                        <input type="hidden" name="bisnis_numb" id="bisnis_numb">
                        <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                        <input type="hidden" name="approval_status" id="approval_status">
                        <input type="hidden" name="data_no_id" id="data_no_id"> <!-- Hidden field to hold data_no_id -->
                        <label for="recipient-name" class="col-form-label">Approval Request : </label>
                        <div class="row">
                            <div class="col-md-12 mb-3 text-center" id="nameList"></div>
                            <div class="col-md-12 mb-3 text-center" id="buttonList"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReject" tabindex="-1" aria-labelledby="modalRejectLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalRejectLabel">Rejection Reason - <label id="reject_no_ca_2"></label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_rejectDec" method="POST" action="{{ route('approvalAdmin.cashadvancedApprovedAdmin',':id') }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reasons for Refusal</label>
                                <textarea name="reject_info" id="reject_info" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="data_no_id" id="data_no_id">
                            <input type="hidden" name="rejectDec_no_id" id="rejectDec_no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_reject" value="Reject" class=" btn btn-primary btn-pill px-4 me-2">Reject</button>
                        {{-- <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="rejectButton">Extending</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approvalDecModal" tabindex="-1" aria-labelledby="approvalDecModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalDecModalLabel">Approval Cash Advanced Declaration Update - <label id="approval_no_ca"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveFormDec" action="{{ route('approvalDecAdmin.cashadvancedDecApprovedAdmin', ':id') }}" method="POST">@csrf
                    <div class="modal-body">
                        <input type="hidden" name="no_id" id="no_id">
                        <input type="hidden" name="ca_type" id="ca_type">
                        <input type="hidden" name="totalca" id="totalca">
                        <input type="hidden" name="no_ca" id="no_ca">
                        <input type="hidden" name="bisnis_numb" id="bisnis_numb">
                        <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                        <input type="hidden" name="approval_status" id="approval_status">
                        <input type="hidden" name="data_no_id" id="data_no_id"> <!-- Hidden field to hold data_no_id -->
                        <div class="row">
                            <div class="col-md-6 mb-3 text-right border-end border-danger-subtle" id="requestList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Request : </label>
                            </div>
                            <div class="col-md-6 mb-3 text-right" id="declarationList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Declaration : </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRejectDec" tabindex="-1" aria-labelledby="modalRejectDecLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalRejectDecLabel">Rejection Reason - <label id="rejectDec_no_ca_2"></label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_rejectDec" method="POST" action="{{ route('approvalDecAdmin.cashadvancedDecApprovedAdmin',':id') }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reasons for Refusal</label>
                                <textarea name="reject_info" id="reject_info" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="data_no_id" id="data_no_id">
                            <input type="hidden" name="rejectDec_no_id" id="rejectDec_no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_reject" value="Reject" class=" btn btn-primary btn-pill px-4 me-2">Reject</button>
                        {{-- <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="rejectButton">Extending</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approvalExtModal" tabindex="-1" aria-labelledby="approvalExtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalExtModalLabel">Approval Cash Advanced Extend Update - <label id="approvalExt_no_ca"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveFormExt" action="{{ route('approvalExtAdmin.cashadvancedExtApprovedAdmin', ':id') }}" method="POST">@csrf
                    <div class="modal-body">
                        <input type="hidden" name="no_id" id="no_id">
                        <input type="hidden" name="ca_type" id="ca_type">
                        <input type="hidden" name="totalca" id="totalca">
                        <input type="hidden" name="no_ca" id="no_ca">
                        <input type="hidden" name="bisnis_numb" id="bisnis_numb">
                        <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                        <input type="hidden" name="approval_status" id="approval_status">
                        <input type="hidden" name="data_no_id" id="data_no_id"> <!-- Hidden field to hold data_no_id -->
                        <input type="hidden" name="ext_end_date" id="ext_end_date"> <!-- Hidden field to hold data_no_id -->
                        <input type="hidden" name="ext_totaldays" id="ext_totaldays"> <!-- Hidden field to hold data_no_id -->
                        <input type="hidden" name="ext_reason" id="ext_reason"> <!-- Hidden field to hold data_no_id -->
                        <div class="row">
                            <div class="col-md-6 mb-3 text-right border-end border-danger-subtle" id="requestExtList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Request : </label>
                            </div>
                            <div class="col-md-6 mb-3 text-right" id="extendList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Extend : </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRejectExt" tabindex="-1" aria-labelledby="modalRejectExtLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-center fs-5" id="modalRejectExtLabel">Rejection Reason Extend - <label id="rejectExt_no_ca_2"></label></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_rejectExt" method="POST" action="{{ route('approvalExtAdmin.cashadvancedExtApprovedAdmin',':id') }}">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label" for="reason">Reasons for Refusal</label>
                                <textarea name="reject_info" id="reject_info" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="data_no_id" id="data_no_id">
                            <input type="hidden" name="rejectExt_no_id" id="rejectExt_no_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="action_ca_reject" value="Reject" class=" btn btn-primary btn-pill px-4 me-2">Reject</button>
                        {{-- <button type="submit" name="action_ca_submit" value="Pending" class="btn btn-primary" id="rejectButton">Extending</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approvalDecExtModal" tabindex="-1" aria-labelledby="approvalDecExtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalDecExtModalLabel">Approval Cash Advanced Declaration Update - <label id="approvalExtDec_no_ca"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveFormDecExt" action="{{ route('approvalDecAdmin.cashadvancedDecApprovedAdmin', ':id') }}" method="POST">@csrf
                    <div class="modal-body">
                        <input type="hidden" name="no_id" id="no_id">
                        <input type="hidden" name="ca_type" id="ca_type">
                        <input type="hidden" name="totalca" id="totalca">
                        <input type="hidden" name="no_ca" id="no_ca">
                        <input type="hidden" name="bisnis_numb" id="bisnis_numb">
                        <input type="hidden" name="repeat_days_selected" id="repeatDaysSelected">
                        <input type="hidden" name="approval_status" id="approval_status">
                        <input type="hidden" name="data_no_id" id="data_no_id"> <!-- Hidden field to hold data_no_id -->
                        <div class="row">
                            <div class="col-md-4 mb-3 text-right border-end border-danger-subtle" id="requestExtDecList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Request : </label>
                            </div>
                            <div class="col-md-4 mb-3 text-right border-end border-danger-subtle" id="declarationExtDecList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Declaration : </label>
                            </div> 
                            <div class="col-md-3 mb-3 text-right" id="extendExtDecList">
                                <label for="recipient-name" class="col-form-label mb-3">Approval Declaration : </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Success --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'Ok'
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
                confirmButtonText: 'Ok'
            });
        });
    </script>
@endif

<script>
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', () => {
            const transactionId = button.getAttribute('data-id');
            const form = document.getElementById(`delete-form-${transactionId}`);

            Swal.fire({
                title: "Do you want to delete this transaction?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#AB2F2B",
                cancelButtonColor: "#CCCCCC",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
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
</script>

{{-- Form Deklarasi --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.declaration-button').forEach(button => {
            button.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent immediate form submission

                const form = document.getElementById('cashadvancedForm');
                const caType = document.getElementById('ca_type').value; // Get the value from the hidden input

                // Check if the form is valid before proceeding
                if (!form.checkValidity()) {
                    form.reportValidity(); // Show validation messages if invalid
                    return; // Exit if the form is not valid
                }
                const totalAwal = document.getElementById('totalca_deklarasi').value || '0';
                const totalCA = document.getElementById('totalca').value || '0';
                const totalReal = document.getElementById('totalca_real').value || '0';

                let inputSummary = '';

                if (caType === "dns") {
                    const totalBtPerdiem = document.getElementById('total_bt_perdiem').value || '0';
                    const totalBtPenginapan = document.getElementById('total_bt_penginapan').value || '0';
                    const totalBtTransport = document.getElementById('total_bt_transport').value || '0';
                    const totalBtLainnya = document.getElementById('total_bt_lainnya').value || '0';

                    // Summary for Business Trip
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Perdiem</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtPerdiem}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Transport</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtTransport}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Accommodation</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtPenginapan}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Others</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtLainnya}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Declaration</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Cash Advanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalAwal}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Balanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalReal}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalBtPerdiem == 0 && totalBtPenginapan == 0 && totalBtTransport == 0 && totalBtLainnya == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "All fields (Perdiem, Accommodation, Transport, Others) are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else if (caType === "ndns") {
                    // Summary for Non Business Trip
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Non Bussiness Trip</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Declaration</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Cash Advanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalAwal}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Balanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalReal}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalCA == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Fields are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else if (caType === "entr") {
                    const totalEntDetail = document.getElementById('total_e_detail').value || '0';
                    // Summary for Entertainment
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Entertainment Detail</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalEntDetail}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Declaration</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Total Cash Advanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalAwal}</td>
                            </tr>
                            <tr>
                                <td style="width: 60%"><strong>Balanced</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalReal}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalEntDetail == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Fields are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else {
                    // Default message if no valid option is selected
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a valid CA Type!'
                    });
                    return; // Exit if no valid option is selected
                }

                // Show SweetAlert confirmation with the input summary
                Swal.fire({
                    title: "Do you want to submit this request?",
                    html: `You won't be able to revert this!<br><br>${inputSummary}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#AB2F2B",
                    cancelButtonColor: "#CCCCCC",
                    confirmButtonText: "Yes, submit it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a hidden input field to hold the action value
                        const input = document.createElement('input');
                        input.type = 'hidden'; // Hidden input so it doesn't show in the form
                        input.name = button.name; // Use the button's name attribute
                        input.value = button.value; // Use the button's value attribute

                        form.appendChild(input); // Append the hidden input to the form
                        form.submit(); // Submit the form only if confirmed
                    }
                });
            });
        });
    });
</script>

{{-- Form Add --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.submit-button').forEach(button => {
            button.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent immediate form submission

                const form = document.getElementById('cashadvancedForm');
                const caType = document.getElementById('ca_type').value; // Get the value from the hidden input
                const isDraft = button.value === "Draft";

                // Check if the form is valid before proceeding
                if (!form.checkValidity()) {
                    form.reportValidity(); // Show validation messages if invalid
                    return; // Exit if the form is not valid
                }

                const totalCA = document.getElementById('totalca').value || '0';

                let inputSummary = '';

                if (caType === "dns") {
                    const totalBtPerdiem = document.getElementById('total_bt_perdiem').value || '0';
                    const totalBtPenginapan = document.getElementById('total_bt_penginapan').value || '0';
                    const totalBtTransport = document.getElementById('total_bt_transport').value || '0';
                    const totalBtLainnya = document.getElementById('total_bt_lainnya').value || '0';

                    // Summary for Business Trip
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="text-align:right;width: 60%"><strong>Total Perdiem</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtPerdiem}</td>
                            </tr>
                            <tr>
                                <td style="text-align:right;width: 60%"><strong>Total Transport</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtTransport}</td>
                            </tr>
                            <tr>
                                <td style="text-align:right;width: 60%"><strong>Total Accommodation</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtPenginapan}</td>
                            </tr>
                            <tr>
                                <td style="text-align:right;width: 60%"><strong>Total Others</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalBtLainnya}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="text-align:right;width: 60%"><strong>Total Request</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalBtPerdiem == 0 && totalBtPenginapan == 0 && totalBtTransport == 0 && totalBtLainnya == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "All fields (Perdiem, Accommodation, Transport, Others) are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else if (caType === "ndns") {
                    // Summary for Non Business Trip
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Non Bussiness Trip</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Request</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalCA == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Fields are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else if (caType === "entr") {
                    const totalEntDetail = document.getElementById('total_e_detail').value || '0';
                    // Summary for Entertainment
                    inputSummary = `
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Entertainment Detail</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalEntDetail}</td>
                            </tr>
                        </table>

                        <hr>

                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 60%"><strong>Total Request</strong></td>
                                <td>:</td>
                                <td><b>Rp.</b> ${totalCA}</td>
                            </tr>
                        </table>
                    `;

                    if ( totalEntDetail == 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "Fields are 0. Please fill in the values.",
                            icon: "warning",
                            confirmButtonColor: "#AB2F2B",
                            confirmButtonText: "OK",
                        });
                        return; // Exit without showing the confirmation if all fields are zero
                    }
                } else {
                    // Default message if no valid option is selected
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a valid CA Type!'
                    });
                    return; // Exit if no valid option is selected
                }

                // Show SweetAlert confirmation with the input summary
                Swal.fire({
                    title: isDraft ? "Do you want to save this request as a draft?" : "Do you want to submit this request?",
                    html: `You won't be able to revert this!<br><br>${inputSummary}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#AB2F2B",
                    cancelButtonColor: "#CCCCCC",
                    confirmButtonText: isDraft ? "Yes" : "Yes, submit it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a hidden input field to hold the action value
                        const input = document.createElement('input');
                        input.type = 'hidden'; // Hidden input so it doesn't show in the form
                        input.name = button.name; // Use the button's name attribute
                        input.value = button.value; // Use the button's value attribute

                        form.appendChild(input); // Append the hidden input to the form
                        form.submit(); // Submit the form only if confirmed
                    }
                });
            });
        });
    });
</script>

{{-- Approval --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.approve-button').forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault(); // Mencegah submit form secara langsung
                const transactionCA = button.getAttribute('data-no-ca');
                const form = document.getElementById('approveForm');

                Swal.fire({
                    title: `Do you want to approve transaction "${transactionCA}"?`,
                    text: "You won't be able to revert this!",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#0c63e4",
                    cancelButtonColor: "#9a2a27",
                    confirmButtonText: "Yes, approve it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Buat input baru untuk action_ca_approve
                        const input = document.createElement('input');
                        input.type = 'hidden'; // Set input sebagai hidden
                        input.name = 'action_ca_approve'; // Set nama input
                        input.value = 'Approve'; // Set nilai input

                        // Tambahkan input ke form
                        form.appendChild(input);

                        form.submit(); // Kirim form
                    }
                });
            });
        });
    });
</script>
