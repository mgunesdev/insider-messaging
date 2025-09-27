<?php

namespace Tests\Unit;

use App\Domain\Messages\Contracts\MessageRepositoryInterface;
use App\Domain\Messages\Services\MessageSenderService;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class MessageSenderServiceTest extends TestCase
{
    /** @test */
    public function it_sets_status_sent_on_success_response(): void
    {
        $mockRepo = Mockery::mock(MessageRepositoryInterface::class);
        $service  = new MessageSenderService($mockRepo);

        $message = new Message(['id' => 1, 'to' => '+905551111111', 'content' => 'Hello']);

        $mockRepo->shouldReceive('markSent')
            ->once()
            ->with($message, 'abc-123', Mockery::type(Carbon::class));

        Http::fake([
            env('MSG_WEBHOOK_URL') => Http::response([
                'message'   => 'Accepted',
                'messageId' => 'abc-123',
            ], 202),
        ]);

        $service->send($message);
    }

    /** @test */
    public function it_sets_status_failed_on_error_response(): void
    {
        $mockRepo = Mockery::mock(MessageRepositoryInterface::class);
        $service  = new MessageSenderService($mockRepo);

        $message = new Message(['id' => 2, 'to' => '+905551111111', 'content' => 'Hello']);

        $mockRepo->shouldReceive('markFailed')
            ->once()
            ->with($message, Mockery::type('string'));

        Http::fake([
            env('MSG_WEBHOOK_URL') => Http::response('Bad Request', 400),
        ]);

        $service->send($message);
    }

    /** @test */
    public function it_sets_status_failed_when_content_too_long(): void
    {
        $mockRepo = Mockery::mock(MessageRepositoryInterface::class);
        $service  = new MessageSenderService($mockRepo);

        $longText = str_repeat('x', config('messaging.max_length', 160) + 5);
        $message  = new Message(['id' => 3, 'to' => '+905551111111', 'content' => $longText]);

        $mockRepo->shouldReceive('markFailed')
            ->once()
            ->with($message, Mockery::type('string'));

        $service->send($message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
