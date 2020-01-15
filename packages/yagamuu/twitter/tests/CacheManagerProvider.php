<?php

namespace yagamuu\TwitterClientForRtainjapan\Tests;

use Phpfastcache\CacheManager;

class CacheManagerProvider
{
    private static $cache_manager;

    private static $driver;

    private function __construct()
    {
    }

    public static function getCacheManager()
    {
        if (!isset(self::$cache_manager)) {
            if (!isset(self::$driver)) {
                self::$driver = 'files';
            }
            self::$cache_manager = CacheManager::getInstance(self::$driver);
        }
        return self::$cache_manager;
    }

    public static function setDriver($driver)
    {
        self::$driver = $driver;
    }
}