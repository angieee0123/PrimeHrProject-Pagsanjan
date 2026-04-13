# How to Use the Government Chatbot with Groq Llama 3.3 70B

## Quick Start

1. **Run the improved version:**
```bash
cd "c:\Users\eyouth\Desktop\GOVERNMENT CHATBOT\4. web application"
python app_improved.py
```

2. **Open your browser:**
```
http://localhost:5000
```

## What Changed

### Before (FLAN-T5-Small):
- Local model (80M parameters)
- Slower responses (2-5 seconds)
- Basic quality
- Uses your computer's resources

### After (Groq Llama 3.3 70B):
- Cloud API (70B parameters)
- Fast responses (0.5-2 seconds)
- High quality, natural language
- No local GPU needed
- Free tier: 30 requests/minute

## How It Works

1. **User asks a question** → "How do I get a birth certificate?"

2. **Fast local search** (instant):
   - Embeddings find relevant service
   - FAISS searches knowledge base
   - Returns: Birth Certificate service

3. **Groq generates response** (0.5-1 sec):
   - Sends service info to Llama 3.3 70B
   - Gets natural, conversational response
   - Adds structured details

4. **User sees complete answer**:
   - Natural intro from Llama 3.3
   - Requirements list
   - Step-by-step process
   - Fees and processing time

## Example Queries

### Simple Questions:
- "birth certificate"
- "business permit requirements"
- "how much is cedula"

### Detailed Questions:
- "Show full details for Processing of Vouchers"
- "What are the requirements for marriage certificate?"
- "How long does business permit take?"

### General Questions:
- "What are all the offices?"
- "Show me all services"
- "What services does health office offer?"

## API Usage Limits

**Groq Free Tier:**
- 30 requests per minute
- 14,400 requests per day
- Enough for ~500-1000 users/day

**If you hit limits:**
- Responses fall back to basic format
- No conversational intro
- Still shows all service details

## Benefits

✅ **Faster**: Cloud API vs local inference
✅ **Better Quality**: 70B model vs 80M
✅ **No GPU Needed**: Runs on any computer
✅ **Scalable**: Handles multiple users
✅ **Cost Effective**: Free tier is generous

## Troubleshooting

### "Groq Error" in console:
- Check internet connection
- Verify API key is valid
- Check rate limits (30/min)

### Slow responses:
- First query loads models (5-10 sec)
- Subsequent queries are fast
- Check internet speed

### Wrong answers:
- Try rephrasing question
- Be more specific
- Use follow-up questions

## Monitoring

Watch the console for:
```
Loading models and data...
Municipality: Sampaloc, Quezon
Total Services: 123
✓ All models loaded
```

Then:
```
* Running on http://127.0.0.1:5000
```

## Next Steps

1. Test with various questions
2. Monitor response quality
3. Check API usage at: https://console.groq.com
4. Adjust max_tokens if responses too short/long
5. Consider upgrading Groq plan for production

## Security Note

⚠️ **Important**: The API key is hardcoded. For production:
1. Move to environment variable
2. Use `.env` file
3. Never commit API keys to git

```python
# Better approach:
import os
from dotenv import load_dotenv

load_dotenv()
groq_client = Groq(api_key=os.getenv("GROQ_API_KEY"))
```
