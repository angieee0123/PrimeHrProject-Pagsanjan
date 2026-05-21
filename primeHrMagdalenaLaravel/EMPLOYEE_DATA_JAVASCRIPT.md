# Employee Data in JavaScript

## Passing Data from Blade to JavaScript

### Method 1: Inline Script Variables
```blade
<script>
    const currentUser = {
        id: {{ $authUser->id ?? 'null' }},
        employeeId: '{{ $authEmployeeId ?? '' }}',
        fullName: '{{ $authFullName ?? '' }}',
        initials: '{{ $authInitials ?? '' }}',
        role: '{{ $authRole ?? '' }}'
    };
    
    console.log('Current User:', currentUser);
</script>
```

### Method 2: Data Attributes
```blade
<div id="app" 
     data-user-id="{{ $authUser->id ?? '' }}"
     data-employee-id="{{ $authEmployeeId ?? '' }}"
     data-full-name="{{ $authFullName ?? '' }}"
     data-role="{{ $authRole ?? '' }}">
</div>

<script>
    const app = document.getElementById('app');
    const userId = app.dataset.userId;
    const employeeId = app.dataset.employeeId;
    const fullName = app.dataset.fullName;
    const role = app.dataset.role;
</script>
```

### Method 3: Meta Tags
```blade
<meta name="user-id" content="{{ $authUser->id ?? '' }}">
<meta name="employee-id" content="{{ $authEmployeeId ?? '' }}">
<meta name="user-name" content="{{ $authFullName ?? '' }}">

<script>
    const userId = document.querySelector('meta[name="user-id"]').content;
    const employeeId = document.querySelector('meta[name="employee-id"]').content;
    const userName = document.querySelector('meta[name="user-name"]').content;
</script>
```

## AJAX Requests with Employee Context

### Fetch Employee Data via API
```javascript
// Using existing API endpoint
fetch('/api/auth/user-id')
    .then(response => response.json())
    .then(data => {
        console.log('User ID:', data.user_id);
        console.log('Email:', data.email);
        console.log('Name:', data.name);
    });
```

### Send Employee ID in AJAX Request
```javascript
const employeeId = '{{ $authEmployeeId ?? "" }}';

fetch('/api/attendance/log', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        employee_id: employeeId,
        timestamp: new Date().toISOString()
    })
})
.then(response => response.json())
.then(data => console.log('Success:', data));
```

## Dynamic UI Updates

### Update Sidebar Name Dynamically
```javascript
function updateUserDisplay(fullName, initials) {
    document.querySelector('.user-name').textContent = fullName;
    document.querySelector('.user-avatar').textContent = initials;
}

// Example: After profile update
updateUserDisplay('{{ $authFullName }}', '{{ $authInitials }}');
```

### Role-Based UI
```javascript
const userRole = '{{ $authRole ?? "" }}';

if (userRole === 'Admin' || userRole === 'Hr') {
    document.getElementById('admin-panel').style.display = 'block';
}

if (userRole === 'Employee') {
    document.getElementById('employee-panel').style.display = 'block';
}
```

## Vue.js / React Integration

### Vue Component
```vue
<template>
    <div class="user-profile">
        <div class="avatar">{{ initials }}</div>
        <h3>{{ fullName }}</h3>
        <p>{{ employeeId }}</p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            userId: @json($authUser->id ?? null),
            employeeId: @json($authEmployeeId ?? ''),
            fullName: @json($authFullName ?? ''),
            initials: @json($authInitials ?? ''),
            role: @json($authRole ?? '')
        }
    }
}
</script>
```

### React Component
```jsx
const UserProfile = () => {
    const userData = {
        userId: {{ $authUser->id ?? 'null' }},
        employeeId: '{{ $authEmployeeId ?? "" }}',
        fullName: '{{ $authFullName ?? "" }}',
        initials: '{{ $authInitials ?? "" }}',
        role: '{{ $authRole ?? "" }}'
    };

    return (
        <div className="user-profile">
            <div className="avatar">{userData.initials}</div>
            <h3>{userData.fullName}</h3>
            <p>{userData.employeeId}</p>
        </div>
    );
};
```

## Local Storage

### Save User Data
```javascript
const userData = {
    id: {{ $authUser->id ?? 'null' }},
    employeeId: '{{ $authEmployeeId ?? "" }}',
    fullName: '{{ $authFullName ?? "" }}',
    initials: '{{ $authInitials ?? "" }}',
    role: '{{ $authRole ?? "" }}'
};

localStorage.setItem('currentUser', JSON.stringify(userData));
```

### Retrieve User Data
```javascript
const userData = JSON.parse(localStorage.getItem('currentUser'));
console.log('Employee ID:', userData.employeeId);
console.log('Full Name:', userData.fullName);
```

## WebSocket / Real-time Updates

### Socket.io Example
```javascript
const socket = io();

socket.emit('user:connect', {
    userId: {{ $authUser->id ?? 'null' }},
    employeeId: '{{ $authEmployeeId ?? "" }}',
    fullName: '{{ $authFullName ?? "" }}'
});

socket.on('user:update', (data) => {
    if (data.employeeId === '{{ $authEmployeeId }}') {
        updateUserDisplay(data.fullName, data.initials);
    }
});
```

## Best Practices

1. **Always escape data** when passing to JavaScript
2. **Use @json() directive** for complex objects
3. **Validate data** before using in JavaScript
4. **Handle null/undefined** values gracefully
5. **Don't expose sensitive data** in client-side code

## Security Notes

```javascript
// ❌ Bad: Exposing sensitive data
const user = {
    password: '{{ $authUser->password }}', // Never do this!
    token: '{{ $authUser->api_token }}'    // Never do this!
};

// ✅ Good: Only expose necessary data
const user = {
    id: {{ $authUser->id ?? 'null' }},
    employeeId: '{{ $authEmployeeId ?? "" }}',
    fullName: '{{ $authFullName ?? "" }}'
};
```
