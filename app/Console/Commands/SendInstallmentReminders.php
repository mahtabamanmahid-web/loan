<?php

namespace App\Console\Commands;

use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Models\LoanInstallment;
use App\Notifications\InstallmentReminder;
use App\Services\LoanService;
use Illuminate\Console\Command;

/**
 * Runs daily (see routes/console.php):
 *  1. marks overdue installments and applies late fees
 *  2. reminds customers N days before the due date (and on overdue installments)
 */
class SendInstallmentReminders extends Command
{
    protected $signature = 'loans:reminders';

    protected $description = 'Mark overdue installments and send installment reminders';

    public function handle(LoanService $loans): int
    {
        $overdue = $loans->markOverdueInstallments();
        $this->info("Overdue installments updated: $overdue");

        $days = (int) sys_setting('reminder_days_before');
        $sent = 0;

        LoanInstallment::query()
            ->where('status', '!=', InstallmentStatus::Paid->value)
            ->whereDate('due_date', '<=', today()->addDays($days))
            ->where(fn ($q) => $q->whereNull('reminder_sent_at')->orWhereDate('reminder_sent_at', '<', today()->subDays(2)))
            ->whereHas('loan', fn ($q) => $q->where('status', LoanStatus::Active->value))
            ->with('loan.user')
            ->chunkById(200, function ($installments) use (&$sent) {
                foreach ($installments as $inst) {
                    $inst->loan->user->notify(new InstallmentReminder($inst));
                    $inst->update(['reminder_sent_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Reminders sent: $sent");

        return self::SUCCESS;
    }
}
