<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\{
    InquiryCommand,
    QuoteCommand,
    BidBillCommand,
    SupplierCommand,
    WorkermanCommand,
    ProjectCommand
};

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        InquiryCommand::class,
        BidBillCommand::class,
        QuoteCommand::class,
        WorkermanCommand::class,
        SupplierCommand::class,
        ProjectCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->Command("inquiry:operate opening")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("quote:operate not_attend")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("inquiry:operate deadline")->hourly()->withoutOverlapping(1);
        $schedule->Command("inquiry:operate expired")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("bidbill:operate deadline")->hourly()->withoutOverlapping(1);
        $schedule->Command("bidbill:operate expired")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("bidbill:operate evaluation")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("bidbill:operate be_about")->everyTenMinutes()->withoutOverlapping(1);
        $schedule->Command("project:operate publish")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("project:operate open")->cron('*/20 * * * *')->withoutOverlapping(1);
        $schedule->Command("project:operate quote")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("project:operate invitation")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("project:operate expired")->everyMinute()->withoutOverlapping(1);
        $schedule->Command("project:operate statistic")->cron('00 8,12,15,17,21,01 * * *')->withoutOverlapping(1);
    }

}
