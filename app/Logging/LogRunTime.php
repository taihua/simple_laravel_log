<?php

namespace App\Logging;

use Log;
use Ramsey\Uuid\Uuid;

/**
 * Class LogRunTime
 * @package App\Logging
 */
class LogRunTime
{
    /** @var null|LogEvent  */
    private static $rootLogEvent = null;
    private static $nowEvent = null;

    public static function set(string $action): void
    {
        if (self::$rootLogEvent === null) {
            $rootLogEvent = new LogEvent('root', null, LARAVEL_START);
            self::$rootLogEvent = $rootLogEvent;
            self::$nowEvent = $rootLogEvent;
        }


        if (self::$nowEvent->action === $action) {
            self::$nowEvent->save();
            self::$nowEvent = self::$nowEvent->parent;
            return ;
        }
        // 如果子log還沒關閉 就檢查是否要關閉父log
        $tmp = self::$nowEvent;
        while ($tmp = $tmp->parent) {
            if ($tmp->action === $action) {
                $tmp->save();
                self::$nowEvent = $tmp->parent;
                return ;
            }
        }

        self::$nowEvent = new LogEvent($action, self::$nowEvent);
    }

    public static function log(): void
    {
        $uuid = (string) Uuid::uuid4();

        if (self::$rootLogEvent) {
            self::$rootLogEvent->save();
            self::$rootLogEvent->log($uuid);
        }

        Log::info('total run time', [
            'uuid' => $uuid,
            'response_time' => (float) number_format(microtime(true) - LARAVEL_START, 4),
        ]);
    }
}
