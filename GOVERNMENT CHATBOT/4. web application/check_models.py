import os
import json

print("Testing model files...\n")

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, '..', '3. training script', 'models')

# Check if models exist
files_to_check = [
    'faiss_index.bin',
    'documents.json',
    'time_waster_tfidf.pkl',
    'time_waster_classifier.pkl'
]

print("Checking model files:")
all_exist = True
for file in files_to_check:
    path = os.path.join(MODELS_DIR, file)
    exists = os.path.exists(path)
    status = "OK" if exists else "MISSING"
    print(f"  {status} {file}")
    if not exists:
        all_exist = False

if all_exist:
    # Load and check documents
    with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
        docs = json.load(f)
    
    print(f"\nOK All model files exist!")
    print(f"OK Knowledge base has {len(docs)} services")
    print(f"\nSample service:")
    print(f"  - {docs[0]['service_name']}")
    print(f"  - Office: {docs[0]['office']}")
    print(f"  - Has {len(docs[0].get('requirements', []))} requirements")
    
    print("\n" + "="*60)
    print("MODEL FILES ARE READY!")
    print("="*60)
    print("\nYou can now run: python app.py")
else:
    print("\nX Some model files are missing!")
    print("Please run the training script first.")
