<h4>Details for SPPD: {{ $sppd }}</h4>

@if($tickets->isNotEmpty())
    <h5>Tickets</h5>
    @foreach($tickets as $ticket)
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>No. Ticket:</strong> {{ $ticket->no_tkt }}</p>
                <p><strong>From:</strong> {{ $ticket->dari_tkt }}</p>
                <p><strong>To:</strong> {{ $ticket->ke_tkt }}</p>
                <p><strong>Departure Date:</strong> {{ $ticket->tgl_brkt_tkt }}</p>
                <p><strong>Return Date:</strong> {{ $ticket->tgl_plg_tkt ?? 'N/A' }}</p>
            </div>
        </div>
    @endforeach
@endif
