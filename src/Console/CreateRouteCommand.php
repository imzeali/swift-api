<?php
/**
 * User: babybus zhili
 * Date: 2019-06-14 09:25
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Console;


use Illuminate\Console\Command;

class CreateRouteCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:create-route {path} {--controller=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a swift-api route';

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
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path');
        $controller = $this->option('controller');
        $stub_api_resources_path = $this->getStub();
        $api_resources_path = api_path('api_resources.php');
        $current_api_resources = $this->laravel['files']->get($api_resources_path);


        $rows = ["      \"{$path}\" => \"{$controller}\",\n];"];

        $dummy_api_resources = trim(implode(str_repeat(' ', 4), $rows), "\n");

        $content = str_replace("];", $dummy_api_resources, $current_api_resources);

        $this->laravel['files']->put($api_resources_path, $content);

        $this->info("Route created successfully.");

    }

    protected function getStub()
    {
        return __DIR__ . "/stubs/api_resources.stub";
    }
}
