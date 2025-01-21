@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <style>
        th {
            color: white !important;
            text-align: center;
        }

        #dt-length-0 {
            margin-bottom: 10px;
        }

        .table {
            border-collapse: separate;
            width: 100%;
            /* position: relative; */
            overflow: auto;
        }

        .table thead th {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            top: 0 !important;
            z-index: 2 !important;
            background-color: #AB2F2B !important;
            border-bottom: 2px solid #ddd !important;
            padding-right: 6px;
            /* box-shadow: inset 2px 0 0 #fff; */
        }

        .table tbody td {
            background-color: #fff !important;
            padding-right: 10px;
            position: relative;
        }

        .table th.sticky-col-header {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            left: 0 !important;
            z-index: 3 !important;
            background-color: #AB2F2B !important;
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            /* box-shadow: inset 2px 0 0 #fff; */
        }

        .table td.sticky-col {
            position: -webkit-sticky !important;
            /* For Safari */
            position: sticky !important;
            left: 0 !important;
            z-index: 1 !important;
            background-color: #fff !important;
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            box-shadow: inset 6px 0 0 #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Breadcrumb Navigation -->
            <div class="col-md-6 mt-3">
                <div class="page-title-box d-flex align-items-center">
                    <ol class="breadcrumb mb-0" style="display: flex; align-items: center; padding-left: 0;">
                        <li class="breadcrumb-item" style="font-size: 25px; display: flex; align-items: center;">
                            <a href="/reimbursements" style="text-decoration: none;" class="text-primary">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ $parentLink }}
                        </li>
                        <li class="breadcrumb-item">
                            {{ $link }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        @include('hcis.reimbursements.approval.navigation.navigationAll')
    </div>

    @include('hcis.reimbursements.businessTrip.modal')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title">{{ $link }}</h3>
                    </div>
                    @php
                        $currentFilter = request('filter', 'all');
                    @endphp
                    @include('hcis.reimbursements.businessTrip.navigation.navigationApproval')
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-light rounded-4 border-0 shadow" style="border-radius: 1rem;">
                    <div class="modal-body text-center p-5" style="padding: 2rem;">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill" style="font-size: 100px; color: #AB2F2B !important;"></i>
                        </div>
                        <h4 class="mb-3 fw-bold" style="font-size: 32px; color: #AB2F2B !important;">Success!</h4>
                        <p class="mb-4" id="successModalBody" style="font-size: 20px;">
                            <!-- The success message will be inserted here -->
                        </p>
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Detail Modal -->
        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
            aria-hidden="true">
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

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
        <script>
            window.addEventListener('resize', function() {
                document.body.style.display = 'none';
                document.body.offsetHeight; // Force a reflow
                document.body.style.display = '';
            });

            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('.status-form');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const action = this.querySelector('input[name="status_approval"]').value;
                        const confirmMessage = action === 'Rejected' ?
                            'Are you sure you want to reject this?' :
                            'Are you sure you want to confirm this?';

                        if (confirm(confirmMessage)) {
                            const formData = new FormData(this);
                            fetch(this.action, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update the success modal content
                                        document.getElementById('successModalBody').textContent =
                                            data.message;

                                        // Show the success modal
                                        var successModal = new bootstrap.Modal(document
                                            .getElementById('successModal'));
                                        successModal.show();

                                        // Reload the page after modal is closed
                                        document.getElementById('successModal').addEventListener(
                                            'hidden.bs.modal',
                                            function() {
                                                window.location.reload();
                                            });
                                    } else {
                                        alert('An error occurred. Please try again.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred. Please try again.');
                                });
                        }
                    });
                });
            });
            //    let table = new DataTable('#defaultTable');

            function getDate() {
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1; // January is 0!
                var yyyy = today.getFullYear();

                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }

                // Correct date format for input fields
                var formattedToday = yyyy + '-' + mm + '-' + dd;
                console.log(formattedToday);

                var startDateElement = document.getElementById("start-date");
                var endDateElement = document.getElementById("end-date");

                // Only set the value if it's not already set
                if (!startDateElement.value) {
                    startDateElement.value = formattedToday;
                }
                if (!endDateElement.value) {
                    endDateElement.value = formattedToday;
                }

                document.addEventListener('DOMContentLoaded', function() {
                    getDate();
                });

                document.getElementById('recordsPerPage').addEventListener('change', function() {
                    const perPage = this.value;
                    const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
                    window.location.search = `?per_page=${perPage}&page=${currentPage}`;
                });

                function confirmDelete(id) {
                    if (confirm("Are you sure you want to delete this item?")) {
                        document.getElementById('deleteForm_' + id).submit();
                    }
                }

            }

            // Ensure the DOM is fully loaded before manipulating it
            document.addEventListener('DOMContentLoaded', function() {
                getDate();
            });

            function confirmDelete(id) {
                if (confirm("Are you sure you want to delete this item?")) {
                    document.getElementById('deleteForm_' + id).submit();
                }
            }

            $(document).ready(function() {
                $('.btn-detail').click(function() {
                    var ca = $(this).data('ca');
                    var tiket = $(this).data('tiket');
                    var hotel = $(this).data('hotel');
                    var taksi = $(this).data('taksi');

                    function createTableHtml(data, title) {
                        var tableHtml = '<h5>' + title + '</h5>';
                        tableHtml += '<div class="table-responsive"><table class="table table-sm"><thead><tr>';
                        var isArray = Array.isArray(data) && data.length > 0;

                        // Assuming all objects in the data array have the same keys, use the first object to create headers
                        if (isArray) {
                            for (var key in data[0]) {
                                if (data[0].hasOwnProperty(key)) {
                                    tableHtml += '<th>' + key + '</th>';
                                }
                            }
                        } else if (typeof data === 'object') {
                            // If data is a single object, create headers from its keys
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    tableHtml += '<th>' + key + '</th>';
                                }
                            }
                        }

                        tableHtml += '</tr></thead><tbody>';

                        // Loop through each item in the array and create a row for each
                        if (isArray) {
                            data.forEach(function(row) {
                                tableHtml += '<tr>';
                                for (var key in row) {
                                    if (row.hasOwnProperty(key)) {
                                        tableHtml += '<td>' + row[key] + '</td>';
                                    }
                                }
                                tableHtml += '</tr>';
                            });
                        } else if (typeof data === 'object') {
                            // If data is a single object, create a single row
                            tableHtml += '<tr>';
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    tableHtml += '<td>' + data[key] + '</td>';
                                }
                            }
                            tableHtml += '</tr>';
                        }

                        tableHtml += '</tbody></table>';
                        return tableHtml;
                    }

                    // $('#detailTypeHeader').text('Detail Information');
                    $('#detailContent').empty();

                    try {
                        var content = '';

                        if (ca && ca !== 'undefined') {
                            var caData = typeof ca === 'string' ? JSON.parse(ca) : ca;
                            content += createTableHtml(caData, 'CA Detail');
                        }
                        if (tiket && tiket !== 'undefined') {
                            var tiketData = typeof tiket === 'string' ? JSON.parse(tiket) : tiket;
                            content += createTableHtml(tiketData, 'Ticket Detail');
                        }
                        if (hotel && hotel !== 'undefined') {
                            var hotelData = typeof hotel === 'string' ? JSON.parse(hotel) : hotel;
                            content += createTableHtml(hotelData, 'Hotel Detail');
                        }
                        if (taksi && taksi !== 'undefined') {
                            var taksiData = typeof taksi === 'string' ? JSON.parse(taksi) : taksi;
                            content += createTableHtml(taksiData, 'Taxi Detail');
                        }

                        if (content !== '') {
                            $('#detailContent').html(content);
                        } else {
                            $('#detailContent').html('<p>No detail information available.</p>');
                        }

                        $('#detailModal').modal('show');
                    } catch (e) {
                        $('#detailContent').html('<p>Error loading data</p>');
                    }
                });

                $('#detailModal').on('hidden.bs.modal', function() {
                    $('body').removeClass('modal-open').css({
                        overflow: '',
                        padding: ''
                    });
                    $('.modal-backdrop').remove();
                });
            });



            $(document).ready(function() {
                var table = $('#yourTableId').DataTable({
                    "pageLength": 10 // Set default page length
                });
                // Set to 10 entries per page
                $('#dt-length-0').val(10);

                // Trigger the change event to apply the selected value
                $('#dt-length-0').trigger('change');
            });
        </script>
    @endsection
