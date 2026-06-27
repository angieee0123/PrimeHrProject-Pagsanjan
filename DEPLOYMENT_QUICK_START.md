# 🚀 Quick Deployment Checklist

## Before You Start

1. ✅ Push all code to GitHub
2. ✅ Have Railway account (sign up at https://railway.app)
3. ✅ Generate Laravel APP_KEY: `php artisan key:generate --show`

---

## Deployment Order

### 1️⃣ Deploy MySQL Database
- Railway Project → + New → Database → MySQL
- **Copy credentials** (you'll need them)

### 2️⃣ Deploy Laravel Backend
- Root Directory: `primeHrMagdalenaLaravel`
- Environment Variables:
  ```
  APP_KEY=base64:YOUR_KEY
  APP_ENV=production
  APP_DEBUG=false
  DB_HOST=${{MySQL.MYSQLHOST}}
  DB_PORT=${{MySQL.MYSQLPORT}}
  DB_DATABASE=${{MySQL.MYSQLDATABASE}}
  DB_USERNAME=${{MySQL.MYSQLUSER}}
  DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
  ```
- Generate Public Domain
- Import database SQL files

### 3️⃣ Deploy Government Chatbot
- Root Directory: `GOVERNMENT CHATBOT/4. web application`
- Start Command: `python app_improved.py`
- Environment Variables:
  ```
  PORT=5000
  GROQ_API_KEY=***REMOVED-GROQ-KEY***
  ```
- Memory: 2GB
- Generate Public Domain

### 4️⃣ Deploy HR Database Chatbot
- Root Directory: `GOVERNMENT CHATBOT/4. web application`
- Start Command: `python chatbot_to_database.py`
- Environment Variables:
  ```
  PORT=5001
  GROQ_API_KEY=***REMOVED-GROQ-KEY***
  DB_HOST=${{MySQL.MYSQLHOST}}
  DB_PORT=${{MySQL.MYSQLPORT}}
  DB_DATABASE=${{MySQL.MYSQLDATABASE}}
  DB_USER=${{MySQL.MYSQLUSER}}
  DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
  ```
- Memory: 1GB
- Generate Public Domain

### 5️⃣ Update Frontend URLs
- Update chatbot URLs in Laravel blade files
- Commit and push (auto-redeploys)

---

## Critical Environment Variables

### Laravel
```env
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
SESSION_DRIVER=database
CACHE_STORE=database
```

### Gov Chatbot (app_improved.py)
```env
PORT=5000
GROQ_API_KEY=***REMOVED-GROQ-KEY***
FLASK_ENV=production
```

### HR Chatbot (chatbot_to_database.py)
```env
PORT=5001
GROQ_API_KEY=***REMOVED-GROQ-KEY***
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

---

## Verification URLs

After deployment, test:

- **Laravel:** `https://your-app.railway.app`
- **Gov Chatbot:** `https://gov-chatbot.railway.app/`
- **HR Chatbot:** `https://hr-chatbot.railway.app/`

Test endpoints:
```bash
curl https://your-app.railway.app
curl https://gov-chatbot.railway.app/
curl https://hr-chatbot.railway.app/
```

---

## Common Issues & Fixes

### Issue: 500 Error on Laravel
**Fix:** Check logs, verify DB connection, run migrations

### Issue: Chatbot not responding
**Fix:** Check PORT env var, verify Groq API key, check memory allocation

### Issue: Database connection failed
**Fix:** Verify MySQL env vars match Railway credentials

### Issue: CORS error
**Fix:** Updated in code (already done), redeploy

---

## Cost Estimate

| Service | Monthly Cost |
|---------|--------------|
| Laravel + MySQL | $5-10 |
| Gov Chatbot (2GB) | $10-15 |
| HR Chatbot (1GB) | $7-10 |
| **TOTAL** | **$22-35** |

Free tier: $5/month credit (enough for Laravel only)

---

## Next Steps After Deployment

1. Test all features
2. Update mobile app API URLs
3. Import production database
4. Set up monitoring
5. Configure domain (optional)
6. Enable HTTPS (automatic)

---

**Need help?** See `COMPLETE_DEPLOYMENT_GUIDE.md` for detailed steps.
