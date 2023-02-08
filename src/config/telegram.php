<?php

return [
    'call_api' => env('TELEGRAM_CALL_API', 'https://api.telegram.org/bot'),

    'bot' => [
        'default' => [
            'callback'  => env('TELEGRAM_DEFAULT_CALLBACK'),    // Site Callback URL
            'token'     => env('TELEGRAM_DEFAULT_TOKEN'),       // Bot token
            'username'  => env('TELEGRAM_DEFAULT_USERNAME'),    // Bot username
            'admin'     => env('TELEGRAM_DEFAULT_ADMIN')        // Admin Telegram ID
        ],
        // ...
    ],

    'default_header' => [
        'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36',
        'accept'        => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
    ],

    'settings' => [
        'auth'          => true,
        'send_sms'      => true,
        'send_mail'     => false,
        'confirm_sms'   => true,
        'confirm_mail'  => false,
    ],

];

?>
