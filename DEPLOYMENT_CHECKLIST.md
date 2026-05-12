# ✅ Deployment Checklist - AMS v2.0.0

## 📋 **PRE-DEPLOYMENT**

### **1. Code Review**
- [ ] All code changes reviewed
- [ ] No console.log() or dd() left in code
- [ ] No commented code blocks
- [ ] Code follows PSR-12 standards
- [ ] All variables properly named

### **2. Testing**
- [ ] All manual tests passed
- [ ] Performance tests passed
- [ ] Language switching tested
- [ ] Dark mode tested
- [ ] Browser compatibility verified
- [ ] Mobile responsiveness checked

### **3. Documentation**
- [ ] CHANGELOG updated
- [ ] README updated
- [ ] Deployment guide reviewed
- [ ] Testing guide reviewed
- [ ] All documentation files created

### **4. Backup**
- [ ] Database backup created
- [ ] Files backup created
- [ ] Backup stored securely
- [ ] Backup tested (can restore)

### **5. Environment**
- [ ] .env file configured
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Database credentials correct
- [ ] APP_KEY generated

---

## 🚀 **DEPLOYMENT**

### **Step 1: Code Deployment**
- [ ] Pull latest code from GitHub
- [ ] Verify correct branch (main)
- [ ] Check file permissions
- [ ] Verify all files present

### **Step 2: Dependencies**
- [ ] Run `composer install --no-dev`
- [ ] Run `npm install` (if needed)
- [ ] Verify all dependencies installed
- [ ] No error messages

### **Step 3: Database Migration**
- [ ] Run `php artisan migrate --force`
- [ ] Verify migration successful
- [ ] Check database for new indexes
- [ ] No migration errors

### **Step 4: Cache Management**
- [ ] Run `php artisan cache:clear`
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan view:clear`
- [ ] Run `php artisan route:clear`

### **Step 5: Optimization**
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Verify caches created

### **Step 6: Permissions**
- [ ] Set storage permissions (755)
- [ ] Set bootstrap/cache permissions (755)
- [ ] Verify web server can write
- [ ] Check log file permissions

---

## 🔍 **POST-DEPLOYMENT VERIFICATION**

### **1. Database Verification**
- [ ] Connect to database
- [ ] Run `SHOW INDEX FROM checks;`
- [ ] Verify all 5 indexes exist
- [ ] Run `SHOW INDEX FROM employees;`
- [ ] Verify all 2 indexes exist

### **2. Application Health**
- [ ] Homepage loads successfully
- [ ] Login works correctly
- [ ] Dashboard displays properly
- [ ] No 500 errors
- [ ] No 404 errors

### **3. Feature Testing**

#### **Performance:**
- [ ] Page loads in < 2 seconds
- [ ] Filters apply instantly
- [ ] DataTables loads quickly
- [ ] No console errors
- [ ] Memory usage acceptable

#### **Language Switching:**
- [ ] Language dropdown visible
- [ ] Can switch to Indonesian
- [ ] All text translates
- [ ] Can switch to English
- [ ] All text translates back
- [ ] Language persists after refresh

#### **Dark Mode:**
- [ ] Dark mode toggle visible
- [ ] Can enable dark mode
- [ ] All pages support dark mode
- [ ] Badges are readable
- [ ] Tables are readable
- [ ] Forms are readable
- [ ] Dark mode persists after refresh

#### **Existing Features:**
- [ ] Employee CRUD works
- [ ] Schedule CRUD works
- [ ] Attendance recording works
- [ ] Late time calculation works
- [ ] Overtime calculation works
- [ ] Holiday override works
- [ ] CSV import works
- [ ] Excel export works

### **4. Browser Testing**
- [ ] Chrome (latest) - Desktop
- [ ] Firefox (latest) - Desktop
- [ ] Safari (latest) - Desktop
- [ ] Edge (latest) - Desktop
- [ ] Chrome - Mobile
- [ ] Safari - Mobile

### **5. Performance Monitoring**
- [ ] Check response times
- [ ] Monitor database queries
- [ ] Check memory usage
- [ ] Review error logs
- [ ] No slow queries

---

## 📊 **METRICS VERIFICATION**

### **Expected Performance:**
```
✅ Page Load Time: < 2 seconds
✅ Database Queries: < 50 per page
✅ Memory Usage: < 30MB
✅ DataTables Load: < 1 second
✅ No errors in logs
```

### **Actual Performance:**
```
Page Load Time: _____ seconds
Database Queries: _____ per page
Memory Usage: _____ MB
DataTables Load: _____ seconds
Errors in logs: _____ errors
```

---

## 🐛 **ISSUE TRACKING**

### **Critical Issues:**
```
Issue #1: _____________________
Status: [ ] Fixed [ ] In Progress [ ] Pending
Priority: [ ] Critical [ ] High [ ] Medium [ ] Low

Issue #2: _____________________
Status: [ ] Fixed [ ] In Progress [ ] Pending
Priority: [ ] Critical [ ] High [ ] Medium [ ] Low
```

### **Non-Critical Issues:**
```
Issue #1: _____________________
Status: [ ] Fixed [ ] In Progress [ ] Pending

Issue #2: _____________________
Status: [ ] Fixed [ ] In Progress [ ] Pending
```

---

## 📞 **COMMUNICATION**

### **Stakeholder Notification:**
- [ ] Management notified
- [ ] Users notified
- [ ] Support team notified
- [ ] Development team notified

### **Notification Content:**
```
Subject: AMS v2.0.0 Deployed Successfully

Dear Team,

AMS v2.0.0 has been successfully deployed to production.

New Features:
- 60% faster performance
- English & Indonesian language support
- Complete dark mode

What to expect:
- Faster page loads
- Language switcher in top-right
- Dark mode toggle in top-right

Known Issues:
- None

Support:
- Email: support@ams.com
- Issues: GitHub Issues

Thank you!
AMS Team
```

---

## 🔄 **ROLLBACK PLAN**

### **If Deployment Fails:**

**Step 1: Stop Application**
```bash
# Put application in maintenance mode
php artisan down
```

**Step 2: Rollback Code**
```bash
git revert HEAD
git push origin main
```

**Step 3: Rollback Database**
```bash
php artisan migrate:rollback --step=1
```

**Step 4: Restore Backup**
```bash
# Restore database from backup
mysql -u username -p database_name < backup.sql

# Restore files from backup
tar -xzf backup.tar.gz
```

**Step 5: Clear Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Step 6: Bring Application Back**
```bash
php artisan up
```

**Step 7: Verify**
- [ ] Application loads
- [ ] No errors
- [ ] All features work

---

## ✅ **SIGN-OFF**

### **Deployment Team:**

**Deployed By:**
- Name: _____________________
- Date: _____________________
- Time: _____________________
- Signature: _____________________

**Verified By:**
- Name: _____________________
- Date: _____________________
- Time: _____________________
- Signature: _____________________

**Approved By:**
- Name: _____________________
- Date: _____________________
- Time: _____________________
- Signature: _____________________

---

## 📝 **NOTES**

### **Deployment Notes:**
```
_____________________________________
_____________________________________
_____________________________________
```

### **Issues Encountered:**
```
_____________________________________
_____________________________________
_____________________________________
```

### **Lessons Learned:**
```
_____________________________________
_____________________________________
_____________________________________
```

---

## 🎉 **SUCCESS CRITERIA**

**Deployment is successful when:**

- ✅ All migrations run without errors
- ✅ All indexes created successfully
- ✅ Language switching works perfectly
- ✅ Dark mode toggles smoothly
- ✅ Performance metrics met
- ✅ No errors in logs
- ✅ All existing features work
- ✅ All browsers supported
- ✅ Mobile responsive
- ✅ Stakeholders notified

---

## 📊 **FINAL STATUS**

**Overall Deployment Status:**
- [ ] ✅ **SUCCESS** - All checks passed
- [ ] ⚠️ **PARTIAL** - Some issues found
- [ ] ❌ **FAILED** - Critical issues found

**Deployment Time:**
- Start: _____________________
- End: _____________________
- Duration: _____________________

**Next Steps:**
```
_____________________________________
_____________________________________
_____________________________________
```

---

**Deployment Completed! 🚀**

**Version:** 2.0.0  
**Date:** May 12, 2026  
**Status:** _____________________
