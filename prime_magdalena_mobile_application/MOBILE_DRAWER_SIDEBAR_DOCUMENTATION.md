# 📱 Mobile App Drawer/Sidebar Documentation

## 📋 Overview
This document provides a complete reference for the mobile application's navigation drawer (sidebar) implemented in Flutter. The drawer provides access to secondary features while the bottom navigation bar handles primary navigation.

---

## 📍 Location
```
prime_magdalena_mobile_application/lib/screens/main_app_shell.dart
```

**Method:** `_buildDrawer(BuildContext context)` (Line 314)

---

## 🎨 Design Style

### iOS-Inspired Modern Design
- **Background Color:** `#F7F8FA` (Light gray)
- **Width:** 85% of screen width
- **Style:** Clean, modern iOS-style with rounded cards
- **Sections:** Grouped menu items with section headers
- **Profile Header:** Large circular avatar with gradient background

---

## 🏗️ Structure

### 1. Profile Header Section
```dart
Container(
  padding: const EdgeInsets.all(24),
  child: Column(
    children: [
      // Avatar (80x80 circle with gradient)
      // Full Name (18px, bold)
      // Position/Designation (13px, medium)
      // Active Status Badge (green)
    ],
  ),
)
```

**Features:**
- **Avatar:** 80x80 circular gradient (Blue: `#0B044D` → `#1E3A8A`)
- **Initials:** Extracted from first and last name
- **Name:** Bold, 18px, dark color
- **Position:** Medium weight, 13px, gray
- **Status Badge:** Green "Active" indicator with dot

### 2. Menu Sections

#### Section 1: Account
| Icon | Label | Color | Screen |
|------|-------|-------|--------|
| 👤 `person_rounded` | Profile | Blue `#3B82F6` | `ProfileScreen` |

#### Section 2: Learning & Growth
| Icon | Label | Color | Screen |
|------|-------|-------|--------|
| 🎓 `school_rounded` | Training | Purple `#8B5CF6` | `TrainingScreen` |
| 📈 `trending_up_rounded` | Performance | Green `#10B981` | `PerformanceScreen` |

#### Section 3: App
| Icon | Label | Color | Screen |
|------|-------|-------|--------|
| 🔔 `notifications_rounded` | Notifications | Amber `#F59E0B` | `NotificationsScreen` |
| 💬 `chat_bubble_rounded` | HR Chatbot | Cyan `#06B6D4` | `HrChatbotScreen` |
| ⚙️ `settings_rounded` | Settings | Slate `#64748B` | `SettingsScreen` |

### 3. Footer Section
```dart
Container(
  padding: const EdgeInsets.all(20),
  child: Column(
    children: [
      // Logout Button (Red gradient)
      // App Version Text
    ],
  ),
)
```

**Features:**
- **Logout Button:** Red gradient (`#DC2626` → `#EF4444`)
- **Confirmation Dialog:** Shows before logout
- **Version Text:** "PRIME HRIS v1.0.0" in gray

---

## 🎯 Navigation Flow

### Bottom Navigation Bar (Primary)
These screens are in the bottom nav bar:

| Index | Icon | Label | Screen |
|-------|------|-------|--------|
| 0 | 🏠 `home_rounded` | Home | `HomeDashboardScreen` |
| 1 | 🧾 `receipt_long_rounded` | Payslip | `PayslipListScreen` |
| 2 | 📅 `calendar_month_rounded` | Attendance | `AttendanceScreen` |
| 3 | ✅ `event_available_rounded` | Leave | `LeaveManagementScreen` |
| 4 | ✈️ `flight_takeoff_rounded` | Travel | `TravelOrderScreen` |

### Drawer (Secondary)
These screens are in the drawer:

| Section | Screen | Purpose |
|---------|--------|---------|
| Account | Profile | View/edit personal information |
| Learning & Growth | Training | View training records |
| Learning & Growth | Performance | View performance reviews |
| App | Notifications | View all notifications |
| App | HR Chatbot | Chat with HR assistant |
| App | Settings | App settings and preferences |

---

## 💻 Code Implementation

### Opening the Drawer
```dart
// Hamburger menu button (top-left)
GestureDetector(
  onTap: () {
    HapticFeedback.lightImpact();
    _scaffoldKey.currentState?.openDrawer();
  },
  child: Container(
    // Styled button with icon
  ),
)
```

### Drawer Widget
```dart
Drawer(
  width: MediaQuery.of(context).size.width * 0.85,
  backgroundColor: const Color(0xFFF7F8FA),
  child: SafeArea(
    child: Column(
      children: [
        // Profile Header
        // Menu Items (Scrollable)
        // Footer (Logout + Version)
      ],
    ),
  ),
)
```

### Section Builder
```dart
Widget _buildIOSDrawerSection(String title, List<Widget> items) {
  return Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      // Section Title (uppercase, gray, small)
      Padding(
        padding: const EdgeInsets.only(left: 16, bottom: 8),
        child: Text(
          title.toUpperCase(),
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: Colors.grey.shade500,
            letterSpacing: 0.5,
          ),
        ),
      ),
      // White card container with items
      Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [/* subtle shadow */],
        ),
        child: Column(children: items),
      ),
    ],
  );
}
```

### Menu Item Builder
```dart
Widget _buildIOSDrawerItem({
  required IconData icon,
  required String label,
  required Color color,
  required VoidCallback onTap,
}) {
  return Material(
    color: Colors.transparent,
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            // Colored icon container (36x36)
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 14),
            // Label text
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: const Color(0xFF0F172A),
                ),
              ),
            ),
            // Chevron right arrow
            Icon(
              Icons.chevron_right_rounded,
              color: Colors.grey.shade400,
              size: 20,
            ),
          ],
        ),
      ),
    ),
  );
}
```

### Navigation Handler
```dart
void _openDrawerScreen(BuildContext context, Widget screen) {
  Navigator.pop(context); // Close drawer
  Navigator.of(context).push(
    MaterialPageRoute(builder: (_) => screen)
  );
}
```

### Initials Generator
```dart
String _drawerInitials(String? first, String? last, String fullName) {
  // Try to use first and last name
  if (first != null && first.isNotEmpty && last != null && last.isNotEmpty) {
    return '${first[0]}${last[0]}'.toUpperCase();
  }
  
  // Fallback: split full name
  final parts = fullName.trim().split(RegExp(r'\s+'));
  if (parts.length >= 2) {
    return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
  }
  
  // Last resort: first character
  return fullName.isNotEmpty ? fullName[0].toUpperCase() : 'E';
}
```

---

## 🎨 Visual Design Details

### Profile Avatar
```dart
Container(
  width: 80,
  height: 80,
  decoration: BoxDecoration(
    shape: BoxShape.circle,
    gradient: const LinearGradient(
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
      colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
    ),
    boxShadow: [
      BoxShadow(
        color: const Color(0xFF0B044D).withValues(alpha: 0.2),
        blurRadius: 16,
        offset: const Offset(0, 4),
      ),
    ],
  ),
  child: Center(
    child: Text(
      initials, // e.g., "JD"
      style: GoogleFonts.inter(
        fontSize: 28,
        fontWeight: FontWeight.w700,
        color: Colors.white,
      ),
    ),
  ),
)
```

### Active Status Badge
```dart
Container(
  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
  decoration: BoxDecoration(
    color: const Color(0xFF22C55E).withValues(alpha: 0.1),
    borderRadius: BorderRadius.circular(12),
  ),
  child: Row(
    mainAxisSize: MainAxisSize.min,
    children: [
      Container(
        width: 6,
        height: 6,
        decoration: const BoxDecoration(
          shape: BoxShape.circle,
          color: Color(0xFF22C55E),
        ),
      ),
      const SizedBox(width: 6),
      Text(
        'Active',
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: const Color(0xFF22C55E),
        ),
      ),
    ],
  ),
)
```

### Logout Button
```dart
Container(
  width: double.infinity,
  height: 50,
  decoration: BoxDecoration(
    gradient: const LinearGradient(
      colors: [Color(0xFFDC2626), Color(0xFFEF4444)],
    ),
    borderRadius: BorderRadius.circular(14),
    boxShadow: [
      BoxShadow(
        color: const Color(0xFFDC2626).withValues(alpha: 0.3),
        blurRadius: 12,
        offset: const Offset(0, 4),
      ),
    ],
  ),
  child: Material(
    color: Colors.transparent,
    child: InkWell(
      onTap: () async {
        // Show confirmation dialog
        final shouldLogout = await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: Text('Logout'),
            content: Text('Are you sure you want to logout?'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: Text('Cancel'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                child: Text('Logout'),
              ),
            ],
          ),
        );
        
        if (shouldLogout == true) {
          widget.onLogout?.call();
        }
      },
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.logout_rounded, color: Colors.white, size: 20),
          const SizedBox(width: 8),
          Text('Logout', style: GoogleFonts.inter(/* ... */)),
        ],
      ),
    ),
  ),
)
```

---

## 🔧 Additional Features

### 1. Hamburger Menu Button (Top-Left)
```dart
Positioned(
  left: 16,
  top: MediaQuery.of(context).padding.top + 8,
  child: GestureDetector(
    onTap: () {
      HapticFeedback.lightImpact();
      _scaffoldKey.currentState?.openDrawer();
    },
    child: Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [/* shadow */],
      ),
      child: const Icon(
        Icons.menu_rounded,
        size: 24,
        color: Color(0xFF0B044D),
      ),
    ),
  ),
)
```

### 2. Floating Chatbot Button (Bottom-Right)
```dart
Positioned(
  right: 20,
  bottom: 100, // Above bottom nav bar
  child: GestureDetector(
    onTap: () {
      HapticFeedback.mediumImpact();
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const HrChatbotScreen()),
      );
    },
    child: Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [/* shadows */],
      ),
      child: Stack(
        children: [
          Center(
            child: Icon(
              Icons.chat_bubble_rounded,
              color: Colors.white,
              size: 26,
            ),
          ),
          // Green notification badge (top-right)
          Positioned(
            right: 8,
            top: 8,
            child: Container(
              width: 10,
              height: 10,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFF22C55E),
                border: Border.all(
                  color: const Color(0xFF0B044D),
                  width: 2,
                ),
              ),
            ),
          ),
        ],
      ),
    ),
  ),
)
```

**Note:** Chatbot FAB is hidden on Leave screen (index 3) to avoid conflict with "File Leave" FAB.

### 3. Haptic Feedback
All interactive elements provide haptic feedback:
- **Light Impact:** Menu button, bottom nav items
- **Medium Impact:** Chatbot FAB

---

## 📊 Data Flow

### User Information
```dart
final auth = AuthService();
final stored = auth.currentEmployee;
final user = auth.currentUser;

final fullName = stored?.fullName ?? user?.name ?? 'Employee';
final position = stored?.designation ?? 'Position';
final initials = _drawerInitials(stored?.firstName, stored?.lastName, fullName);
```

**Data Sources:**
1. **Primary:** `auth.currentEmployee` (from SharedPreferences)
2. **Fallback:** `auth.currentUser` (from login response)
3. **Default:** Generic strings

---

## 🎯 User Experience Features

### 1. Smooth Animations
- Menu items have ripple effect on tap
- Drawer slides in from left
- Bottom nav items animate on selection

### 2. Visual Feedback
- Haptic feedback on all interactions
- Active state highlighting
- Hover/press states on buttons

### 3. Confirmation Dialogs
- Logout requires confirmation
- Prevents accidental logouts

### 4. Accessibility
- Proper touch targets (44x44 minimum)
- Clear visual hierarchy
- Readable text sizes
- Color contrast compliance

---

## 🧪 Testing Checklist

### Drawer Functionality
- [ ] Drawer opens from hamburger menu button
- [ ] Drawer closes when tapping outside
- [ ] Profile information displays correctly
- [ ] All menu items are tappable
- [ ] Navigation works for all items
- [ ] Logout button shows confirmation dialog
- [ ] Logout actually logs out user

### Visual Design
- [ ] Avatar displays initials correctly
- [ ] Active status badge shows
- [ ] Section headers are visible
- [ ] Icons have correct colors
- [ ] Shadows render properly
- [ ] Text is readable

### Responsive Design
- [ ] Drawer width is 85% of screen
- [ ] Works on small screens (iPhone SE)
- [ ] Works on large screens (iPad)
- [ ] Safe area respected (notch/status bar)

### Navigation
- [ ] Bottom nav bar works
- [ ] Drawer navigation works
- [ ] Chatbot FAB works
- [ ] Back button closes screens properly

---

## 🔄 Comparison: Web vs Mobile

| Feature | Web Sidebar | Mobile Drawer |
|---------|-------------|---------------|
| **Style** | Vertical sidebar (always visible on desktop) | Drawer (slides from left) |
| **Width** | Fixed width | 85% of screen width |
| **Trigger** | Always visible / Toggle button | Hamburger menu button |
| **Profile** | Footer with small avatar | Header with large avatar |
| **Sections** | Single list | Grouped sections with headers |
| **Theme** | Role-based (Admin/Permanent/Job Order) | Single modern iOS-style |
| **Logout** | Button in footer | Button in footer with confirmation |
| **Primary Nav** | Sidebar | Bottom navigation bar |
| **Secondary Nav** | N/A | Drawer |

---

## 📝 Customization Guide

### Adding a New Menu Item

1. **Add to appropriate section:**
```dart
_buildIOSDrawerSection('Your Section', [
  _buildIOSDrawerItem(
    icon: Icons.your_icon,
    label: 'Your Label',
    color: const Color(0xFFYOURCOLOR),
    onTap: () => _openDrawerScreen(context, const YourScreen()),
  ),
]),
```

2. **Import the screen:**
```dart
import 'package:prime_magdalena_mobile_application/screens/your/your_screen.dart';
```

### Changing Colors

**Avatar Gradient:**
```dart
gradient: const LinearGradient(
  colors: [Color(0xFFYOURCOLOR1), Color(0xFFYOURCOLOR2)],
)
```

**Logout Button:**
```dart
gradient: const LinearGradient(
  colors: [Color(0xFFYOURCOLOR1), Color(0xFFYOURCOLOR2)],
)
```

### Changing Drawer Width
```dart
width: MediaQuery.of(context).size.width * 0.85, // Change 0.85 to your value
```

---

## ✅ Summary

### Drawer Structure
- **Header:** Profile with avatar, name, position, status
- **Body:** 3 sections with 6 menu items
- **Footer:** Logout button + version text

### Navigation
- **Primary:** Bottom nav bar (5 items)
- **Secondary:** Drawer (6 items)
- **Quick Access:** Floating chatbot button

### Design
- **Style:** Modern iOS-inspired
- **Colors:** Blue gradient theme
- **Layout:** Clean, grouped sections
- **Interactions:** Smooth animations + haptic feedback

### Key Features
- ✅ User profile display
- ✅ Grouped menu sections
- ✅ Logout confirmation
- ✅ Haptic feedback
- ✅ Responsive design
- ✅ Safe area handling

---

**Last Updated:** May 29, 2026  
**File:** `lib/screens/main_app_shell.dart`  
**Maintained By:** Development Team
