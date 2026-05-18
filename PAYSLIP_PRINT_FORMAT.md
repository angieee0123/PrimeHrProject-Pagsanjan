# Payslip Print Format - Documentation

## Overview
Enhanced the payslip modal with professional print formatting including the Municipal Government of Pagsanjan logo.

## What Was Improved

### 1. Added Official Logo
**Location:** Top center of payslip
**File:** `public/municipal-of-pagsanjan-logo.jpg`
**Size:** 80px × 80px (screen), 100px × 100px (print)

### 2. Enhanced Header
```
┌─────────────────────────────────────┐
│         [PAGSANJAN LOGO]            │
│  MUNICIPAL GOVERNMENT OF PAGSANJAN  │
│         Province of Laguna          │
│            PAYSLIP                  │
└─────────────────────────────────────┘
```

### 3. Professional Print Styles
- **A4 Page Size** with 1cm margins
- **Color Preservation** for logos and badges
- **Page Break Control** to keep sections together
- **Clean Layout** without modal chrome
- **Proper Spacing** for readability

---

## Print Layout

### Page Structure:

```
┌────────────────────────────────────────────┐
│                                            │
│         [MUNICIPAL LOGO - 100px]           │
│    MUNICIPAL GOVERNMENT OF PAGSANJAN       │
│           Province of Laguna               │
│               PAYSLIP                      │
│  ──────────────────────────────────────   │
│                                            │
│  Employee: Juan Dela Cruz    ID: 2024-001 │
│  Department: Mayor's Office               │
│  Position: Admin Aide IV                  │
│  Period: Apr 01 - Apr 16, 2026           │
│  Pay Date: May 18, 2026                   │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  EARNINGS                                  │
│  Monthly Rate:        ₱14,308.00          │
│  Daily Rate:          ₱650.36             │
│  Days Worked:         12                  │
│  Basic Pay:           ₱7,804.32           │
│  Overtime Pay:        ₱0.00               │
│  Gross Pay:           ₱7,804.32           │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  DEDUCTIONS                                │
│  Late Deduction:      ₱0.00               │
│  Undertime Deduction: ₱0.00               │
│  Emergency Loan:      ₱900.00             │
│  MP LOAN:             ₱924.03             │
│  Total Deductions:    ₱1,824.03           │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  NET PAY              ₱5,980.29           │
│                                            │
│  Status: [Approved]                       │
│                                            │
└────────────────────────────────────────────┘
```

---

## Print Features

### 1. Logo Display
- **Screen View:** 80px × 80px
- **Print View:** 100px × 100px (larger for clarity)
- **Color:** Full color preserved
- **Position:** Centered at top

### 2. Header Formatting
- **Municipality Name:** 20px, Bold, Uppercase
- **Province:** 13px, Gray
- **"PAYSLIP" Title:** 18px, Bold, Uppercase, Letter-spaced

### 3. Content Sections
- **Employee Info:** 2-column grid
- **Earnings:** Itemized list with totals
- **Deductions:** Individual breakdown
- **Net Pay:** Large, prominent box

### 4. Color Preservation
```css
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
```
This ensures:
- Logo colors print correctly
- Status badges maintain colors
- Total rows keep dark background
- Net pay box keeps gradient

### 5. Page Control
```css
@page {
    margin: 1cm;
    size: A4;
}
```
- Standard A4 paper size
- 1cm margins all around
- Portrait orientation

---

## How to Print

### Method 1: Print Button
1. Open payslip detail modal
2. Click **"Print Payslip"** button
3. Browser print dialog opens
4. Review preview
5. Click "Print"

### Method 2: Keyboard Shortcut
1. Open payslip detail modal
2. Press **Ctrl+P** (Windows) or **Cmd+P** (Mac)
3. Browser print dialog opens
4. Review preview
5. Click "Print"

### Method 3: Browser Menu
1. Open payslip detail modal
2. Click browser menu (⋮)
3. Select "Print"
4. Review preview
5. Click "Print"

---

## Print Settings Recommendations

### For Best Results:

**Paper Size:** A4 (210mm × 297mm)
**Orientation:** Portrait
**Margins:** Default (or 1cm)
**Scale:** 100%
**Background Graphics:** ✅ Enabled (to print colors)
**Headers/Footers:** ❌ Disabled (for clean look)

### Browser-Specific:

#### Chrome/Edge:
```
☑ Background graphics
☐ Headers and footers
Scale: 100%
Margins: Default
```

#### Firefox:
```
☑ Print backgrounds
☐ Headers and footers
Scale: 100%
```

#### Safari:
```
☑ Print backgrounds
Scale: 100%
```

---

## Print Preview

### What Prints:
✅ Municipal logo (full color)
✅ Official header
✅ Employee information
✅ Complete earnings breakdown
✅ Individual deductions
✅ Net pay (with gradient background)
✅ Status badge (with color)

### What Doesn't Print:
❌ Modal header (title bar)
❌ Modal footer (buttons)
❌ Close button
❌ Print button
❌ Dark overlay background

---

## Troubleshooting

### Issue: Logo doesn't print
**Solution:** Enable "Background graphics" in print settings

### Issue: Colors are missing
**Solution:** 
1. Enable "Print backgrounds" in browser
2. Check printer supports color
3. Verify color ink/toner available

### Issue: Layout is cut off
**Solution:**
1. Check paper size is A4
2. Adjust margins if needed
3. Try 90% scale if still cut off

### Issue: Logo is blurry
**Solution:**
1. Ensure original logo is high resolution
2. Check printer DPI settings
3. Use "Best" quality print setting

### Issue: Page breaks in wrong places
**Solution:**
1. Already handled with CSS `page-break-inside: avoid`
2. If still issues, adjust content or use landscape

---

## Logo Requirements

### Current Logo:
**File:** `public/municipal-of-pagsanjan-logo.jpg`
**Format:** JPG
**Recommended:** PNG with transparency

### Optimal Logo Specs:
- **Format:** PNG (for transparency)
- **Size:** 300px × 300px minimum
- **Resolution:** 300 DPI for print quality
- **Background:** Transparent
- **Colors:** RGB for screen, CMYK for print

### To Replace Logo:
1. Save new logo as `municipal-of-pagsanjan-logo.png`
2. Place in `public/` folder
3. Update path in modal if needed:
   ```php
   <img src="{{ asset('municipal-of-pagsanjan-logo.png') }}" ...>
   ```

---

## CSS Print Styles

### Key Styles Applied:

```css
@media print {
    /* Hide everything except modal */
    body * { visibility: hidden; }
    #payslipDetailModal * { visibility: visible; }
    
    /* Full page modal */
    #payslipDetailModal {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        background: white;
    }
    
    /* Hide UI elements */
    .modal-header,
    .modal-footer {
        display: none !important;
    }
    
    /* Larger logo for print */
    .logo-image {
        width: 100px;
        height: 100px;
    }
    
    /* Preserve colors */
    .table-row.total,
    .net-pay-box,
    .badge-status {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    /* Page settings */
    @page {
        margin: 1cm;
        size: A4;
    }
}
```

---

## Testing Checklist

### Before Printing:
- [ ] Logo displays correctly on screen
- [ ] All information is accurate
- [ ] Deductions are itemized
- [ ] Totals are correct
- [ ] Status is shown

### Print Preview Check:
- [ ] Logo is visible and clear
- [ ] Header is centered
- [ ] All sections are visible
- [ ] Colors are preserved
- [ ] No content is cut off
- [ ] Page fits on one sheet

### After Printing:
- [ ] Logo printed in color
- [ ] Text is readable
- [ ] Numbers are clear
- [ ] Layout is professional
- [ ] No missing information

---

## Example Print Output

### For Employee: Juan Dela Cruz

```
┌────────────────────────────────────────────┐
│                                            │
│         [PAGSANJAN LOGO - COLOR]           │
│    MUNICIPAL GOVERNMENT OF PAGSANJAN       │
│           Province of Laguna               │
│               PAYSLIP                      │
│  ──────────────────────────────────────   │
│                                            │
│  Employee Name: Juan Dela Cruz             │
│  Employee ID: 2024-001                     │
│  Department: Mayor's Office                │
│  Position: Admin Aide IV                   │
│  Period: Apr 01, 2026 - Apr 16, 2026      │
│  Pay Date: May 18, 2026                    │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  EARNINGS                                  │
│  Monthly Rate:        ₱14,308.00          │
│  Daily Rate:          ₱650.36             │
│  Days Worked:         12                  │
│  Basic Pay:           ₱7,804.32           │
│  Overtime Pay:        ₱0.00               │
│  ─────────────────────────────────        │
│  Gross Pay:           ₱7,804.32           │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  DEDUCTIONS                                │
│  Late Deduction:      ₱0.00               │
│  Undertime Deduction: ₱0.00               │
│  Emergency Loan:      ₱900.00             │
│  MP LOAN:             ₱924.03             │
│  ─────────────────────────────────        │
│  Total Deductions:    ₱1,824.03           │
│                                            │
│  ──────────────────────────────────────   │
│                                            │
│  NET PAY              ₱5,980.29           │
│  [Dark gradient background]               │
│                                            │
│  Status: [Approved - Green Badge]         │
│                                            │
└────────────────────────────────────────────┘
```

---

## Files Modified

1. **resources/views/admin/payroll/modals/payslip-detail-modal.blade.php**
   - Added logo image
   - Enhanced header structure
   - Improved print styles
   - Added color preservation
   - Added page break control

---

## Summary

✅ **Official logo added to payslip**
✅ **Professional print format**
✅ **Color preservation for printing**
✅ **A4 page size with proper margins**
✅ **Clean layout without UI elements**
✅ **One-page format**
✅ **Print button included**

**Status:** Ready for production printing!

---

**Last Updated:** January 2024
**Print Quality:** Professional
**Paper Size:** A4
**Color:** Full color supported
