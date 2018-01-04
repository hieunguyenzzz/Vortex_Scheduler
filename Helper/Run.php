<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Helper;

use Vortex\Scheduler\Model\Schedule;

class Run
{
    protected static $_registry;

    /**
     * Configure run
     */
    public static function configure($registry)
    {
        static $configured = false;
        self::$_registry = $registry;
        if (!$configured) {
            register_shutdown_function(['Vortex\Scheduler\Helper\Run', 'beforeDyingShutdown']);
            if (extension_loaded('pcntl')) {
                declare(ticks = 1);
                pcntl_signal(SIGINT, ['Vortex\Scheduler\Helper\Run', 'beforeDyingSigint']); // CTRL + C
                pcntl_signal(SIGTERM, ['Vortex\Scheduler\Helper\Run', 'beforeDyingSigterm']); // kill <pid>
            }
            $configured = true;
        }
    }

    public static function beforeDying($message = null, $exit = false)
    {
        $schedule = self::$_registry->registry('currently_running_schedule');
        if ($schedule !== null) {
            if ($message) {
                $schedule->addMessages($message);
            }
            $schedule
                ->setStatus(Schedule::STATUS_DIED)
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->save();
            self::$_registry->unregister('currently_running_schedule');
        }
        if ($exit) {
            return;
        }
    }

    /**
     * Callback
     */
    public static function beforeDyingShutdown()
    {
        self::beforeDying('TRIGGER: shutdown function', false);
    }

    /**
     * Callback
     */
    public static function beforeDyingSigint()
    {
        self::beforeDying('TRIGGER: Signal SIGINT', true);
    }

    /**
     * Callback
     */
    public static function beforeDyingSigterm()
    {
        self::beforeDying('TRIGGER: Signal SIGTERM', true);
    }
}
