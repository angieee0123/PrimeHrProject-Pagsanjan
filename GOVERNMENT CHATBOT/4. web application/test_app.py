import json
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
from transformers import AutoTokenizer, AutoModelForSeq2SeqLM
import os

print("Testing chatbot components...")

# Test 1: Load documents
print("\n1. Loading documents.json...")
try:
    with open('../3. training script/models/documents.json', 'r', encoding='utf-8') as f:
        knowledge_base = json.load(f)
    print(f"   ✓ Loaded {len(knowledge_base)} services")
    
    # Check structure
    sample = knowledge_base[0]
    print(f"   ✓ Sample service: {sample['service_name']}")
    if sample.get('process_flow'):
        print(f"   ✓ Process flow has {len(sample['process_flow'])} steps")
        if sample['process_flow'][0].get('agency_actions'):
            print(f"   ✓ Nested agency_actions structure detected")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 2: Load FAISS index
print("\n2. Loading FAISS index...")
try:
    faiss_index = faiss.read_index('../3. training script/models/faiss_index.bin')
    print(f"   ✓ FAISS index loaded with {faiss_index.ntotal} vectors")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 3: Load embedder
print("\n3. Loading sentence transformer...")
try:
    embedder = SentenceTransformer('all-MiniLM-L6-v2')
    print(f"   ✓ Embedder loaded")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 4: Load FLAN-T5
print("\n4. Loading FLAN-T5...")
try:
    tokenizer = AutoTokenizer.from_pretrained("google/flan-t5-small")
    llm = AutoModelForSeq2SeqLM.from_pretrained("google/flan-t5-small")
    print(f"   ✓ FLAN-T5 loaded")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 5: Search functionality
print("\n5. Testing search...")
try:
    query = "how to get business clearance"
    query_embedding = embedder.encode([query])
    distances, indices = faiss_index.search(query_embedding.astype('float32'), 3)
    
    print(f"   ✓ Search completed")
    print(f"   Top result: {knowledge_base[indices[0][0]]['service_name']}")
    print(f"   Score: {distances[0][0]:.4f}")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 6: FLAN-T5 generation
print("\n6. Testing FLAN-T5 generation...")
try:
    service = knowledge_base[indices[0][0]]
    context = f"Service: {service['service_name']}. Office: {service['office']}. Description: {service.get('description', '')}"
    prompt = f"Answer this question about a government service.\n\nInformation: {context[:200]}\n\nQuestion: {query}\n\nAnswer:"
    
    inputs = tokenizer(prompt, return_tensors="pt", max_length=512, truncation=True)
    outputs = llm.generate(**inputs, max_length=100, num_beams=2)
    response = tokenizer.decode(outputs[0], skip_special_tokens=True)
    
    print(f"   ✓ FLAN-T5 response generated")
    print(f"   Response: {response[:150]}...")
except Exception as e:
    print(f"   ✗ Error: {e}")

# Test 7: Process flow structure
print("\n7. Testing process flow structure...")
try:
    service = knowledge_base[indices[0][0]]
    if service.get('process_flow'):
        step = service['process_flow'][0]
        print(f"   ✓ Step number: {step.get('step_number', step.get('step'))}")
        print(f"   ✓ Client action: {step.get('client_action', '')[:50]}...")
        if step.get('agency_actions'):
            action = step['agency_actions'][0]
            print(f"   ✓ Processing time: {action.get('processing_time')}")
            print(f"   ✓ Fees: {action.get('fees')}")
    else:
        print(f"   ✗ No process_flow found")
except Exception as e:
    print(f"   ✗ Error: {e}")

print("\n" + "="*60)
print("TEST COMPLETE")
print("="*60)
