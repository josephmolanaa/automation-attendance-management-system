# 📊 Performance Monitoring Guide - AMS v2.0.0

## 🎯 **OVERVIEW**

This guide helps you monitor and maintain the performance improvements in v2.0.0.

**Key Metrics to Monitor:**
- Page load time
- Database query count
- Memory usage
- Cache hit rate
- Error rate

---

## 📈 **PERFORMANCE BASELINES**

### **Expected Performance (v2.0.0):**

| Metric | Target | Acceptable | Critical |
|--------|--------|------------|----------|
| Page Load Time | < 1.5s | < 2.0s | > 3.0s |
| DB Queries/Page | < 40 | < 50 | > 80 |
| Memory Usage | < 25MB | < 30MB | > 40MB |
| DataTables Load | < 0.8s | < 1.0s | > 1.5s |
| Cache Hit Rate | > 90% | > 80% | < 70% |
| Error Rate | 0% | < 0.1% | > 1% |

---

## 🔍 **MONITORING TOOLS**

### **1. Laravel Telescope (Recommended)**

**Installation:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Configuration:**
```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', false),

// .env
TELESCOPE_ENABLED=true  // Only in development!
```

**Access:** `https://your-app.com/telescope`

**What to Monitor:**
- Requests (response time, memory)
- Queries (count, duration)
- Cache (hits, misses)
- Exceptions (errors)

---

### **2. Laravel Debugbar (Development)**

**Installation:**
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Usage:**
- Automatically shows at bottom of page
- Shows queries, memory, time
- Click tabs for detailed info

---

### **3. Database Query Logging**

**Enable in .env:**
```env
DB_LOG_QUERIES=true
LOG_LEVEL=debug
```

**View logs:**
```bash
tail -f storage/logs/laravel.log | grep "select"
```

**Analyze queries:**
```php
// In controller
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

// Your code here

$queries = DB::getQueryLog();
dd($queries);
```

---

### **4. New Relic (Production)**

**Installation:**
```bash
# Install New Relic PHP agent
wget -O - https://download.newrelic.com/548C16BF.gpg | sudo apt-key add -
sudo apt-get update
sudo apt-get install newrelic-php5
```

**Configuration:**
```ini
; php.ini
newrelic.appname = "AMS Production"
newrelic.license = "YOUR_LICENSE_KEY"
```

**Dashboard:** https://one.newrelic.com

---

### **5. Custom Performance Logging**

**Create Performance Logger:**

```php
// app/Helpers/PerformanceLogger.php
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceLogger
{
    private static $startTime;
    private static $startMemory;
    
    public static function start()
    {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
        DB::enableQueryLog();
    }
    
    public static function end($label = 'Performance')
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $duration = round(($endTime - self::$startTime) * 1000, 2);
        $memory = round(($endMemory - self::$startMemory) / 1024 / 1024, 2);
        $queries = count(DB::getQueryLog());
        
        Log::info("[$label] Duration: {$duration}ms, Memory: {$memory}MB, Queries: {$queries}");
        
        return [
            'duration' => $duration,
            'memory' => $memory,
            'queries' => $queries,
        ];
    }
}
```

**Usage:**
```php
use App\Helpers\PerformanceLogger;

PerformanceLogger::start();

// Your code here

$metrics = PerformanceLogger::end('Attendance Page');
```

---

## 📊 **MONITORING QUERIES**

### **Check Query Count:**

```php
// In AttendanceController
public function ajaxData(Request $request)
{
    DB::enableQueryLog();
    
    // Your existing code
    
    $queries = DB::getQueryLog();
    Log::info('Attendance Query Count: ' . count($queries));
    
    return response()->json(['data' => $data]);
}
```

### **Identify N+1 Problems:**

```bash
# Install Laravel N+1 Detector
composer require beyondcode/laravel-query-detector --dev
```

**Configuration:**
```php
// config/querydetector.php
'enabled' => env('QUERY_DETECTOR_ENABLED', true),
```

---

## 🗄️ **MONITORING DATABASE**

### **Check Index Usage:**

```sql
-- Check if indexes are being used
EXPLAIN SELECT * FROM checks WHERE emp_id = 123;

-- Should show "Using index" in Extra column
-- Should NOT show "Using filesort" or "Using temporary"
```

### **Find Slow Queries:**

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;  -- Log queries > 1 second

-- View slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;
```

### **Check Index Efficiency:**

```sql
-- Check index cardinality
SHOW INDEX FROM checks;

-- High cardinality = good
-- Low cardinality = index may not be useful
```

---

## 💾 **MONITORING CACHE**

### **Check Cache Status:**

```php
// In tinker
php artisan tinker

>>> cache()->has('all_schedules')
// Should return true after first page load

>>> cache()->get('all_schedules')
// Should return collection

>>> cache()->store('file')->getDirectory()
// Shows cache directory
```

### **Monitor Cache Hit Rate:**

```php
// Create CacheMonitor middleware
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheMonitor
{
    private static $hits = 0;
    private static $misses = 0;
    
    public function handle($request, Closure $next)
    {
        // Monitor cache operations
        Cache::macro('monitoredGet', function($key, $default = null) {
            if (Cache::has($key)) {
                CacheMonitor::$hits++;
            } else {
                CacheMonitor::$misses++;
            }
            return Cache::get($key, $default);
        });
        
        $response = $next($request);
        
        // Log cache stats
        $total = self::$hits + self::$misses;
        if ($total > 0) {
            $hitRate = round((self::$hits / $total) * 100, 2);
            Log::info("Cache Hit Rate: {$hitRate}% (Hits: " . self::$hits . ", Misses: " . self::$misses . ")");
        }
        
        return $response;
    }
}
```

---

## 🚨 **ALERTING**

### **Set Up Alerts:**

**1. Email Alerts for Slow Pages:**

```php
// In AppServiceProvider
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

public function boot()
{
    // Alert if page takes > 3 seconds
    if (app()->environment('production')) {
        $this->app->terminating(function () {
            $duration = microtime(true) - LARAVEL_START;
            
            if ($duration > 3) {
                Log::warning("Slow page detected: {$duration}s");
                
                // Send email alert
                Mail::raw(
                    "Page took {$duration}s to load\nURL: " . request()->url(),
                    function ($message) {
                        $message->to('admin@ams.com')
                                ->subject('Slow Page Alert');
                    }
                );
            }
        });
    }
}
```

**2. Slack Alerts:**

```bash
composer require laravel/slack-notification-channel
```

```php
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

// Send alert
Notification::route('slack', env('SLACK_WEBHOOK_URL'))
    ->notify(new SlowPageNotification($duration));
```

---

## 📈 **PERFORMANCE REPORTS**

### **Daily Performance Report:**

```php
// app/Console/Commands/PerformanceReport.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceReport extends Command
{
    protected $signature = 'performance:report';
    protected $description = 'Generate daily performance report';
    
    public function handle()
    {
        // Get average response time from logs
        $logs = file_get_contents(storage_path('logs/laravel.log'));
        
        // Parse logs and calculate metrics
        // ...
        
        $this->info('Performance Report Generated');
    }
}
```

**Schedule:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('performance:report')
             ->daily()
             ->at('23:00');
}
```

---

## 🔧 **OPTIMIZATION TIPS**

### **If Performance Degrades:**

**1. Check Database Indexes:**
```sql
SHOW INDEX FROM checks;
```

**2. Clear All Caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

**3. Rebuild Caches:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**4. Optimize Composer:**
```bash
composer dump-autoload --optimize
```

**5. Check for N+1 Queries:**
```php
// Use Laravel Debugbar or Telescope
// Look for repeated queries
```

**6. Increase Cache TTL:**
```php
// If cache hit rate is low
cache()->remember('all_schedules', 7200, function() {
    return Schedule::all();
});
```

---

## 📊 **PERFORMANCE DASHBOARD**

### **Create Custom Dashboard:**

```blade
{{-- resources/views/admin/performance.blade.php --}}
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Avg Response Time</h5>
                <h2>{{ $avgResponseTime }}ms</h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Avg Query Count</h5>
                <h2>{{ $avgQueryCount }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Cache Hit Rate</h5>
                <h2>{{ $cacheHitRate }}%</h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Error Rate</h5>
                <h2>{{ $errorRate }}%</h2>
            </div>
        </div>
    </div>
</div>
```

---

## ✅ **MONITORING CHECKLIST**

### **Daily:**
- [ ] Check error logs
- [ ] Review slow query log
- [ ] Monitor response times
- [ ] Check cache hit rate

### **Weekly:**
- [ ] Review performance trends
- [ ] Analyze query patterns
- [ ] Check database size
- [ ] Review memory usage

### **Monthly:**
- [ ] Generate performance report
- [ ] Compare with baselines
- [ ] Identify optimization opportunities
- [ ] Update documentation

---

## 📞 **SUPPORT**

If performance degrades:

1. Check this guide first
2. Review logs: `storage/logs/laravel.log`
3. Check database: Run EXPLAIN on slow queries
4. Contact support: support@ams.com

---

**Happy Monitoring! 📊**
