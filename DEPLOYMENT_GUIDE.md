# 🚀 Deployment Guide - AMS v2.0.0

## 📋 **PRE-DEPLOYMENT CHECKLIST**

### **1. Backup Database**
```bash
# Export database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Or use Railway CLI
railway db backup
```

### **2. Backup Files**
```bash
# Create backup of current deployment
tar -czf ams_backup_$(date +%Y%m%d).tar.gz .
```

---

## 🔧 **DEPLOYMENT STEPS**

### **Step 1: Pull Latest Code**
```bash
git pull origin main
```

### **Step 2: Install Dependencies**
```bash
composer install --optimize-autoloader --no-dev
```

### **Step 3: Run Migrations**
```bash
php artisan migrate --force
```

**Expected Output:**
```
Migrating: 2026_05_12_000001_add_performance_indexes
Migrated:  2026_05_12_000001_add_performance_indexes (XX.XXms)
```

### **Step 4: Clear All Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### **Step 5: Optimize for Production**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 6: Set Permissions**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🌐 **RAILWAY DEPLOYMENT**

### **Option 1: Automatic Deployment (Recommended)**
Railway will automatically deploy when you push to GitHub:

```bash
git add .
git commit -m "feat: Performance, i18n & Dark Mode improvements"
git push origin main
```

### **Option 2: Manual Deployment**
```bash
# Using Railway CLI
railway up

# Or redeploy from Railway dashboard
railway redeploy
```

### **Environment Variables (Railway)**
Ensure these are set in Railway dashboard:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-app-key
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Optional: For better performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🔍 **POST-DEPLOYMENT VERIFICATION**

### **1. Check Database Indexes**
```sql
-- Connect to database
SHOW INDEX FROM checks;
SHOW INDEX FROM employees;

-- Expected indexes:
-- idx_checks_emp_id
-- idx_checks_attendance_time
-- idx_checks_leave_time
-- idx_checks_emp_attendance
-- idx_checks_schedule_id
-- idx_employees_name
-- idx_employees_email
```

### **2. Test Language Switching**
1. Open application in browser
2. Click language dropdown in top navigation
3. Switch between English and Indonesian
4. Verify all text changes
5. Refresh page - language should persist

### **3. Test Dark Mode**
1. Click moon icon in top navigation
2. Verify smooth transition to dark mode
3. Check all pages (Attendance, Employees, Reports)
4. Verify badges, tables, and forms are readable
5. Refresh page - dark mode should persist

### **4. Test Performance**
```bash
# Check query count (should be ~40 per page)
# Enable query logging in .env temporarily
DB_LOG_QUERIES=true

# Monitor logs
tail -f storage/logs/laravel.log
```

### **5. Test DataTables**
1. Open Attendance page
2. Apply filters (Month, Year, Date Range)
3. Verify fast loading (<1 second)
4. Test search functionality
5. Test pagination

---

## 📊 **MONITORING**

### **1. Performance Monitoring**
```bash
# Check response times
curl -w "@curl-format.txt" -o /dev/null -s https://your-app.railway.app/attendance

# Create curl-format.txt:
time_namelookup:  %{time_namelookup}\n
time_connect:  %{time_connect}\n
time_appconnect:  %{time_appconnect}\n
time_pretransfer:  %{time_pretransfer}\n
time_redirect:  %{time_redirect}\n
time_starttransfer:  %{time_starttransfer}\n
----------\n
time_total:  %{time_total}\n
```

### **2. Database Performance**
```sql
-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;

-- Check index usage
SELECT * FROM sys.schema_unused_indexes;
```

### **3. Error Monitoring**
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Check for errors
grep "ERROR" storage/logs/laravel.log
```

---

## 🐛 **TROUBLESHOOTING**

### **Issue 1: Migration Fails**
```bash
# Check if indexes already exist
php artisan migrate:status

# Rollback if needed
php artisan migrate:rollback --step=1

# Re-run migration
php artisan migrate
```

### **Issue 2: Language Not Switching**
```bash
# Clear config cache
php artisan config:clear

# Check session driver
php artisan tinker
>>> config('session.driver')

# Verify middleware is registered
php artisan route:list | grep lang
```

### **Issue 3: Dark Mode Not Persisting**
```javascript
// Check browser console for errors
console.log(localStorage.getItem('theme'));

// Clear localStorage and test again
localStorage.clear();
```

### **Issue 4: Slow Performance**
```bash
# Check if indexes were created
php artisan tinker
>>> DB::select("SHOW INDEX FROM checks");

# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Issue 5: 500 Error After Deployment**
```bash
# Check logs
tail -n 100 storage/logs/laravel.log

# Common fixes:
php artisan key:generate
php artisan storage:link
chmod -R 755 storage bootstrap/cache
```

---

## 🔄 **ROLLBACK PROCEDURE**

If something goes wrong:

### **1. Rollback Code**
```bash
git revert HEAD
git push origin main
```

### **2. Rollback Database**
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Or restore from backup
mysql -u username -p database_name < backup_20260512.sql
```

### **3. Clear Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## ✅ **DEPLOYMENT CHECKLIST**

- [ ] Backup database
- [ ] Backup files
- [ ] Pull latest code
- [ ] Run `composer install`
- [ ] Run migrations
- [ ] Clear all caches
- [ ] Optimize for production
- [ ] Set correct permissions
- [ ] Verify database indexes
- [ ] Test language switching
- [ ] Test dark mode
- [ ] Test performance
- [ ] Monitor logs for errors
- [ ] Update documentation
- [ ] Notify team

---

## 📞 **SUPPORT**

If you encounter issues during deployment:

1. Check logs: `storage/logs/laravel.log`
2. Check Railway logs: `railway logs`
3. Contact development team
4. Create GitHub issue with error details

---

## 🎉 **SUCCESS CRITERIA**

Deployment is successful when:

- ✅ All migrations run without errors
- ✅ Language switching works (EN ↔ ID)
- ✅ Dark mode toggles smoothly
- ✅ Attendance page loads in <2 seconds
- ✅ DataTables filters work correctly
- ✅ No errors in logs
- ✅ Mobile responsive
- ✅ All tests pass

---

**Happy Deploying! 🚀**
