<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MissionType;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\SystemSetting;
use App\Services\CampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::query()->with('category')->latest()->paginate(20);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.campaigns.create', [
            'missionTypes' => MissionType::cases(),
            'defaultTitle' => $this->nextDefaultTitle(),
        ]);
    }

    public function store(Request $request, CampaignService $campaigns): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'mission_type' => ['required', Rule::enum(MissionType::class)],
            'target_url' => ['required', 'url', 'max:500'],
        ]);

        $type = MissionType::from($data['mission_type']);
        $category = CampaignCategory::query()->where('slug', $type->categorySlug())->firstOrFail();

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = $this->nextDefaultTitle($type);
        }

        $reward = (int) SystemSetting::getValue('default_reward_per_task_xof', 10);

        $payload = [
            'campaign_category_id' => $category->id,
            'title' => $title,
            'description' => $type->defaultDescription(),
            'instructions' => $type->defaultInstructions(),
            'validation_criteria' => 'Confirmation cochée après ouverture du lien.',
            'evidence_type' => $type->value,
            'target_url' => $data['target_url'],
            'reward_per_task_xof' => $reward,
            'min_vip_level' => 0,
            'completion_minutes' => 30,
        ];

        try {
            $campaign = $campaigns->create($request->user(), $payload);
            $campaigns->fundAndActivate($campaign, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['target_url' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.campaigns.index')
            ->with('status', 'Mission créée et activée.');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['category', 'fundings', 'assignments' => fn ($q) => $q->latest()->limit(20)]);

        return view('admin.campaigns.show', compact('campaign'));
    }

    public function activate(Campaign $campaign, Request $request, CampaignService $campaigns): RedirectResponse
    {
        $campaigns->fundAndActivate($campaign, $request->user());

        return back()->with('status', 'Campagne financée et activée.');
    }

    public function suspend(Campaign $campaign, CampaignService $campaigns): RedirectResponse
    {
        $campaigns->suspend($campaign);

        return back()->with('status', 'Mission suspendue.');
    }

    public function destroy(Campaign $campaign, CampaignService $campaigns): RedirectResponse
    {
        $campaigns->delete($campaign);

        return redirect()
            ->route('admin.campaigns.index')
            ->with('status', 'Mission supprimée du pool.');
    }

    private function nextDefaultTitle(?MissionType $type = null): string
    {
        $next = Campaign::withTrashed()->count() + 1;
        $code = str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        $prefix = match ($type) {
            MissionType::VisitWebsite => 'Mission',
            MissionType::WatchVideo => 'Tâche',
            MissionType::LikePost => 'Job',
            default => 'Mission',
        };

        return $prefix.' #'.$code;
    }
}
