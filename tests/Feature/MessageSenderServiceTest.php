<?php

namespace Tests\Feature;

use App\Domain\Messages\Contracts\MessageRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domain\Messages\Services\MessageSenderService;
use App\Models\Message;
use Illuminate\Support\Facades\Http;

class MessageSenderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MessageSenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MessageSenderService(app(MessageRepositoryInterface::class));
    }

    /** @test */
    public function it_marks_message_as_sent_when_api_returns_202_with_message_id()
    {
        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => 'abc-123',
            ], 202),
        ]);

        $message = Message::factory()->create([
            'status' => 'pending',
            'content' => 'Hello Test',
        ]);

        $this->service->send($message->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'sent',
            'provider_message_id' => 'abc-123',
        ]);
    }

    /** @test */
    public function it_marks_message_as_failed_if_content_too_long()
    {
        $message = Message::factory()->create([
            'content' => str_repeat('x', config('messaging.max_length', 160) + 5),
        ]);

        $this->service->send($message->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_marks_message_as_failed_if_api_returns_error()
    {
        Http::fake([
            '*' => Http::response('Bad Request', 400),
        ]);

        $message = Message::factory()->create();

        $this->service->send($message->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'failed',
        ]);
    }
}
