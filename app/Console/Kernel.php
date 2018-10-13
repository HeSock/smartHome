<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 定时任务必须在此添加才能生效
//        'App\Service\Channel\getRedisData',
//        \App\Service\Channel\getRedisData::class,
//        \App\Service\Channel\StatData::class,
        \App\Console\Commands\GetRedisData::class,
        \App\Console\Commands\statData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//         $schedule->command('GetRedisData')
//             ->everyMinute(); // 每分钟执行一次
//             ->withoutOverlapping();  可避免任务重叠
//        $schedule->command('TestDemo')
//            ->everyMinute();
//         $schedule->command('StatData')
//            ->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
