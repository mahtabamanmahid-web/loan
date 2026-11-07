<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
| On the server add ONE cron entry:
|   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
*/

// Overdue marking + installment reminders every morning at 9:00 (Asia/Dhaka)
Schedule::command('loans:reminders')->dailyAt('09:00')->withoutOverlapping();
