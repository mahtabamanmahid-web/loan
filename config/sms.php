<?php

/*
| SMS gateway configuration (OTP & reminders).
| driver = "log"  -> messages are written to storage/logs/laravel.log (development)
| driver = "http" -> a generic HTTP GET/POST gateway (most Bangladeshi SMS providers
|                    such as BulkSMSBD, SSL Wireless, Alpha SMS accept this format).
*/
return [
    'driver' => env('SMS_DRIVER', 'log'),
    'api_url' => env('SMS_API_URL'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID'),
    'method' => env('SMS_HTTP_METHOD', 'POST'),
    // Parameter names expected by your provider
    'params' => [
        'api_key' => env('SMS_PARAM_API_KEY', 'api_key'),
        'sender' => env('SMS_PARAM_SENDER', 'senderid'),
        'to' => env('SMS_PARAM_TO', 'number'),
        'message' => env('SMS_PARAM_MESSAGE', 'message'),
    ],
];
