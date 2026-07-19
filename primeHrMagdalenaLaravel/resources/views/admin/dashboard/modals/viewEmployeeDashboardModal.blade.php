{{-- View Employee Modal — content is fetched and rendered client-side by viewEmployeeDashboard() in the page script. --}}
<div id="viewEmployeeDashboardModal" style="display:none; position:fixed; inset:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:900px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:24px; border-bottom:1.5px solid #f2f1fb;">
            <div>
                <h3 style="margin:0 0 4px; font-size:18px; font-weight:700; color:#0b044d;">Employee Details</h3>
                <p id="viewEmployeeDashboardId" style="margin:0; font-size:13px; color:#56547a;"></p>
            </div>
            <button onclick="closeViewDashboardModal()" style="background:none; border:none; font-size:28px; color:#56547a; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        <div id="viewEmployeeDashboardContent" style="padding:24px;">
            <p style="text-align:center; color:#56547a;">Loading...</p>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/dashboard/viewEmployeeDashboardModal.js')
@endpush
