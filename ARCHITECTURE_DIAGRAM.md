# 🏗️ PrimeHR System Architecture - Production Deployment

```
┌─────────────────────────────────────────────────────────────────────┐
│                        RAILWAY PLATFORM                              │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                    PROJECT: PrimeHR-HRIS                       │ │
│  │                                                                 │ │
│  │  ┌─────────────────┐      ┌──────────────────┐               │ │
│  │  │  MySQL Database │◄─────┤ Laravel Backend  │               │ │
│  │  │                 │      │                  │               │ │
│  │  │ - Employees     │      │ - PHP 8.3        │               │ │
│  │  │ - Attendance    │      │ - Sanctum Auth   │               │ │
│  │  │ - Leave         │      │ - Session Mgmt   │               │ │
│  │  │ - Deductions    │      │ - API Routes     │               │ │
│  │  │ - Payroll       │      │ - Blade Views    │               │ │
│  │  │                 │      │                  │               │ │
│  │  │ Port: 3306      │      │ Port: 8000       │               │ │
│  │  │ Memory: Shared  │      │ Memory: 512MB    │               │ │
│  │  └─────────────────┘      └──────────────────┘               │ │
│  │           ▲                        ▲                          │ │
│  │           │                        │                          │ │
│  │           │                ┌───────┴────────┐                │ │
│  │           │                │                │                │ │
│  │           │     ┌──────────┴──────┐  ┌─────┴──────────┐    │ │
│  │           │     │                 │  │                │     │ │
│  │           └─────┤  HR DB Chatbot  │  │ Gov Services   │     │ │
│  │                 │                 │  │ Chatbot        │     │ │
│  │                 │ - Groq LLM      │  │                │     │ │
│  │                 │ - MySQL Queries │  │ - Groq LLM     │     │ │
│  │                 │ - HR Policies   │  │ - FAISS Search │     │ │
│  │                 │ - Flask API     │  │ - Citizens     │     │ │
│  │                 │                 │  │   Charter Data │     │ │
│  │                 │ Port: 5001      │  │ - Flask API    │     │ │
│  │                 │ Memory: 1-2GB   │  │                │     │ │
│  │                 │                 │  │ Port: 5000     │     │ │
│  │                 └─────────────────┘  │ Memory: 2-4GB  │     │ │
│  │                                      └────────────────┘     │ │
│  │                                                              │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                              ▲
                              │ HTTPS (SSL Auto-Configured)
                              │
         ┌────────────────────┼────────────────────┐
         │                    │                    │
         │                    │                    │
    ┌────▼─────┐       ┌──────▼──────┐      ┌─────▼──────┐
    │  Web     │       │   Flutter   │      │  Mobile    │
    │ Browser  │       │   Mobile    │      │  Browser   │
    │          │       │     App     │      │            │
    │ - Admin  │       │             │      │ - Employee │
    │ - HR     │       │ - Android   │      │ - Public   │
    │ - Reports│       │ - iOS       │      │            │
    └──────────┘       └─────────────┘      └────────────┘
```

---

## 📊 Data Flow

### User Authentication Flow
```
Mobile/Web → Laravel API → MySQL → Return JWT/Session
                ↓
         Set Session Cookie
                ↓
      Store User Context (user_id, role)
```

### Chatbot Interaction Flow

**Government Services Chatbot:**
```
User Question → Laravel Frontend
                     ↓
              Gov Chatbot API (Port 5000)
                     ↓
           [Groq LLM + FAISS Search]
                     ↓
          Citizens Charter Knowledge Base
                     ↓
              Natural Response
                     ↓
         Return to Laravel Frontend
```

**HR Database Chatbot:**
```
User Question → Laravel Frontend (with user_id)
                     ↓
              HR Chatbot API (Port 5001)
                     ↓
           [Groq LLM Generates SQL]
                     ↓
              MySQL Database Query
                     ↓
              Natural Response
                     ↓
         Save to chat_history table
                     ↓
         Return to Laravel Frontend
```

---

## 🔒 Security Layers

```
┌─────────────────────────────────────────┐
│  Railway Platform Security              │
│  - SSL/TLS (HTTPS)                      │
│  - DDoS Protection                      │
│  - Private Networking                   │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  Laravel Application Security           │
│  - Sanctum Authentication               │
│  - CSRF Protection                      │
│  - Session Management                   │
│  - Role-Based Access Control            │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  Database Security                      │
│  - Encrypted Connections                │
│  - Private Network Access               │
│  - User Authentication                  │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  API Security                           │
│  - CORS Configuration                   │
│  - Rate Limiting (recommended)          │
│  - Input Validation                     │
└─────────────────────────────────────────┘
```

---

## 🌐 Domain Structure (After Deployment)

```
Primary Domain (Laravel):
https://primehr.railway.app
  ├── /                      → Dashboard
  ├── /login                 → Authentication
  ├── /admin/*               → Admin Panel
  ├── /permanent/*           → Employee Portal
  ├── /api/*                 → REST API
  └── /chatbot               → Chatbot Interface

Gov Chatbot Domain:
https://gov-chatbot.railway.app
  └── /chat                  → POST endpoint

HR Chatbot Domain:
https://hr-chatbot.railway.app
  └── /chat                  → POST endpoint
```

---

## 💾 Database Schema (Production)

```sql
primehrismagdalena
├── employees                 (Master employee data)
├── users                     (Authentication)
├── attendances              (Time records)
├── accredited_hours_logs    (Computed hours)
├── leave_balances           (Leave credits)
├── leave_applications       (Leave requests)
├── leave_transactions       (Leave history)
├── leave_types_config       (Leave types)
├── deduction_types          (Deduction categories)
├── employee_deductions      (Employee deductions)
├── loan_types               (Loan configurations)
├── employee_loans           (Active loans)
├── salary_computations      (Payroll data)
├── chat_history             (Chatbot conversations)
└── ... (50+ tables total)
```

---

## 🔄 Deployment Pipeline

```
┌─────────────────┐
│  GitHub Repo    │
│  (main branch)  │
└────────┬────────┘
         │
         │ Push Code
         ↓
┌─────────────────┐
│ Railway Detects │
│    Changes      │
└────────┬────────┘
         │
         │ Auto Build
         ↓
┌─────────────────┐
│ Build Services  │
│ - Laravel       │
│ - Chatbots      │
└────────┬────────┘
         │
         │ Deploy
         ↓
┌─────────────────┐
│ Health Checks   │
└────────┬────────┘
         │
         │ ✅ Success
         ↓
┌─────────────────┐
│  Live & Running │
└─────────────────┘
```

---

## 📈 Scaling Strategy

### Current Setup (Starter)
- Laravel: 512MB RAM
- Gov Chatbot: 2GB RAM
- HR Chatbot: 1GB RAM
- **Cost:** ~$25-35/month

### Scaled Up (Production)
- Laravel: 1-2GB RAM (handle more users)
- Gov Chatbot: 4GB RAM (faster model loading)
- HR Chatbot: 2GB RAM (complex queries)
- Add Redis cache
- **Cost:** ~$60-80/month

### Enterprise (High Traffic)
- Laravel: 4GB RAM (multiple instances)
- Gov Chatbot: 8GB RAM (multiple instances)
- HR Chatbot: 4GB RAM (multiple instances)
- Dedicated MySQL (external)
- Redis cache cluster
- CDN for assets
- **Cost:** ~$150-250/month

---

## ⚡ Performance Optimization

1. **Laravel:**
   - Route caching: `php artisan route:cache`
   - Config caching: `php artisan config:cache`
   - View caching: `php artisan view:cache`

2. **Chatbots:**
   - Model preloading on startup
   - Response caching for common queries
   - Connection pooling for MySQL

3. **Database:**
   - Proper indexing on frequently queried columns
   - Query optimization
   - Regular maintenance

---

## 🎯 Success Metrics

After deployment, monitor:

- ✅ Response time < 2 seconds
- ✅ Uptime > 99.5%
- ✅ Database queries < 100ms
- ✅ Chatbot response < 3 seconds
- ✅ Memory usage < 80%
- ✅ Zero CORS errors

---

**This architecture supports:**
- 100+ concurrent users
- 10,000+ employee records
- Real-time attendance tracking
- AI-powered chatbots
- Mobile & web access
- Secure data handling
