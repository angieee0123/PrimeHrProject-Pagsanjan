@extends('layouts.employee')

@section('title', 'Profile · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Profile'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content permanent-dashboard permanent-profile glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.profileTopbar')

        {{-- Stats --}}
        <div class="stats-grid stats-grid-4 profile-stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Years of Service</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ number_format($yearsOfService, 1) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">Since {{ $employee->employmentDetail && $employee->employmentDetail->appointment_date ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('M Y') : 'N/A' }}</p>
                </div>
            </div>
            {{-- Attendance rate, computed from this year's DTR. This card used to
                 show a hardcoded "4.9" performance rating; the schema has no
                 performance or evaluation table to draw one from. --}}
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance Rate</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">
                        @if($attendanceRate !== null)
                            {{ $daysPresent }} present · {{ $daysAbsent }} absent in {{ $year }}
                        @else
                            No DTR records for {{ $year }}
                        @endif
                    </p>
                </div>
            </div>
            @if($isPermanent ?? false)
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Leave Balance</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ number_format($leaveBalance) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">Days remaining</p>
                </div>
            </div>
            @endif
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Trainings Completed</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $trainingsCompleted }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Total programs</p>
                </div>
            </div>
        </div>

        {{-- Profile Info Card --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Profile Information</p>
                    <p class="table-sub">View and manage your personal details</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="profile-tabs">
                <button class="profile-tab active" onclick="switchProfileTab('personal', this)">Personal Info</button>
                <button class="profile-tab" onclick="switchProfileTab('employment', this)">Employment</button>
                <button class="profile-tab" onclick="switchProfileTab('government', this)">Government IDs</button>
                <button class="profile-tab" onclick="switchProfileTab('emergency', this)">Emergency Contact</button>
            </div>

            <div class="profile-tab-content">

                {{-- Personal Info --}}
                <div id="tab-personal" class="tab-pane active">
                    @php
                        // addresses has no `full_address` column, so the old template
                        // always fell through to a concatenation that left ", , ,"
                        // behind whenever a part was blank. Join only what exists.
                        $addr = $employee->addresses->first();
                        $addrLine = $addr
                            ? collect([
                                trim(($addr->house_no ?? '') . ' ' . ($addr->street ?? '')),
                                $addr->barangay, $addr->city, $addr->province,
                              ])->filter(fn ($p) => filled(trim((string) $p)))->implode(', ')
                            : '';
                        if ($addr && filled($addr->zip_code)) {
                            $addrLine = trim($addrLine . ' ' . $addr->zip_code);
                        }
                    @endphp
                    <div class="profile-grid">
                        <div class="profile-field"><span>Full Name</span><strong>{{ $employee->first_name }} {{ $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '' }} {{ $employee->last_name }}{{ $employee->suffix ? ' ' . $employee->suffix : '' }}</strong></div>
                        <div class="profile-field"><span>Gender</span><strong>{{ $employee->sex ?? 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Date of Birth</span><strong>{{ $employee->birth_date ? \Carbon\Carbon::parse($employee->birth_date)->format('M d, Y') : 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Contact No.</span><strong id="display-contact">{{ $employee->contacts->firstWhere('type', 'mobile')->number ?? 'N/A' }}</strong></div>
                        <div class="profile-field profile-field-full"><span>Email Address</span><strong id="display-email">{{ Auth::user()->email }}</strong></div>
                        <div class="profile-field profile-field-full"><span>Address</span><strong id="display-address">{{ $addrLine ?: 'N/A' }}</strong></div>
                    </div>
                </div>

                {{-- Employment --}}
                <div id="tab-employment" class="tab-pane">
                    <div class="profile-grid">
                        <div class="profile-field"><span>Employee ID</span><strong>{{ $employee->employee_id ?? 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Employment Type</span><strong>{{ $employee->employmentDetail->employment_status ?? 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Date Hired</span><strong>{{ $employee->employmentDetail && $employee->employmentDetail->appointment_date ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('M d, Y') : 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Status</span><strong>{{ Auth::user()->status ?? 'N/A' }}</strong></div>
                        <div class="profile-field profile-field-full"><span>Position / Designation</span><strong>{{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }}</strong></div>
                        <div class="profile-field profile-field-full"><span>Department / Office</span><strong>{{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</strong></div>
                    </div>
                </div>

                {{-- Government IDs --}}
                <div id="tab-government" class="tab-pane">
                    @php $ids = $employee->governmentIds->first(); @endphp
                    <div class="profile-grid">
                        <div class="profile-field"><span>GSIS No.</span><strong>{{ $ids?->gsis_no ?: 'N/A' }}</strong></div>
                        <div class="profile-field"><span>PhilHealth No.</span><strong>{{ $ids?->philhealth_no ?: 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Pag-IBIG No.</span><strong>{{ $ids?->pagibig_no ?: 'N/A' }}</strong></div>
                        <div class="profile-field"><span>TIN</span><strong>{{ $ids?->tin_no ?: 'N/A' }}</strong></div>
                        {{-- license_no is stored but was never surfaced. --}}
                        <div class="profile-field profile-field-full"><span>PRC / Professional License No.</span><strong>{{ $ids?->license_no ?: 'N/A' }}</strong></div>
                    </div>
                    <p class="profile-note">These identifiers are maintained by HR. Contact the HR office if any is incorrect.</p>
                </div>

                {{-- Emergency Contact --}}
                <div id="tab-emergency" class="tab-pane">
                    <div class="profile-grid">
                        <div class="profile-field"><span>Contact Person</span><strong id="display-emergency-contact">{{ $employee->contacts->firstWhere('type', 'emergency')->contact_person ?? 'N/A' }}</strong></div>
                        <div class="profile-field"><span>Phone Number</span><strong id="display-emergency-phone">{{ $employee->contacts->firstWhere('type', 'emergency')->number ?? 'N/A' }}</strong></div>
                    </div>
                </div>

            </div>
        </div>

    </main>
</div>

{{-- Edit Modal --}}
<div class="p-modal-overlay" id="editModal" onclick="if(event.target===this)closeEditModal()">
    <div class="p-modal-box">
        <div class="p-modal-header">
            <div>
                <span class="p-modal-eyebrow">EDIT PROFILE</span>
                <h3 class="p-modal-title">Update Personal Information</h3>
            </div>
            <button class="p-modal-close" onclick="closeEditModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="p-modal-body">
            <p class="p-form-error hidden" id="editModalMsg"></p>
            <span class="p-form-label">CONTACT INFORMATION</span>
            <div class="p-form-grid">
                <div class="p-form-field">
                    <label>Contact No.</label>
                    <input type="text" id="edit-contact" value="{{ $employee->contacts->firstWhere('type', 'mobile')->number ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Email Address</label>
                    <input type="email" id="edit-email" value="{{ Auth::user()->email }}">
                </div>
            </div>
            <span class="p-form-label p-form-label-gap">ADDRESS</span>
            <div class="p-form-grid">
                <div class="p-form-field">
                    <label>House No.</label>
                    <input type="text" id="edit-house-no" value="{{ $employee->addresses->first()->house_no ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Street</label>
                    <input type="text" id="edit-street" value="{{ $employee->addresses->first()->street ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Barangay</label>
                    <input type="text" id="edit-barangay" value="{{ $employee->addresses->first()->barangay ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>City</label>
                    <input type="text" id="edit-city" value="{{ $employee->addresses->first()->city ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Province</label>
                    <input type="text" id="edit-province" value="{{ $employee->addresses->first()->province ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Zip Code</label>
                    <input type="text" id="edit-zip" value="{{ $employee->addresses->first()->zip_code ?? '' }}">
                </div>
            </div>
            <span class="p-form-label p-form-label-gap">EMERGENCY CONTACT</span>
            <div class="p-form-grid">
                <div class="p-form-field">
                    <label>Contact Person</label>
                    <input type="text" id="edit-emergencyContact" value="{{ $employee->contacts->firstWhere('type', 'emergency')->contact_person ?? '' }}">
                </div>
                <div class="p-form-field">
                    <label>Phone Number</label>
                    <input type="text" id="edit-emergencyPhone" value="{{ $employee->contacts->firstWhere('type', 'emergency')->number ?? '' }}">
                </div>
            </div>
        </div>
        <div class="p-modal-footer">
            <button class="p-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="p-btn-primary" id="editSaveBtn" onclick="saveProfile()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

{{-- Save Success Modal --}}
<div class="p-modal-overlay" id="saveSuccessModal" onclick="if(event.target===this)closeSaveSuccess()">
    <div class="p-modal-box p-success-box">
        <div class="p-modal-body p-success-body">
            <div class="p-success-icon">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="p-success-title">Profile Updated!</h3>
            <p class="p-success-text">Your profile information has been saved successfully.</p>
            <div class="p-success-meta">
                <div class="p-success-meta-row p-success-meta-row-border"><span class="p-success-meta-label">Updated by</span><strong class="p-success-meta-value">{{ $employee->first_name }} {{ $employee->last_name }}</strong></div>
                <div class="p-success-meta-row p-success-meta-row-border"><span class="p-success-meta-label">Section</span><strong class="p-success-meta-value">Contact &amp; Address</strong></div>
                <div class="p-success-meta-row"><span class="p-success-meta-label">Saved at</span><strong id="saveTimestamp" class="p-success-meta-value">—</strong></div>
            </div>
        </div>
        <div class="p-modal-footer p-success-footer">
            <button class="p-btn-primary p-success-btn" onclick="closeSaveSuccess()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>

@include('employee.chatbot.employeeChatbot')

@push('scripts')
<script>
const sidebar    = document.getElementById('sidebar');
const toggleBtn  = document.getElementById('toggle-btn');
const logoText   = document.getElementById('logo-text');
const navLabel   = document.getElementById('nav-label');
const userInfo   = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn  = document.getElementById('mobile-menu-btn');
const overlay    = document.getElementById('mobile-overlay');

if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => el.style.display = collapsed ? 'none' : '');
    });
}
if (mobileBtn && sidebar) {
    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        const open = sidebar.classList.contains('mobile-open');
        overlay.classList.toggle('active', open);
        overlay.style.display = open ? 'block' : 'none';
    });
}
if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        overlay.style.display = 'none';
    });
}

function switchProfileTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function openEditModal()  { document.getElementById('editModal').classList.add('show'); }
function closeEditModal() { document.getElementById('editModal').classList.remove('show'); }
function closeSaveSuccess() { document.getElementById('saveSuccessModal').classList.remove('show'); }

function showEditError(message) {
    const box = document.getElementById('editModalMsg');
    box.textContent = message;
    box.classList.remove('hidden');
    document.querySelector('#editModal .p-modal-body').scrollTop = 0;
}

function saveProfile() {
    const box = document.getElementById('editModalMsg');
    box.classList.add('hidden');

    const data = {
        contact_number: document.getElementById('edit-contact').value.trim(),
        email: document.getElementById('edit-email').value.trim(),
        house_no: document.getElementById('edit-house-no').value.trim(),
        street: document.getElementById('edit-street').value.trim(),
        barangay: document.getElementById('edit-barangay').value.trim(),
        city: document.getElementById('edit-city').value.trim(),
        province: document.getElementById('edit-province').value.trim(),
        zip_code: document.getElementById('edit-zip').value.trim(),
        emergency_contact_person: document.getElementById('edit-emergencyContact').value.trim(),
        emergency_phone: document.getElementById('edit-emergencyPhone').value.trim(),
    };

    const btn = document.getElementById('editSaveBtn');
    btn.disabled = true;

    fetch('{{ route("employee.profile.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            // Laravel returns 422 with {message, errors:{field:[...]}} — surface the
            // first one. Previously any failure was swallowed and the modal just
            // sat there looking like nothing had happened.
            const first = result.errors ? Object.values(result.errors)[0]?.[0] : null;
            throw new Error(first || result.message || 'Could not save your changes. Please try again.');
        }
        return result;
    })
    .then(result => {
        document.getElementById('display-contact').textContent = result.data.contact_number;
        document.getElementById('display-email').textContent = result.data.email;
        document.getElementById('display-address').textContent = result.data.address;
        document.getElementById('display-emergency-contact').textContent = result.data.emergency_contact_person;
        document.getElementById('display-emergency-phone').textContent = result.data.emergency_phone;
        document.getElementById('saveTimestamp').textContent = new Date().toLocaleString('en-PH', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });
        closeEditModal();
        document.getElementById('saveSuccessModal').classList.add('show');
    })
    .catch(error => showEditError(error.message))
    .finally(() => { btn.disabled = false; });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeEditModal(); closeSaveSuccess(); } });
</script>
@endpush

@endsection
