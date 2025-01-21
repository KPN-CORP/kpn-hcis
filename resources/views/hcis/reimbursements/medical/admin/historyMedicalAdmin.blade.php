  {{-- Detail Penggunaan Plafond --}}
  <h4>Health Coverage Usage History</h4>
  <div class="table-responsive">
      <table class="display nowrap responsive" id="example" width="100%">
          <thead class="bg-primary text-center align-middle">
              <tr>
                  <th></th>
                  <th class="text-center">No</th>
                  @if (request()->routeIs('medical.confirmation'))
                      <th class="text-center">Employee Name</th>
                  @endif
                  <th>Submit Date</th>
                  <th>Claim Date</th>
                  <th class="text-center">Period</th>
                  <th data-priority="0" class="text-center">No. Medical</th>
                  <th class="text-center">Hospital Name</th>
                  <th class="text-center">Patient Name</th>
                  <th data-priority="1">Total Medical</th>
                  <th class="text-center">Disease</th>
                  <th data-priority="4" class="text-center">Attachment</th>
                  @foreach ($master_medical as $master_medicals)
                      <th class="text-center">{{ $master_medicals->name }}</th>
                  @endforeach
                  <th data-priority="2" class="text-center">Status</th>
                  <th data-priority="3" class="text-center">Action</th>
              </tr>

          </thead>
          <tbody>
              @foreach ($medical as $item)
                  <tr>
                      <td class="text-center"></td>
                      <td class="text-center">{{ $loop->iteration }}</td>
                      @if (request()->routeIs('medical.confirmation'))
                          <td class="text-center">{{ $item->employee_fullname  }}</td>
                      @endif
                      <td>{{ \Carbon\Carbon::parse($item->latest_created_at)->format('d M Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                      <td class="text-center">{{ $item->period }}</td>
                      <td class="text-center">{{ $item->no_medic }}</td>
                      <td>{{ $item->hospital_name }}</td>
                      <td>{{ $item->patient_name }}</td>
                      <td>{{ 'Rp. ' . number_format($item->total_per_no_medic, 0, ',', '.') }}</td>
                      <td>{{ $item->disease }}</td>
                      <td>
                        @if (isset($item->medical_proof) && $item->medical_proof)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($item->medical_proof) }}" 
                            target="_blank" 
                            class="btn btn-sm btn-warning rounded-pill">
                            View
                            </a>
                        @endif
                      </td>
                      @foreach ($master_medical as $master_medicals)
                          <td class="text-center">
                              @php
                                  // Dynamically determine the corresponding total field for each medical type
                                  $medical_type = strtolower(str_replace(' ', '_', $master_medicals->name)) . '_total';
                              @endphp

                              {{ 'Rp. ' . number_format($item->$medical_type, 0, ',', '.') }}
                          </td>
                      @endforeach
                      <td style="align-content: center; text-align: center">
                          @php
                              $badgeClass = match ($item->status) {
                                  'Pending' => 'bg-warning',
                                  'Done' => 'bg-success',
                                  'Rejected' => 'bg-danger',
                                  'Draft' => 'bg-secondary',
                                  default => 'bg-light',
                              };
                          @endphp
                          <span class="badge rounded-pill {{ $badgeClass }} text-center"
                              style="font-size: 12px; padding: 0.5rem 1rem; cursor: pointer;"
                              @if ($item->status == 'Rejected' && isset($rejectMedic[$item->no_medic])) onclick="showRejectInfo('{{ $item->no_medic }}')"
                                    title="Click to see rejection reason"
                                @elseif ($item->status == 'Done')
                                    title="{{ 'Approved GA by - '.$item->approved_by_fullname.' ' }}"
                                @else
                                    title="{{ $item->verif_by == null && $item->balance_verif == null ? 'Waiting for Admin GA to confirmation' : 'Pending GA - '.implode(', ', $gaFullname->toArray()).' ' }}"
                                @endif>
                              {{ $item->status }}
                          </span>
                      </td>
                      <td class="text-center">
                          @if (request()->routeIs('medical.confirmation'))
                              <form method="GET" action="/medical/admin/form-update/{{ $item->usage_id }}"
                                  style="display: inline-block;">
                                  <button type="submit" class="btn btn-outline-warning rounded-pill my-1"
                                      data-toggle="tooltip" title="Edit">
                                      <i class="bi bi-pencil-square"></i>
                                  </button>
                              </form>
                          @endif
                          <form id="deleteForm_{{ $item->no_medic }}" method="POST"
                              action="/medical/admin/delete/{{ $item->usage_id }}" style="display: inline-block;">
                              @csrf
                              @method('DELETE')
                              <input type="hidden" id="no_sppd_{{ $item->no_medic }}" value="{{ $item->no_medic }}">
                              <button type="button" class="btn btn-outline-danger rounded-pill delete-button"
                                  data-id="{{ $item->no_medic }}">
                                  <i class="bi bi-trash-fill"></i>
                              </button>
                          </form>
                      </td>
                  </tr>
              @endforeach
          </tbody>
      </table>
  </div>
