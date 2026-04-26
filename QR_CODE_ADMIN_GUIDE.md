# QR Code Generation Feature - Admin Personnel

## Overview
Admins can now generate QR codes for employees directly from the Personnel page. These QR codes are used for attendance tracking via the QR scanner system.

## Features Added

### 1. QR Code Button
- Added "QR Code" button in the Actions column for each employee
- Styled with yellow/gold color (#d9bb00) for visibility
- Located between "Edit" and "Activate/Deactivate" buttons

### 2. QR Code Modal
- Displays employee name and ID
- Shows generated QR code (256x256 pixels)
- Professional card-style layout
- Two action buttons: Download and Print

### 3. Download Functionality
- Creates a printable card (400x550px) with:
  - QR code centered
  - Employee name
  - Employee ID
  - "Attendance QR Code" label
  - Professional border
- Downloads as PNG file: `QR_{employeeId}_{employeeName}.png`

### 4. Print Functionality
- Opens print-friendly page
- Formatted card layout
- Optimized for printing on standard paper
- Can be printed on ID cards or paper

## How to Use

### For Admins:

1. **Navigate to Personnel Page**
   - Go to Admin → Personnel

2. **Generate QR Code**
   - Find the employee in the table
   - Click the "QR Code" button in the Actions column
   - QR code modal will appear

3. **Download QR Code**
   - Click "Download" button
   - PNG file will be saved to your downloads folder
   - Print or share the file as needed

4. **Print QR Code**
   - Click "Print" button
   - Print dialog will open
   - Print directly to printer or save as PDF

## Technical Details

### Files Modified:
1. `adminPersonnel.blade.php` - Added QR button and modal
2. `adminPersonnel.js` - Added QR generation functions
3. `employeeWizard.css` - Added QR button styling

### Libraries Used:
- **QRCode.js** (v1.0.0) - Client-side QR code generation
- CDN: `https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js`

### QR Code Data:
- Contains: Employee ID (bigint)
- Format: Plain text number
- Error correction: High (Level H)
- Color: Dark blue (#0b044d) on white

## Integration with Attendance System

The generated QR codes work seamlessly with the attendance scanner:

1. Employee receives printed QR code
2. Employee scans QR at attendance terminal
3. System reads employee ID from QR code
4. Attendance is recorded (AM/PM/OT)

## Best Practices

### For Printing:
- Use high-quality printer for clear QR codes
- Print on durable material (laminated cards recommended)
- Test scan before distributing to employees

### For Distribution:
- Generate QR codes for all active employees
- Keep digital copies in employee records
- Regenerate if QR code is damaged or lost

### Security:
- QR codes are static (don't expire)
- Only contain employee ID (no sensitive data)
- Can be regenerated anytime by admin
- Consider adding location verification in scanner

## Troubleshooting

**QR Code not generating?**
- Check browser console for errors
- Ensure QRCode.js library is loaded
- Verify employee ID is valid

**Download not working?**
- Check browser download permissions
- Try different browser
- Ensure popup blocker is disabled

**Print quality poor?**
- Use higher quality printer settings
- Ensure QR code is not pixelated
- Try printing at larger size

## Future Enhancements

Potential improvements:
- Bulk QR code generation for all employees
- Email QR codes directly to employees
- QR code expiration dates
- Dynamic QR codes with time-based tokens
- QR code usage analytics
