<?php

namespace App\Console\Commands;

use App\Logging\LogRunTime;
use Illuminate\Console\Command;

class TestLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        LogRunTime::set('test');
        sleep(1);
        LogRunTime::set('sub-test');
        sleep(2);
        LogRunTime::set('sub-test');
        LogRunTime::set('test');
        LogRunTime::log();
        return 0;
    }
}
