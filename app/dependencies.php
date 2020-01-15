<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use mpyw\Cowitter\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use yagamuu\TwitterClientForRtainjapan\Twitter;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        Twitter::class => function (ContainerInterface $c) {
            $twitterSettings = $c->get('settings')['twitter'];
            $coClient = new Client([
                $twitterSettings['key'],
                $twitterSettings['secret'],
                $twitterSettings['token'],
                $twitterSettings['token_secret'],
            ]);
            $cacheSettings = $c->get('settings')['phpfastcache'];
            CacheManager::setDefaultConfig(new Config($cacheSettings['config']));
            $cache = CacheManager::getInstance($cacheSettings['driver']);
            return new Twitter($coClient, $cache);
        }
    ]);
};
