# Government Chatbot with TinyLlama

## What's New?
This version uses **TinyLlama-1.1B-Chat** - a free, fast, lightweight LLM that generates natural conversational responses without API fees or usage limits.

## Key Features
✅ **100% Free** - No API costs, runs completely offline
✅ **Natural Responses** - Much better than FLAN-T5-Small
✅ **Very Fast** - Optimized for CPU, ~1-2 seconds per response
✅ **Lightweight** - Only 2.2GB model size
✅ **Unlimited** - No rate limits or quotas
✅ **Privacy** - All processing happens locally

## Installation

1. **Install dependencies:**
```bash
pip install -r requirements_phi3.txt
```

2. **First run will download TinyLlama (~2.2GB):**
```bash
python "4. web application/other_llm_model_in_app.py"
```

3. **Open browser:**
```
http://localhost:5000
```

## System Requirements
- **RAM:** 4GB minimum (8GB recommended)
- **Storage:** 5GB free space for model
- **CPU:** Any modern processor (GPU optional but faster)

## Performance Comparison

| Model | Quality | Speed | Size | Cost |
|-------|---------|-------|------|------|
| **TinyLlama** | ⭐⭐⭐ | ~1-2s | 2.2GB | FREE |
| Phi-3-Mini | ⭐⭐⭐⭐ | ~2-3s | 7.6GB | FREE (config issues) |
| Groq Llama 3.3 | ⭐⭐⭐⭐⭐ | ~0.5s | N/A | Limited free tier |
| FLAN-T5-Small | ⭐⭐ | ~1s | 300MB | FREE |

## Example Queries
- "How do I get a business permit?"
- "What are the requirements for birth certificate?"
- "Tell me about health services"
- "How much does a barangay clearance cost?"

## Troubleshooting

**Model download is slow:**
- First download takes 10-20 minutes depending on internet speed
- Model is cached, subsequent runs are instant

**Out of memory error:**
- Close other applications
- Reduce max_new_tokens in code (line 217)
- Consider using TinyLlama (1.1B) instead

**Slow responses:**
- Normal on CPU (2-3 seconds)
- Use GPU for faster inference (0.5-1 second)

## Alternative Models

If Phi-3-Mini is too large, edit line 32-39 to use:

**TinyLlama (1.1B - Faster, smaller):**
```python
tokenizer = AutoTokenizer.from_pretrained("TinyLlama/TinyLlama-1.1B-Chat-v1.0")
llm = AutoModelForCausalLM.from_pretrained("TinyLlama/TinyLlama-1.1B-Chat-v1.0")
```

**Gemma-2B (2B - Balanced):**
```python
tokenizer = AutoTokenizer.from_pretrained("google/gemma-2b-it")
llm = AutoModelForCausalLM.from_pretrained("google/gemma-2b-it")
```

## Notes
- First query takes longer (model initialization)
- Responses are generated fresh each time (no caching)
- All data stays on your computer (privacy-friendly)
