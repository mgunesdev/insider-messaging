<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;


class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'to' => $this->faker->e164PhoneNumber(), // Örn: +905555555555
            'content' => $this->faker->sentence(6),
            'status' => 'pending',
            'attempts' => 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn() => [
            'status' => 'sent',
            'provider_message_id' => $this->faker->uuid(),
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn() => [
            'status' => 'failed',
            'last_error' => 'Simulated failure',
        ]);
    }
}
