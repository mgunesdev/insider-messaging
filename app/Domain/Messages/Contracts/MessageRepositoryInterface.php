<?php

namespace App\Domain\Messages\Contracts;

use App\Models\Message;

interface MessageRepositoryInterface {
    public function findPendingForDispatch(int $limit = 100): \Illuminate\Support\Collection;
    public function markSent(Message $message, string $providerId, \Carbon\Carbon $sentAt): void;
    public function markFailed(Message $message, string $error): void;
}
