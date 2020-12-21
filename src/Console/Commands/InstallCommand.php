<?php

namespace Katra\Katra\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'katra:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Katra';

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
     * @return int
     */
    public function handle()
    {
        $this->callSilent('vendor:publish', [
            '--provider' => 'Katra\Katra\Providers\KatraServiceProvider',
            '--tag'      => 'assets',
            '--force'    => true,
        ]);

        copy(__DIR__.'/../../../stubs/app/Models/User.php', app_path('Models/User.php'));

        $this->callSilent('migrate');
    }
}
