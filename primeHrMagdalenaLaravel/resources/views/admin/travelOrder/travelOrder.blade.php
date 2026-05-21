@extends('layouts.app')

@section('content')

@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Travel Order Management</h2>
            <p class="text-muted">Approve or disapprove employee travel orders</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#approved">Approved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#disapproved">Disapproved</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="pending">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Destination</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->employee_name }}</td>
                                <td>{{ $order->destination }}</td>
                                <td>{{ $order->purpose }}</td>
                                <td>{{ $order->travel_date }}</td>
                                <td>{{ $order->duration }} days</td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="approveOrder({{ $order->id }})">Approve</button>
                                    <button class="btn btn-sm btn-danger" onclick="disapproveOrder({{ $order->id }})">Disapprove</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No pending travel orders</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="approved">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Destination</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Approved By</th>
                                <th>Approved Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvedOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->employee_name }}</td>
                                <td>{{ $order->destination }}</td>
                                <td>{{ $order->purpose }}</td>
                                <td>{{ $order->travel_date }}</td>
                                <td>{{ $order->approved_by }}</td>
                                <td>{{ $order->approved_date }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No approved travel orders</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="disapproved">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Destination</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Disapproved By</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($disapprovedOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->employee_name }}</td>
                                <td>{{ $order->destination }}</td>
                                <td>{{ $order->purpose }}</td>
                                <td>{{ $order->travel_date }}</td>
                                <td>{{ $order->disapproved_by }}</td>
                                <td>{{ $order->disapproval_reason }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No disapproved travel orders</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveOrder(id) {
    if(confirm('Approve this travel order?')) {
        fetch(`/admin/travelorder/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function disapproveOrder(id) {
    const reason = prompt('Reason for disapproval:');
    if(reason) {
        fetch(`/admin/travelorder/${id}/disapprove`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason })
        }).then(() => location.reload());
    }
}
</script>
@endsection
