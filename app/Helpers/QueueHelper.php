<?php

namespace App\Helpers;

class QueueHelper
{
    /**
     * Check if queue is running synchronously (no worker needed)
     */
    public static function isSync(): bool
    {
        return config('queue.default') === 'sync';
    }

    /**
     * Check if queue worker is required
     */
    public static function requiresWorker(): bool
    {
        return !self::isSync();
    }

    /**
     * Get queue driver name
     */
    public static function getDriver(): string
    {
        return config('queue.default', 'sync');
    }

    /**
     * Check if running on shared hosting (cPanel-like environment)
     */
    public static function isSharedHosting(): bool
    {
        // Check common shared hosting indicators
        $indicators = [
            // cPanel specific
            isset($_SERVER['cPanel']),
            // Limited shell access
            !function_exists('proc_open'),
            // Common shared hosting paths
            strpos(__DIR__, '/home/') === 0,
            // No supervisor/systemd
            !file_exists('/etc/supervisor/supervisord.conf'),
        ];

        return in_array(true, $indicators, true);
    }

    /**
     * Get recommended queue driver for current environment
     */
    public static function getRecommendedDriver(): string
    {
        if (self::isSharedHosting()) {
            return 'sync';
        }

        // Check if Redis is available
        if (extension_loaded('redis')) {
            return 'redis';
        }

        // Default to database for VPS/dedicated
        return 'database';
    }

    /**
     * Get queue status message for UI
     */
    public static function getStatusMessage(): string
    {
        $driver = self::getDriver();

        $messages = [
            'sync' => 'Import akan diproses langsung (tidak menggunakan queue)',
            'database' => 'Import menggunakan database queue (pastikan queue worker berjalan)',
            'redis' => 'Import menggunakan Redis queue (pastikan queue worker berjalan)',
        ];

        return $messages[$driver] ?? 'Queue driver: ' . $driver;
    }

    /**
     * Check if queue worker is running (for database/redis drivers)
     */
    public static function isWorkerRunning(): bool
    {
        if (self::isSync()) {
            return true; // No worker needed
        }

        // Check if there are recent processed jobs
        try {
            $recentJobs = \DB::table('jobs')
                ->where('created_at', '>', now()->subMinutes(5))
                ->count();

            // If jobs exist but not being processed, worker might be down
            if ($recentJobs > 0) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
