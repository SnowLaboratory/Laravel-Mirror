<?php

namespace SnowBuilds\Mirror\Console;

use Illuminate\Console\Command;

class SyncMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mirror:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ranking matrix for models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
