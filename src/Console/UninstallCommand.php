<?php

namespace SwiftApi\Console;

use Illuminate\Console\Command;

class UninstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the swift-api package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirm('Are you sure to uninstall swift-api?')) {
            return;
        }

        $this->removeFilesAndDirectories();

        $this->line('<info>Uninstalling swift-api!</info>');
    }

    /**
     * Remove files and directories.
     *
     * @return void
     */
    protected function removeFilesAndDirectories()
    {
        $this->laravel['files']->deleteDirectory(config('api.directory'));
        $this->laravel['files']->delete(config_path('api.php'));
        $this->laravel['files']->deleteDirectory(config('api.resources.directory'));
    }
}
