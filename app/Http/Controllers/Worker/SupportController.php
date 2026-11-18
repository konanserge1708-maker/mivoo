<?php

namespace App\Http\Controllers\Worker;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('worker.support.index', compact('tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:general,payment,task,account'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'status' => SupportTicketStatus::Open,
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'is_staff' => false,
            'body' => $data['body'],
        ]);

        return redirect()
            ->route('worker.support.show', $ticket)
            ->with('status', 'Ticket créé.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
        $ticket->load('messages.user');

        return view('worker.support.show', compact('ticket'));
    }
}
