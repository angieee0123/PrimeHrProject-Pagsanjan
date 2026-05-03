# Layout Files Created

## Issue Fixed
Error: `View [layouts.joborder] not found`

## Solution
Created missing layout files for joborder and permanent employee views.

## Layout Files

### 1. layouts/app.blade.php (Existing)
- Used by: Admin views
- Includes: Admin sidebar, chatbot, theme settings

### 2. layouts/joborder.blade.php (NEW)
- Used by: Job Order employee views
- Clean layout without admin components
- Extends basic HTML structure with Vite assets

### 3. layouts/permanent.blade.php (NEW)
- Used by: Permanent employee views
- Clean layout without admin components
- Extends basic HTML structure with Vite assets

## File Structure

```
resources/views/layouts/
├── app.blade.php       (Admin layout)
├── joborder.blade.php  (Job Order layout)
└── permanent.blade.php (Permanent layout)
```

## Layout Usage

### Job Order Views:
```blade
@extends('layouts.joborder')

@section('title', 'Dashboard · PRIME HRIS')

@section('content')
    <!-- Content here -->
@endsection
```

### Permanent Views:
```blade
@extends('layouts.permanent')

@section('title', 'Dashboard · PRIME HRIS')

@section('content')
    <!-- Content here -->
@endsection
```

### Admin Views:
```blade
@extends('layouts.app')

@section('content')
    <!-- Content here -->
@endsection
```

## Features

Both new layouts include:
- ✅ CSRF token meta tag
- ✅ Poppins font from Google Fonts
- ✅ Vite CSS assets
- ✅ Vite JS assets
- ✅ Dynamic title support
- ✅ Stack support for additional styles/scripts

## Notes

- Job Order and Permanent layouts are minimal (no admin sidebar)
- Each view includes its own sidebar component
- Layouts support @stack('styles') and @stack('scripts')
- All layouts use Vite for asset compilation
