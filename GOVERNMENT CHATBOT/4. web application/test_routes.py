"""
Test script to verify all attendance routes are registered
"""

import requests

BASE_URL = "http://localhost:5000"

routes_to_test = [
    "/attendance",
    "/attendance/test",
    "/attendance/report",
    "/attendance/manual"
]

print("=" * 60)
print("TESTING ATTENDANCE ROUTES")
print("=" * 60)

for route in routes_to_test:
    url = BASE_URL + route
    try:
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            print(f"✅ {route} - OK (Status: {response.status_code})")
        else:
            print(f"⚠️  {route} - Status: {response.status_code}")
    except requests.exceptions.ConnectionError:
        print(f"❌ {route} - Server not running or route not found")
    except Exception as e:
        print(f"❌ {route} - Error: {e}")

print("\n" + "=" * 60)
print("If you see ❌ errors:")
print("1. Make sure server is running: python app.py")
print("2. Check if port 5000 is correct")
print("3. Restart the server")
print("=" * 60)
