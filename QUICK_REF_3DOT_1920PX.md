# Quick Reference: 3-Dot Menu (1920px Breakpoint)

## 🎯 At a Glance

**Breakpoint**: 1920px  
**Coverage**: ~95% of users  
**Menu Type**: Kebab menu (3 vertical dots)

---

## 📊 Who Sees What?

```
≤ 1920px (95% of users)  →  [⋮] 3-dot menu
> 1920px (5% of users)   →  [View] [Edit] [QR] Individual buttons
```

---

## 🖥️ Common Resolutions

| Device | Resolution | Width | Menu |
|--------|-----------|-------|------|
| iPhone | 390x844 | 390px | [⋮] |
| iPad | 768x1024 | 768px | [⋮] |
| MacBook Air | 1440x900 | 1440px | [⋮] |
| 1080p Monitor | 1920x1080 | 1920px | [⋮] |
| 1440p Monitor | 2560x1440 | 2560px | [Buttons] |

---

## 💻 Code

### CSS
```css
@media (max-width: 1920px) {
    .action-buttons-desktop { display: none; }
    .action-menu-wrapper { display: block; }
}
```

### HTML
```html
<div class="row-actions">
    <!-- Desktop buttons (> 1920px) -->
    <div class="action-buttons-desktop">...</div>
    
    <!-- 3-dot menu (≤ 1920px) -->
    <div class="action-menu-wrapper">...</div>
</div>
```

---

## ✅ Benefits

- Cleaner interface for 95% of users
- More space for table data
- Consistent UX across devices
- Modern, professional look

---

## 🔧 Customization

### Change Breakpoint
```css
@media (max-width: 2560px) { /* 1440p and below */ }
```

### Always 3-Dot
```css
.action-menu-wrapper { display: block !important; }
```

---

## 📁 Files Modified

- `employeeWizard.css` - Line ~520
- Breakpoint changed: 1024px → 1920px

---

## 🧪 Test On

- [x] 1920x1080 (most common)
- [x] 1440x900 (MacBook)
- [x] 768px (tablet)
- [x] 375px (mobile)

---

**Status**: ✅ Ready  
**Version**: 2.2.0  
**Date**: June 2025
