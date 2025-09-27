<?php

namespace App\Domain\Messages\Services;

use App\Domain\Messages\Contracts\MessageRepositoryInterface;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessageSenderService
{
    public function __construct(
        private MessageRepositoryInterface $repo
    ) {}

    public function send(Message $m): void
    {
        $maxLen = (int) config('messaging.max_length', env('MSG_MAX_LENGTH', 160));
        if (mb_strlen($m->content) > $maxLen) {
            $error = "Content exceeds limit: " . mb_strlen($m->content) . " > {$maxLen}";
            $this->repo->markFailed($m, $error);

            Log::warning("Message failed: content too long", [
                'message_id' => $m->id,
                'to'         => $m->to,
                'length'     => mb_strlen($m->content),
                'max'        => $maxLen,
            ]);
            return;
        }

        try {
            $resp = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'x-ins-auth-key' => env('MSG_AUTH_KEY'),
            ])->timeout(10)->post(env('MSG_WEBHOOK_URL'), [
                'to'      => $m->to,
                'content' => $m->content,
            ]);

            $status = $resp->status();
            $body   = $resp->body();

            // Başarı kriteri: 200 veya 202
            if (in_array($status, [200, 202])) {
                $id = data_get($resp->json(), 'messageId', uniqid('msg_'));
                $now = Carbon::now();

                $this->repo->markSent($m, $id, $now);

                Redis::setex("messages:sent:{$m->id}", 86400, json_encode([
                    'messageId' => $id,
                    'sent_at'   => $now->toIso8601String(),
                ]));

                Log::info("Message sent successfully", [
                    'message_id' => $m->id,
                    'to'         => $m->to,
                    'provider_id'=> $id,
                    'status'     => $status,
                ]);
            } else {
                $error = "HTTP {$status} - " . substr($body, 0, 1000);
                $this->repo->markFailed($m, $error);

                Log::error("Message send FAILED (bad status)", [
                    'message_id' => $m->id,
                    'to'         => $m->to,
                    'status'     => $status,
                    'body'       => $body,
                ]);
            }
        } catch (\Throwable $e) {
            $this->repo->markFailed($m, $e->getMessage());

            Log::error("Message send FAILED (exception)", [
                'message_id' => $m->id,
                'to'         => $m->to,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }
    }
}
