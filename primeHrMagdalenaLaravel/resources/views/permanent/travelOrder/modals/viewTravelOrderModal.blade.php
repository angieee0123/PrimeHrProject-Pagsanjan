{{-- View Travel Order Detail Modal --}}
<div class="modal-overlay" id="viewTravelDetailModal" onclick="closeTravelDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">TRAVEL ORDER · TO-<span id="detailOrderId">-</span></span>
                <h3 class="modal-title" id="detailDestination">-</h3>
                <p class="modal-sub" id="detailTravelDates">-</p>
            </div>
            <button class="modal-close" onclick="closeTravelDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge-status" id="detailTravelStatus">-</span>
                </div>
            </div>
            
            <span class="modal-section-label">TRAVEL DETAILS</span>
            <div class="modal-row"><span>Destination</span><strong id="detailDestinationFull">-</strong></div>
            <div class="modal-row"><span>Departure Date</span><strong id="detailDepartureDate">-</strong></div>
            <div class="modal-row"><span>Return Date</span><strong id="detailReturnDate">-</strong></div>
            <div class="modal-row"><span>Duration</span><strong id="detailDuration">-</strong></div>
            <div class="modal-row"><span>Transportation</span><strong id="detailTransportation">-</strong></div>
            <div class="modal-row"><span>Estimated Budget</span><strong id="detailBudget">-</strong></div>
            
            <span class="modal-section-label modal-section-deductions">PURPOSE OF TRAVEL</span>
            <div class="modal-row"><span id="detailPurpose" style="line-height: 1.6;">-</span></div>
            
            <div id="detailApprovalSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">APPROVAL INFORMATION</span>
                <div class="modal-row"><span>Processed By</span><strong id="detailApprovedBy">-</strong></div>
                <div class="modal-row"><span>Date Processed</span><strong id="detailApprovedAt">-</strong></div>
            </div>

            <div id="detailRemarksSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">REMARKS</span>
                <div class="modal-row"><span id="detailRemarks" style="color: #991b1b; font-style: italic; line-height: 1.6;">-</span></div>
            </div>

            <div id="detailAttachmentSection" style="display: none; margin-top: 16px;">
                <span class="modal-section-label">SUPPORTING DOCUMENT</span>
                <a id="detailAttachmentLink" href="#" target="_blank" class="document-link" style="margin-top: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    View Attachment
                </a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeTravelDetailModal()">Close</button>
            <button class="modal-btn-primary" id="detailCancelBtn" style="display: none; background: #dc2626;" onclick="cancelTravelOrder()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Cancel Travel Order
            </button>
        </div>
    </div>
</div>

<script>
let currentTravelOrderId = null;

function closeTravelDetailModal() {
    document.getElementById('viewTravelDetailModal').style.display = 'none';
    document.body.style.overflow = '';
    currentTravelOrderId = null;
}

function viewTravelOrder(id) {
    currentTravelOrderId = id;
    
    fetch(`/permanent/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set header info
            document.getElementById('detailOrderId').textContent = data.id;
            document.getElementById('detailDestination').textContent = data.destination;
            document.getElementById('detailTravelDates').textContent = 
                new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + 
                ' — ' + 
                new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            
            // Set status badge
            const statusBadge = document.getElementById('detailTravelStatus');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Rejected';
            } else {
                statusBadge.className = 'badge-status';
                statusBadge.style.background = '#f3f4f6';
                statusBadge.style.color = '#6b7280';
                statusBadge.textContent = 'Cancelled';
            }
            
            // Set travel details
            document.getElementById('detailDestinationFull').textContent = data.destination;
            document.getElementById('detailDepartureDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailDuration').textContent = data.duration + ' days';
            document.getElementById('detailTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('detailBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';
            document.getElementById('detailPurpose').textContent = data.purpose;
            
            // Show/hide approval section
            const approvalSection = document.getElementById('detailApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('detailApprovedBy').textContent = data.approver ? 
                    `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` : 
                    'Admin User';
                document.getElementById('detailApprovedAt').textContent = data.approved_at ? 
                    new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 
                    'N/A';
            } else {
                approvalSection.style.display = 'none';
            }
            
            // Show/hide remarks
            const remarksSection = document.getElementById('detailRemarksSection');
            if (data.status === 'rejected' && data.remarks) {
                remarksSection.style.display = 'block';
                document.getElementById('detailRemarks').textContent = data.remarks;
            } else {
                remarksSection.style.display = 'none';
            }
            
            // Show/hide attachment
            const attachmentSection = document.getElementById('detailAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('detailAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }
            
            // Show/hide cancel button (only for pending orders)
            const cancelBtn = document.getElementById('detailCancelBtn');
            if (data.status === 'pending') {
                cancelBtn.style.display = 'inline-flex';
            } else {
                cancelBtn.style.display = 'none';
            }
            
            // Show modal
            document.getElementById('viewTravelDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
}

function cancelTravelOrder() {
    if (!currentTravelOrderId) return;
    
    if (confirm('Are you sure you want to cancel this travel order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permanent/travelorder/${currentTravelOrderId}`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
