# 🚀 Complete Deployment Guide - PrimeHR HRIS System

## 📋 System Overview

Your project has **3 main components**:

1. **Laravel Backend** (primeHrMagdalenaLaravel)
   - PHP 8.3, MySQL database
   - Main HRIS API & Web Interface
   - User authentication & sessions

2. **Python Chatbot #1** (app_improved.py - Port 5000)
   - Government Services Chatbot
   - Uses Groq LLM + FAISS + Citizens Charter data
   - Serves government policy queries

3. **Python Chatbot #2** (chatbot_to_database.py - Port 5001)
   - HR Database Chatbot
   - Uses Groq LLM + MySQL queries
   - Answers employee/attendance/leave questions

4. **Flutter Mobile App** (prime_magdalena_mobile_application)
   - Cross-platform mobile client
   - Connects to Laravel API

---

## ⚠️ IMPORTANT: Platform Recommendation

### ✅ **USE RAILWAY** (Strongly Recommended)

**Why Railway?**
- ✅ Supports heavy ML models (FAISS, SentenceTransformers)
- ✅ Better memory allocation (up to 8GB RAM)
- ✅ Fast deployments with Nixpacks
- ✅ Built-in MySQL database
- ✅ Monorepo-friendly
- ✅ No cold starts on paid tier

### ❌ **DON'T USE RENDER** for this project

**Why NOT Render?**
- ❌ Free tier: Only 512MB RAM (can't load ML models)
- ❌ Strict 15-minute build timeout
- ❌ Aggressive cold starts (sleeps after 15 min)
- ❌ Build failures with PyTorch/transformers

---

## 💰 Cost Estimate (Railway)

| Service | Memory | Cost/Month |
|---------|--------|------------|
| Laravel Backend | 512MB | $5-7 |
| Chatbot #1 (Gov Services) | 2GB | $10-15 |
| Chatbot #2 (HR Database) | 1GB | $7-10 |
| MySQL Database | Shared | $5 |
| **TOTAL** | - | **$27-37/month** |

**Free Tier:** $5 credit/month (enough for testing Laravel only)

---

## 🎯 Step-by-Step Deployment

### **Phase 1: Prepare Your Code**

#### Step 1.1: Update Environment Variables

**Create production .env file for Laravel:**

```bash
cd primeHrMagdalenaLaravel
```

Create `.env.production` (copy from `.env`):
```env
APP_NAME=PrimeHR
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

CORS_ALLOWED_ORIGINS=https://your-app.railway.app,https://your-chatbot1.railway.app,https://your-chatbot2.railway.app
```

Generate APP_KEY locally:
```bash
php artisan key:generate --show
```

#### Step 1.2: Update Chatbot Configurations

**For `app_improved.py`** (Government Chatbot):

Find this line (around line 18):
```python
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})
```

Change to:
```python
CORS(app, resources={r"/chat": {"origins": "*"}})  # Or specify your Railway domains
```

**For `chatbot_to_database.py`** (HR Database Chatbot):

1. Find database config (line 12-18):
```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}
```

Change to use environment variables:
```python
import os

DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', 'admin'),
    'database': os.getenv('DB_DATABASE', 'primehrismagdalena'),
    'auth_plugin': 'mysql_native_password'
}
```

2. Find CORS (line 10):
```python
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})
```

Change to:
```python
CORS(app, resources={r"/chat": {"origins": "*"}})
```

3. **CRITICAL:** Add `PORT` environment variable support:

Find the last lines:
```python
if __name__ == '__main__':
    app.run(debug=True, port=5001)
```

Change to:
```python
if __name__ == '__main__':
    port = int(os.getenv('PORT', 5001))
    app.run(debug=False, host='0.0.0.0', port=port)
```

#### Step 1.3: Create Requirements Files

**For Government Chatbot:**

Create `/GOVERNMENT CHATBOT/4. web application/requirements.txt`:
```txt
flask==3.0.0
flask-cors==4.0.0
groq==0.4.1
sentence-transformers==2.3.1
faiss-cpu==1.7.4
numpy==1.24.3
gunicorn==21.2.0
```

**For HR Database Chatbot:**

The same file already covers both. Ensure it has:
```txt
flask==3.0.0
flask-cors==4.0.0
groq==0.4.1
sentence-transformers==2.3.1
faiss-cpu==1.7.4
numpy==1.24.3
mysql-connector-python==8.2.0
gunicorn==21.2.0
```

#### Step 1.4: Update Laravel Chatbot View URLs

Find `/primeHrMagdalenaLaravel/resources/views/admin/chatbot.blade.php`:

Change line 265-266:
```javascript
const chatAPI = 'http://localhost:5001';
const laravelAPI = 'http://localhost:8000';
```

To:
```javascript
const chatAPI = 'https://your-hr-chatbot.railway.app';  // Change after deployment
const laravelAPI = window.location.origin;  // Auto-detect Laravel URL
```

#### Step 1.5: Commit All Changes

```bash
git add .
git commit -m "Prepare for Railway deployment: Update CORS, env vars, and ports"
git push origin main
```

---

### **Phase 2: Deploy to Railway**

#### Step 2.1: Create Railway Account

1. Go to https://railway.app
2. Sign up with GitHub
3. Connect your GitHub account

#### Step 2.2: Create New Project

1. Click **"New Project"**
2. Select **"Deploy from GitHub repo"**
3. Choose **`PrimeHrProject-Magdalena`** repository
4. Railway will create your first service

#### Step 2.3: Deploy Laravel Backend

1. **Configure the service:**
   - Click the service → **Settings**
   - **Root Directory:** `primeHrMagdalenaLaravel`
   - **Build Command:** (leave empty, Nixpacks auto-detects)
   - **Start Command:** `php artisan migrate --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=$PORT`

2. **Add MySQL Database:**
   - In your project, click **"+ New"**
   - Select **"Database"** → **"Add MySQL"**
   - Railway will create a MySQL instance

3. **Configure Environment Variables:**
   - Click Laravel service → **"Variables"** tab
   - Add these variables:

```env
APP_NAME=PrimeHR
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_FROM_STEP_1.1
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

4. **Generate Public Domain:**
   - Settings → **"Networking"** → **"Generate Domain"**
   - Copy the URL (e.g., `https://primehr.railway.app`)

5. **Deploy:**
   - Click **"Deploy"** (or it auto-deploys)
   - Wait 3-5 minutes

#### Step 2.4: Import Database

**Option A: Using Railway CLI**

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Select MySQL service
railway service

# Import SQL files
railway run mysql -h $MYSQLHOST -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < database/primehrismagdalena_employees.sql
railway run mysql -h $MYSQLHOST -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < database/primehrismagdalena_attendance.sql
# Repeat for all SQL files in /database folder
```

**Option B: Using MySQL Workbench**

1. In Railway, click **MySQL service** → **"Connect"**
2. Copy the connection details
3. Open MySQL Workbench → New Connection
4. Enter Railway MySQL credentials
5. Use **Data Import/Restore** to import all SQL files

#### Step 2.5: Deploy Government Chatbot (app_improved.py)

1. **In Railway project, click "+ New"** → **"GitHub Repo"**
2. Select same repository
3. **Configure service:**
   - **Service Name:** `gov-chatbot`
   - **Root Directory:** `GOVERNMENT CHATBOT/4. web application`
   - **Build Command:** `pip install -r requirements.txt`
   - **Start Command:** `python app_improved.py`

4. **Environment Variables:**
```env
PORT=5000
GROQ_API_KEY=***REMOVED-GROQ-KEY***
FLASK_ENV=production
```

5. **Memory Allocation:**
   - Settings → **"Resources"**
   - Set **Memory:** 2GB (requires paid plan)

6. **Generate Public Domain:**
   - Settings → Networking → Generate Domain
   - Copy URL (e.g., `https://gov-chatbot.railway.app`)

7. **Deploy**

⚠️ **Note:** This chatbot needs the trained models in `/3. training script/models/`. Make sure:
- `faiss_index.bin`
- `documents.json`
- Sentence transformer model files

Are committed to GitHub (if small) or uploaded manually.

#### Step 2.6: Deploy HR Database Chatbot (chatbot_to_database.py)

1. **In Railway project, click "+ New"** → **"GitHub Repo"**
2. Select same repository
3. **Configure service:**
   - **Service Name:** `hr-chatbot`
   - **Root Directory:** `GOVERNMENT CHATBOT/4. web application`
   - **Build Command:** `pip install -r requirements.txt`
   - **Start Command:** `python chatbot_to_database.py`

4. **Environment Variables:**
```env
PORT=5001
GROQ_API_KEY=***REMOVED-GROQ-KEY***
FLASK_ENV=production

DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

5. **Memory Allocation:**
   - Settings → Resources
   - Set Memory: 1GB

6. **Generate Public Domain:**
   - Copy URL (e.g., `https://hr-chatbot.railway.app`)

7. **Deploy**

---

### **Phase 3: Update Frontend URLs**

#### Step 3.1: Update Laravel Chatbot Views

Update these files with your Railway URLs:

**`/resources/views/admin/chatbot.blade.php`** (line 265):
```javascript
const chatAPI = 'https://hr-chatbot.railway.app';
const laravelAPI = window.location.origin;
```

**If you have separate government chatbot view**, update similarly.

#### Step 3.2: Update CORS in Laravel

**`/config/cors.php`:**
```php
'allowed_origins' => [
    'https://your-laravel-app.railway.app',
    'https://gov-chatbot.railway.app',
    'https://hr-chatbot.railway.app',
],
```

#### Step 3.3: Commit and Redeploy

```bash
git add .
git commit -m "Update chatbot URLs to Railway domains"
git push origin main
```

Railway will auto-redeploy.

---

### **Phase 4: Mobile App Configuration**

#### Step 4.1: Update Flutter API Config

**File:** `/prime_magdalena_mobile_application/lib/config/api_config.dart`

```dart
class ApiConfig {
  // Production URLs
  static const String baseUrl = 'https://your-laravel-app.railway.app';
  static const String apiUrl = '$baseUrl/api';
  
  // Chatbot URLs
  static const String govChatbotUrl = 'https://gov-chatbot.railway.app';
  static const String hrChatbotUrl = 'https://hr-chatbot.railway.app';
  
  // Endpoints
  static const String loginEndpoint = '$apiUrl/login';
  static const String attendanceEndpoint = '$apiUrl/attendance';
  // ... add other endpoints
}
```

#### Step 4.2: Build Mobile App

**For Android:**
```bash
cd prime_magdalena_mobile_application
flutter build apk --release
```

Output: `build/app/outputs/flutter-apk/app-release.apk`

**For iOS:**
```bash
flutter build ios --release
```

---

## ✅ Verification Checklist

After deployment, test these:

### Laravel Backend:
- [ ] Can access homepage: `https://your-laravel-app.railway.app`
- [ ] Can login with test user
- [ ] Database connection working (check personnel list)
- [ ] Sessions working

### Government Chatbot:
- [ ] API responds: `curl https://gov-chatbot.railway.app/`
- [ ] Can ask questions from Laravel chatbot interface
- [ ] CORS working (no browser errors)

### HR Database Chatbot:
- [ ] API responds: `curl https://hr-chatbot.railway.app/`
- [ ] Can query employee data
- [ ] MySQL connection working

### Mobile App:
- [ ] Can login
- [ ] Can view attendance
- [ ] Can chat with chatbots

---

## 🐛 Troubleshooting

### Issue: "Application failed to respond"
**Solution:**
- Check if start command uses `--host=0.0.0.0 --port=$PORT`
- Verify environment variables are set
- Check deployment logs

### Issue: "CORS policy error"
**Solution:**
- Update CORS to allow Railway domains
- Check if chatbot URLs are correct in frontend

### Issue: "Database connection failed"
**Solution:**
- Verify `${{MySQL.MYSQLHOST}}` references are correct
- Check if database is running
- Import SQL files if tables are missing

### Issue: "Model files not found" (Python chatbots)
**Solution:**
- Ensure `/3. training script/models/` is in GitHub
- Or use Railway Volumes to upload models manually
- Check file paths in Python code

### Issue: "Out of memory" (Python chatbots)
**Solution:**
- Increase memory allocation in Railway (Settings → Resources)
- Requires paid plan for >512MB RAM

---

## 📊 Monitoring & Logs

### View Logs:
```bash
# Railway CLI
railway logs --service=laravel-backend
railway logs --service=gov-chatbot
railway logs --service=hr-chatbot
```

### Or in Railway Dashboard:
- Click service → **"Deployments"** → Latest deployment → **"View Logs"**

---

## 🔐 Security Checklist

- [ ] Change all default passwords
- [ ] Rotate Groq API keys for production
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use HTTPS only (Railway provides SSL automatically)
- [ ] Implement rate limiting on chatbot endpoints
- [ ] Secure MySQL with Railway's private networking
- [ ] Add authentication middleware to chatbot routes

---

## 💡 Tips for Success

1. **Start with Laravel only** on free tier to test
2. **Deploy chatbots one at a time** to debug issues
3. **Use Railway's private networking** to connect services (saves bandwidth)
4. **Monitor usage** in Railway dashboard to avoid surprise bills
5. **Set up CI/CD** with Railway's GitHub integration (auto-deploy on push)
6. **Use environment variables** for all secrets (never hardcode)
7. **Test locally first** with same production configs

---

## 📞 Support Resources

- **Railway Docs:** https://docs.railway.app
- **Laravel Deployment:** https://laravel.com/docs/deployment
- **Flask Production:** https://flask.palletsprojects.com/en/latest/deploying/

---

## 🎉 Success Criteria

Your deployment is complete when:

✅ Laravel responds with login page  
✅ Can authenticate and view dashboard  
✅ Both chatbots respond to queries  
✅ Database queries work (employees, attendance, leave)  
✅ Mobile app can connect and authenticate  
✅ No CORS errors in browser console  
✅ SSL certificates active (HTTPS)  

---

**Estimated Total Deployment Time:** 2-4 hours (first time)

**Good luck! 🚀**
