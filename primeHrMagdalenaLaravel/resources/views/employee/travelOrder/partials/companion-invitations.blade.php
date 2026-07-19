{{-- Companion Invitations: travel orders where this employee is invited as a companion --}}
@if(isset($companionInvitations) && $companionInvitations->isNotEmpty())
<section class="table-section to-mb-24" id="companion-invitations-section">
    <div class="table-header">
        <div>
            <h3 class="table-title to-flex-gap-8">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Companion Requests
                @php $pendingInvites = $companionInvitations->where('status', 'pending')->count(); @endphp
                @if($pendingInvites > 0)
                    <span class="to-pill-amber">{{ $pendingInvites }} pending</span>
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
                    <th class="to-ta-center">Your Response</th>
                    <th class="to-ta-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companionInvitations as $invitation)
                @php $inviteOrder = $invitation->travelOrder; @endphp
                @if($inviteOrder)
                <tr>
                    <td data-label="Order No." class="to-td-bold">{{ $inviteOrder->order_number }}</td>
                    <td data-label="Filed By" class="to-td-semibold">
                        <div class="to-flex-gap-10">
                            @include('partials.travel-party-avatars', ['order' => $inviteOrder])
                            {{ $inviteOrder->employee->first_name ?? '' }} {{ $inviteOrder->employee->last_name ?? '' }}
                        </div>
                    </td>
                    <td data-label="Destination" class="to-td-muted">{{ $inviteOrder->destination }}</td>
                    <td data-label="Travel Date" class="to-td-semibold">{{ $inviteOrder->formatted_dates }}</td>
                    <td data-label="Your Response" class="to-ta-center">
                        @if($invitation->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($invitation->status === 'accepted')
                            <span class="badge-status processed">Accepted</span>
                        @else
                            <span class="badge-status on-hold">Rejected</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="row-actions to-justify-center">
                            <button class="btn-view" onclick="viewTravelOrder({{ $inviteOrder->id }})">View</button>
                            @if($invitation->status === 'pending' && $inviteOrder->status === 'awaiting_companions')
                                <button class="btn-edit to-btn-accept" onclick="respondToCompanionRequest({{ $inviteOrder->id }}, 'accepted')">Accept</button>
                                <button class="btn-edit to-btn-reject" onclick="respondToCompanionRequest({{ $inviteOrder->id }}, 'rejected')">Reject</button>
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
@endif
