<!-- View Employee (Employee Details) Modal — shared by the personnel page and
     the admin dashboard; brings its own styles and script. -->
<div id="viewEmployeeModal">
    <div class="view-employee-box">
        <div class="view-employee-header">
            <div>
                <h3>Employee Details</h3>
                <p id="viewEmployeeId"></p>
            </div>
            <button onclick="closeViewModal()" class="view-employee-close">&times;</button>
        </div>
        <div class="view-employee-body" id="viewEmployeeContent">
            <p style="text-align:center; color:#56547a;">Loading...</p>
        </div>
    </div>
</div>

@push('styles')
    @vite('resources/css/admin/viewEmployeeModal.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/personnel/viewEmployeeModal.js')
@endpush
