<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketReplied;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'active');
        $q = SupportTicket::with('user', 'assignee')->latest('updated_at');
        if ($status === 'active') {
            $q->whereIn('status', ['open', 'customer_reply']);
        } elseif ($status !== 'all') {
            $q->where('status', $status);
        }
        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn ($q) => $q->where('ticket_number', 'like', "%$s%")->orWhere('subject', 'like', "%$s%"));
        }

        return view('admin.tickets.index', ['tickets' => $q->paginate(20)->withQueryString(), 'status' => $status]);
    }

    public function show(SupportTicket $ticket): View
    {
        return view('admin.tickets.show', [
            'ticket' => $ticket->load('replies.user', 'user'),
            'staff' => User::whereNotNull('admin_role_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:3000']]);
        $ticket->replies()->create(['user_id' => $request->user()->id, 'is_staff' => true, 'message' => strip_tags($data['message'])]);
        $ticket->forceFill(['status' => 'answered', 'assigned_to' => $ticket->assigned_to ?? $request->user()->id])->save();
        $ticket->user->notify(new TicketReplied($ticket));

        return back()->with('success', __('Reply sent.'));
    }

    public function status(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'answered', 'closed'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high'])],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->whereNotNull('admin_role_id')],
        ]);
        $ticket->forceFill($data + ['closed_at' => $data['status'] === 'closed' ? now() : null])->save();

        return back()->with('success', __('Ticket updated.'));
    }
}
