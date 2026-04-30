import json
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer

print("Rebuilding FAISS index...")

# Load documents
with open('models/documents.json', 'r', encoding='utf-8') as f:
    documents = json.load(f)

print(f"Loaded {len(documents)} documents")

# Load embedder
print("Loading sentence transformer...")
embedder = SentenceTransformer('all-MiniLM-L6-v2')

# Generate embeddings
print("Generating embeddings...")
doc_texts = [doc['text'] for doc in documents]
embeddings = embedder.encode(doc_texts, show_progress_bar=True)

# Build FAISS index
print("Building FAISS index...")
dimension = embeddings.shape[1]
index = faiss.IndexFlatL2(dimension)
index.add(embeddings.astype('float32'))

# Save index
faiss.write_index(index, 'models/faiss_index.bin')

print(f"FAISS index rebuilt with {index.ntotal} vectors")
print("Dimension:", dimension)

# Test search
print("\nTesting search with 'carabao'...")
query_embedding = embedder.encode(["carabao"])
distances, indices = index.search(query_embedding.astype('float32'), 3)

print("\nTop 3 results:")
for i, (idx, dist) in enumerate(zip(indices[0], distances[0]), 1):
    print(f"{i}. {documents[idx]['service_name']}")
    print(f"   Office: {documents[idx]['office']}")
    print(f"   Score: {dist:.4f}\n")
