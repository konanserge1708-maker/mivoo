<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::query()->with('user')->latest()->paginate(25);

        return view('admin.support.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'messages.user']);

        return view('admin.support.show', compact('ticket'));
    }

    public function reply(SupportTicket $ticket, Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'is_staff' => true,
            'body' => $data['body'],
        ]);

        $ticket->forceFill(['status' => SupportTicketStatus::InProgress])->save();

        return back()->with('status', 'Réponse envoyée.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        $ticket->forceFill([
            'status' => SupportTicketStatus::Closed,
            'closed_at' => now(),
        ])->save();

        return back()->with('status', 'Ticket fermé.');
    }
}
