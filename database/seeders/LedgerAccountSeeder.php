<?php

namespace Database\Seeders;

use App\Services\LedgerService;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    public function run(): void
    {
        app(LedgerService::class)->ensureSystemAccounts();
    }
}
