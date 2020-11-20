<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoadSqlAdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dw:loadSqlAdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load SQL ADB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'SqlAdbSeeder',
            '--force' => true,
        ]);
    }
}
