  {{-- Detail Penggunaan Plafond --}}
  @if (request()->routeIs('medical.detail'))
    <h4>Health Coverage Usage History</h4>
  @else
    <h4>Admin Confirmation Medical Request</h4>
  @endif
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
                        {{-- @if (isset($item->medical_proof) && $item->medical_proof)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($item->medical_proof) }}" 
                            target="_blank" 
                            class="btn btn-sm btn-warning rounded-pill">
                            View ubah
                            </a>
                        @endif --}}
                        @if (isset($item->medical_proof) && $item->medical_proof)
                            <button type="button" class="btn btn-sm btn-warning rounded-pill" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewAttachmentModal-{{ $item->no_medic }}">
                                View
                            </button>
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
@php
  use Illuminate\Support\Facades\Storage;
@endphp
@foreach ($medical as $item)
    @if (isset($item->medical_proof) && $item->medical_proof)
        @php
            // Decode JSON jika ada, jika tidak valid anggap sebagai array kosong
            $decodedData = json_decode($item->medical_proof, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $existingFiles = $decodedData;
            } else {
                // Jika gagal decode, anggap sebagai string biasa
                $existingFiles = [$item->medical_proof];
            }
        @endphp

        <div class="modal fade" id="viewAttachmentModal-{{ $item->no_medic }}" tabindex="-1" aria-labelledby="viewAttachmentModalLabel-{{ $item->no_medic }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewAttachmentModalLabel-{{ $item->no_medic }}">View Attachment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="attachmentContent">
                            @if (is_array($existingFiles) && count($existingFiles) > 0)
                                @foreach ($existingFiles as $file)
                                    @php 
                                        $fileUrl = Storage::url($file); 
                                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    @endphp
                                    
                                    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'PNG', 'JPG', 'JPEG']))
                                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ $fileUrl }}" alt="Proof Image" style="width: 100px; height: 100px; border: 1px solid rgb(221, 221, 221); border-radius: 5px; padding: 5px;">
                                        </a>
                                    @elseif (in_array($extension, ['pdf', 'PDF']))
                                        {{-- <iframe src="{{ $fileUrl }}" width="100%" height="500px" class="mb-2"></iframe> --}}
                                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer">

                                            <img src="{{ asset('images/pdf_icon.png') }}" alt="PDF File">
                                            <p>Click to view PDF</p>
                                        </a>
                                    @else
                                        <p>File type not supported: {{ $file }}</p>
                                    @endif
                                @endforeach
                            @else
                                <p>No valid files found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach