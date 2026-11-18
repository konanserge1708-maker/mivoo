<x-layouts.admin title="Utilisateurs">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-3xl font-extrabold text-mivoo-ink">Utilisateurs</h1>
        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" class="mivoo-input" placeholder="Email, tél, nom…">
            <x-ui.button type="submit" variant="secondary">Chercher</x-ui.button>
        </form>
    </div>
    <div class="overflow-hidden rounded-[1.75rem] bg-white/90 shadow-mivoo">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-mivoo-soft text-mivoo-muted">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">VIP</th>
                    <th class="px-4 py-3">Solde</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-t border-mivoo-soft">
                        <td class="px-4 py-3 font-semibold">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->activeSubscription?->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ number_format($user->wallet?->available_balance_xof ?? 0, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3">{{ $user->status->label() }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-mivoo-violet">Voir</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
