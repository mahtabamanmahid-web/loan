<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', ['announcements' => Announcement::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['announcement' => new Announcement(['is_active' => true, 'type' => 'info', 'audience' => 'all'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('success', __('Announcement saved.'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('success', __('Announcement saved.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', __('Announcement deleted.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:500'],
            'type' => ['required', Rule::in(['info', 'success', 'warning', 'danger'])],
            'audience' => ['required', Rule::in(['all', 'public', 'customers'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
