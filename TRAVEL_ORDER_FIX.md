# Travel Order Filing Fix

## Problem
The mobile application was unable to save travel orders to the database. The error was:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'attachment_path' in 'field list'
```

## Root Cause
There was a mismatch between the database schema and the code:
- **Database column name**: `attachment`
- **Code was using**: `attachment_path`

## Solution
Updated the following files to use the correct column name `attachment`:

### 1. Model - `app/Models/TravelOrder.php`
- Changed `'attachment_path'` to `'attachment'` in the `$fillable` array

### 2. Mobile API Controller - `app/Http/Controllers/Api/MobileTravelOrderController.php`
- Changed `'attachment_path' => $attachmentPath` to `'attachment' => $attachmentPath` in the `store()` method
- Changed `$order->attachment_path` to `$order->attachment` in the `destroy()` method
- Changed `$attachmentPath = $order->attachment_path` to `$attachmentPath = $order->attachment` in the `formatOrder()` method

### 3. Web Controller - `app/Http/Controllers/TravelOrderController.php`
- Changed `'attachment_path' => $attachmentPath` to `'attachment' => $attachmentPath` in the travel order creation

## Testing
After applying these fixes:
1. The mobile app should now be able to successfully file travel orders
2. File attachments will be properly saved to the database
3. Both web and mobile interfaces will work correctly

## Files Modified
- `app/Models/TravelOrder.php`
- `app/Http/Controllers/Api/MobileTravelOrderController.php`
- `app/Http/Controllers/TravelOrderController.php`

## Date Fixed
May 30, 2026
