<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('workerProfile', 'wallet');
        $today = Carbon::now('Africa/Abidjan');

        $week = collect(range(0, 6))->map(function (int $offset) use ($today) {
            $day = $today->copy()->startOfWeek()->addDays($offset);

            return [
                'label' => $day->translatedFormat('D'),
                'date' => $day->day,
                'active' => $day->isSameDay($today),
            ];
        });

        return view('worker.progress', [
            'user' => $user,
            'week' => $week,
            'chart' => [
                'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'completed' => [2, 4, 6, 3, 5, 1, 0],
                'earnings' => [1, 3, 5, 2, 4, 1, 0],
            ],
            'summary' => [
                'tasks_week' => $user->workerProfile?->tasks_completed_total ?? 0,
                'avg_daily' => 4,
                'validated' => 13,
                'pending' => 2,
            ],
        ]);
    }
}
