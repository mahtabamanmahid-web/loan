<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;

/** Writes admin audit-log entries: who did what, to which record, from where. */
class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        $request = request();

        AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 500),
        ]);
    }
}
