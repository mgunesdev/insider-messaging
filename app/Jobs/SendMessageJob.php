<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Message;
use App\Domain\Messages\Services\MessageSenderService;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public int $messageId)
    {
    }

    public function handle(MessageSenderService $service): void
    {
        $message = Message::find($this->messageId);
        if (!$message || $message->status !== 'pending') {
            return;
        }

        $allow = (int)config('messaging.rate.allow', 2);
        $every = (int)config('messaging.rate.every', 5);

        Redis::throttle('global-message-send')
            ->allow($allow)->every($every)->block(0)
            ->then(function () use ($service, $message) {
                try {
                    $service->send($message);

                    Log::info('Message sent successfully', [
                        'message_id'   => $message->id,
                        'to'           => $message->to,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Message send FAILED (exception)', [
                        'message_id' => $message->id,
                        'to'         => $message->to,
                        'error'      => $e->getMessage(),
                    ]);

                    // tekrar denensin istiyorsak:
                    throw $e;

                    // eğer direkt fail olsun istiyorsak:
                    // $this->fail($e);
                }
            }, function () {
                // Slot yoksa kısa gecikme ile tekrar dene
                $this->release(2);
            });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Queue job permanently failed", [
            'job'        => self::class,
            'message_id' => $this->messageId,
            'error'      => $exception->getMessage(),
        ]);
    }
}
