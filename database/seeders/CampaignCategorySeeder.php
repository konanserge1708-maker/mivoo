<?php

namespace Database\Seeders;

use App\Enums\MissionType;
use App\Models\CampaignCategory;
use Illuminate\Database\Seeder;

class CampaignCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (MissionType::cases() as $type) {
            CampaignCategory::query()->updateOrCreate(
                ['slug' => $type->categorySlug()],
                [
                    'name' => $type->label(),
                    'description' => $type->defaultDescription(),
                    'is_active' => true,
                ]
            );
        }

        // Anciennes catégories : on les désactive pour ne laisser que les 3 types actifs.
        CampaignCategory::query()
            ->whereNotIn('slug', collect(MissionType::cases())->map->categorySlug()->all())
            ->update(['is_active' => false]);
    }
}
