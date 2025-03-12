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
                            <div class="col-md-12 mb-3 text-right" id="buttonList"></div>
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

{{-- Admin Approval --}}
@if (request()->routeIs('cashadvanced.admin'))
<script>
    // Modal Export
    document.addEventListener("DOMContentLoaded", function () {
        var exportModal = document.getElementById("exportModal");
        var declareSection = document.querySelector(".declare-section");
        exportModal.addEventListener("show.bs.modal", function (event) {
            var button = event.relatedTarget;

            var transactionId = button.getAttribute("data-id");
            var status = button.getAttribute("data-status");
            console.log(status);

            var downloadLink = document.getElementById("downloadLink");
            downloadLink.href =
                '{{ route("cashadvanced.download", ":id") }}'.replace(
                    ":id",
                    transactionId
                );

            var declareLink = document.getElementById("declareLink");
            declareLink.href =
                '{{ route("cashadvanced.downloadDeclare", ":id") }}'.replace(
                    ":id",
                    transactionId
                );

            var transactionInput = document.getElementById("transaction_id");
            transactionInput.value = transactionId;

            if (
                status === "Pending" ||
                status === "Approved"
            ) {
                declareSection.style.display = "flex"; // Tampilkan
            } else {
                declareSection.style.display = "none"; // Sembunyikan
            }
        });
    });

    // Modal Mengubah Status
    document.addEventListener("DOMContentLoaded", function () {
        var statusModal = document.getElementById("statusModal");
        statusModal.addEventListener("show.bs.modal", function (event) {
            // Dapatkan tombol yang men-trigger modal
            var button = event.relatedTarget;

            // Ambil data-id dan data-status dari tombol tersebut
            var transactionId = button.getAttribute("data-id");
            var transactionStatus = button.getAttribute("data-status");
            var appSett = button.getAttribute("data-appsett");
            var dateReq = button.getAttribute("data-datereq");
            var caPaidDate = button.getAttribute("data-capaiddate");
            var paidDate = button.getAttribute("data-paiddate");

            // Temukan form di dalam modal dan update action-nya
            var form = statusModal.querySelector("form");
            var action = form.getAttribute("action");
            form.setAttribute("action", action.replace(":id", transactionId));

            // Set nilai transaction_id di input hidden
            var transactionInput = form.querySelector("#transaction_id");
            transactionInput.value = transactionId;

            // Pilih status yang sesuai di dropdown
            var statusSelect = form.querySelector("#ca_status");
            statusSelect.value = transactionStatus;

            var date_required = form.querySelector("#date_required");
            date_required.value = dateReq;
            
            var ca_paid_date = form.querySelector("#ca_paid_date");
            ca_paid_date.value = caPaidDate;

            var paidDateInput = form.querySelector("#paid_date");
            paid_date.value = paidDate;

            if (appSett === "Approved") {
                statusSelect.parentElement.style.display = "block";
                paidDateInput.parentElement.style.display = "block";
            } else {
                statusSelect.parentElement.style.display = "none";
                paidDateInput.parentElement.style.display = "none";
            }

            // Update opsi dropdown berdasarkan status yang dipilih
            updateStatusOptions(transactionStatus, statusSelect);
        });

        function updateStatusOptions(selectedStatus, statusSelect) {
            // Reset opsi yang ada
            var options = [
                { value: 'On Progress', text: 'On Progress' },
                { value: 'Refund', text: 'Refund' },
                { value: 'Done', text: 'Done' }
            ];

            // Filter opsi berdasarkan status yang dipilih
            var filteredOptions;
            if (selectedStatus === 'On Progress') {
                filteredOptions = options.filter(function(option) {
                    return option.value === 'On Progress' || option.value === 'Done';
                });
            } else if (selectedStatus === 'Refund') {
                filteredOptions = options.filter(function(option) {
                    return option.value === 'Refund' || option.value === 'Done';
                });
            } else {
                filteredOptions = options; // Default: tampilkan semua opsi
            }

            // Hapus opsi yang ada
            while (statusSelect.options.length > 0) {
                statusSelect.remove(0);
            }

            // Tambahkan opsi baru yang sudah difilter
            filteredOptions.forEach(function(option) {
                var newOption = new Option(option.text, option.value);
                statusSelect.add(newOption);
            });

            // Set nilai dropdown ke status yang dipilih
            statusSelect.value = selectedStatus;
        }
    });

    // Approval Request Modal
    document.addEventListener('DOMContentLoaded', function () {
        var approvalModal = document.getElementById('approvalModal');
        approvalModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;

            var transactionType = button.getAttribute('data-type');
            var transactionTotal = button.getAttribute('data-total');
            var transactionId = button.getAttribute('data-id');
            var transactionNo = button.getAttribute('data-no');
            var transactionSPPD = button.getAttribute('data-sppd');

            var form = approvalModal.querySelector('form');
            var action = form.getAttribute('action');
            form.setAttribute('action', action.replace(':id', transactionId));

            form.querySelector('#ca_type').value = transactionType;
            form.querySelector('#totalca').value = transactionTotal;
            form.querySelector('#no_id').value = transactionId;
            form.querySelector('#no_ca').value = transactionNo;
            form.querySelector('#bisnis_numb').value = transactionSPPD;

            var buttonList = document.getElementById('buttonList');
            buttonList.innerHTML = '';

            var previousLayerApproved = true; // Untuk mengecek status layer sebelumnya

            @foreach ($ca_approvals as $approval)
                if (transactionId === "{{ $approval->ca_id }}") {
                    var rowContainer = document.createElement('div');
                    rowContainer.className = 'row mb-3 text-right';

                    var nameCol = document.createElement('div');
                    nameCol.className = 'col-md-12 mb-2';
                    var nameText = document.createElement('div');
                    nameText.innerHTML = `
                        <b>{{ $approval->role_name }}</b> : {{ $approval->ReqName }}
                    `;
                    nameCol.appendChild(nameText);

                    var buttonCol = document.createElement('div');
                    buttonCol.className = 'col-md-10';

                    var dateText = document.createElement('p');

                    if ("{{ $approval->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval->by_admin }}" === "T" 
                            ? "{{ $approval->admin->name ?? 'Admin tidak tersedia.' }}" 
                            : "{{ $approval->ReqName }}";
                        
                        dateText.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy} ${"{{ $approval->by_admin }}" === "T" ? " (Admin)" : ""}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonCol.appendChild(dateText);
                    } else if (previousLayerApproved) {
                        // Form Data
                        var dataNoIdInput = document.createElement('input');
                        dataNoIdInput.type = 'hidden';
                        dataNoIdInput.name = 'data_no_id';
                        dataNoIdInput.value = "{{ $approval->id }}";

                        // Tombol Approve
                        var rejectButton = document.createElement('button');
                        rejectButton.type = 'button';
                        rejectButton.className = 'btn btn-sm btn-primary btn-pill px-1 me-1';
                        rejectButton.setAttribute('data-bs-toggle', 'modal'); // Menambahkan atribut data-bs-toggle
                        rejectButton.setAttribute('data-bs-target', '#modalReject'); // Menambahkan atribut data-bs-target
                        rejectButton.setAttribute('data-no-id', transactionId); // Menambahkan atribut data-no-id
                        rejectButton.setAttribute('data-no-ca', transactionNo); // Menambahkan atribut data-no-ca
                        rejectButton.setAttribute('data-no-idCA', '{{ $approval->id }}');
                        rejectButton.textContent = 'Reject';

                        // Tombol Approve
                        var approveButton = document.createElement('button');
                        approveButton.type = 'submit';
                        approveButton.name = 'action_ca_approve';
                        approveButton.value = 'Approve';
                        approveButton.className = 'btn btn-sm btn-success btn-pill px-1 me-1';
                        approveButton.textContent = 'Approve';
                        approveButton.setAttribute('data-no-ca', transactionNo); // Tambahkan data-no-ca agar SweetAlert bisa menggunakan nilai ini

                        // Tambahkan event listener SweetAlert pada tombol Approve
                        addSweetAlert(approveButton);

                        form.querySelector('#data_no_id').value = "{{ $approval->id }}";

                        buttonCol.appendChild(approveButton);
                        buttonCol.appendChild(rejectButton);
                    } else {
                        // Jika layer sebelumnya tidak disetujui, layer ini tidak menampilkan tombol
                        dateText.textContent = 'Waiting for previous approval';
                        buttonCol.appendChild(dateText);
                    }

                    // Jika approval_status tidak "Approved", previousLayerApproved menjadi false
                    if ("{{ $approval->approval_status }}" !== "Approved") {
                        previousLayerApproved = false;
                    }

                    rowContainer.appendChild(nameCol);
                    rowContainer.appendChild(buttonCol);

                    buttonList.appendChild(rowContainer);
                }
            @endforeach
        });
    });

    // Approval Declaration Modal
    document.addEventListener('DOMContentLoaded', function () {
        var approvalDecModal = document.getElementById('approvalDecModal');

        approvalDecModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;

            var transactionType = button.getAttribute('data-type');
            var transactionTotal = button.getAttribute('data-total');
            var transactionId = button.getAttribute('data-id');
            var transactionNo = button.getAttribute('data-no');
            var transactionSPPD = button.getAttribute('data-sppd');
            var transactionStart = button.getAttribute('data-start-date');
            var transactionEnd = button.getAttribute('data-end-date');
            var transactionTotal = button.getAttribute('data-total-days');

            document.getElementById('approval_no_ca').textContent = transactionNo;

            var form = approvalDecModal.querySelector('form');
            var action = form.getAttribute('action');
            form.setAttribute('action', action.replace(':id', transactionId));

            form.querySelector('#ca_type').value = transactionType;
            form.querySelector('#totalca').value = transactionTotal;
            form.querySelector('#no_id').value = transactionId;
            form.querySelector('#no_ca').value = transactionNo;
            form.querySelector('#bisnis_numb').value = transactionSPPD;

            // Clear existing content to prevent duplicates
            document.getElementById('requestList').innerHTML = '';
            document.getElementById('declarationList').innerHTML = '';

            var previousLayerApproved = true; // To check previous layer status
            var previousLayerApprovedDec = true; // To check previous declaration status

            var requestLabel = document.createElement('label');
            requestLabel.className = 'col-form-label mb-3';
            requestLabel.textContent = 'Approval Request';
            document.getElementById('requestList').appendChild(requestLabel);

            var declarationLabel = document.createElement('label');
            declarationLabel.className = 'col-form-label mb-3';
            declarationLabel.textContent = 'Approval Declaration';
            document.getElementById('declarationList').appendChild(declarationLabel);

            @foreach ($ca_approvals as $approval)
                if (transactionId === "{{ $approval->ca_id }}") {
                    var rowContainer = document.createElement('div');
                    rowContainer.className = 'row mb-3 text-right';

                    var nameCol = document.createElement('div');
                    nameCol.className = 'col-md-12';
                    var nameText = document.createElement('p');
                    nameText.innerHTML = `
                        <b>{{ $approval->role_name }}</b> : {{ $approval->ReqName }}
                    `;
                    nameCol.appendChild(nameText);

                    var buttonCol = document.createElement('div');
                    buttonCol.className = 'col-md-10';

                    var dateText = document.createElement('p');

                    if ("{{ $approval->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval->by_admin }}" === "T" 
                            ? "{{ $approval->admin->name ?? 'Admin tidak tersedia.' }}" 
                            : "{{ $approval->ReqName }}";
                        
                        dateText.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy} ${"{{ $approval->by_admin }}" === "T" ? " (Admin)" : ""}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonCol.appendChild(dateText);
                    } else if (previousLayerApproved) {
                        dateText.textContent = 'Something Wrong, This form just for Approve Declaration';
                        buttonCol.appendChild(dateText);
                    } else {
                        dateText.textContent = 'Waiting for previous approval';
                        buttonCol.appendChild(dateText);
                    }

                    if ("{{ $approval->approval_status }}" !== "Approved") {
                        previousLayerApproved = false;
                    }

                    rowContainer.appendChild(nameCol);
                    rowContainer.appendChild(buttonCol);

                    document.getElementById('requestList').appendChild(rowContainer);
                }
            @endforeach

            @foreach ($ca_sett as $approval_sett)
                if (transactionId === "{{ $approval_sett->ca_id }}") {
                    var rowContainerDec = document.createElement('div');
                    rowContainerDec.className = 'row mb-3 text-right';

                    var nameColDec = document.createElement('div');
                    nameColDec.className = 'col-md-12';
                    var nameTextDec = document.createElement('p');
                    nameTextDec.innerHTML = `
                        <b>{{ $approval_sett->role_name }}</b> : {{ $approval_sett->ReqName }}
                    `;
                    nameColDec.appendChild(nameTextDec);

                    var buttonColDec = document.createElement('div');
                    buttonColDec.className = 'col-md-10';

                    var dateTextDec = document.createElement('p');

                    if ("{{ $approval_sett->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval_sett->by_admin }}" === "T" 
                            ? "{{ $approval_sett->admin->name ?? 'Admin tidak tersedia.' }} (Admin)" 
                            : "{{ $approval_sett->ReqName }}";

                        dateTextDec.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval_sett->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval_sett->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonColDec.appendChild(dateTextDec);
                    } else if (previousLayerApprovedDec) {
                        var rejectButtonDec = document.createElement('button');
                        rejectButtonDec.type = 'button'; // Mengubah type menjadi 'button'
                        rejectButtonDec.className = 'btn btn-sm btn-primary btn-pill px-1 me-1 mb-2';
                        rejectButtonDec.setAttribute('data-bs-toggle', 'modal'); // Menambahkan atribut data-bs-toggle
                        rejectButtonDec.setAttribute('data-bs-target', '#modalRejectDec'); // Menambahkan atribut data-bs-target
                        rejectButtonDec.setAttribute('data-no-id', transactionId); // Menambahkan atribut data-no-id
                        rejectButtonDec.setAttribute('data-no-ca', transactionNo); // Menambahkan atribut data-no-ca
                        rejectButtonDec.setAttribute('data-start-date', transactionStart); // Menambahkan atribut data-start-date
                        rejectButtonDec.setAttribute('data-end-date', transactionEnd); // Menambahkan atribut data-end-date
                        rejectButtonDec.setAttribute('data-total-days', transactionTotal); // Menambahkan atribut data-total-days
                        rejectButtonDec.setAttribute('data-no-idCA', '{{ $approval_sett->id }}');
                        rejectButtonDec.textContent = 'Reject'; // Mengubah text button

                        var approveButtonDec = document.createElement('button');
                        approveButtonDec.type = 'submit';
                        approveButtonDec.name = 'action_ca_approve';
                        approveButtonDec.value = 'Approve';
                        approveButtonDec.className = 'btn btn-sm btn-success btn-pill px-1 me-1 mb-2';
                        approveButtonDec.textContent = 'Approve';
                        approveButtonDec.setAttribute('data-no-ca', transactionNo); // Tambahkan data-no-ca agar SweetAlert bisa menggunakan nilai ini

                        // Tambahkan event listener SweetAlert pada tombol Approve
                        addSweetAlertDec(approveButtonDec);

                        form.querySelector('#data_no_id').value = "{{ $approval_sett->id }}";

                        buttonColDec.appendChild(approveButtonDec);
                        buttonColDec.appendChild(rejectButtonDec);
                    } else {
                        dateTextDec.textContent = 'Waiting for previous approval';
                        buttonColDec.appendChild(dateTextDec);
                    }

                    if ("{{ $approval_sett->approval_status }}" !== "Approved") {
                        previousLayerApprovedDec = false;
                    }
                    rowContainerDec.appendChild(nameColDec);
                    rowContainerDec.appendChild(buttonColDec);

                    document.getElementById('declarationList').appendChild(rowContainerDec);
                }
            @endforeach
        });
    });

    // Approval Extend Modal
    document.addEventListener('DOMContentLoaded', function () {
        var approvalExtModal = document.getElementById('approvalExtModal');
        
        approvalExtModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;

            var transactionType = button.getAttribute('data-type');
            var transactionTotal = button.getAttribute('data-total');
            var transactionTotalAmmount = button.getAttribute('data-total-amount');
            var transactionId = button.getAttribute('data-id');
            var transactionNo = button.getAttribute('data-no');
            var transactionSPPD = button.getAttribute('data-sppd');
            var transactionStart = button.getAttribute('data-start-date');
            var transactionEnd = button.getAttribute('data-end-date');
            var extendEnd = button.getAttribute('data-ext-end');
            var extendTotal = button.getAttribute('data-ext-total');
            var extendReason = button.getAttribute('data-ext-reason');

            document.getElementById('approvalExt_no_ca').textContent = transactionNo;

            var form = approvalExtModal.querySelector('form');
            var action = form.getAttribute('action');
            form.setAttribute('action', action.replace(':id', transactionId));

            form.querySelector('#ca_type').value = transactionType;
            form.querySelector('#totalca').value = transactionTotalAmmount;
            form.querySelector('#no_id').value = transactionId;
            form.querySelector('#no_ca').value = transactionNo;
            form.querySelector('#bisnis_numb').value = transactionSPPD;
            form.querySelector('#ext_end_date').value = extendEnd;
            form.querySelector('#ext_totaldays').value = extendTotal;
            form.querySelector('#ext_reason').value = extendReason;

            // Clear existing content to prevent duplicates
            document.getElementById('requestExtList').innerHTML = '';
            document.getElementById('extendList').innerHTML = '';

            var previousLayerApproved = true;
            var previousLayerApprovedExt = true;

            var requestLabel = document.createElement('label');
            requestLabel.className = 'col-form-label mb-3';
            requestLabel.textContent = 'Approval Request';
            document.getElementById('requestExtList').appendChild(requestLabel);

            var extendLabel = document.createElement('label');
            extendLabel.className = 'col-form-label mb-3';
            extendLabel.textContent = 'Approval Extend';
            document.getElementById('extendList').appendChild(extendLabel);

            // First loop for CA approvals
            @foreach ($ca_approvals as $approval)
                if (transactionId === "{{ $approval->ca_id }}") {
                    var rowContainer = document.createElement('div');
                    rowContainer.className = 'row mb-3 text-right';

                    var nameCol = document.createElement('div');
                    nameCol.className = 'col-md-12';
                    var nameText = document.createElement('p');
                    nameText.innerHTML = `
                        <b>{{ $approval->role_name }}</b> : {{ $approval->ReqName }}
                    `;
                    nameCol.appendChild(nameText);

                    var buttonCol = document.createElement('div');
                    buttonCol.className = 'col-md-10';

                    var dateText = document.createElement('p');

                    if ("{{ $approval->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval->by_admin }}" === "T" 
                            ? "{{ $approval->admin->name ?? 'Admin tidak tersedia.' }}" 
                            : "{{ $approval->ReqName }}";
                        
                        dateText.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy} ${"{{ $approval->by_admin }}" === "T" ? " (Admin)" : ""}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonCol.appendChild(dateText);
                    } else if (previousLayerApproved) {
                        dateText.textContent = 'Something Wrong, This form just for Approve Extend';
                        buttonCol.appendChild(dateText);
                    } else {
                        dateText.textContent = 'Waiting for previous approval';
                        buttonCol.appendChild(dateText);
                    }

                    if ("{{ $approval->approval_status }}" !== "Approved") {
                        previousLayerApproved = false;
                    }

                    rowContainer.appendChild(nameCol);
                    rowContainer.appendChild(buttonCol);

                    document.getElementById('requestExtList').appendChild(rowContainer);
                }
            @endforeach

            // Create array to store matching extend items
            var matchingExtendItems = [];
            
            // First collect all matching items
            @foreach ($ca_extend as $approval_extend)
                if (transactionId === "{{ $approval_extend->ca_id }}") {
                    matchingExtendItems.push({
                        employee_id: "{{ $approval_extend->ReqName }}",
                        layer: "{{ $approval_extend->layer }}",
                        approval_status: "{{ $approval_extend->approval_status }}",
                        by_admin: "{{ $approval_extend->by_admin }}",
                        role_name: "{{ $approval_extend->role_name }}",
                        admin_name: "{{ $approval_extend->admin->name ?? 'Admin tidak tersedia.' }}",
                        approved_at: "{{ $approval_extend->approved_at }}",
                        id: "{{ $approval_extend->id }}"
                    });
                }
            @endforeach

            // Then render the items with dividers
            matchingExtendItems.forEach((item, index) => {
                var rowContainerExt = document.createElement('div');
                rowContainerExt.className = 'row mb-3 text-right';

                var nameColExt = document.createElement('div');
                nameColExt.className = 'col-md-12';
                var nameTextExt = document.createElement('p');
                nameTextExt.innerHTML = `
                    <b>${item.role_name}</b> : ${item.employee_id}
                `;
                nameColExt.appendChild(nameTextExt);

                var buttonColExt = document.createElement('div');
                buttonColExt.className = 'col-md-10';

                var dateTextExt = document.createElement('p');

                if (item.approval_status === "Approved") {
                    const approvedBy = item.by_admin === "T" 
                        ? item.admin_name 
                        : item.employee_id;
                    
                    dateTextExt.innerHTML = `
                        <div class="border rounded p-2 mb-2">  
                            <strong>Status:</strong> ${item.approval_status}<br>  
                            <strong>Approved By:</strong> ${approvedBy} ${item.by_admin === "T" ? " (Admin)" : ""}<br> 
                            <strong>Approved At:</strong> ${moment(item.approved_at).format('DD-MMM-YY')}  
                        </div>  
                    `;
                    buttonColExt.appendChild(dateTextExt);
                } else if (previousLayerApprovedExt) {
                    var rejectButtonExt = document.createElement('button');
                    rejectButtonExt.type = 'button';
                    rejectButtonExt.className = 'btn btn-sm btn-primary btn-pill px-1 me-1 mb-2';
                    rejectButtonExt.setAttribute('data-bs-toggle', 'modal');
                    rejectButtonExt.setAttribute('data-bs-target', '#modalRejectExt');
                    rejectButtonExt.setAttribute('data-no-id', transactionId);
                    rejectButtonExt.setAttribute('data-no-ca', transactionNo);
                    rejectButtonExt.setAttribute('data-start-date', transactionStart);
                    rejectButtonExt.setAttribute('data-end-date', transactionEnd);
                    rejectButtonExt.setAttribute('data-total-days', transactionTotal);
                    rejectButtonExt.setAttribute('data-no-idCA', item.id);
                    rejectButtonExt.textContent = 'Reject';

                    var approveButtonExt = document.createElement('button');
                    approveButtonExt.type = 'submit';
                    approveButtonExt.name = 'action_ca_approve';
                    approveButtonExt.value = 'Approve';
                    approveButtonExt.className = 'btn btn-sm btn-success btn-pill px-1 me-1 mb-2';
                    approveButtonExt.textContent = 'Approve';
                    approveButtonExt.setAttribute('data-no-ca', transactionNo);

                    addSweetAlertExt(approveButtonExt);

                    form.querySelector('#data_no_id').value = item.id;

                    buttonColExt.appendChild(approveButtonExt);
                    buttonColExt.appendChild(rejectButtonExt);
                } else {
                    dateTextExt.textContent = 'Waiting for previous approval';
                    buttonColExt.appendChild(dateTextExt);
                }

                if (item.approval_status !== "Approved") {
                    previousLayerApprovedExt = false;
                }

                rowContainerExt.appendChild(nameColExt);
                rowContainerExt.appendChild(buttonColExt);

                document.getElementById('extendList').appendChild(rowContainerExt);

                // Add divider after every 4 items, but only if we have more than 4 items total
                // and we're not at the last item
                if (matchingExtendItems.length > 4 && 
                    (index + 1) % 4 === 0 && 
                    (index + 1) !== matchingExtendItems.length) {
                    var dividerContainer = document.createElement('div');
                    dividerContainer.className = 'd-flex align-items-center my-3'; // Container fleksibel untuk divider
                    var lineLeft = document.createElement('hr');
                    lineLeft.className = 'flex-grow-1 border border-primary opacity-50 m-0'; // Garis kiri
                    var text = document.createElement('span');
                    text.className = 'mx-2 text-primary'; // Teks di tengah
                    text.textContent = 'Other Approval Extend';
                    var lineRight = document.createElement('hr');
                    lineRight.className = 'flex-grow-1 border border-primary opacity-50 m-0'; // Garis kanan
                    // Susun elemen dalam container
                    dividerContainer.appendChild(lineLeft);
                    dividerContainer.appendChild(text);
                    dividerContainer.appendChild(lineRight);

                    document.getElementById('extendList').appendChild(dividerContainer);
                }
            });
        });
    });

    // Approval Extend dan Declaration Modal
    document.addEventListener('DOMContentLoaded', function () {
        var approvalDecExtModal = document.getElementById('approvalDecExtModal');

        approvalDecExtModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;

            var transactionType = button.getAttribute('data-type');
            var transactionTotal = button.getAttribute('data-total');
            var transactionId = button.getAttribute('data-id');
            var transactionNo = button.getAttribute('data-no');
            var transactionSPPD = button.getAttribute('data-sppd');
            var transactionStart = button.getAttribute('data-start-date');
            var transactionEnd = button.getAttribute('data-end-date');
            var transactionTotal = button.getAttribute('data-total-days');

            document.getElementById('approvalExtDec_no_ca').textContent = transactionNo;

            var form = approvalDecExtModal.querySelector('form');
            var action = form.getAttribute('action');
            form.setAttribute('action', action.replace(':id', transactionId));

            form.querySelector('#ca_type').value = transactionType;
            form.querySelector('#totalca').value = transactionTotal;
            form.querySelector('#no_id').value = transactionId;
            form.querySelector('#data_no_id').value = transactionId;
            form.querySelector('#no_ca').value = transactionNo;
            form.querySelector('#bisnis_numb').value = transactionSPPD;

            // Clear existing content to prevent duplicates
            document.getElementById('requestExtDecList').innerHTML = '';
            document.getElementById('declarationExtDecList').innerHTML = '';
            document.getElementById('extendExtDecList').innerHTML = '';

            var previousLayerApproved = true; // To check previous layer status
            var previousLayerApprovedDec = true; // To check previous declaration status

            var requestLabel = document.createElement('label');
            requestLabel.className = 'col-form-label mb-3';
            requestLabel.textContent = 'Approval Request';
            document.getElementById('requestExtDecList').appendChild(requestLabel);

            var declarationLabel = document.createElement('label');
            declarationLabel.className = 'col-form-label mb-3';
            declarationLabel.textContent = 'Approval Declaration';
            document.getElementById('declarationExtDecList').appendChild(declarationLabel);

            var extendLabel = document.createElement('label');
            extendLabel.className = 'col-form-label mb-3';
            extendLabel.textContent = 'Approval Extend';
            document.getElementById('extendExtDecList').appendChild(extendLabel);

            @foreach ($ca_approvals as $approval)
                if (transactionId === "{{ $approval->ca_id }}") {
                    var rowContainer = document.createElement('div');
                    rowContainer.className = 'row mb-3 text-right';

                    var nameCol = document.createElement('div');
                    nameCol.className = 'col-md-12';
                    var nameText = document.createElement('p');
                    nameText.innerHTML = `
                        <b>{{ $approval->role_name }}</b> : {{ $approval->ReqName }}
                     `;
                    nameCol.appendChild(nameText);

                    var buttonCol = document.createElement('div');
                    buttonCol.className = 'col-md-12';

                    var dateText = document.createElement('p');

                    if ("{{ $approval->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval->by_admin }}" === "T" 
                            ? "{{ $approval->admin->name ?? 'Admin tidak tersedia.' }}" 
                            : "{{ $approval->ReqName }}";
                        
                        dateText.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy} ${"{{ $approval->by_admin }}" === "T" ? " (Admin)" : ""}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonCol.appendChild(dateText);
                        // dateText.textContent = "{{ $approval->approval_status }} ({{ \Carbon\Carbon::parse($approval->approved_at)->format('d-M-y') }})";
                        // buttonCol.appendChild(dateText);
                    } else if (previousLayerApproved) {
                        dateText.textContent = 'Something Wrong, This form just for Approve Declaration';
                        buttonCol.appendChild(dateText);
                    } else {
                        dateText.textContent = 'Waiting for previous approval';
                        buttonCol.appendChild(dateText);
                    }

                    if ("{{ $approval->approval_status }}" !== "Approved") {
                        previousLayerApproved = false;
                    }

                    rowContainer.appendChild(nameCol);
                    rowContainer.appendChild(buttonCol);

                    document.getElementById('requestExtDecList').appendChild(rowContainer);
                }
            @endforeach

            @foreach ($ca_sett as $approval_sett)
                if (transactionId === "{{ $approval_sett->ca_id }}") {
                    var rowContainerDec = document.createElement('div');
                    rowContainerDec.className = 'row mb-3 text-right';

                    var nameColDec = document.createElement('div');
                    nameColDec.className = 'col-md-12';
                    var nameTextDec = document.createElement('p');
                    nameTextDec.innerHTML = `
                        <b>{{ $approval_sett->role_name }}</b> : {{ $approval_sett->ReqName }}
                    `;
                    nameColDec.appendChild(nameTextDec);

                    var buttonColDec = document.createElement('div');
                    buttonColDec.className = 'col-md-12';

                    var dateTextDec = document.createElement('p');

                    if ("{{ $approval_sett->approval_status }}" === "Approved") {
                        const approvedBy = "{{ $approval_sett->by_admin }}" === "T" 
                            ? "{{ $approval_sett->admin->name ?? 'Admin tidak tersedia.' }} (Admin)" 
                            : "{{ $approval_sett->ReqName }}";

                        dateTextDec.innerHTML = `
                            <div class="border rounded p-2 mb-2">  
                                <strong>Status:</strong> {{ $approval_sett->approval_status }}<br>  
                                <strong>Approved By:</strong> ${approvedBy}<br> 
                                <strong>Approved At:</strong> {{ \Carbon\Carbon::parse($approval_sett->approved_at)->format('d-M-y') }}  
                            </div>  
                        `;
                        buttonColDec.appendChild(dateTextDec);
                    } else if (previousLayerApprovedDec) {
                        var rejectButtonDec = document.createElement('button');
                        rejectButtonDec.type = 'button'; // Mengubah type menjadi 'button'
                        rejectButtonDec.className = 'btn btn-sm btn-primary btn-pill px-1 me-1 mb-2';
                        rejectButtonDec.setAttribute('data-bs-toggle', 'modal'); // Menambahkan atribut data-bs-toggle
                        rejectButtonDec.setAttribute('data-bs-target', '#modalRejectDec'); // Menambahkan atribut data-bs-target
                        rejectButtonDec.setAttribute('data-no-id', transactionId); // Menambahkan atribut data-no-id
                        rejectButtonDec.setAttribute('data-no-ca', transactionNo); // Menambahkan atribut data-no-ca
                        rejectButtonDec.setAttribute('data-start-date', transactionStart); // Menambahkan atribut data-start-date
                        rejectButtonDec.setAttribute('data-end-date', transactionEnd); // Menambahkan atribut data-end-date
                        rejectButtonDec.setAttribute('data-total-days', transactionTotal); // Menambahkan atribut data-total-days
                        rejectButtonDec.setAttribute('data-no-idCA', '{{ $approval_sett->id }}');
                        rejectButtonDec.textContent = 'Reject'; // Mengubah text button

                        var approveButtonDec = document.createElement('button');
                        approveButtonDec.type = 'submit';
                        approveButtonDec.name = 'action_ca_approve';
                        approveButtonDec.value = 'Approve';
                        approveButtonDec.className = 'btn btn-sm btn-success btn-pill px-1 me-1 mb-2';
                        approveButtonDec.textContent = 'Approve';
                        approveButtonDec.setAttribute('data-no-ca', transactionNo); // Tambahkan data-no-ca agar SweetAlert bisa menggunakan nilai ini

                        // Tambahkan event listener SweetAlert pada tombol Approve
                        addSweetAlertDecExt(approveButtonDec);

                        form.querySelector('#data_no_id').value = "{{ $approval_sett->id }}";

                        buttonColDec.appendChild(approveButtonDec);
                        buttonColDec.appendChild(rejectButtonDec);
                    } else {
                        dateTextDec.textContent = 'Waiting for previous approval';
                        buttonColDec.appendChild(dateTextDec);
                    }

                    if ("{{ $approval_sett->approval_status }}" !== "Approved") {
                        previousLayerApprovedDec = false;
                    }
                    rowContainerDec.appendChild(nameColDec);
                    rowContainerDec.appendChild(buttonColDec);

                    document.getElementById('declarationExtDecList').appendChild(rowContainerDec);
                }
            @endforeach

            var matchingDeclarationExtendItems = [];

            @foreach ($ca_extend as $approval_extend)
                if (transactionId === "{{ $approval_extend->ca_id }}") {
                    matchingDeclarationExtendItems.push({
                        employee_id: "{{ $approval_extend->ReqName }}",
                        layer: "{{ $approval_extend->layer }}",
                        approval_status: "{{ $approval_extend->approval_status }}",
                        by_admin: "{{ $approval_extend->by_admin }}",
                        role_name: "{{ $approval_extend->role_name }}",
                        admin_name: "{{ $approval_extend->admin->name ?? 'Admin tidak tersedia.' }}",
                        approved_at: "{{ $approval_extend->approved_at }}",
                        id: "{{ $approval_extend->id }}"
                    });
                }
            @endforeach

            matchingDeclarationExtendItems.forEach((item, index) => {
                var rowContainerExt = document.createElement('div');
                rowContainerExt.className = 'row mb-3 text-right';

                var nameColExt = document.createElement('div');
                nameColExt.className = 'col-md-12';
                var nameTextExt = document.createElement('p');
                nameTextExt.innerHTML = `
                    <b>${item.role_name}</b> : ${item.employee_id}
                `;
                nameColExt.appendChild(nameTextExt);

                var buttonColExt = document.createElement('div');
                buttonColExt.className = 'col-md-12';

                var dateTextExt = document.createElement('p');

                if (item.approval_status === "Approved") {
                    const approvedBy = item.by_admin === "T" 
                        ? item.admin_name 
                        : item.employee_id;
                    
                    dateTextExt.innerHTML = `
                        <div class="border rounded p-2 mb-2">  
                            <strong>Status:</strong> ${item.approval_status}<br>  
                            <strong>Approved By:</strong> ${approvedBy} ${item.by_admin === "T" ? " (Admin)" : ""}<br> 
                            <strong>Approved At:</strong> ${moment(item.approved_at).format('DD-MMM-YY')}  
                        </div>  
                    `;
                    buttonColExt.appendChild(dateTextExt);
                } else if (previousLayerApprovedExt) {
                    dateText.textContent = 'Something Wrong, This form just for Approve Declaration';
                    buttonCol.appendChild(dateTextExt);
                } else {
                    dateTextExt.textContent = 'Waiting for previous approval';
                    buttonColExt.appendChild(dateTextExt);
                }

                if (item.approval_status !== "Approved") {
                    previousLayerApprovedExt = false;
                }

                rowContainerExt.appendChild(nameColExt);
                rowContainerExt.appendChild(buttonColExt);

                document.getElementById('extendExtDecList').appendChild(rowContainerExt);

                // Add divider after every 4 items, but only if we have more than 4 items total
                // and we're not at the last item
                if (matchingDeclarationExtendItems.length > 4 && 
                    (index + 1) % 4 === 0 && 
                    (index + 1) !== matchingDeclarationExtendItems.length) {
                    var dividerContainer = document.createElement('div');
                    dividerContainer.className = 'd-flex align-items-center my-3'; // Container fleksibel untuk divider
                    var lineLeft = document.createElement('hr');
                    lineLeft.className = 'flex-grow-1 border border-primary opacity-50 m-0'; // Garis kiri
                    var text = document.createElement('span');
                    text.className = 'mx-2 text-primary'; // Teks di tengah
                    text.textContent = 'Other Approval Extend';
                    var lineRight = document.createElement('hr');
                    lineRight.className = 'flex-grow-1 border border-primary opacity-50 m-0'; // Garis kanan
                    // Susun elemen dalam container
                    dividerContainer.appendChild(lineLeft);
                    dividerContainer.appendChild(text);
                    dividerContainer.appendChild(lineRight);

                    document.getElementById('extendExtDecList').appendChild(dividerContainer);
                }

            });
        });
    });

    function addSweetAlert(approveButton) {
        approveButton.addEventListener("click", function (event) {
            event.preventDefault(); // Mencegah submit form secara langsung
            const transactionCA = approveButton.getAttribute("data-no-ca");
            const form = document.getElementById("approveForm");

            Swal.fire({
                title: `Do you want to approve transaction "${transactionCA}"?`,
                text: "You won't be able to revert this!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0c63e4",
                cancelButtonColor: "#9a2a27",
                confirmButtonText: "Yes, approve it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat input baru untuk action_ca_approve
                    const input = document.createElement("input");
                    input.type = "hidden"; // Set input sebagai hidden
                    input.name = "action_ca_approve"; // Set nama input
                    input.value = "Approve"; // Set nilai input

                    // Tambahkan input ke form
                    form.appendChild(input);

                    form.submit(); // Kirim form
                }
            });
        });
    }

    function addSweetAlertDec(approveButtonDec) {
        approveButtonDec.addEventListener("click", function (event) {
            event.preventDefault(); // Mencegah submit form secara langsung
            const transactionCA = approveButtonDec.getAttribute("data-no-ca");
            const form = document.getElementById("approveFormDec");

            Swal.fire({
                title: `Do you want to approve transaction "${transactionCA}"?`,
                text: "You won't be able to revert this!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0c63e4",
                cancelButtonColor: "#9a2a27",
                confirmButtonText: "Yes, approve it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat input baru untuk action_ca_approve
                    const input = document.createElement("input");
                    input.type = "hidden"; // Set input sebagai hidden
                    input.name = "action_ca_approve"; // Set nama input
                    input.value = "Approve"; // Set nilai input

                    // Tambahkan input ke form
                    form.appendChild(input);

                    form.submit(); // Kirim form
                }
            });
        });
    }

    function addSweetAlertExt(approveButtonExt) {
        approveButtonExt.addEventListener("click", function (event) {
            event.preventDefault(); // Mencegah submit form secara langsung
            const transactionCA = approveButtonExt.getAttribute("data-no-ca");
            const form = document.getElementById("approveFormExt");

            Swal.fire({
                title: `Do you want to approve transaction "${transactionCA}"?`,
                text: "You won't be able to revert this!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0c63e4",
                cancelButtonColor: "#9a2a27",
                confirmButtonText: "Yes, approve it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat input baru untuk action_ca_approve
                    const input = document.createElement("input");
                    input.type = "hidden"; // Set input sebagai hidden
                    input.name = "action_ca_approve"; // Set nama input
                    input.value = "Approve"; // Set nilai input

                    // Tambahkan input ke form
                    form.appendChild(input);

                    form.submit(); // Kirim form
                }
            });
        });
    }

    function addSweetAlertDecExt(approveButtonDec) {
        approveButtonDec.addEventListener("click", function (event) {
            event.preventDefault(); // Mencegah submit form secara langsung
            const transactionCA = approveButtonDec.getAttribute("data-no-ca");
            const form = document.getElementById("approveFormDecExt");

            Swal.fire({
                title: `Do you want to approve transaction "${transactionCA}"?`,
                text: "You won't be able to revert this!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0c63e4",
                cancelButtonColor: "#9a2a27",
                confirmButtonText: "Yes, approve it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat input baru untuk action_ca_approve
                    const input = document.createElement("input");
                    input.type = "hidden"; // Set input sebagai hidden
                    input.name = "action_ca_approve"; // Set nama input
                    input.value = "Approve"; // Set nilai input

                    // Tambahkan input ke form
                    form.appendChild(input);

                    form.submit(); // Kirim form
                }
            });
        });
    }


    // Reject Request Modal
    document.addEventListener("DOMContentLoaded", function () {
        var modalReject = document.getElementById("modalReject");
        modalReject.addEventListener("show.bs.modal", function (event) {
            var button = event.relatedTarget;

            var transactionId = button.getAttribute("data-no-id");
            var transactionNo = button.getAttribute("data-no-ca");
            var transactionIdCA = button.getAttribute("data-no-idCA");
            console.log(transactionIdCA);

            // Mendefinisikan form terlebih dahulu
            var form = modalReject.querySelector("form");

            form.querySelector("#data_no_id").value = transactionIdCA;

            document.getElementById("reject_no_ca_2").textContent = transactionNo;

            var form = modalReject.querySelector("form");
            var action = form.getAttribute("action");
            form.setAttribute("action", action.replace(":id", transactionId));
        });
    });

    // Reject Declaration Modal
    document.addEventListener("DOMContentLoaded", function () {
        var modalRejectDec = document.getElementById("modalRejectDec");
        modalRejectDec.addEventListener("show.bs.modal", function (event) {
            var button = event.relatedTarget;

            var transactionId = button.getAttribute("data-no-id");
            var transactionNo = button.getAttribute("data-no-ca");
            var transactionIdCA = button.getAttribute("data-no-idCA");
            console.log(transactionIdCA);

            // Mendefinisikan form terlebih dahulu
            var form = modalRejectDec.querySelector("form");

            form.querySelector("#data_no_id").value = transactionIdCA;

            document.getElementById("rejectDec_no_ca_2").textContent =
                transactionNo;

            var form = modalRejectDec.querySelector("form");
            var action = form.getAttribute("action");
            form.setAttribute("action", action.replace(":id", transactionId));
        });
    });

    // Reject Extend Modal
    document.addEventListener("DOMContentLoaded", function () {
        var modalRejectExt = document.getElementById("modalRejectExt");
        modalRejectExt.addEventListener("show.bs.modal", function (event) {
            var button = event.relatedTarget;

            var transactionId = button.getAttribute("data-no-id");
            var transactionNo = button.getAttribute("data-no-ca");
            var transactionIdCA = button.getAttribute("data-no-idCA");
            console.log(transactionIdCA);

            // Mendefinisikan form terlebih dahulu
            var form = modalRejectExt.querySelector("form");

            form.querySelector("#data_no_id").value = transactionIdCA;

            document.getElementById("rejectExt_no_ca_2").textContent =
                transactionNo;

            var form = modalRejectExt.querySelector("form");
            var action = form.getAttribute("action");
            form.setAttribute("action", action.replace(":id", transactionId));
        });
    });
</script>
@endif
