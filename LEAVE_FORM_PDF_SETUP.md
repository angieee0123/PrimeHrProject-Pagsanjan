# Leave Application Form PDF Feature - Setup Guide

## Overview
This feature allows admins to view, print, and download leave application forms in PDF format directly from the leave request modal.

## What Was Added

### 1. **Backend Files**
- **LeaveController.php** - Added `generateLeaveForm()` method that creates PDF from leave application data
- **Routes (web.php)** - Added two routes for print and download functionality

### 2. **Frontend Files**
- **leave-requests-tab.blade.php** - Updated modal footer with Print Form and Download PDF buttons
- **adminLeaveAndBenefits.js** - Added `printLeaveForm()` and `downloadLeaveForm()` functions
- **leave-form-pdf.blade.php** - New PDF template file that mimics CS Form No. 6

### 3. **Dependencies**
- **composer.json** - Added `barryvdh/laravel-dompdf` package

## Installation Steps

### Step 1: Install DomPDF Package
Run the following command in your project directory:

```bash
composer require barryvdh/laravel-dompdf
```

### Step 2: Publish DomPDF Configuration (Optional)
If you need to customize DomPDF settings:

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Step 3: Clear Laravel Cache
```bash
php artisan cache:clear
php artisan config:clear
```

### Step 4: Test the Feature
1. Navigate to Admin Dashboard → Leave & Benefits → Leave Requests
2. Click the "View" button on any leave request
3. In the modal, you'll see two new buttons:
   - **Print Form** - Opens the form in a new window with print dialog
   - **Download PDF** - Downloads the form as PDF file

## How It Works

### User Flow
1. Admin clicks "View" on a leave request row
2. The leave detail modal opens showing all application information
3. At the bottom of the modal, admin has two options:
   - **Print Form** - Generates PDF and opens print dialog
   - **Download PDF** - Generates PDF and downloads to computer

### Form Content
The generated PDF includes:
- **Header** - CS Form No. 6 title and application type
- **Applicant Information** - Employee name, ID, department, position
- **Leave Details** - Leave type, dates, number of days, status
- **Reason** - Full reason for leave
- **Application Details** - Application number, submission date, approval date (if approved)
- **Signature Lines** - Space for employee and approver signatures

### Status Indicators
- Approved applications show green badge
- Pending applications show yellow badge
- Disapproved applications show red badge with remarks

## API Endpoints

```
GET /admin/leave/{id}/print-form
- Opens PDF in browser for printing
- Requires authentication

GET /admin/leave/{id}/download-form
- Downloads PDF file
- Requires authentication
```

## Technical Details

### PDF Generation Process
1. Fetches leave application from database with all relationships
2. Loads blade template `leave-form-pdf.blade.php`
3. Passes application data to template
4. DomPDF converts HTML to PDF
5. Returns PDF as download or display

### Template File Structure
- Located at: `resources/views/admin/leaveAndBenefits/leave-form-pdf.blade.php`
- Uses inline CSS for styling
- Includes page breaks and formatting suitable for printing

## Features

✅ **Print Support** - Opens PDF in new window with browser print dialog
✅ **Download Support** - Downloads PDF with application number as filename
✅ **Professional Format** - Resembles official CS Form No. 6
✅ **Dynamic Content** - Automatically includes all application data
✅ **Status Badges** - Shows current approval status on form
✅ **Remarks Display** - Shows approver remarks if disapproved

## File Names Generated
```
Leave-Form-{APPLICATION_NUMBER}.pdf
Example: Leave-Form-LV-2025-001.pdf
```

## Error Handling
If an error occurs during PDF generation:
- An error message is displayed in JSON format
- The user is informed that PDF generation failed
- The application continues to function normally

## Browser Support
- Works in all modern browsers (Chrome, Firefox, Safari, Edge)
- Print dialog works natively in all browsers
- Download supported in all browsers

## Customization

### Modify PDF Styling
Edit `resources/views/admin/leaveAndBenefits/leave-form-pdf.blade.php`:
- Change colors in the `<style>` section
- Adjust margins and page size
- Modify layout and formatting

### Change Page Size/Orientation
In `LeaveController.php`, modify the `generateLeaveForm()` method:
```php
->setPaper('a4')  // Change to 'letter', 'a3', etc.
->setOrientation('portrait')  // or 'landscape'
```

### Add More Fields
Add fields to the PDF template by:
1. Adding data to the `$data` array in controller
2. Displaying the data in the blade template

## Troubleshooting

### "Class not found" Error
- Run: `composer require barryvdh/laravel-dompdf`
- Run: `composer dump-autoload`
- Clear cache: `php artisan cache:clear`

### PDF Not Generating
- Check that leave application exists in database
- Verify all relationships are properly loaded
- Check Laravel logs: `storage/logs/laravel.log`

### Print Dialog Not Opening
- Ensure pop-ups are not blocked in browser
- Check browser console for JavaScript errors
- Try different browser

### File Download Not Working
- Check browser download settings
- Verify file permissions on server
- Check Laravel storage permissions

## Future Enhancements

Potential improvements:
- [ ] Add option to include employee photo on form
- [ ] Support for batch PDF generation
- [ ] Email PDF directly to approver
- [ ] Add QR code for verification
- [ ] Save PDF copies to leave application record
- [ ] Support for multiple language versions
- [ ] Custom header/footer with municipality logo

## Support

For issues or questions:
1. Check the Laravel logs in `storage/logs/`
2. Verify DomPDF is properly installed: `php artisan tinker` → `dd(class_exists('Barryvdh\DomPDF\Facade\Pdf'))`
3. Ensure all file paths are correct
4. Check browser console for JavaScript errors
