<?php

namespace SwiftApi\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class CreateApiCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:create-api {name} 
        {--fields=*} 
        {--model} 
        {--migration} 
        {--request} 
        {--route} 
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a admin api';

//    public $composer;
//
//    public function __construct(Composer $composer)
//    {
//        parent::__construct();
//        $this->composer = $composer;
//    }

    public function handle()
    {

        $name = $this->argument('name');

        if ($this->option('model') == true) {
            $this->createModel();
        }

        if ($this->option('migration') == true) {
            $this->createMigration();
        }

        if ($this->option('request') == true) {
            $this->createRequest();
        }

        $this->createController();

        if ($this->option('route') == true) {
            $this->createRoute();
        }

//        $this->composer->dumpAutoloads();
    }

    public function createModel()
    {
        $this->call('swift-api:create-model', ['name' => $this->getModelWithPath()]);
    }

    public function createMigration()
    {
        $name = $this->getMigrationName();
        if ($this->option('fields')) {
            $this->call('swift-api:create-migration', [
                'name' => $name,
                '--table' => $name,
                '--fields' => $this->option('fields')
            ]);
        } else {
            $this->call('swift-api:create-migration', ['name' => $name]);
        }

    }

    public function createRoute()
    {
        $this->call('swift-api:create-route', [
            'path' => $this->getRoutePath(),
            '--controller' => $this->getControllerName()
        ]);

    }

    public function createRequest()
    {

        $this->call("swift-api:create-request", [
            'name' => $this->getRequestWithPath() . 'Store',
            '--fields' => $this->option('fields')
        ]);

        $this->call("swift-api:create-request", [
            'name' => $this->getRequestWithPath() . 'Update',
            '--fields' => $this->option('fields')
        ]);

    }

    public function createController()
    {
        $this->call('swift-api:create-controller', [
                'name' => $this->getControllerName(),
                '--model' => $this->getModelWithPath(),
                '--store_request' => $this->getRequestWithPath() . 'Store',
                '--update_request' => $this->getRequestWithPath() . 'Update'
            ]
        );

    }

    public function getRoutePath()
    {
        return ltrim(strtolower($this->argument('name')), "/");

    }

    public function getControllerName()
    {
        return ltrim($this->getClassNameWithPath() . 'Controller', '\\');
    }

    public function getMigrationName()
    {
        return $this->replaceUnderline($this->getRoutePath());
    }

    public function getClassNameWithPath()
    {
        $name = $this->replaceUnderline($this->argument('name'));
        return '\\' . $this->replaceSlash($name);
    }

    public function getModelWithPath()
    {
        return '\\' . config('api.database.namespace') . $this->getClassNameWithPath();
    }

    public function getRequestWithPath()
    {
        return '\\' . config('api.request.namespace') . $this->getClassNameWithPath();
    }

    public function replaceSlash($str)
    {
        return str_replace('_', '\\', str_replace('/', '\\', trim($str)));
    }

    public function replaceUnderline($str)
    {
        $str = str_replace('\\', '/', trim($str));
        $str = explode('/', $str);
        foreach ($str as &$s) {
            $s = Str::studly($s);
        }
        $str = implode('\\', $str);
        return $str;

    }

}
