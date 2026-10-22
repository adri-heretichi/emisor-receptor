
<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily_json'],
            'ignore_exceptions' => false,
        ],

        // General en JSON con rotación diaria
        'daily_json' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'warning'),
            'days' => 14,
            'tap' => [App\Logging\CustomizeFormatter::class],
        ],

        // SQL dedicado
        'sql' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql.log'),
            'level' => 'info',
            'days' => 7,
            'tap' => [App\Logging\CustomizeFormatter::class],
        ],

        // Broadcasting / WebSocket
        'broadcast' => [
            'driver' => 'daily',
            'path' => storage_path('logs/broadcast.log'),
            'level' => 'info',
            'days' => 7,
            'tap' => [App\Logging\CustomizeFormatter::class],
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],
];
