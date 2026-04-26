"""
QR Attendance System Test Script
Run this to verify the system is working correctly
"""

import sys
import os

print("=" * 60)
print("QR ATTENDANCE SYSTEM - VERIFICATION TEST")
print("=" * 60)

# Test 1: Check Python packages
print("\n[TEST 1] Checking required Python packages...")
required_packages = {
    'flask': 'Flask',
    'cv2': 'opencv-python',
    'pyzbar': 'pyzbar',
    'mysql.connector': 'mysql-connector-python',
    'qrcode': 'qrcode',
    'PIL': 'Pillow'
}

missing_packages = []
for module, package in required_packages.items():
    try:
        __import__(module)
        print(f"  ✓ {package}")
    except ImportError:
        print(f"  ✗ {package} - MISSING")
        missing_packages.append(package)

if missing_packages:
    print(f"\n⚠️  Missing packages: {', '.join(missing_packages)}")
    print(f"   Install with: pip install {' '.join(missing_packages)}")
else:
    print("\n✅ All packages installed!")

# Test 2: Check database connection
print("\n[TEST 2] Checking database connection...")
try:
    import mysql.connector
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='primehrismagdalena'
    )
    cursor = conn.cursor()
    
    # Check if attendance table exists
    cursor.execute("SHOW TABLES LIKE 'attendance'")
    if cursor.fetchone():
        print("  ✓ Database connected")
        print("  ✓ Attendance table exists")
        
        # Check table structure
        cursor.execute("DESCRIBE attendance")
        columns = [row[0] for row in cursor.fetchall()]
        required_columns = ['id', 'employee_id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out']
        
        missing_cols = [col for col in required_columns if col not in columns]
        if missing_cols:
            print(f"  ✗ Missing columns: {', '.join(missing_cols)}")
        else:
            print("  ✓ All required columns present")
        
        # Check if there are employees
        cursor.execute("SELECT COUNT(*) FROM employees")
        emp_count = cursor.fetchone()[0]
        print(f"  ✓ Found {emp_count} employees in database")
        
    else:
        print("  ✗ Attendance table not found")
        print("     Run: mysql -u root -p primehrismagdalena < database/primehrismagdalena_attendance.sql")
    
    cursor.close()
    conn.close()
    print("\n✅ Database check complete!")
    
except mysql.connector.Error as e:
    print(f"  ✗ Database connection failed: {e}")
    print("     Check your database credentials in qr_attendance.py")
except Exception as e:
    print(f"  ✗ Error: {e}")

# Test 3: Check Flask app
print("\n[TEST 3] Checking Flask application...")
try:
    from app import app
    print("  ✓ Flask app imported successfully")
    
    # Check if qr_attendance blueprint is registered
    if 'qr_attendance' in [bp.name for bp in app.blueprints.values()]:
        print("  ✓ QR Attendance blueprint registered")
    else:
        print("  ✗ QR Attendance blueprint not registered")
        print("     Check if 'from qr_attendance import qr_attendance' is in app.py")
    
    print("\n✅ Flask app check complete!")
    
except ImportError as e:
    print(f"  ✗ Failed to import Flask app: {e}")
except Exception as e:
    print(f"  ✗ Error: {e}")

# Test 4: Check template files
print("\n[TEST 4] Checking template files...")
template_path = os.path.join(os.path.dirname(__file__), 'templates', 'attendance.html')
if os.path.exists(template_path):
    print("  ✓ attendance.html exists")
    
    with open(template_path, 'r', encoding='utf-8') as f:
        content = f.read()
        if 'jsQR' in content:
            print("  ✓ jsQR library included")
        if 'getUserMedia' in content:
            print("  ✓ Webcam access code present")
        if '/scan_qr' in content:
            print("  ✓ API endpoint configured")
    
    print("\n✅ Template check complete!")
else:
    print(f"  ✗ attendance.html not found at {template_path}")

# Summary
print("\n" + "=" * 60)
print("TEST SUMMARY")
print("=" * 60)
print("\nTo start the QR Attendance Scanner:")
print("1. Run: python app.py")
print("2. Open browser: http://localhost:5000/attendance")
print("3. Click 'Start Camera'")
print("4. Hold QR code in front of camera")
print("5. System will auto-scan and record attendance")
print("\nTo generate QR codes:")
print("- Admin Panel → Personnel → Click 'QR Code' button")
print("- Or run: python generate_employee_qr.py")
print("\n" + "=" * 60)
