<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    public function __construct(private BackupService $backups) {}

    public function index(): View
    {
        return view('admin.backups.index', ['backups' => $this->backups->list()]);
    }

    public function store(): RedirectResponse
    {
        try {
            @set_time_limit(300);
            $name = $this->backups->create();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', __('Backup failed: :msg', ['msg' => $e->getMessage()]));
        }
        ActivityLogger::log('backup.created', null, "Backup $name created");

        return back()->with('success', __('Backup created.'));
    }

    public function download(string $name): StreamedResponse
    {
        $path = $this->backups->path($name);
        ActivityLogger::log('backup.downloaded', null, "Backup $name downloaded");

        return Storage::disk(BackupService::DISK)->download($path);
    }

    public function destroy(string $name): RedirectResponse
    {
        $this->backups->delete($name);
        ActivityLogger::log('backup.deleted', null, "Backup $name deleted");

        return back()->with('success', __('Backup deleted.'));
    }
}
