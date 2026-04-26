"""
Test OpenCV QR Code Detection
This verifies that OpenCV can detect QR codes without pyzbar
"""

import cv2
import numpy as np
import qrcode
from io import BytesIO

print("=" * 60)
print("TESTING OPENCV QR CODE DETECTION")
print("=" * 60)

# Test 1: Generate a test QR code
print("\n[TEST 1] Generating test QR code...")
try:
    qr = qrcode.QRCode(version=1, box_size=10, border=4)
    qr.add_data("123")  # Test employee ID
    qr.make(fit=True)
    
    img = qr.make_image(fill_color="black", back_color="white")
    
    # Convert PIL image to OpenCV format
    img_array = np.array(img.convert('RGB'))
    img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
    
    print("  ✓ QR code generated successfully")
    print(f"  ✓ Image size: {img_cv.shape}")
except Exception as e:
    print(f"  ✗ Failed to generate QR code: {e}")
    exit(1)

# Test 2: Detect QR code using OpenCV
print("\n[TEST 2] Detecting QR code with OpenCV...")
try:
    qr_detector = cv2.QRCodeDetector()
    data, bbox, straight_qrcode = qr_detector.detectAndDecode(img_cv)
    
    if data:
        print(f"  ✓ QR code detected successfully!")
        print(f"  ✓ Decoded data: '{data}'")
        print(f"  ✓ Bounding box: {bbox is not None}")
    else:
        print("  ✗ QR code not detected")
        print("  ℹ This might be due to OpenCV version or image quality")
except Exception as e:
    print(f"  ✗ Error during detection: {e}")
    exit(1)

# Test 3: Check OpenCV version
print("\n[TEST 3] Checking OpenCV version...")
try:
    print(f"  ✓ OpenCV version: {cv2.__version__}")
    
    # QRCodeDetector was added in OpenCV 4.0
    major_version = int(cv2.__version__.split('.')[0])
    if major_version >= 4:
        print("  ✓ OpenCV version supports QRCodeDetector")
    else:
        print("  ⚠ OpenCV version might be too old (need 4.0+)")
        print("  → Upgrade with: pip install --upgrade opencv-python")
except Exception as e:
    print(f"  ✗ Error checking version: {e}")

# Summary
print("\n" + "=" * 60)
print("TEST SUMMARY")
print("=" * 60)

if data:
    print("\n✅ SUCCESS! OpenCV can detect QR codes.")
    print("\nYour QR attendance system should work!")
    print("\nNext steps:")
    print("1. Run: python app.py")
    print("2. Open: http://localhost:5000/attendance")
    print("3. Generate QR code from admin panel")
    print("4. Scan it with the webcam scanner")
else:
    print("\n⚠ WARNING: QR detection might have issues.")
    print("\nTroubleshooting:")
    print("1. Ensure OpenCV version is 4.0 or higher")
    print("2. Try: pip install --upgrade opencv-python")
    print("3. The frontend jsQR library will still work for webcam scanning")

print("\n" + "=" * 60)
