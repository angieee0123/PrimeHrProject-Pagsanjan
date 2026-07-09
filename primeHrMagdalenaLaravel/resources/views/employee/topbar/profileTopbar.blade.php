{{-- Permanent Profile Topbar --}}
<div class="welcome-banner profile-banner">
    <div class="banner-left profile-banner-left">
        <div class="profile-avatar-lg">
            {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
        </div>
        <div class="profile-banner-info">
            <div class="profile-banner-row profile-banner-name-row">
                <h2 class="profile-banner-name">
                    {{ $employee->first_name }} {{ $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '' }} {{ $employee->last_name }}{{ $employee->suffix ? ' ' . $employee->suffix : '' }}
                </h2>
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>{{ Auth::user()->status ?? 'Active' }}
                </span>
            </div>
            <p class="profile-banner-sub">
                {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
            </p>
            <div class="profile-banner-badges">
                <span class="banner-badge outline">{{ $employee->employmentDetail->employment_status ?? 'N/A' }}</span>
                <span class="banner-badge outline">{{ $employee->employee_id ?? 'N/A' }}</span>
                <span class="banner-badge outline">
                    Hired: {{ $employee->employmentDetail && $employee->employmentDetail->appointment_date ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('M d, Y') : 'N/A' }}
                </span>
            </div>
        </div>
    </div>
    <div class="banner-right">
        <button class="btn-edit-profile" onclick="openEditModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Profile
        </button>
    </div>
</div>
