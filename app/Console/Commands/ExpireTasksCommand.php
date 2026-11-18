<?php

namespace App\Console\Commands;

use App\Services\TaskAssignmentService;
use Illuminate\Console\Command;

class ExpireTasksCommand extends Command
{
    protected $signature = 'mivoo:expire-tasks';

    protected $description = 'Expire les missions non terminées dans les délais';

    public function handle(TaskAssignmentService $assignments): int
    {
        $count = $assignments->expireOverdue();
        $this->info("Missions expirées : {$count}");

        return self::SUCCESS;
    }
}
