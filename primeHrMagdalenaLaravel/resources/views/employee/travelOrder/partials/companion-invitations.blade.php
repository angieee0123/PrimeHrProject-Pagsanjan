{{-- Companion Invitations: travel orders where this employee is invited as a companion --}}
@if(isset($companionInvitations) && $companionInvitations->isNotEmpty())
<section class="table-section" id="companion-invitations-section" style="margin-bottom: 24px;">
    <div class="table-header">
        <div>
            <h3 class="table-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Companion Requests
                @php $pendingInvites = $companionInvitations->where('status', 'pending')->count(); @endphp
                @if($pendingInvites > 0)
                    <span style="background: #fef3c7; color: #a16207; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 999px;">{{ $pendingInvites }} pending</span>
                @endif
            </h3>
            <p class="table-sub">Travel orders where you were included as a companion</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Filed By</th>
                    <th>Destination</th>
                    <th>Travel Date</th>
                    <th style="text-align: center;">Your Response</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companionInvitations as $invitation)
                @php $inviteOrder = $invitation->travelOrder; @endphp
                @if($inviteOrder)
                <tr>
                    <td data-label="Order No." style="font-size: 13px; color: #0b044d; font-weight: 700;">{{ $inviteOrder->order_number }}</td>
                    <td data-label="Filed By" style="font-size: 13px; color: #0b044d; font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @include('partials.travel-party-avatars', ['order' => $inviteOrder])
                            {{ $inviteOrder->employee->first_name ?? '' }} {{ $inviteOrder->employee->last_name ?? '' }}
                        </div>
                    </td>
                    <td data-label="Destination" style="font-size: 13px; color: #6b6a8a;">{{ $inviteOrder->destination }}</td>
                    <td data-label="Travel Date" style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $inviteOrder->formatted_dates }}</td>
                    <td data-label="Your Response" style="text-align: center;">
                        @if($invitation->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($invitation->status === 'accepted')
                            <span class="badge-status processed">Accepted</span>
                        @else
                            <span class="badge-status on-hold">Rejected</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="row-actions" style="justify-content: center;">
                            <button class="btn-view" onclick="viewTravelOrder({{ $inviteOrder->id }})">View</button>
                            @if($invitation->status === 'pending' && $inviteOrder->status === 'awaiting_companions')
                                <button class="btn-edit" style="background: #15803d; border-color: #15803d;" onclick="respondToCompanionRequest({{ $inviteOrder->id }}, 'accepted')">Accept</button>
                                <button class="btn-edit" style="background: #dc2626; border-color: #dc2626;" onclick="respondToCompanionRequest({{ $inviteOrder->id }}, 'rejected')">Reject</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<script>
function respondToCompanionRequest(travelOrderId, response) {
    const verb = response === 'accepted' ? 'accept' : 'reject';
    if (!confirm(`Are you sure you want to ${verb} this companion request?`)) return;

    let note = null;
    if (response === 'rejected') {
        note = prompt('Optionally, tell the filer why you are rejecting (or leave blank):', '');
        if (note === null) return; // prompt cancelled
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/employee/travelorder/${travelOrderId}/companion-response`;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const responseInput = document.createElement('input');
    responseInput.type = 'hidden';
    responseInput.name = 'response';
    responseInput.value = response;
    form.appendChild(responseInput);

    if (note) {
        const noteInput = document.createElement('input');
        noteInput.type = 'hidden';
        noteInput.name = 'response_note';
        noteInput.value = note.substring(0, 300);
        form.appendChild(noteInput);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>
@endif
