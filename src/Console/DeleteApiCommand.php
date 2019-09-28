<?php

namespace SwiftApi\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class DeleteApiCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:delete-api {name} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a admin api';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');

        $answer = $this->ask("create a [{$name}] model ？Y/n");
        if ($answer == 'Y' || $answer == 'y' || $answer == null) {
            $is_create_model = true;
        }

        $answer = $this->ask("create a [{$name}] migration ？Y/n");
        if ($answer == 'Y' || $answer == 'y' || $answer == null) {
            $is_create_migration = true;
        }

        $answer = $this->ask("create a [{$name}] request ？Y/n");
        if ($answer == 'Y' || $answer == 'y' || $answer == null) {
            $is_create_request = true;
        }

        $answer = $this->ask("create a [{$name}] route ？Y/n");
        if ($answer == 'Y' || $answer == 'y' || $answer == null) {
            $is_create_route = true;
        }

        if ($is_create_model == true) {
            $this->createModel();
        }
        if ($is_create_migration == true) {
            $this->createMigration();
        }

        if ($is_create_migration == true) {
            $this->createRequest();
        }

        if ($is_create_route == true) {
            $this->createRoute();
        }

        $this->createController();

        $this->info("Api [{$this->argument('name')}] was created suceessfully");

    }

    public function createModel()
    {
        $this->call('swift-api:create-model', ['name' => $this->argument('name')]);
    }

    public function createMigration()
    {
        $this->call('make:migration', ['name' => 'create_' . strtolower($this->replaceUnderline($this->argument('name'))) . '_table']);

    }

    public function createRoute()
    {
        $this->call('swift-api:create-route', [
            'path' => strtolower($this->argument('name')),
            '--controller' => $this->replaceSlash($this->argument('name')) . 'Controller'
        ]);

    }

    public function createRequest()
    {
        $store_request_name = trim($this->argument('name')) . 'Store';
        $update_request_name = trim($this->argument('name')) . 'Update';

        $this->call("swift-api:create-request", ['name' => $store_request_name]);
        $this->call("swift-api:create-request", ['name' => $update_request_name]);

    }

    public function createController()
    {
        $name = $this->replaceSlash($this->argument('name'));
        $model = $this->getDummyModel($name);
        $store_request = '\\' . config('api.request.namespace') . '\\' . $name . 'Store';
        $update_request = '\\' . config('api.request.namespace') . '\\' . $name . 'Update';
        $this->call('swift-api:create-controller', [
                'name' => $name,
                '--model' => $model,
                '--store_request' => $store_request,
                '--update_request' => $update_request
            ]
        );

    }

    public function getDummyModel($name)
    {
        return '\\' . config('api.database.namespace') . '\\' . $name;
    }

    public function replaceSlash($str)
    {
        return str_replace('/', '\\', trim($str));
    }

    public function replaceUnderline($str)
    {
        return str_replace('/', '_', trim($str));

    }

}
