<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'mivoo:expire-subscriptions';

    protected $description = 'Expire les abonnements VIP arrivés à échéance et rétablit le niveau gratuit';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $count = $subscriptionService->expireDueSubscriptions();
        $this->info("Abonnements expirés : {$count}");

        return self::SUCCESS;
    }
}
