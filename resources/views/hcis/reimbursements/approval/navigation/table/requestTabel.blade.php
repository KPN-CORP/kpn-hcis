<div class="table-responsive">
    <table class="table table-sm dt-responsive nowrap scheduleTable" width="100%" cellspacing="0">
        <thead class="thead-light">
            <tr class="text-center">
                <th>No</th>
                <th class="sticky-col-header" style="background-color: white">Cash Advance No</th>
                <th>Type</th>
                <th>Requestor</th>
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
            @foreach($ca_transactions as $transaction)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="background-color: white;" class="sticky-col">{{ $transaction->no_ca }}</td>
                    @if($transaction->type_ca == 'dns')
                        <td>Business Travel</td>
                    @elseif($transaction->type_ca == 'ndns')
                        <td>Non Business Travel</td>
                    @elseif($transaction->type_ca == 'entr')
                        <td>Entertainment</td>
                    @endif
                    <td>{{ $transaction->employee->fullname }}</td>
                    <td>{{ $transaction->contribution_level_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->start_date)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->end_date)->format('d-m-Y') }}</td>
                    <td>Rp. {{ number_format($transaction->total_ca) }}</td>
                    <td>Rp. {{ number_format($transaction->total_real) }}</td>
                    <td>Rp. {{ number_format($transaction->total_cost) }}</td>
                    <td>
                        {{-- <h1>{{ $fullnames_req[$transaction->id][0]['role_name'] ?? 'N/A' }}</h1> --}}
                        <p class="badge text-bg-{{ $transaction->approval_status == 'Approved' ? 'success' : ($transaction->approval_status == 'Declaration' ? 'info' : ($transaction->approval_status == 'Pending' ? 'warning' : ($transaction->approval_status == 'Rejected' ? 'danger' : ($transaction->approval_status == 'Draft' ? 'secondary' : 'success')))) }}"
                            onclick="showManagerInfo('All Approval', {{ json_encode($fullnames_req[$transaction->id] ?? []) }})">
                            {{ $transaction->approval_status }}
                        </p>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('approval.cashadvancedForm', encrypt($transaction->id)) }}" class="btn btn-outline-info" title="Approve" ><i class="bi bi-card-checklist"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('.scheduleTable').DataTable();
    });
</script>
