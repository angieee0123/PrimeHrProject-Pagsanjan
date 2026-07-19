@if($status === 'active')
    <span class="badge-status processed eh-no-transform">Active</span>
@elseif($status === 'pending')
    <span class="badge-status pending">Pending</span>
@else
    <span class="badge-status on-hold">{{ ucfirst($status) }}</span>
@endif
