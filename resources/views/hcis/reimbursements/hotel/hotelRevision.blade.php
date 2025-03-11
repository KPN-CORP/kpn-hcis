<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #AB2F2B; color:white">
            <h5 class="card-title mb-0">SPPD Revision Form</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('revision.hotel', ['id' => $id, 'manager_id' => $manager_id, 'status' => $status]) }}"
                method="POST" id="revisionnForm">
                @csrf
                {{-- @method('GET') --}}
                <!-- SPPD Details -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <table class="table table-borderless">
                            <tbody>
                                {{-- @foreach ($hotels as $ticket) --}}
                                <tr>
                                    <th class="w-25">No. SPPD</th>
                                    <td>: {{ $hotels->no_sppd ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>No. Ticket</th>
                                    <td>: {{ $hotels->no_htl }}</td>
                                </tr>
                                <tr>
                                    <th>Employee Name</th>
                                    <td>: {{ $employeeName }}</td>
                                </tr>
                                <tr>
                                    <th>Check In Date</th>
                                    <td>: {{ \Carbon\Carbon::parse($hotels->tgl_masuk_htl)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Check Out Date</th>
                                    <td>: {{ \Carbon\Carbon::parse($hotels->tgl_keluar_htl)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Nights/Hotels</th>
                                    <td>: {{ $hotels->total_hari }} Nights / {{ $hotelsTotal }} Hotels</td>
                                </tr>
                                {{-- @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Revision Info section -->
                <div class="row g-3">
                    <div class="col-12">
                        <label for="revision_info" class="form-label fw-bold">Revision Info</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="revision_info" name="revision_info"
                                placeholder="Enter revision reason" required>
                            <button class="btn" type="submit" style="background-color: #AB2F2B; color:white">
                                <i class="fas fa-paper-plane me-1"></i> Submit
                            </button>
                        </div>
                        <div class="form-text">Please provide a reason for revision</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
