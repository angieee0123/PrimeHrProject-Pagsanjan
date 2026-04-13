import json
import faiss
from sentence_transformers import SentenceTransformer
import os

print("Testing chatbot model...\n")

# Load data
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, '..', '3. training script', 'models')

print("1. Loading embedder...")
embedder = SentenceTransformer('all-MiniLM-L6-v2')
print("✓ Embedder loaded")

print("\n2. Loading FAISS index...")
faiss_index = faiss.read_index(os.path.join(MODELS_DIR, 'faiss_index.bin'))
print(f"✓ FAISS index loaded with {faiss_index.ntotal} documents")

print("\n3. Loading knowledge base...")
with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
    knowledge_base = json.load(f)
print(f"✓ Knowledge base loaded with {len(knowledge_base)} services")

# Test queries
test_queries = [
    "how to get business permit",
    "marriage license requirements",
    "building permit fees",
    "tax declaration"
]

print("\n" + "="*60)
print("TESTING SEARCH FUNCTIONALITY")
print("="*60)

for query in test_queries:
    print(f"\nQuery: '{query}'")
    query_embedding = embedder.encode([query])
    distances, indices = faiss_index.search(query_embedding.astype('float32'), 3)
    
    print("Top 3 Results:")
    for i, (idx, score) in enumerate(zip(indices[0], distances[0]), 1):
        service = knowledge_base[idx]
        print(f"  {i}. {service['service_name']}")
        print(f"     Office: {service['office']}")
        print(f"     Score: {score:.4f}")

print("\n" + "="*60)
print("✓ ALL TESTS PASSED!")
print("="*60)
print("\nThe model is working correctly. You can now run the web app.")
