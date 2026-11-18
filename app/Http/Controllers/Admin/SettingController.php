<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\TaskAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'minWithdrawal' => SystemSetting::getValue('min_withdrawal_xof', 1000),
            'maxWithdrawal' => SystemSetting::getValue('max_withdrawal_xof', 0),
            'withdrawalsEnabled' => (bool) SystemSetting::getValue('withdrawals_enabled', true),
            'minDwellSeconds' => SystemSetting::getValue('mission_min_dwell_seconds', 12),
            'maxTasksPerDay' => min(TaskAssignmentService::HARD_DAILY_MAX, (int) SystemSetting::getValue('max_tasks_per_day', 10)),
            'dailyMissionsMode' => (string) SystemSetting::getValue('daily_missions_mode', 'fixed'),
            'dailyMissionsCount' => (int) SystemSetting::getValue('daily_missions_count', 10),
            'paymentDriver' => config('payments.default'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_withdrawal_xof' => ['required', 'integer', 'min:1000'],
            'max_withdrawal_xof' => ['nullable', 'integer', 'min:0'],
            'max_tasks_per_day' => ['required', 'integer', 'min:1', 'max:'.TaskAssignmentService::HARD_DAILY_MAX],
            'daily_missions_mode' => ['required', Rule::in(['fixed', 'random'])],
            'daily_missions_count' => ['required', 'integer', 'min:1', 'max:'.TaskAssignmentService::HARD_DAILY_MAX],
            'mission_min_dwell_seconds' => ['required', 'integer', 'min:3', 'max:300'],
            'referral_commission_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'withdrawal_fee_xof' => ['required', 'integer', 'min:0'],
            'withdrawals_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['withdrawals_enabled'] = $request->boolean('withdrawals_enabled');
        $validated['max_withdrawal_xof'] = (int) ($validated['max_withdrawal_xof'] ?? 0);
        $validated['max_tasks_per_day'] = min(TaskAssignmentService::HARD_DAILY_MAX, (int) $validated['max_tasks_per_day']);
        $validated['daily_missions_count'] = min(TaskAssignmentService::HARD_DAILY_MAX, (int) $validated['daily_missions_count']);

        foreach ($validated as $key => $value) {
            $type = match ($key) {
                'withdrawals_enabled' => 'boolean',
                'daily_missions_mode' => 'string',
                default => 'integer',
            };

            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => $type,
                    'group' => match (true) {
                        str_starts_with($key, 'min_withdrawal'),
                        str_starts_with($key, 'max_withdrawal'),
                        str_starts_with($key, 'withdrawal') => 'withdrawals',
                        str_starts_with($key, 'referral') => 'referrals',
                        default => 'tasks',
                    },
                ]
            );
            SystemSetting::forgetCache($key);
        }

        return back()->with('status', 'Paramètres enregistrés.');
    }
}
