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
            @foreach($ca_transactions_dec as $transaction)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="background-color: white;" class="sticky-col">{{ $transaction->no_ca }}</td>
                    @if($transaction->type_ca == 'dns')
                        <td>Business Trip</td>
                    @elseif($transaction->type_ca == 'ndns')
                        <td>Non Business Trip</td>
                    @elseif($transaction->type_ca == 'entr')
                        <td>Entertainment</td>
                    @endif
                    <td>{{ $transaction->employee->fullname }}</td>
                    <td>{{ $transaction->contribution_level_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->start_date)->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->end_date)->format('d-M-y') }}</td>
                    <td>Rp. {{ number_format($transaction->total_ca) }}</td>
                    <td>Rp. {{ number_format($transaction->total_real) }}</td>
                    <td>
                        @if ($transaction->total_cost < 0)
                            <span class="text-danger">Rp. -{{ number_format(abs($transaction->total_cost)) }}</span>
                        @else
                            <span class="text-success">Rp. {{ number_format($transaction->total_cost) }}</span>
                        @endif
                    </td>
                    <td>
                        <p class="badge text-bg-{{ $transaction->approval_sett == 'Approved' ? 'success' : ($transaction->approval_sett == 'Declaration' ? 'info' : ($transaction->approval_sett == 'Pending' ? 'warning' : ($transaction->approval_sett == 'Rejected' ? 'danger' : ($transaction->approval_sett == 'Draft' ? 'secondary' : 'success')))) }}"
                             title="Waiting Approve by: {{ isset($fullnames[$transaction->sett_id]) ? $fullnames[$transaction->sett_id] : 'Unknown Employee' }}">
                            {{ $transaction->approval_sett }}
                        </p>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('approval.cashadvancedFormDeklarasi', encrypt($transaction->id)) }}" class="btn btn-outline-info" title="Approve" ><i class="bi bi-card-checklist"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
