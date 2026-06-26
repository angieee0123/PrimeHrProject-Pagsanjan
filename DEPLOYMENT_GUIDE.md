# PrimeHR System Deployment Guide

## 🚀 Quick Deploy Options

### Option 1: Railway Deployment (Recommended)

#### Laravel Backend
1. **Create Railway Account**: https://railway.app
2. **New Project** → Select "Deploy from GitHub repo"
3. **Connect Repository** → Select `PrimeHrProject-Magdalena`
4. **Root Directory**: Set to `primeHrMagdalenaLaravel`
5. **Add Database**: Click "New" → "Database" → "Add MySQL"
6. **Configure Environment Variables**:
   ```
   APP_NAME=PrimeHR
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=(generate using: php artisan key:generate --show)
   APP_URL=https://your-app.railway.app
   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   SESSION_DRIVER=database
   CACHE_STORE=database
   ```
7. **Deploy**: Railway will auto-detect and deploy

#### Python Chatbot
1. **New Service** in same Railway project
2. **Root Directory**: Set to `GOVERNMENT CHATBOT/4. web application`
3. **Environment Variables**:
   ```
   PORT=8000
   FLASK_ENV=production
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   ```
4. **Deploy**

---

### Option 2: Render Deployment

#### Laravel Backend
1. **Create Render Account**: https://render.com
2. **New** → **Blueprint**
3. **Connect Repository**: Select `PrimeHrProject-Magdalena`
4. **File**: Uses `primeHrMagdalenaLaravel/render.yaml`
5. **Generate APP_KEY**:
   ```bash
   php artisan key:generate --show
   ```
6. **Update Environment Variables** in Render dashboard
7. **Deploy**

#### Python Chatbot
1. **New** → **Web Service**
2. **Root Directory**: `GOVERNMENT CHATBOT/4. web application`
3. **Build Command**: `pip install -r requirements.txt`
4. **Start Command**: `python app.py`
5. **Environment Variables**:
   ```
   PORT=10000
   FLASK_ENV=production
   ```
6. **Deploy**

---

## 📋 Pre-Deployment Checklist

### Laravel Backend
- [ ] Run `composer install` locally to verify dependencies
- [ ] Run `npm install && npm run build` to build assets
- [ ] Test database connection locally
- [ ] Generate APP_KEY: `php artisan key:generate`
- [ ] Update CORS settings if needed
- [ ] Configure session/cache drivers for production

### Python Chatbot
- [ ] Verify `requirements.txt` exists
- [ ] Test Flask app locally: `python app.py`
- [ ] Ensure models are trained and saved
- [ ] Check database connection strings

### Database
- [ ] Export your local database
- [ ] Have SQL dump files ready from `/database` folder
- [ ] Plan database migration strategy

---

## 🔧 Post-Deployment Steps

### 1. Import Database
**Railway:**
```bash
railway run mysql -h <host> -u <user> -p <database> < database/your_table.sql
```

**Render:**
Use Render's shell or external MySQL client to import SQL files

### 2. Run Migrations
**Laravel:**
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 3. Set Permissions
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 4. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Test Endpoints
- Laravel: `https://your-app.railway.app/api/health`
- Chatbot: `https://your-chatbot.railway.app/`

---

## 🔗 Connect Mobile App

Update Flutter app configuration:

**File**: `prime_magdalena_mobile_application/lib/config/api_config.dart`
```dart
class ApiConfig {
  static const String baseUrl = 'https://your-app.railway.app';
  static const String chatbotUrl = 'https://your-chatbot.railway.app';
}
```

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error
**Solution**: Check logs
```bash
railway logs
# or in Render dashboard
```

### Issue: Database Connection Failed
**Solution**: Verify environment variables match database credentials

### Issue: APP_KEY not set
**Solution**: Generate and set APP_KEY
```bash
php artisan key:generate --show
```

### Issue: Storage permissions
**Solution**: Run after deployment
```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📱 Flutter Mobile App Deployment

### Android (Google Play Store)
```bash
cd prime_magdalena_mobile_application
flutter build appbundle --release
```
Upload `build/app/outputs/bundle/release/app-release.aab` to Play Console

### iOS (App Store)
```bash
flutter build ipa --release
```
Upload using Xcode or Transporter

---

## 🎯 Production Environment Variables

### Laravel (.env for production)
```env
APP_NAME=PrimeHR
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=<railway-mysql-host>
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=<railway-generated-password>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=.your-domain.com
```

### Python Chatbot
```env
PORT=8000
FLASK_ENV=production
DB_HOST=<same-as-laravel>
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=<same-as-laravel>
```

---

## 💰 Cost Estimates

### Railway (Free Tier)
- $5 credit/month free
- ~500 hours execution time
- Good for development/testing

### Render (Free Tier)
- 750 hours/month free
- Sleeps after 15 min inactivity
- Good for staging/demo

### Recommended for Production
- Railway: ~$20-50/month with custom domain
- Render: ~$7-25/month per service
- Database: Separate MySQL hosting recommended

---

## 🔐 Security Checklist

- [ ] Set APP_DEBUG=false in production
- [ ] Use strong APP_KEY
- [ ] Enable HTTPS only
- [ ] Configure CORS properly
- [ ] Set secure session cookies
- [ ] Use environment variables for secrets
- [ ] Enable rate limiting
- [ ] Regular security updates

---

## 📚 Additional Resources

- Railway Docs: https://docs.railway.app
- Render Docs: https://render.com/docs
- Laravel Deployment: https://laravel.com/docs/deployment
- Flask Deployment: https://flask.palletsprojects.com/en/latest/deploying/

---

## ✅ Success Criteria

Your deployment is successful when:
1. ✅ Laravel API responds at `/api/health`
2. ✅ Database migrations completed
3. ✅ Chatbot responds to queries
4. ✅ Mobile app can authenticate
5. ✅ No 500 errors in logs
6. ✅ SSL certificate active (HTTPS)

---

**Need Help?** Check logs first:
- Railway: `railway logs`
- Render: Dashboard → Logs tab
