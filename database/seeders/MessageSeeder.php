<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        // 10 tane pending mesaj
        Message::factory()->count(10)->create([
            'status' => 'pending',
        ]);

        // 5 tane sent mesaj
        Message::factory()->count(5)->sent()->create();

        // 3 tane failed mesaj
        Message::factory()->count(3)->failed()->create();
    }
}
