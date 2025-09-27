<?php

namespace App\Console\Commands;

use App\Domain\Messages\Contracts\MessageRepositoryInterface as Repo;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;

class MessagesDispatchPending extends Command
{
    protected $signature = 'messages:dispatch-pending {--limit=500}';
    protected $description = 'Queue all pending messages';

    public function handle(Repo $repo): int
    {
        $limit = (int)$this->option('limit');
        $pending = $repo->findPendingForDispatch($limit);
        foreach ($pending as $m) {
            SendMessageJob::dispatch($m->id)->onQueue('messages');
        }
        $this->info("Queued " . $pending->count() . " messages.");
        return self::SUCCESS;
    }
}
