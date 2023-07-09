<?php

namespace App\Console;

use App\Jobs\RandomUser;
use App\Models\Post;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
        
        $schedule->command('model:prune', [
            '--model' => [Post::class]
        ])->daily();

        $schedule->job(new RandomUser())->everySixHours();
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
