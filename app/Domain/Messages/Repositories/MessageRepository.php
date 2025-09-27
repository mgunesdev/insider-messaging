<?php

namespace App\Domain\Messages\Repositories;

use App\Domain\Messages\Contracts\MessageRepositoryInterface;
use App\Models\Message;
use Illuminate\Support\Str;

class MessageRepository implements MessageRepositoryInterface
{
    public function findPendingForDispatch(int $limit = 100): \Illuminate\Support\Collection
    {
        return Message::where('status','pending')->orderBy('id')->limit($limit)->get();
    }
    public function markSent(Message $m, string $pid, \Carbon\Carbon $at): void {
        $m->update([
            'status' => 'sent',
            'provider_message_id' => $pid,
            'sent_at' => $at,
            'last_error' => null
        ]);
    }
    public function markFailed(Message $m, string $err): void {
        $m->update([
            'status' => 'failed',
            'last_error' => Str::limit($err, 3000)
        ]);
    }
}
