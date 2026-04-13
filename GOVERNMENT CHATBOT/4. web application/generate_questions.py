import json
import os
import random

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, '..', '3. training script', 'models')

with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
    services = json.load(f)

# Group by office
offices = {}
for service in services:
    office = service['office']
    if office not in offices:
        offices[office] = []
    offices[office].append(service['service_name'])

print("="*70)
print("SAMPLE QUESTIONS YOUR CHATBOT CAN ANSWER")
print("="*70)

# General questions
print("\n### GENERAL QUESTIONS:")
questions = [
    "How do I get a business permit?",
    "What are the requirements for a marriage license?",
    "How much is the building permit fee?",
    "How long does it take to process a birth certificate?",
    "Where can I get a tax declaration?",
    "What documents do I need for a business permit?",
    "Who can apply for a scholarship grant?",
    "What is the process for getting a health certificate?",
    "How do I register my business?",
    "What are the fees for electrical wiring permit?"
]
for q in questions:
    print(f"  • {q}")

# Office-specific questions
print("\n### BY OFFICE:")
sample_offices = list(offices.keys())[:5]
for office in sample_offices:
    print(f"\n{office}:")
    for service in offices[office][:3]:
        print(f"  • What is {service}?")
        print(f"  • Requirements for {service}?")

# Service-specific questions
print("\n### SPECIFIC SERVICES:")
sample_services = random.sample(services, min(10, len(services)))
for service in sample_services:
    print(f"\n{service['service_name']}:")
    print(f"  • What are the requirements?")
    print(f"  • How much does it cost?")
    print(f"  • How long does it take?")
    print(f"  • Who can avail this service?")
    print(f"  • Which office handles this?")

print("\n" + "="*70)
print(f"Total Services Available: {len(services)}")
print("="*70)
