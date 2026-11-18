<?php

namespace Database\Seeders;

use App\Enums\MissionType;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\User;
use App\Services\CampaignService;
use Illuminate\Database\Seeder;

class DemoCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@mivoo.test')->first()
            ?? User::query()->role('admin')->first()
            ?? User::query()->role('super_admin')->first();

        if (! $admin) {
            $this->command?->warn('Aucun admin trouvé : missions démo non créées.');

            return;
        }

        $service = app(CampaignService::class);

        $demos = [
            [
                'type' => MissionType::VisitWebsite,
                'title' => 'Mission #101 — Visiter Mivoo',
                'target_url' => url('/'),
                'reward' => 10,
                'description' => 'Ouvrez le lien, consultez le site, puis confirmez votre visite.',
                'instructions' => "1. Cliquez sur « Ouvrir le site »\n2. Parcourez la page quelques secondes\n3. Revenez ici et cochez « J’ai visité le site »\n4. Validez la mission",
            ],
            [
                'type' => MissionType::VisitWebsite,
                'title' => 'Mission #102 — Visiter Google',
                'target_url' => 'https://www.google.com/',
                'reward' => 10,
                'description' => 'Ouvrez le lien, consultez le site, puis confirmez votre visite.',
                'instructions' => "1. Cliquez sur « Ouvrir le site »\n2. Parcourez la page quelques secondes\n3. Revenez ici et cochez « J’ai visité le site »\n4. Validez la mission",
            ],
            [
                'type' => MissionType::WatchVideo,
                'title' => 'Tâche #201 — Regarder une vidéo YouTube',
                'target_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'reward' => 10,
                'description' => 'Ouvrez le lien, regardez la vidéo, puis confirmez.',
                'instructions' => "1. Cliquez sur « Ouvrir la vidéo »\n2. Regardez la vidéo\n3. Revenez ici et cochez « J’ai regardé la vidéo »\n4. Validez la mission",
            ],
            [
                'type' => MissionType::WatchVideo,
                'title' => 'Tâche #202 — Regarder une vidéo courte',
                'target_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'reward' => 10,
                'description' => 'Ouvrez le lien, regardez la vidéo, puis confirmez.',
                'instructions' => "1. Cliquez sur « Ouvrir la vidéo »\n2. Regardez la vidéo\n3. Revenez ici et cochez « J’ai regardé la vidéo »\n4. Validez la mission",
            ],
            [
                'type' => MissionType::LikePost,
                'title' => 'Job #301 — Liker un post X',
                'target_url' => 'https://x.com/',
                'reward' => 10,
                'description' => 'Ouvrez le post, mettez un like, puis confirmez.',
                'instructions' => "1. Cliquez sur « Ouvrir le post »\n2. Mettez un like sur le post\n3. Revenez ici et cochez « J’ai liké le post »\n4. Validez la mission",
            ],
            [
                'type' => MissionType::LikePost,
                'title' => 'Job #302 — Liker une page Facebook',
                'target_url' => 'https://www.facebook.com/',
                'reward' => 10,
                'description' => 'Ouvrez le post, mettez un like, puis confirmez.',
                'instructions' => "1. Cliquez sur « Ouvrir le post »\n2. Mettez un like sur le post\n3. Revenez ici et cochez « J’ai liké le post »\n4. Validez la mission",
            ],
        ];

        $created = 0;

        foreach ($demos as $demo) {
            /** @var MissionType $type */
            $type = $demo['type'];
            $category = CampaignCategory::query()->where('slug', $type->categorySlug())->first();

            if (! $category) {
                $this->command?->warn('Catégorie manquante : '.$type->categorySlug());

                continue;
            }

            if (Campaign::withTrashed()->where('title', $demo['title'])->exists()) {
                continue;
            }

            $campaign = $service->create($admin, [
                'campaign_category_id' => $category->id,
                'title' => $demo['title'],
                'description' => $demo['description'],
                'instructions' => $demo['instructions'],
                'validation_criteria' => 'Confirmation cochée après ouverture du lien et délai minimum.',
                'evidence_type' => $type->value,
                'target_url' => $demo['target_url'],
                'reward_per_task_xof' => $demo['reward'],
                'min_vip_level' => 0,
                'completion_minutes' => 30,
            ]);

            $service->fundAndActivate($campaign, $admin, 'SEED-DEMO-'.$campaign->id);
            $created++;
        }

        $this->command?->info("Missions démo créées : {$created}");
    }
}
