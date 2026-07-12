@if($status === 'active')
    <span class="badge-status" style="background:#e8f9ef;color:#15803d;border:1px solid #bbf7d0;text-transform:none">Active</span>
@elseif($status === 'pending')
    <span class="badge-status pending">Pending</span>
@else
    <span class="badge-status on-hold">{{ ucfirst($status) }}</span>
@endif
