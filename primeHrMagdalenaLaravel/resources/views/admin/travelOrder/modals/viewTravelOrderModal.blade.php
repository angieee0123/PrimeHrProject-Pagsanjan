{{-- View Travel Order Modal --}}
<div id="viewTravelOrderModal" class="modal-overlay" onclick="closeViewTravelOrderModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">TRAVEL ORDER · #<span id="viewOrderId">-</span></span>
                <h3 class="modal-title" id="viewModalTitle">Travel Order Details</h3>
                <p class="modal-sub" id="viewEmployeeInfo">-</p>
            </div>
            <button class="modal-close" onclick="closeViewTravelOrderModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            {{-- Status Badge --}}
            <div style="text-align: center; margin-bottom: 20px;">
                <span id="viewStatusBadge" class="badge-status">-</span>
            </div>

            <div class="modal-section-label">TRAVEL INFORMATION</div>
            <div class="modal-row"><span>Destination</span><strong id="viewDestination">-</strong></div>
            <div class="modal-row"><span>Departure Date</span><strong id="viewTravelDate">-</strong></div>
            <div class="modal-row"><span>Return Date</span><strong id="viewReturnDate">-</strong></div>
            <div class="modal-row"><span>Duration</span><strong id="viewDuration">-</strong></div>
            <div class="modal-row"><span>Transportation Mode</span><strong id="viewTransportation">-</strong></div>
            <div class="modal-row"><span>Estimated Budget</span><strong id="viewBudget">-</strong></div>

            <div class="modal-section-label" style="margin-top: 16px;">PURPOSE OF TRAVEL</div>
            <p id="viewPurpose" style="font-size: 13px; color: #374151; line-height: 1.6; margin: 8px 0; padding: 12px; background: #f9fafb; border-radius: 6px;">-</p>

            {{-- Approval Information --}}
            <div id="viewApprovalSection" style="display: none;">
                <div class="modal-section-label" style="margin-top: 16px;">APPROVAL INFORMATION</div>
                <div class="modal-row"><span>Processed By</span><strong id="viewApprovedBy">-</strong></div>
                <div class="modal-row"><span>Date Processed</span><strong id="viewApprovedAt">-</strong></div>
                
                <div id="viewRemarksSection" style="display: none; margin-top: 12px;">
                    <div class="modal-section-label">REMARKS</div>
                    <p id="viewRemarks" style="font-size: 13px; color: #991b1b; line-height: 1.6; margin: 8px 0; padding: 12px; background: #fee2e2; border-radius: 6px; border-left: 3px solid #dc2626;">-</p>
                </div>
            </div>

            {{-- Attachment --}}
            <div id="viewAttachmentSection" style="display: none; margin-top: 16px;">
                <div class="modal-section-label">SUPPORTING DOCUMENT</div>
                <a id="viewAttachmentLink" href="#" target="_blank" class="document-link" style="margin-top: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    View Attachment
                </a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeViewTravelOrderModal()">Close</button>
        </div>
    </div>
</div>

<script>
function closeViewTravelOrderModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('viewTravelOrderModal').style.display = 'none';
    document.body.style.overflow = '';
}

function viewOrder(id) {
    fetch(`/admin/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set basic info
            document.getElementById('viewOrderId').textContent = data.id;
            document.getElementById('viewEmployeeInfo').textContent = 
                `${data.employee.first_name} ${data.employee.last_name} · ${data.employee.employee_id}`;
            
            // Set status badge
            const statusBadge = document.getElementById('viewStatusBadge');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending Approval';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Disapproved';
            } else {
                statusBadge.className = 'badge-status';
                statusBadge.style.background = '#f3f4f6';
                statusBadge.style.color = '#6b7280';
                statusBadge.textContent = 'Cancelled';
            }
            
            // Set travel info
            document.getElementById('viewDestination').textContent = data.destination;
            document.getElementById('viewDuration').textContent = data.duration + ' days';
            document.getElementById('viewTravelDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('viewReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('viewPurpose').textContent = data.purpose;
            document.getElementById('viewTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('viewBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';
            
            // Show/hide approval section
            const approvalSection = document.getElementById('viewApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('viewApprovedBy').textContent = data.approver ? `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` : 'Admin User';
                document.getElementById('viewApprovedAt').textContent = data.approved_at ? new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
                
                // Show remarks if rejected
                const remarksSection = document.getElementById('viewRemarksSection');
                if (data.status === 'rejected' && data.remarks) {
                    remarksSection.style.display = 'block';
                    document.getElementById('viewRemarks').textContent = data.remarks;
                } else {
                    remarksSection.style.display = 'none';
                }
            } else {
                approvalSection.style.display = 'none';
            }
            
            // Show/hide attachment
            const attachmentSection = document.getElementById('viewAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('viewAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }
            
            // Show modal
            document.getElementById('viewTravelOrderModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
}
</script>
