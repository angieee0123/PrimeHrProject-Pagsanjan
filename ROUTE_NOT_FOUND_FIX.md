# Fix: Route Not Found Error

## ❌ Error:
```
http://localhost:5000/attendance/manual
404 Not Found
```

## ✅ Solution:

### Step 1: Restart the Server

The routes are defined but the server needs to be restarted to register them.

```bash
# Stop the current server (Ctrl+C)

# Start it again
cd "GOVERNMENT CHATBOT/4. web application"
python app.py
```

### Step 2: Verify Server Started

You should see:
```
Loading models and data...
Municipality: Sampaloc, Quezon
Total Services: X
✓ All models loaded
 * Running on http://127.0.0.1:5000
 * Restarting with stat
```

### Step 3: Test the Routes

Open these URLs in your browser:

1. **QR Scanner**: `http://localhost:5000/attendance`
2. **Test Page**: `http://localhost:5000/attendance/test`
3. **Report**: `http://localhost:5000/attendance/report`
4. **Manual Entry**: `http://localhost:5000/attendance/manual` ⭐

---

## 🔍 If Still Not Working:

### Check 1: Verify Files Exist

```bash
# Check if template exists
dir "templates\attendance_manual.html"
```

Should show the file exists.

### Check 2: Verify Blueprint Registration

Open `app.py` and check these lines exist:
```python
from qr_attendance import qr_attendance
app.register_blueprint(qr_attendance)
```

### Check 3: Test Routes Programmatically

```bash
python test_routes.py
```

This will test all routes and show which ones work.

### Check 4: Check for Errors

Look at the server console for any error messages when starting.

---

## 🎯 Quick Test:

### Test 1: Simple Route
```
http://localhost:5000/attendance
```
If this works, the blueprint is registered.

### Test 2: Manual Entry
```
http://localhost:5000/attendance/manual
```
If this works, all routes are working!

---

## 🐛 Common Issues:

### Issue 1: Server Not Restarted
**Problem**: Made changes but didn't restart server
**Solution**: Stop (Ctrl+C) and restart (`python app.py`)

### Issue 2: Wrong Port
**Problem**: Server running on different port
**Solution**: Check console output for actual port

### Issue 3: Template Not Found
**Problem**: `attendance_manual.html` missing
**Solution**: Verify file exists in `templates/` folder

### Issue 4: Import Error
**Problem**: `qr_attendance` module not found
**Solution**: Ensure `qr_attendance.py` is in same folder as `app.py`

---

## ✅ Verification Checklist:

- [ ] Server is running (`python app.py`)
- [ ] No errors in console
- [ ] Port 5000 is correct
- [ ] `attendance_manual.html` exists in `templates/`
- [ ] `qr_attendance.py` exists
- [ ] Blueprint is registered in `app.py`

---

## 🚀 After Restart:

All these should work:

| Route | Purpose |
|-------|---------|
| `/attendance` | QR Scanner |
| `/attendance/test` | Test Page |
| `/attendance/report` | View Reports |
| `/attendance/manual` | Manual Entry ⭐ |

---

## 💡 Pro Tip:

Use **Flask Debug Mode** to auto-reload on changes:

```python
# In app.py, at the bottom:
if __name__ == '__main__':
    app.run(debug=True, port=5000)  # debug=True enables auto-reload
```

Then you don't need to manually restart!

---

## 📞 Still Not Working?

### Check Server Output:

Look for these lines when server starts:
```
 * Running on http://127.0.0.1:5000
 * Debug mode: on
```

### Check Browser Console (F12):

Look for any JavaScript errors.

### Try Different Browser:

Sometimes cache causes issues. Try:
- Chrome (Incognito mode)
- Edge
- Firefox

---

## ✅ Summary:

**Most Common Fix**: Just restart the server!

```bash
# Stop server (Ctrl+C)
python app.py
# Open: http://localhost:5000/attendance/manual
```

**Should work now!** 🎉
