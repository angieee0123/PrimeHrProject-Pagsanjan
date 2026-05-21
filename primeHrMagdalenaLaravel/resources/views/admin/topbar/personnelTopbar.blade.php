{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <h2>Personnel Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Records</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="personnelSearchInput" class="topbar-search-input" placeholder="Search by ID, name, or position..." oninput="searchPersonnel(this.value)">
        </div>
    </div>
</div>

<style>
.topbar-search-wrap { position: relative; display: flex; align-items: center; }
.topbar-search-icon { position: absolute; left: 10px; color: #6b6a8a; pointer-events: none; }
.topbar-search-input { background: #fff; border: 1.5px solid #e5e4f0; border-radius: 9px; padding: 8px 12px 8px 32px; font-size: 12.5px; color: #0b044d; outline: none; width: 280px; font-family: 'Poppins', sans-serif; transition: border-color 0.2s, box-shadow 0.2s; box-shadow: 0 1px 3px rgba(11,4,77,0.06); }
.topbar-search-input::placeholder { color: #aaa8cc; }
.topbar-search-input:focus { border-color: #0b044d; box-shadow: 0 0 0 3px rgba(11,4,77,0.1); }
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    .topbar-search-wrap { width: 100%; }
    .topbar-search-input { width: 100%; }
}
</style>
