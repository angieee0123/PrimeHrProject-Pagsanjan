@extends('layouts.app')

@section('title', 'Training & Development · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

        @php
        $trainings = collect([
            ['id' => 'TRN-001', 'title' => 'Leadership Development Program', 'type' => 'Leadership', 'participants' => 25, 'capacity' => 30, 'status' => 'Ongoing', 'startDate' => 'Jun 15, 2025', 'endDate' => 'Jul 15, 2025', 'venue' => 'Municipal Hall Conference Room'],
            ['id' => 'TRN-002', 'title' => 'Digital Literacy Training', 'type' => 'Technical', 'participants' => 18, 'capacity' => 20, 'status' => 'Ongoing', 'startDate' => 'Jun 20, 2025', 'endDate' => 'Jun 30, 2025', 'venue' => 'IT Training Center'],
            ['id' => 'TRN-003', 'title' => 'Customer Service Excellence', 'type' => 'Soft Skills', 'participants' => 30, 'capacity' => 30, 'status' => 'Completed', 'startDate' => 'May 10, 2025', 'endDate' => 'May 20, 2025', 'venue' => 'Municipal Hall Conference Room'],
            ['id' => 'TRN-004', 'title' => 'Financial Management Workshop', 'type' => 'Technical', 'participants' => 12, 'capacity' => 25, 'status' => 'Scheduled', 'startDate' => 'Jul 5, 2025', 'endDate' => 'Jul 10, 2025', 'venue' => 'Treasurer Office Training Room'],
            ['id' => 'TRN-005', 'title' => 'Emergency Response Training', 'type' => 'Safety', 'participants' => 40, 'capacity' => 50, 'status' => 'Scheduled', 'startDate' => 'Jul 20, 2025', 'endDate' => 'Jul 22, 2025', 'venue' => 'MDRRM Office'],
        ]);

        $typeAccents = ['Leadership' => '#0b044d', 'Technical' => '#15803d', 'Soft Skills' => '#d9bb00', 'Safety' => '#8e1e18', 'Compliance' => '#6b3fa0'];
        $trainingTypes = ['All Types', 'Leadership', 'Technical', 'Soft Skills', 'Safety', 'Compliance'];

        $totalPrograms = $trainings->count();
        $ongoingCount = $trainings->where('status', 'Ongoing')->count();
        $totalParticipants = $trainings->sum('participants');
        $completedCount = $trainings->where('status', 'Completed')->count();
        @endphp

        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <h2>Training & Development</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Training Programs</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    {{ $ongoingCount }} Active
                </span>
                <span class="banner-badge outline">FY {{ now()->year }}</span>
            </div>
        </div>

        <div class="stats-grid stats-grid-4" style="margin-bottom: 24px;">
            <div class="stat-card" style="--accent-color: #0b044d">
                <div class="stat-top">
                    <p class="stat-label">Total Programs</p>
                    <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalPrograms }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All training programs</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #15803d">
                <div class="stat-top">
                    <p class="stat-label">Ongoing</p>
                    <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $ongoingCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#15803d"></span>
                    <p class="stat-sub">Currently active</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #d9bb00">
                <div class="stat-top">
                    <p class="stat-label">Total Participants</p>
                    <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalParticipants }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#d9bb00"></span>
                    <p class="stat-sub">All enrollments</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #8e1e18">
                <div class="stat-top">
                    <p class="stat-label">Completed</p>
                    <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $completedCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Finished programs</p>
                </div>
            </div>
        </div>

        <div class="table-section" style="margin-bottom: 22px;">
            <div class="table-header">
                <div>
                    <p class="table-title">Training Programs</p>
                    <p class="table-sub">Municipal Government of Pagsanjan · <span id="showing-count">{{ $totalPrograms }}</span> of {{ $totalPrograms }} programs</p>
                </div>
                <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search programs..." class="search-input">
                    </div>
                    <select class="filter-select" id="type-filter">
                        @foreach($trainingTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter">
                        <option value="All">All Status</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" id="add-training-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Training
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Training ID</th>
                            <th>Program Title</th>
                            <th>Type</th>
                            <th>Participants</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="training-table-body">
                        @foreach($trainings as $training)
                        @php
                        $fillPct = round(($training['participants'] / $training['capacity']) * 100);
                        $statusClass = $training['status'] === 'Ongoing' ? 'processed' : ($training['status'] === 'Completed' ? 'on-hold' : 'pending');
                        $accent = $typeAccents[$training['type']] ?? '#0b044d';
                        @endphp
                        <tr data-type="{{ $training['type'] }}" data-status="{{ $training['status'] }}">
                            <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">{{ $training['id'] }}</td>
                            <td><span class="position-cell">{{ $training['title'] }}</span></td>
                            <td><span class="badge-emptype" style="border-color: {{ $accent }}40; background: {{ $accent }}10; color: {{ $accent }}">{{ $training['type'] }}</span></td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <div style="width: 60px; height: 6px; background: #f0effe; border-radius: 99px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $fillPct }}%; background: {{ $accent }}; border-radius: 99px;"></div>
                                    </div>
                                    <span style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $training['participants'] }}</span>
                                    <span style="font-size: 11px; color: #9999bb;">/ {{ $training['capacity'] }}</span>
                                </div>
                            </td>
                            <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $training['startDate'] }}</td>
                            <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $training['endDate'] }}</td>
                            <td><span class="dept-tag">{{ $training['venue'] }}</span></td>
                            <td><span class="badge-status {{ $statusClass }}">{{ $training['status'] }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-view" onclick="viewTraining('{{ $training['id'] }}')">View</button>
                                    <button class="btn-edit" onclick="editTraining('{{ $training['id'] }}')">Edit</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="empty-state" id="empty-state" style="display: none; text-align: center; padding: 40px 20px;">
                    <p style="font-size: 13px; color: #9999bb; margin: 0;">No training programs match your criteria</p>
                </div>
            </div>

            <div class="table-footer">
                <p>Showing <strong id="visible-count">{{ $totalPrograms }}</strong> of <strong>{{ $totalPrograms }}</strong> programs</p>
                <div class="pagination">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">›</button>
                </div>
            </div>
        </div>

    </main>

    @include('admin.admin-chatbot')

</div>

<script>
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('toggle-btn');
    const logoText     = document.getElementById('logo-text');
    const navLabel     = document.getElementById('nav-label');
    const userInfo     = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn    = document.getElementById('mobile-menu-btn');
    const overlay      = document.getElementById('mobile-overlay');

    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        logoText.style.display  = collapsed ? 'none' : '';
        navLabel.style.display  = collapsed ? 'none' : '';
        userInfo.style.display  = collapsed ? 'none' : '';
        sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });

    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
</script>

<div class="modal-overlay" id="view-modal" style="display: none;">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="pmodal-hero" style="display: flex; gap: 16px; align-items: flex-start;">
                <div id="modal-icon" style="width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow" id="modal-training-id">TRAINING PROGRAM · TRN-001</span>
                    <h3 class="modal-title" id="modal-training-title">Leadership Development Program</h3>
                    <p class="modal-sub" id="modal-training-sub">Leadership Training · Municipal Hall Conference Room</p>
                    <div class="pmodal-badges" style="display: flex; gap: 8px; margin-top: 8px;">
                        <span class="badge-status" id="modal-status-badge">Ongoing</span>
                        <span class="badge-emptype" id="modal-type-badge">Leadership</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 18px;">
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2;">Participants</p>
                        <p style="font-size: 16px; font-weight: 800; color: #0b044d;"><span id="modal-participants">25</span><span style="font-size: 12px; font-weight: 500; color: #9999bb;"> / <span id="modal-capacity">30</span></span></p>
                    </div>
                </div>
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div id="capacity-icon" style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2;">Capacity Fill</p>
                        <p style="font-size: 16px; font-weight: 800;" id="modal-fill-pct">83%</p>
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 16;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6;">
                    <span style="font-size: 11px; color: #9999bb; font-weight: 600; letter-spacing: 0.8px;">ENROLLMENT PROGRESS</span>
                    <span style="font-size: 11px; font-weight: 700;" id="modal-progress-pct">83%</span>
                </div>
                <div style="height: 8px; background: #f0effe; border-radius: 99px;">
                    <div id="modal-progress-bar" style="height: 100%; width: 83%; border-radius: 99px; transition: width 0.4s;"></div>
                </div>
            </div>
            <div style="font-size: 11px; color: #9999bb; font-weight: 600; letter-spacing: 0.8px; margin-bottom: 12px; padding-top: 8px; border-top: 1px solid #f0effe;">SCHEDULE & DETAILS</div>
            <div class="modal-row"><span>Start Date</span><strong id="modal-start-date">Jun 15, 2025</strong></div>
            <div class="modal-row"><span>End Date</span><strong id="modal-end-date">Jul 15, 2025</strong></div>
            <div class="modal-row"><span>Venue</span><strong id="modal-venue">Municipal Hall Conference Room</strong></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary">View Participants</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="training-form-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="form-eyebrow">NEW TRAINING</span>
                <h3 class="modal-title" id="form-title">Create Training Program</h3>
            </div>
            <button class="modal-close" onclick="closeModal('training-form-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="training-form" method="POST" action="{{ route('training.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field form-full">
                        <label>Training Title</label>
                        <input type="text" name="title" id="form-title-input" placeholder="e.g. Leadership Development Program" required />
                    </div>
                    <div class="form-field">
                        <label>Training Type</label>
                        <select name="type" id="form-type">
                            <option value="Leadership">Leadership</option>
                            <option value="Technical">Technical</option>
                            <option value="Soft Skills">Soft Skills</option>
                            <option value="Safety">Safety</option>
                            <option value="Compliance">Compliance</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Capacity</label>
                        <input type="number" name="capacity" id="form-capacity" value="20" min="1" />
                    </div>
                    <div class="form-field">
                        <label>Start Date</label>
                        <input type="date" name="startDate" id="form-start-date" required />
                    </div>
                    <div class="form-field">
                        <label>End Date</label>
                        <input type="date" name="endDate" id="form-end-date" required />
                    </div>
                    <div class="form-field form-full">
                        <label>Venue</label>
                        <input type="text" name="venue" id="form-venue" placeholder="e.g. Municipal Hall Conference Room" />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('training-form-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitTrainingForm()">Create Training</button>
        </div>
    </div>
</div>

<style>
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-wrap svg {
    position: absolute;
    left: 10px;
    pointer-events: none;
}
.search-input {
    height: 34px;
    padding: 0 10px 0 30px;
    border: 1.5px solid #e4e3f0;
    border-radius: 8px;
    font-size: 12.5px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fafafe;
    outline: none;
    width: 180px;
    transition: border-color 0.2s;
}
.search-input:focus { border-color: #0b044d; }

/* modal-lg override */
.modal-lg { max-width: 560px; }

/* form grid — not in global CSS */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 5px; }
.form-full { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 600; color: #0b044d; }
.form-field input,
.form-field select {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e4e3f0;
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    outline: none;
    box-sizing: border-box;
    background: #fff;
}
.form-field input:focus,
.form-field select:focus { border-color: #0b044d; }

/* training-specific badge */
.badge-emptype {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e4e3f0;
}

/* training edit button — uses dashboard navy */
.btn-edit {
    padding: 5px 14px;
    border: 1.5px solid #0b044d;
    border-radius: 7px;
    background: #0b044d;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-edit:hover { background: #1a0f6e; border-color: #1a0f6e; }

@media (max-width: 900px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-full { grid-column: 1; }
}
</style>

<script>
const trainingData = @json($trainings);
const typeAccents = @json($typeAccents);

document.getElementById('add-training-btn').addEventListener('click', function() {
    document.getElementById('form-eyebrow').textContent = 'NEW TRAINING';
    document.getElementById('form-title').textContent = 'Create Training Program';
    document.getElementById('training-form-modal').style.display = 'flex';
    document.getElementById('form-title-input').value = '';
    document.getElementById('form-type').value = 'Leadership';
    document.getElementById('form-capacity').value = '20';
    document.getElementById('form-start-date').value = '';
    document.getElementById('form-end-date').value = '';
    document.getElementById('form-venue').value = '';
});

function viewTraining(trainingId) {
    const training = trainingData.find(t => t.id === trainingId);
    if (!training) return;
    
    const accent = typeAccents[training.type] || '#0b044d';
    const fillPct = Math.round((training.participants / training.capacity) * 100);
    const statusClass = training.status === 'Ongoing' ? 'processed' : training.status === 'Completed' ? 'on-hold' : 'pending';
    
    document.getElementById('modal-training-id').textContent = 'TRAINING PROGRAM · ' + training.id;
    document.getElementById('modal-training-title').textContent = training.title;
    document.getElementById('modal-training-sub').textContent = training.type + ' Training · ' + training.venue;
    document.getElementById('modal-status-badge').textContent = training.status;
    document.getElementById('modal-status-badge').className = 'badge-status ' + statusClass;
    document.getElementById('modal-type-badge').textContent = training.type;
    document.getElementById('modal-type-badge').style.borderColor = accent + '40';
    document.getElementById('modal-type-badge').style.background = accent + '10';
    document.getElementById('modal-type-badge').style.color = accent;
    document.getElementById('modal-participants').textContent = training.participants;
    document.getElementById('modal-capacity').textContent = training.capacity;
    document.getElementById('modal-fill-pct').textContent = fillPct + '%';
    document.getElementById('modal-fill-pct').style.color = accent;
    document.getElementById('modal-progress-pct').textContent = fillPct + '%';
    document.getElementById('modal-progress-pct').style.color = accent;
    document.getElementById('modal-progress-bar').style.background = 'linear-gradient(90deg, ' + accent + ', ' + accent + '99)';
    document.getElementById('modal-progress-bar').style.width = fillPct + '%';
    document.getElementById('modal-start-date').textContent = training.startDate;
    document.getElementById('modal-end-date').textContent = training.endDate;
    document.getElementById('modal-venue').textContent = training.venue;
    
    const iconDiv = document.getElementById('modal-icon');
    iconDiv.style.background = 'linear-gradient(135deg, ' + accent + ' 0%, ' + accent + '99 100%)';
    
    const capacityIcon = document.getElementById('capacity-icon');
    capacityIcon.style.background = 'linear-gradient(135deg, ' + accent + ' 0%, ' + accent + '99 100%)';
    
    document.getElementById('view-modal').style.display = 'flex';
}

function editTraining(trainingId) {
    const training = trainingData.find(t => t.id === trainingId);
    if (!training) return;
    
    document.getElementById('form-eyebrow').textContent = 'EDIT TRAINING';
    document.getElementById('form-title').textContent = 'Edit — ' + training.title;
    document.getElementById('form-title-input').value = training.title;
    document.getElementById('form-type').value = training.type;
    document.getElementById('form-capacity').value = training.capacity;
    document.getElementById('form-venue').value = training.venue;
    document.getElementById('training-form-modal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function submitTrainingForm() {
    const title = document.getElementById('form-title-input').value;
    const startDate = document.getElementById('form-start-date').value;
    const endDate = document.getElementById('form-end-date').value;
    
    if (title && startDate && endDate) {
        document.getElementById('training-form').submit();
    }
}

document.getElementById('type-filter').addEventListener('change', filterTrainings);
document.getElementById('status-filter').addEventListener('change', filterTrainings);
document.getElementById('search-input').addEventListener('input', filterTrainings);

function filterTrainings() {
    const typeFilter = document.getElementById('type-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#training-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const type = row.dataset.type;
        const status = row.dataset.status;
        const title = row.querySelector('.position-cell').textContent.toLowerCase();
        const trainingId = row.querySelector('td').textContent.toLowerCase();
        const matchType = typeFilter === 'All Types' || type === typeFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || title.includes(searchQuery) || trainingId.includes(searchQuery);
        const isVisible = matchType && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
    document.getElementById('visible-count').textContent = visibleCount;
    document.getElementById('empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('training-form-modal').style.display = 'none';
    }
});

</script>
@endsection