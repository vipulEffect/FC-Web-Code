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
        Commands\DailyQuote::class,
		Commands\TrialNotify::class,
		Commands\MonthlyNotify::class,
		Commands\WeeklyWallpaper::class,
		
		
		Commands\DailyQuoteTest::class,
		Commands\TrialNotifyTest::class,
		Commands\MonthlyNotifyTest::class,
		Commands\WeeklyWallpaperTest::class,
		
		Commands\AndroidSubsInfo::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		///////////////////////////////////////
		///////////////////////////////////////
		//Start PN Live purpose////////////////
		///////////////////////////////////////
		///////////////////////////////////////
		
        //1-Cron For wallpaper change at Monday at 6:30AM weekly
		//$schedule->command('quote:daily')->weeklyOn(1, '1:00'); //at 6:30AM weekly 
		$schedule->command('quote:daily')->twiceMonthly(1, 15, '1:00'); //at 6:30AM
		
		//2-Cron For notify for users which trail period end before 24 hrs	
		$schedule->command('trial:notify')->dailyAt('1:00'); //at 6:30AM daily
		
		//3-Cron For notify for users which monthly period end before 24 hrs	
		$schedule->command('monthly:notify')->dailyAt('1:30'); //at 7:00AM daily
		
		//4-Cron For notify Weekly wallpaper change at Monday at 7AM weekly
		//$schedule->command('wallpaper:weekly')->weeklyOn(1, '1:30'); //at 7AM weekly
		$schedule->command('wallpaper:weekly')->twiceMonthly(1, 15, '1:30'); //at 7AM
		
		///////////////////////////////////////
		///////////////////////////////////////
		//End PN Live purpose//////////////////
		///////////////////////////////////////
		///////////////////////////////////////
		
		
		
		///////////////////////////////////////
		////////////////////////////////////////
		//Start PN Testing purpose//////////////
		////////////////////////////////////////
		////////////////////////////////////////
		
		//1-Cron For Weekly wallpaper change at every 15 min
		//$schedule->command('quote:dailyTest')->everyFifteenMinutes();
		
		//2-Cron For notify for users which trail period end before 6 min	
		//$schedule->command('trial:notifyTest')->everyMinute();
		
		//3-Cron For notify for users which monthly period end before 29 min	
		//$schedule->command('monthly:notifyTest')->everyMinute();
		
		//4-Cron For notify Weekly wallpaper change at every 15 min
		//$schedule->command('wallpaper:weeklyTest')->everyFifteenMinutes();
		
		///////////////////////////////////////
		///////////////////////////////////////
		//End PN Testing purpose///////////////
		///////////////////////////////////////
		///////////////////////////////////////
		
		
		$schedule->command('android:subsInfo')->everyFifteenMinutes();
	}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
		require base_path('routes/console.php');
    }
}