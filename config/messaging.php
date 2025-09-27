<?php


return [
    'max_length' => (int) env('MSG_MAX_LENGTH', 160),
    'rate' => ['allow' => 2, 'every' => 5], // 2 mesaj / 5 saniye
];
