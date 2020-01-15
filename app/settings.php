<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => getenv('ENV') === 'production' ? false : true,
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
            'phpfastcache' => [
                'config' => [
                    'path' => sys_get_temp_dir(),
                    'itemDetailedDate' => false
                ],
                'driver' => 'files'
            ],
            'twitter' => [
                'key' => getenv('CONSUMER_KEY'),
                'secret' => getenv('CONSUMER_SECRET'),
                'token' => getenv('ACCESS_TOKEN'),
                'token_secret' => getenv('ACCESS_TOKEN_SECRET'),
            ]
        ],
    ]);
};
