# Quick Reference: Employee Data in Views

## Available Variables (All Views)

```php
$authUser          // User model instance
$authEmployee      // Employee model instance
$authFullName      // "Juan Dela Cruz"
$authInitials      // "JD"
$authEmployeeId    // "PGS-0041"
$authRole          // "Admin" | "Hr" | "Employee"
```

## Common Use Cases

### Display User Name
```blade
{{ $authFullName ?? 'Guest' }}
```

### Display Avatar with Initials
```blade
<div class="avatar">{{ $authInitials ?? 'U' }}</div>
```

### Display Employee ID
```blade
<span class="emp-id">{{ $authEmployeeId ?? 'N/A' }}</span>
```

### Display Role Badge
```blade
<span class="badge">{{ $authRole ?? 'User' }}</span>
```

### Access Full Employee Data
```blade
@if($authEmployee)
    <p>{{ $authEmployee->first_name }} {{ $authEmployee->last_name }}</p>
    <p>{{ $authEmployee->email }}</p>
    <p>{{ $authEmployee->birth_date }}</p>
@endif
```

### Access Employment Details
```blade
@if($authEmployee && $authEmployee->employmentDetail)
    <p>Department: {{ $authEmployee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
    <p>Position: {{ $authEmployee->employmentDetail->designationRelation->title ?? 'N/A' }}</p>
    <p>Status: {{ $authEmployee->employmentDetail->employment_status }}</p>
@endif
```

### Conditional Display Based on Role
```blade
@if($authUser && $authUser->role === 'admin')
    <button>Admin Only Action</button>
@endif

@if(in_array($authUser->role ?? '', ['admin', 'hr']))
    <div>HR Management Section</div>
@endif
```

### Welcome Message
```blade
<h1>Welcome back, {{ $authFullName }}!</h1>
<p>Employee ID: {{ $authEmployeeId }}</p>
```

### User Profile Card
```blade
<div class="profile-card">
    <div class="avatar">{{ $authInitials }}</div>
    <h3>{{ $authFullName }}</h3>
    <p class="text-muted">{{ $authEmployeeId }}</p>
    <span class="badge">{{ $authRole }}</span>
</div>
```

## Always Use Fallbacks

```blade
{{-- Good: With fallback --}}
{{ $authFullName ?? 'User' }}

{{-- Bad: No fallback (may cause errors) --}}
{{ $authFullName }}
```

## Check Before Accessing Relationships

```blade
{{-- Good: Check existence --}}
@if($authEmployee && $authEmployee->employmentDetail)
    {{ $authEmployee->employmentDetail->departmentRelation->name }}
@endif

{{-- Bad: May cause null reference error --}}
{{ $authEmployee->employmentDetail->departmentRelation->name }}
```
