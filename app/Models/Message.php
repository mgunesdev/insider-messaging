<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'to','content','status','provider_message_id','sent_at','attempts','last_error'
    ];
    protected $casts = ['sent_at' => 'datetime'];
}
