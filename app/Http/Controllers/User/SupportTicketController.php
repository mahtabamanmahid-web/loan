<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\ReferenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public const CATEGORIES = ['general', 'loan', 'payment', 'kyc', 'technical'];

    public function index(Request $request): View
    {
        return view('user.tickets.index', ['tickets' => $request->user()->tickets()->withCount('replies')->paginate(15)]);
    }

    public function create(): View
    {
        return view('user.tickets.create');
    }

    public function store(Request $request, ReferenceGenerator $refs): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data, $refs) {
            $ticket = $request->user()->tickets()->create([
                'ticket_number' => $refs->make('TKT', 'support_tickets', 'ticket_number'),
                'subject' => strip_tags($data['subject']),
                'category' => $data['category'],
                'status' => 'open',
            ]);
            $ticket->replies()->create(['user_id' => $request->user()->id, 'message' => strip_tags($data['message'])]);

            return $ticket;
        });

        return redirect()->route('user.tickets.show', $ticket)->with('success', __('Ticket created. Our team will reply soon.'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->ensureOwner($ticket);

        return view('user.tickets.show', ['ticket' => $ticket->load('replies.user')]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->ensureOwner($ticket);
        abort_if($ticket->status === 'closed', 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:3000']]);
        $ticket->replies()->create(['user_id' => $request->user()->id, 'message' => strip_tags($data['message'])]);
        $ticket->update(['status' => 'customer_reply']);

        return back()->with('success', __('Reply sent.'));
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        $this->ensureOwner($ticket);
        $ticket->forceFill(['status' => 'closed', 'closed_at' => now()])->save();

        return back()->with('success', __('Ticket closed.'));
    }
}
