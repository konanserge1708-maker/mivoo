<x-layouts.worker title="Votre progression" active="progress">
    <header class="mb-6 flex items-center justify-between fade-up">
        <a href="{{ route('worker.dashboard') }}" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/80 shadow-sm" aria-label="Retour">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <a href="{{ route('worker.notifications') }}" class="relative flex h-11 w-11 items-center justify-center rounded-full bg-white/80 shadow-sm" aria-label="Notifications">
            <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-mivoo-danger"></span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
        </a>
    </header>

    <section class="mb-6 fade-up fade-up-delay-1">
        <h1 class="text-4xl font-extrabold tracking-tight text-mivoo-ink">Votre progression</h1>
        <p class="mt-2 text-sm text-mivoo-muted">Suivi des missions validées et des gains.</p>
    </section>

    <div class="mivoo-scroll-x mb-5 flex gap-2 pb-1 fade-up fade-up-delay-2">
        @foreach ($week as $day)
            <div class="flex min-w-[3.4rem] flex-col items-center rounded-2xl px-3 py-3 {{ $day['active'] ? 'bg-mivoo-ink text-white' : 'bg-white/70 text-mivoo-muted' }}">
                <span class="text-xs font-semibold uppercase">{{ $day['label'] }}</span>
                <span class="mt-1 text-lg font-bold">{{ $day['date'] }}</span>
            </div>
        @endforeach
    </div>

    <x-ui.card class="mb-5 fade-up fade-up-delay-2">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-semibold text-mivoo-ink">Mode focus actif</p>
                <p class="mt-1 text-sm text-mivoo-muted">Moyenne : {{ $summary['avg_daily'] }} missions / jour</p>
            </div>
            <span class="rounded-full bg-mivoo-violet/15 px-3 py-1 text-xs font-semibold text-mivoo-violet-deep">Cette semaine</span>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-5 fade-up fade-up-delay-3">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm text-mivoo-muted">Activité hebdomadaire</p>
                <p class="mt-1 text-2xl font-extrabold text-mivoo-ink">Missions & gains</p>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-semibold text-mivoo-muted">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-mivoo-violet"></span> Missions
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-mivoo-lilac"></span> Gains
                </span>
            </div>
        </div>
        @php
            $maxCompleted = max(1, max($chart['completed']));
            $maxEarnings = max(1, max($chart['earnings']));
            $todayIndex = now('Africa/Abidjan')->dayOfWeekIso - 1; // 0 = lundi
        @endphp
        <div class="mivoo-chart">
            <div class="mivoo-chart-plot">
                @foreach ($chart['completed'] as $index => $value)
                    @php
                        $earningsValue = $chart['earnings'][$index] ?? 0;
                        $completedHeight = $value === 0 ? 6 : (int) max(10, round(($value / $maxCompleted) * 100));
                        $earningsHeight = $earningsValue === 0 ? 6 : (int) max(10, round(($earningsValue / $maxEarnings) * 100));
                        $delay = $index * 70;
                        $isActive = $index === $todayIndex;
                    @endphp
                    <div class="mivoo-chart-col {{ $isActive ? 'is-active' : '' }}" style="--bar-delay: {{ $delay }}ms">
                        <div class="mivoo-chart-bars">
                            <div class="chart-bar" style="--bar-h: {{ $completedHeight }}%; --bar-delay: {{ $delay }}ms" title="{{ $value }} missions"></div>
                            <div class="chart-bar chart-bar-alt" style="--bar-h: {{ $earningsHeight }}%; --bar-delay: {{ $delay + 40 }}ms" title="Gains j{{ $index + 1 }}"></div>
                        </div>
                        <span class="mivoo-chart-label {{ $isActive ? 'text-mivoo-ink' : '' }}">{{ $chart['labels'][$index] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-2 gap-3 fade-up fade-up-delay-4">
        <x-ui.stat-card label="Validées" :value="$summary['validated']" tone="peach" />
        <x-ui.stat-card label="Total carrière" :value="$summary['tasks_week']" tone="sky" />
    </div>
</x-layouts.worker>
