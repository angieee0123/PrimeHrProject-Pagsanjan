# 🧭 Sidebar Navigation Documentation

## 📋 Overview
This document provides a complete reference for all sidebar navigation components in the PrimeHR system. There are **three different sidebars** based on user roles:

1. **Admin Sidebar** - For HR Staff and Administrators
2. **Permanent Employee Sidebar** - For Permanent Employees
3. **Job Order Sidebar** - For Job Order Employees

---

## 🎨 Sidebar Comparison

| Feature | Admin | Permanent Employee | Job Order |
|---------|-------|-------------------|-----------|
| **File Location** | `resources/views/admin/sidebar/adminSidebar.blade.php` | `resources/views/permanent/sidebar/permanentSidebar.blade.php` | `resources/views/joborder/sidebar/joborderSidebar.blade.php` |
| **Color Theme** | Default (Blue/Gray) | Default (Blue/Gray) | Purple Gradient |
| **Menu Items** | 13 items | 9 items | 7 items |
| **User Avatar** | AD (Admin) | PE (Permanent) | JO (Job Order) |
| **Collapsible** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Mobile Responsive** | ✅ Yes | ✅ Yes | ✅ Yes |

---

## 1️⃣ Admin Sidebar

### 📍 Location
```
primeHrMagdalenaLaravel/resources/views/admin/sidebar/adminSidebar.blade.php
```

### 📊 Navigation Menu Items

| # | Label | Route | Icon | Description |
|---|-------|-------|------|-------------|
| 1 | Dashboard | `admin.dashboard` | 📊 Grid | Main admin dashboard |
| 2 | Recruitment | `admin.recruitment` | 👤+ | Recruitment management |
| 3 | Personnel | `admin.personnel` | 👥 | Employee management |
| 4 | Training & Development | `admin.training` | 📚 | Training programs |
| 5 | Performance Management | `admin.performance` | 📈 | Performance reviews |
| 6 | Attendance | `admin.attendance` | 📅✓ | Attendance tracking |
| 7 | Leave & Benefits | `admin.leave` | 📄 | Leave management |
| 8 | Travel Orders | `admin.travelorder` | 📍 | Travel order management |
| 9 | Payroll | `admin.payroll` | ₱ | Payroll processing |
| 10 | Deductions | `admin.deductions` | 💲 | Deduction management |
| 11 | Departments | `admin.departments` | 🏢 | Department management |
| 12 | Reports | `admin.reports` | 📊 | Report generation |
| 13 | Settings | `admin.settings` | ⚙️ | System settings |

### 🎨 Theme
- **Background:** Default gradient (Blue/Gray tones)
- **Active Color:** Blue accent
- **User Avatar:** Initials or "AD"
- **Logo:** Municipal of Pagsanjan logo

### 💻 Code Structure
```php
@php
$navItems = [
    ['id' => 'admin.dashboard',   'label' => 'Dashboard',              'route' => route('admin.dashboard')],
    ['id' => 'admin.recruitment', 'label' => 'Recruitment',            'route' => route('admin.recruitment')],
    // ... more items
];
$currentRoute = Route::currentRouteName();
@endphp
```

### 🔍 Active State Detection
```php
class="nav-item {{ $currentRoute === $item['id'] ? 'active' : '' }}"
```

---

## 2️⃣ Permanent Employee Sidebar

### 📍 Location
```
primeHrMagdalenaLaravel/resources/views/permanent/sidebar/permanentSidebar.blade.php
```

### 📊 Navigation Menu Items

| # | Label | Route | Icon | Description |
|---|-------|-------|------|-------------|
| 1 | Dashboard | `permanent.dashboard` | 📊 Grid | Employee dashboard |
| 2 | Payslip | `permanent.payslip` | 💳 | View payslips |
| 3 | Attendance | `permanent.attendance` | 📅 | View attendance records |
| 4 | Leave & Benefits | `permanent.leave` | 📄 | Leave applications |
| 5 | Travel Order | `permanent.travelorder` | 📍 | Travel order requests |
| 6 | Training | `permanent.training` | 📚 | Training records |
| 7 | Performance | `permanent.performance` | 📈 | Performance reviews |
| 8 | Profile | `permanent.profile` | 👤 | Personal profile |
| 9 | Settings | `permanent.settings` | ⚙️ | Account settings |

### 🎨 Theme
- **Background:** Default gradient (Blue/Gray tones)
- **Active Color:** Blue accent with active bar
- **User Avatar:** Initials or "PE"
- **Logo:** Municipal of Pagsanjan logo

### 💻 Code Structure
```php
@php
$navItems = [
    ['id' => 'dashboard',   'label' => 'Dashboard',              'route' => route('permanent.dashboard')],
    ['id' => 'payslip',     'label' => 'Payslip',                'route' => route('permanent.payslip')],
    // ... more items
];
$currentRoute = Route::currentRouteName();
@endphp
```

### 🔍 Active State Detection
```php
class="nav-item {{ str_contains($currentRoute, $item['id']) ? 'active' : '' }}"
```

### ✨ Special Features
- **Active Bar:** Visual indicator on the right side of active menu items
- **User Status Dot:** Green dot indicating online status
- **Logout Button:** Integrated in footer with icon

---

## 3️⃣ Job Order Sidebar

### 📍 Location
```
primeHrMagdalenaLaravel/resources/views/joborder/sidebar/joborderSidebar.blade.php
```

### 📊 Navigation Menu Items

| # | Label | Route | Icon | Description |
|---|-------|-------|------|-------------|
| 1 | Dashboard | `joborder.dashboard` | 📊 Grid | Employee dashboard |
| 2 | Payslip | `joborder.payslip` | 💳 | View payslips |
| 3 | Attendance | `joborder.attendance` | 📅 | View attendance records |
| 4 | Training | `joborder.training` | 📚 | Training records |
| 5 | Performance | `joborder.performance` | 📈 | Performance reviews |
| 6 | Profile | `joborder.profile` | 👤 | Personal profile |
| 7 | Settings | `joborder.settings` | ⚙️ | Account settings |

### 🎨 Theme (Custom Purple Gradient)
```css
--sidebar-bg: linear-gradient(180deg, #0b044d 0%, #1a0f6e 100%);
--sidebar-text: #ffffff;
--sidebar-active-bg: rgba(255,255,255,0.12);
--sidebar-active-text: #ffffff;
--sidebar-hover-bg: rgba(255,255,255,0.08);
--sidebar-border: rgba(255,255,255,0.1);
```

- **Background:** Purple gradient (#0b044d → #1a0f6e)
- **Text Color:** White
- **Active Color:** White with semi-transparent background
- **Active Bar:** Yellow (#d9bb00)
- **User Avatar:** Green gradient with "JO" initials

### 💻 Code Structure
```php
@php
$navItems = [
    ['id' => 'dashboard',   'label' => 'Dashboard',   'route' => route('joborder.dashboard')],
    ['id' => 'payslip',     'label' => 'Payslip',     'route' => route('joborder.payslip')],
    // ... more items
];
$currentPath = request()->path();
@endphp
```

### 🔍 Active State Detection
```php
class="nav-item {{ str_contains($currentPath, $item['id']) ? 'active' : '' }}"
```

### ✨ Special Features
- **Custom Theme:** Unique purple gradient background
- **Inline Styles:** Theme defined in `<style>` block within the component
- **Yellow Active Bar:** Distinctive yellow accent color

---

## 🔧 Common Features (All Sidebars)

### 1. Collapsible Sidebar
All sidebars can be collapsed/expanded using the toggle button:

```javascript
toggleBtn.addEventListener('click', () => {
    const collapsed = sidebar.classList.toggle('collapsed');
    toggleBtn.textContent = collapsed ? '›' : '‹';
    // Hide/show text elements
    if (logoText) logoText.style.display = collapsed ? 'none' : '';
    if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
    if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
});
```

### 2. Mobile Responsive
Mobile menu with overlay:

```javascript
mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
});
```

### 3. Sidebar Structure
```html
<aside class="sidebar" id="sidebar">
    <!-- Header: Logo + Toggle Button -->
    <div class="sidebar-header">
        <div class="logo">...</div>
        <button class="toggle-btn">‹</button>
    </div>

    <!-- Navigation Label -->
    <p class="nav-section-label">NAVIGATION</p>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <a href="..." class="nav-item">
            <span class="nav-icon">...</span>
            <span class="nav-label">...</span>
            <span class="nav-active-bar"></span>
        </a>
    </nav>

    <!-- Footer: User Info + Logout -->
    <div class="sidebar-footer">
        <div class="user-avatar-wrap">...</div>
        <div class="user-info">...</div>
        <form method="POST" action="{{ route('logout') }}">
            <button type="submit" class="logout-btn">...</button>
        </form>
    </div>
</aside>
```

### 4. User Information Display
All sidebars display user information in the footer:

```php
<div class="user-info" id="user-info">
    <p class="user-name">{{ $authFullName ?? 'Default Name' }}</p>
    <p class="user-role">{{ $authRole ?? 'Default Role' }}</p>
</div>
```

**Variables:**
- `$authFullName` - User's full name
- `$authRole` - User's role/position
- `$authInitials` - User's initials for avatar

### 5. Logout Functionality
All sidebars include a logout button:

```html
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="logout-btn" title="Logout">
        <svg>...</svg>
    </button>
</form>
```

---

## 📱 Mobile Overlay

All pages using sidebars include a mobile overlay:

```html
<div class="mobile-overlay" id="mobile-overlay"></div>
```

This overlay:
- Appears when sidebar is opened on mobile
- Darkens the background
- Closes sidebar when clicked
- Prevents interaction with main content

---

## 🎯 Key Differences Summary

### Menu Items
- **Admin:** 13 items (full system management)
- **Permanent:** 9 items (employee self-service + leave/travel)
- **Job Order:** 7 items (basic employee self-service, no leave/travel)

### Visual Theme
- **Admin & Permanent:** Default blue/gray theme
- **Job Order:** Custom purple gradient theme

### Active Detection
- **Admin:** Uses exact route name match
- **Permanent:** Uses `str_contains()` for partial match
- **Job Order:** Uses `str_contains()` on path

### Special Features
- **Permanent:** Has "Leave & Benefits" and "Travel Order"
- **Job Order:** No leave or travel order features
- **Admin:** Has comprehensive management features

---

## 🔗 Usage in Views

### Including Sidebar in Blade Templates

**Admin Pages:**
```blade
@include('admin.sidebar.adminSidebar')
```

**Permanent Employee Pages:**
```blade
@include('permanent.sidebar.permanentSidebar')
```

**Job Order Pages:**
```blade
@include('joborder.sidebar.joborderSidebar')
```

---

## 🎨 CSS Classes Reference

### Sidebar Container
- `.sidebar` - Main sidebar container
- `.sidebar.collapsed` - Collapsed state
- `.sidebar.mobile-open` - Mobile open state

### Header
- `.sidebar-header` - Header container
- `.logo` - Logo container
- `.logo-mark` - Logo image wrapper
- `.logo-text-wrap` - Text wrapper
- `.logo-text` - Main logo text
- `.logo-sub` - Subtitle text
- `.toggle-btn` - Collapse/expand button

### Navigation
- `.nav-section-label` - "NAVIGATION" label
- `.sidebar-nav` - Navigation container
- `.nav-item` - Menu item link
- `.nav-item.active` - Active menu item
- `.nav-icon` - Icon container
- `.nav-label` - Menu item text
- `.nav-active-bar` - Active indicator bar

### Footer
- `.sidebar-footer` - Footer container
- `.user-avatar-wrap` - Avatar wrapper
- `.user-avatar` - Avatar circle
- `.user-status-dot` - Online status indicator
- `.user-info` - User info container
- `.user-name` - User's name
- `.user-role` - User's role
- `.logout-btn` - Logout button

---

## 🧪 Testing Checklist

### Desktop View
- [ ] Sidebar displays correctly
- [ ] All menu items are visible
- [ ] Active state highlights correctly
- [ ] Toggle button collapses/expands sidebar
- [ ] User info displays in footer
- [ ] Logout button works
- [ ] Icons render properly

### Mobile View
- [ ] Mobile menu button appears
- [ ] Sidebar opens from left
- [ ] Overlay appears and darkens background
- [ ] Clicking overlay closes sidebar
- [ ] All menu items accessible
- [ ] Logout button accessible

### Navigation
- [ ] All links navigate to correct routes
- [ ] Active state persists on page reload
- [ ] Hover effects work properly
- [ ] Icons match menu items

---

## 📝 Customization Guide

### Adding a New Menu Item

1. **Add to `$navItems` array:**
```php
['id' => 'newpage', 'label' => 'New Page', 'route' => route('permanent.newpage')]
```

2. **Add icon in the foreach loop:**
```php
@elseif($item['id'] === 'newpage')
    <svg>...</svg>
@endif
```

3. **Create the route in `web.php`:**
```php
Route::get('/permanent/newpage', function () {
    return view('permanent.newpage.permanentNewPage');
})->middleware('auth')->name('permanent.newpage');
```

### Changing Sidebar Colors

**For Admin/Permanent (CSS file):**
```css
.sidebar {
    background: linear-gradient(180deg, #yourcolor1, #yourcolor2);
}
```

**For Job Order (inline styles):**
```css
#sidebar {
    --sidebar-bg: linear-gradient(180deg, #yourcolor1, #yourcolor2);
}
```

---

## ✅ Summary

| Sidebar | Users | Menu Items | Theme | Special Features |
|---------|-------|------------|-------|------------------|
| **Admin** | HR Staff, Admins | 13 | Blue/Gray | Full system management |
| **Permanent** | Permanent Employees | 9 | Blue/Gray | Leave & Travel Order |
| **Job Order** | Job Order Employees | 7 | Purple Gradient | Simplified menu |

All sidebars share:
- ✅ Collapsible functionality
- ✅ Mobile responsive design
- ✅ User info display
- ✅ Logout button
- ✅ Active state indication
- ✅ SVG icons

---

**Last Updated:** May 29, 2026  
**Maintained By:** Development Team
