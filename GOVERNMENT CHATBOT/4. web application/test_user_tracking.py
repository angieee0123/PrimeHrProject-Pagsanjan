#!/usr/bin/env python3
"""
Test script to verify user_id is being saved to chat_history
"""

import requests
import json
import time

BASE_URL = "http://localhost:5001"

def test_chat_with_user_id():
    """Test sending chat message with user_id"""
    print("=" * 60)
    print("TEST 1: Send message WITH user_id in JSON")
    print("=" * 60)
    
    payload = {
        "message": "Test: How do I register a new employee?",
        "user_id": 1
    }
    
    response = requests.post(f"{BASE_URL}/chat", json=payload)
    print(f"Request payload: {json.dumps(payload, indent=2)}")
    print(f"Response status: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print()

def test_chat_with_header():
    """Test sending chat message with X-User-ID header"""
    print("=" * 60)
    print("TEST 2: Send message WITH X-User-ID header")
    print("=" * 60)
    
    headers = {"X-User-ID": "2"}
    payload = {
        "message": "Test: Show me attendance records"
    }
    
    response = requests.post(f"{BASE_URL}/chat", json=payload, headers=headers)
    print(f"Headers: {headers}")
    print(f"Request payload: {json.dumps(payload, indent=2)}")
    print(f"Response status: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print()

def test_set_user():
    """Test setting user in session"""
    print("=" * 60)
    print("TEST 3: Pre-set user via /set-user endpoint")
    print("=" * 60)
    
    payload = {"user_id": 3}
    response = requests.post(f"{BASE_URL}/set-user", json=payload)
    print(f"Set-user payload: {json.dumps(payload, indent=2)}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    
    # Now send a message (should use user_id from session)
    payload2 = {"message": "Test: What is my attendance?"}
    response2 = requests.post(f"{BASE_URL}/chat", json=payload2)
    print(f"\nChat message (should use user_id=3 from session):")
    print(f"Response: {json.dumps(response2.json(), indent=2)}")
    print()

def check_saved_chats():
    """Check what was actually saved"""
    print("=" * 60)
    print("CHECK: View latest saved chats")
    print("=" * 60)
    
    response = requests.get(f"{BASE_URL}/debug/chat-history?limit=5")
    data = response.json()
    
    if data.get('count') > 0:
        print(f"\n✅ Found {data['count']} recent chats:")
        for chat in data['data']:
            print(f"\n  ID: {chat['id']}")
            print(f"  User ID: {chat['user_id']}")
            print(f"  Session ID: {chat['session_id']}")
            print(f"  Question Type: {chat['question_type']}")
            print(f"  Question: {chat['question'][:50]}...")
            print(f"  Created: {chat['created_at']}")
    else:
        print("❌ No chats found")

def check_user_chats(user_id):
    """Check chats for specific user"""
    print(f"\n{'=' * 60}")
    print(f"CHECK: View chats for user {user_id}")
    print(f"{'=' * 60}")
    
    response = requests.get(f"{BASE_URL}/admin/user-conversations/{user_id}")
    data = response.json()
    
    if data.get('total') > 0:
        print(f"\n✅ Found {data['total']} chats for user {user_id}:")
        for chat in data['conversations']:
            print(f"\n  ID: {chat['id']}")
            print(f"  Question: {chat['question'][:50]}...")
            print(f"  Type: {chat['question_type']}")
            print(f"  Created: {chat['created_at']}")
    else:
        print(f"ℹ️  No chats found for user {user_id}")

if __name__ == '__main__':
    print("\n🧪 Testing Chat History with User ID Tracking\n")
    
    try:
        # Run tests
        test_chat_with_user_id()
        time.sleep(1)
        
        test_chat_with_header()
        time.sleep(1)
        
        test_set_user()
        time.sleep(2)
        
        # Check what was saved
        check_saved_chats()
        
        # Check specific users
        for user_id in [1, 2, 3]:
            check_user_chats(user_id)
        
        print("\n" + "=" * 60)
        print("✅ Tests completed!")
        print("=" * 60)
        
    except Exception as e:
        print(f"\n❌ Error: {e}")
        print("\nMake sure the chatbot is running on http://localhost:5001")
