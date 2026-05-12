# 🧪 Testing Guide - AMS v2.0.0

## 📋 **OVERVIEW**

This guide covers comprehensive testing for all new features in v2.0.0:
- ⚡ Performance Optimization
- 🌐 Internationalization (i18n)
- 🌙 Dark Mode Enhancement

---

## 🎯 **TEST CATEGORIES**

### **1. Performance Testing**
### **2. Internationalization Testing**
### **3. Dark Mode Testing**
### **4. Regression Testing**
### **5. Browser Compatibility Testing**
### **6. Mobile Responsiveness Testing**

---

## ⚡ **1. PERFORMANCE TESTING**

### **1.1 Database Index Verification**

**Test:** Verify all indexes were created successfully

```sql
-- Connect to database
mysql -u username -p database_name

-- Check checks table indexes
SHOW INDEX FROM checks;

-- Expected output should include:
-- idx_checks_emp_id
-- idx_checks_attendance_time
-- idx_checks_leave_time
-- idx_checks_emp_attendance
-- idx_checks_schedule_id

-- Check employees table indexes
SHOW INDEX FROM employees;

-- Expected output should include:
-- idx_employees_name
-- idx_employees_email
```

**Pass Criteria:**
- ✅ All 5 indexes exist on `checks` table
- ✅ All 2 indexes exist on `employees` table
- ✅ No errors in migration logs

---

### **1.2 Query Performance Test**

**Test:** Measure query execution time

```php
// Add to AttendanceController temporarily
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

// Your code here
$checks = Check::with(['employee'])->get();

$queries = DB::getQueryLog();
dd(count($queries)); // Should be ~40 or less
```

**Pass Criteria:**
- ✅ Query count ≤ 40 per page load
- ✅ No N+1 query warnings
- ✅ Eager loading is working

---

### **1.3 Page Load Time Test**

**Test:** Measure page load time using browser DevTools

**Steps:**
1. Open Chrome DevTools (F12)
2. Go to Network tab
3. Clear cache (Ctrl+Shift+Delete)
4. Navigate to `/attendance`
5. Check "Load" time in Network tab

**Pass Criteria:**
- ✅ Page load time < 2 seconds
- ✅ DOMContentLoaded < 1 second
- ✅ No failed requests

---

### **1.4 DataTables Performance Test**

**Test:** Measure DataTables rendering time

**Steps:**
1. Open `/attendance` page
2. Open Console (F12)
3. Type: `console.time('dt'); $('#attendance-table').DataTable().draw(); console.timeEnd('dt');`
4. Check execution time

**Pass Criteria:**
- ✅ DataTables render time < 1 second
- ✅ Filter operations < 500ms
- ✅ Pagination is instant

---

### **1.5 Memory Usage Test**

**Test:** Check memory consumption

**Steps:**
1. Open Chrome DevTools (F12)
2. Go to Performance tab
3. Click "Record"
4. Navigate through pages
5. Stop recording
6. Check memory usage

**Pass Criteria:**
- ✅ Memory usage < 50MB
- ✅ No memory leaks
- ✅ Garbage collection is working

---

### **1.6 Cache Verification Test**

**Test:** Verify schedule caching is working

```php
// In tinker
php artisan tinker

>>> cache()->has('all_schedules')
// Should return true after first page load

>>> cache()->get('all_schedules')
// Should return collection of schedules

>>> cache()->forget('all_schedules')
// Clear cache

>>> cache()->has('all_schedules')
// Should return false
```

**Pass Criteria:**
- ✅ Cache is created on first load
- ✅ Cache persists for 1 hour
- ✅ Cache can be cleared manually

---

## 🌐 **2. INTERNATIONALIZATION TESTING**

### **2.1 Language Switcher Test**

**Test:** Verify language switcher appears and works

**Steps:**
1. Login to application
2. Look for language dropdown in top-right corner
3. Click dropdown
4. Verify "English" and "Indonesia" options appear
5. Click "Indonesia"
6. Verify page text changes to Indonesian
7. Click "English"
8. Verify page text changes to English

**Pass Criteria:**
- ✅ Language dropdown is visible
- ✅ Both language options appear
- ✅ Clicking switches language immediately
- ✅ No page reload required
- ✅ Flag icons display correctly

---

### **2.2 Translation Coverage Test**

**Test:** Verify all text is translated

**Pages to Check:**
- `/attendance` - Attendance page
- `/latetime` - Late time page
- `/overtime` - Overtime page
- `/employees` - Employees page
- `/schedule` - Schedule page

**Elements to Check:**
- Page titles
- Breadcrumbs
- Table headers
- Button labels
- Filter labels
- Status badges
- Modal titles
- Form labels
- Validation messages

**Pass Criteria:**
- ✅ All text changes when switching language
- ✅ No English text remains in Indonesian mode
- ✅ No Indonesian text remains in English mode
- ✅ Dates and numbers format correctly

---

### **2.3 Language Persistence Test**

**Test:** Verify language preference persists

**Steps:**
1. Switch to Indonesian
2. Refresh page (F5)
3. Verify language is still Indonesian
4. Navigate to different page
5. Verify language is still Indonesian
6. Logout and login again
7. Verify language is still Indonesian

**Pass Criteria:**
- ✅ Language persists after page refresh
- ✅ Language persists across navigation
- ✅ Language persists after logout/login
- ✅ Language is stored in session

---

### **2.4 DataTables Translation Test**

**Test:** Verify DataTables UI is translated

**Elements to Check:**
- "Show X entries" dropdown
- "Search:" label
- "Showing X to Y of Z results" text
- "Next" / "Previous" buttons
- "No data available" message
- "Loading..." message

**Pass Criteria:**
- ✅ All DataTables text is translated
- ✅ Pagination text is translated
- ✅ Search label is translated
- ✅ Info text is translated

---

### **2.5 Month Names Translation Test**

**Test:** Verify month names are translated

**Steps:**
1. Go to `/attendance` page
2. Click "Month" filter dropdown
3. Verify month names are in current language
4. Switch language
5. Verify month names change

**Pass Criteria:**
- ✅ Month names display in Indonesian when ID selected
- ✅ Month names display in English when EN selected
- ✅ All 12 months are translated

---

## 🌙 **3. DARK MODE TESTING**

### **3.1 Dark Mode Toggle Test**

**Test:** Verify dark mode toggle works

**Steps:**
1. Login to application
2. Look for moon icon in top-right corner
3. Click moon icon
4. Verify page switches to dark mode
5. Click moon icon again
6. Verify page switches back to light mode

**Pass Criteria:**
- ✅ Moon icon is visible
- ✅ Clicking toggles dark mode
- ✅ Transition is smooth (300ms)
- ✅ No visual glitches
- ✅ Icon changes (moon ↔ sun)

---

### **3.2 Dark Mode Coverage Test**

**Test:** Verify all components support dark mode

**Components to Check:**
- Sidebar
- Topbar
- Cards
- Tables
- Forms
- Buttons
- Badges
- Modals
- Dropdowns
- Pagination
- Breadcrumbs

**Pass Criteria:**
- ✅ All components have dark mode styles
- ✅ Text is readable (good contrast)
- ✅ Borders are visible
- ✅ Hover states work correctly
- ✅ Active states work correctly

---

### **3.3 Dark Mode Persistence Test**

**Test:** Verify dark mode preference persists

**Steps:**
1. Enable dark mode
2. Refresh page (F5)
3. Verify dark mode is still enabled
4. Navigate to different page
5. Verify dark mode is still enabled
6. Close browser and reopen
7. Verify dark mode is still enabled

**Pass Criteria:**
- ✅ Dark mode persists after page refresh
- ✅ Dark mode persists across navigation
- ✅ Dark mode persists after browser restart
- ✅ Dark mode is stored in localStorage

---

### **3.4 Color Contrast Test**

**Test:** Verify WCAG AA compliance

**Tools:**
- Chrome DevTools Lighthouse
- WebAIM Contrast Checker
- axe DevTools

**Steps:**
1. Enable dark mode
2. Run Lighthouse accessibility audit
3. Check contrast ratios
4. Verify no contrast issues

**Pass Criteria:**
- ✅ All text has contrast ratio ≥ 4.5:1
- ✅ Large text has contrast ratio ≥ 3:1
- ✅ No accessibility warnings
- ✅ WCAG AA compliant

---

### **3.5 Badge Readability Test**

**Test:** Verify badges are readable in dark mode

**Badges to Check:**
- Success badge (green)
- Danger badge (red)
- Warning badge (amber)
- Info badge (blue)
- Secondary badge (gray)

**Pass Criteria:**
- ✅ All badges have good contrast
- ✅ Text is readable
- ✅ Background colors are appropriate
- ✅ No color blindness issues

---

### **3.6 Auto Dark Mode Detection Test**

**Test:** Verify system preference detection

**Steps:**
1. Clear localStorage: `localStorage.clear()`
2. Set OS to dark mode
3. Open application
4. Verify dark mode is enabled automatically
5. Set OS to light mode
6. Refresh page
7. Verify light mode is enabled automatically

**Pass Criteria:**
- ✅ Detects OS dark mode preference
- ✅ Applies dark mode automatically
- ✅ No flash of unstyled content (FOUC)
- ✅ User preference overrides OS preference

---

## 🔄 **4. REGRESSION TESTING**

### **4.1 Existing Features Test**

**Test:** Verify existing features still work

**Features to Test:**
- Employee CRUD operations
- Schedule CRUD operations
- Attendance recording
- Late time calculation
- Overtime calculation
- Holiday override
- CSV import
- Excel export
- Biometric device sync

**Pass Criteria:**
- ✅ All existing features work as before
- ✅ No broken functionality
- ✅ No new bugs introduced

---

### **4.2 Filter Functionality Test**

**Test:** Verify filters still work correctly

**Filters to Test:**
- Month filter
- Year filter
- Date range filter (From - To)
- Reset button

**Pass Criteria:**
- ✅ Month filter works
- ✅ Year filter works
- ✅ Date range filter works
- ✅ Reset button clears all filters
- ✅ Filters can be combined

---

### **4.3 Holiday Manager Test**

**Test:** Verify holiday manager still works

**Steps:**
1. Click "Holiday Manager" button
2. Verify calendar displays
3. Click a date
4. Verify override form appears
5. Change day type
6. Save override
7. Verify override is saved
8. Delete override
9. Verify override is deleted

**Pass Criteria:**
- ✅ Calendar displays correctly
- ✅ Override form works
- ✅ Save functionality works
- ✅ Delete functionality works
- ✅ Calendar updates after changes

---

## 🌍 **5. BROWSER COMPATIBILITY TESTING**

### **5.1 Desktop Browsers**

**Browsers to Test:**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

**Features to Test:**
- Language switching
- Dark mode toggle
- DataTables functionality
- Form submissions
- Modal interactions

**Pass Criteria:**
- ✅ All features work in all browsers
- ✅ No console errors
- ✅ No visual glitches
- ✅ Performance is acceptable

---

### **5.2 Mobile Browsers**

**Browsers to Test:**
- Mobile Chrome (Android)
- Mobile Safari (iOS)
- Samsung Internet
- Firefox Mobile

**Features to Test:**
- Responsive layout
- Touch interactions
- Language switching
- Dark mode toggle
- DataTables on mobile

**Pass Criteria:**
- ✅ Layout is responsive
- ✅ Touch interactions work
- ✅ All features accessible
- ✅ No horizontal scrolling

---

## 📱 **6. MOBILE RESPONSIVENESS TESTING**

### **6.1 Breakpoint Test**

**Breakpoints to Test:**
- 320px (iPhone SE)
- 375px (iPhone X)
- 768px (iPad)
- 1024px (iPad Pro)
- 1440px (Desktop)

**Pass Criteria:**
- ✅ Layout adapts to all breakpoints
- ✅ No content overflow
- ✅ Buttons are tappable (min 44x44px)
- ✅ Text is readable (min 16px)

---

### **6.2 Sidebar Collapse Test**

**Test:** Verify sidebar collapses on mobile

**Steps:**
1. Open application on mobile device
2. Verify sidebar is collapsed by default
3. Click hamburger menu
4. Verify sidebar expands
5. Click outside sidebar
6. Verify sidebar collapses

**Pass Criteria:**
- ✅ Sidebar collapses on mobile
- ✅ Hamburger menu works
- ✅ Sidebar can be toggled
- ✅ Content is accessible

---

## 📊 **TEST RESULTS TEMPLATE**

### **Test Execution Report**

**Date:** _____________  
**Tester:** _____________  
**Version:** 2.0.0  
**Environment:** _____________

| Test Category | Test Name | Status | Notes |
|---------------|-----------|--------|-------|
| Performance | Database Indexes | ☐ Pass ☐ Fail | |
| Performance | Query Performance | ☐ Pass ☐ Fail | |
| Performance | Page Load Time | ☐ Pass ☐ Fail | |
| Performance | DataTables Performance | ☐ Pass ☐ Fail | |
| Performance | Memory Usage | ☐ Pass ☐ Fail | |
| Performance | Cache Verification | ☐ Pass ☐ Fail | |
| i18n | Language Switcher | ☐ Pass ☐ Fail | |
| i18n | Translation Coverage | ☐ Pass ☐ Fail | |
| i18n | Language Persistence | ☐ Pass ☐ Fail | |
| i18n | DataTables Translation | ☐ Pass ☐ Fail | |
| i18n | Month Names Translation | ☐ Pass ☐ Fail | |
| Dark Mode | Toggle Functionality | ☐ Pass ☐ Fail | |
| Dark Mode | Component Coverage | ☐ Pass ☐ Fail | |
| Dark Mode | Persistence | ☐ Pass ☐ Fail | |
| Dark Mode | Color Contrast | ☐ Pass ☐ Fail | |
| Dark Mode | Badge Readability | ☐ Pass ☐ Fail | |
| Dark Mode | Auto Detection | ☐ Pass ☐ Fail | |
| Regression | Existing Features | ☐ Pass ☐ Fail | |
| Regression | Filter Functionality | ☐ Pass ☐ Fail | |
| Regression | Holiday Manager | ☐ Pass ☐ Fail | |
| Browser | Chrome | ☐ Pass ☐ Fail | |
| Browser | Firefox | ☐ Pass ☐ Fail | |
| Browser | Safari | ☐ Pass ☐ Fail | |
| Browser | Edge | ☐ Pass ☐ Fail | |
| Mobile | Responsive Layout | ☐ Pass ☐ Fail | |
| Mobile | Touch Interactions | ☐ Pass ☐ Fail | |
| Mobile | Sidebar Collapse | ☐ Pass ☐ Fail | |

**Overall Result:** ☐ Pass ☐ Fail

**Critical Issues Found:** _____________

**Recommendations:** _____________

---

## 🐛 **BUG REPORT TEMPLATE**

**Title:** _____________

**Severity:** ☐ Critical ☐ High ☐ Medium ☐ Low

**Category:** ☐ Performance ☐ i18n ☐ Dark Mode ☐ Regression

**Description:**
_____________

**Steps to Reproduce:**
1. _____________
2. _____________
3. _____________

**Expected Result:**
_____________

**Actual Result:**
_____________

**Screenshots:**
_____________

**Environment:**
- Browser: _____________
- OS: _____________
- Screen Size: _____________
- Language: _____________
- Theme: _____________

**Console Errors:**
```
_____________
```

---

## ✅ **SIGN-OFF**

**Tested By:** _____________  
**Date:** _____________  
**Signature:** _____________

**Approved By:** _____________  
**Date:** _____________  
**Signature:** _____________

---

**Happy Testing! 🧪**
