<div class="col-md-12">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="card-title">{{ $link }}</h3>
                <div class="input-group" style="width: 30%;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white w-border-dark-subtle"><i class="ri-search-line"></i></span>
                    </div>
                    <input type="text" name="customsearch" id="customsearch" class="form-control w-border-dark-subtle border-left-0" placeholder="search.." aria-label="search" aria-describedby="search" >&nbsp;&nbsp;&nbsp;
                    
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm dt-responsive nowrap" id="scheduleTable" width="100%"
                    cellspacing="0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Type</th>
                            <th>Cash Advance No</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total CA</th>
                            <th>Total Settlement</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ca_transactions as $ca_transaction)
                            <tr>
                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                @if ($ca_transaction->type_ca == 'dns')
                                    <td>Business Trip</td>
                                @elseif($ca_transaction->type_ca == 'ndns')
                                    <td>Non Business Trip</td>
                                @elseif($ca_transaction->type_ca == 'entr')
                                    <td>Entertainment</td>
                                @endif
                                <td class="text-center">{{ $ca_transaction->no_ca }}</td>
                                <td>{{ $ca_transaction->employee->fullname }}</td>
                                <td>{{ $ca_transaction->contribution_level_code }}</td>
                                <td>{{ date('j M Y', strtotime($ca_transaction->formatted_start_date)) }}</td>
                                <td>{{ date('j M Y', strtotime($ca_transaction->formatted_end_date)) }}</td>
                                <td>Rp. {{ number_format($ca_transaction->total_ca) }}</td>
                                <td>Rp. {{ number_format($ca_transaction->total_real) }}</td>
                                <td>Rp. {{ number_format($ca_transaction->total_cost) }}</td>
                                <td>
                                    <p class="badge text-bg-{{ $ca_transaction->approval_status == 'Approved' ? 'success' : ($ca_transaction->approval_status == 'Declaration' ? 'info' : ($ca_transaction->approval_status == 'Pending' ? 'warning' : ($ca_transaction->approval_status == 'Rejected' ? 'danger' : ($ca_transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}" style="pointer-events: none">
                                        {{ $ca_transaction->approval_status }}
                                    </p>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('cashadvanced.download', $ca_transaction->id) }}" target="_blank" class="btn btn-outline-primary" title="Print"><i class="bi bi-file-earmark-arrow-down"></i></a>
                                    <form action="{{ route('cashadvanced.delete', $ca_transaction->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button onclick="return confirm('Apakah ingin Menghapus?')" class="btn btn-outline-danger" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>