  {{-- Detail Penggunaan Plafond --}}
  <h4>Home Trip Transactions</h4>
  <div class="table-responsive">
      <table class="display nowrap responsive" id="defaultTable" width="100%">
          <thead class="bg-primary text-center align-middle">
              <tr>
                  <th>No</th>
                  <th>No. Ticket</th>
                  <th>Passangers Name</th>
                  <th>Transport Type</th>
                  <th>From/To</th>
                  <th>Total Tickets</th>
                  <th>Ticket Type</th>
                  <th>Details</th>
                  <th data-priority="1">Status</th>
                  <th data-priority="2">Action</th>
              </tr>

          </thead>
          <tbody>
              @foreach ($transactions as $item)
                  <tr>
                      <td class="text-center">{{ $loop->iteration }}</td>
                      <td class="text-left">{{ $item->no_tkt }}</td>
                      <td class="text-left">{{ $item->np_tkt }}</td>
                      <td class="text-left">{{ $item->jenis_tkt }}</td>
                      <td class="text-left">{{ $item->dari_tkt }}/{{ $item->ke_tkt }}</td>
                      <td style="text-align: left">
                          {{ $ticketCounts[$item->no_tkt]['total'] ?? 1 }} Tickets</td>
                      <td class="text-left">{{ $item->type_tkt }}</td>
                      <td class="text-info">
                          <a class="text-info btn-detail" data-toggle="modal" data-target="#detailModal"
                              style="cursor: pointer"
                              data-tiket="{{ json_encode(
                                  $ticket[$item->no_tkt]->map(function ($ticket) {
                                      return [
                                          'No. Ticket' => $ticket->no_tkt,
                                          'Passengers Name' => $ticket->np_tkt,
                                          'Unit' => $ticket->unit,
                                          'Gender' => $ticket->jk_tkt,
                                          'NIK' => $ticket->noktp_tkt,
                                          'Phone No.' => $ticket->tlp_tkt,
                                          'Transport Type.' => $ticket->jenis_tkt,
                                          'From' => $ticket->dari_tkt,
                                          'To' => $ticket->ke_tkt,
                                          'Information' => $ticket->ket_tkt ?? 'No Data',
                                          'Purposes' => $ticket->jns_dinas_tkt,
                                          'Ticket Type' => $ticket->type_tkt,
                                          'Departure Date' => date('d-M-Y', strtotime($ticket->tgl_brkt_tkt)),
                                          'Time' => !empty($ticket->jam_brkt_tkt) ? date('H:i', strtotime($ticket->jam_brkt_tkt)) : 'No Data',
                                          'Return Date' => isset($ticket->tgl_plg_tkt) ? date('d-M-Y', strtotime($ticket->tgl_plg_tkt)) : 'No Data',
                                          'Return Time' => !empty($ticket->jam_plg_tkt) ? date('H:i', strtotime($ticket->jam_plg_tkt)) : 'No Data',
                                      ];
                                  }),
                              ) }}">
                              <u>Details</u>
                          </a>
                      </td>
                      <td style="align-content: center">
                          <span
                              class="badge rounded-pill bg-{{ $item->approval_status == 'Approved' ||
                              $item->approval_status == 'Declaration Approved' ||
                              $item->approval_status == 'Verified'
                                  ? 'success'
                                  : ($item->approval_status == 'Rejected' ||
                                  $item->approval_status == 'Return/Refund' ||
                                  $item->approval_status == 'Declaration Rejected'
                                      ? 'danger'
                                      : (in_array($item->approval_status, [
                                          'Pending L1',
                                          'Pending L2',
                                          'Declaration L1',
                                          'Declaration L2',
                                          'Waiting Submitted',
                                      ])
                                          ? 'warning'
                                          : ($item->approval_status == 'Draft'
                                              ? 'secondary'
                                              : (in_array($item->approval_status, ['Doc Accepted'])
                                                  ? 'info'
                                                  : 'secondary')))) }}"
                              style="font-size: 12px; padding: 0.5rem 1rem; cursor: {{ ($item->approval_status == 'Rejected' || $item->approval_status == 'Declaration Rejected') && isset($ticketApprovals[$item->id]) ? 'pointer' : 'default' }};"
                              @if (
                                  ($item->approval_status == 'Rejected' || $item->approval_status == 'Declaration Rejected') &&
                                      isset($ticketApprovals[$item->id])) onclick="showRejectInfo('{{ $item->id }}')"
                                title="Click to see rejection reason" @endif
                              @if ($item->approval_status == 'Pending L1') title="L1 Manager: {{ $managerL1Names ?? 'Unknown' }}"
                                @elseif ($item->approval_status == 'Pending L2')
                                title="L2 Manager: {{ $managerL2Names ?? 'Unknown' }}"
                                @elseif($item->approval_status == 'Declaration L1') title="L1 Manager: {{ $managerL1Names ?? 'Unknown' }}"
                                @elseif($item->approval_status == 'Declaration L2') title="L2 Manager: {{ $managerL2Names ?? 'Unknown' }}" @endif>
                              {{ $item->approval_status == 'Approved' ? 'Approved' : $item->approval_status }}
                          </span>
                      </td>
                      <td class="text-center">
                          @if ($item->approval_status == 'Draft')
                              <form method="GET" action="{{ route('home-trip-form.edit', encrypt($item->id)) }}"
                                  style="display: inline-block;">
                                  <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill my-1"
                                      data-toggle="tooltip" title="Edit">
                                      <i class="bi bi-pencil-square"></i>
                                  </button>
                              </form>
                              <form action="{{ route('home-trip.delete', encrypt($item->id)) }}" method="POST"
                                  style="display:inline;" id="deleteForm_{{ $item->no_tkt }}">
                                  @csrf
                                  <input type="hidden" id="no_sppd_{{ $item->no_tkt }}" value="{{ $item->no_tkt }}">
                                  <button class="btn btn-sm rounded-pill btn-outline-danger delete-button"
                                      title="Delete" data-id="{{ $item->no_tkt }}">
                                      <i class="ri-delete-bin-line"></i>
                                  </button>
                              </form>
                          @else
                              <a href="{{ route('ticket.export', ['id' => $item->id]) }}"
                                  class="btn btn-sm btn-outline-info rounded-pill" target="_blank">
                                  <i class="bi bi-download"></i>
                              </a>
                          @endif
                          {{-- @if ($item->approval_status == 'Rejected')
                              <form method="GET" action="/medical/form-update/{{ $item->id }}"
                                  style="display: inline-block;">
                                  <button type="submit" class="btn btn-outline-warning rounded-pill my-1"
                                      data-toggle="tooltip" title="Edit">
                                      <i class="bi bi-pencil-square"></i>
                                  </button>
                              </form>
                          @endif --}}
                      </td>
                  </tr>
              @endforeach
          </tbody>
      </table>
  </div>

  <!-- Rejection Reason Modal -->
  <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
      aria-hidden="true">
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
      document.addEventListener('DOMContentLoaded', function() {
          const rejectModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'), {
              keyboard: true,
              backdrop: 'static'
          });

          const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
          closeButtons.forEach(button => {
              button.addEventListener('click', () => {
                  rejectModal.hide();
              });
          });

          function formatDate(dateTimeString) {
              // Create a new Date object from the dateTimeString
              var date = new Date(dateTimeString);

              // Extract day, month, year, hours, and minutes
              var day = ('0' + date.getDate()).slice(-2); // Ensure two digits
              var month = ('0' + (date.getMonth() + 1)).slice(-2); // Month is 0-based, so we add 1
              var year = date.getFullYear();
              var hours = ('0' + date.getHours()).slice(-2);
              var minutes = ('0' + date.getMinutes()).slice(-2);

              // Format the date as d/m/Y H:I
              return `${day}/${month}/${year} ${hours}:${minutes}`;
          }

          window.showRejectInfo = function(transactionId) {
              var ticketApprovals = {!! json_encode($ticketApprovals) !!};
              var employeeName = {!! json_encode($employeeName) !!}; // Add this line

              var approval = ticketApprovals[transactionId];
              if (approval) {
                  var rejectedBy = employeeName[approval.employee_id] || 'N/A'; // Retrieve fullname
                  document.getElementById('rejectedBy').textContent = ': ' + rejectedBy;
                  document.getElementById('rejectionReason').textContent = ': ' + (approval.reject_info ||
                      'N/A');
                  var rejectionDate = approval.approved_at ? formatDate(approval.approved_at) : 'N/A';
                  document.getElementById('rejectionDate').textContent = ': ' + rejectionDate;

                  rejectModal.show();
              } else {
                  console.error('Approval information not found for transaction ID:', transactionId);
              }
          };

          // Add event listener for modal hidden event
          document.getElementById('rejectReasonModal').addEventListener('hidden.bs.modal', function() {
              console.log('Modal closed');
          });
      });

      $(document).ready(function() {
          $('.btn-detail').click(function() {
              var tiket = $(this).data('tiket');

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

                  if (tiket && tiket !== 'undefined') {
                      var tiketData = typeof tiket === 'string' ? JSON.parse(tiket) : tiket;
                      content += createTableHtml(tiketData, 'Home Trip Detail');
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
