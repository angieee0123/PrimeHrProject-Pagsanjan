import requests

# Test the API endpoint
url = "http://localhost:5000/attendance/report/data?date_from=2026-01-01&date_to=2026-12-31"

try:
    response = requests.get(url)
    print(f"Status Code: {response.status_code}")
    print(f"Response: {response.text[:500]}")
    
    if response.status_code == 200:
        data = response.json()
        print(f"\nSuccess: {data.get('success')}")
        print(f"Records: {len(data.get('records', []))}")
        print(f"Stats: {data.get('stats')}")
        
        if data.get('records'):
            print("\nFirst Record:")
            print(data['records'][0])
    
except Exception as e:
    print(f"Error: {e}")
