<?php
/**
 * User: babybus zhili
 * Date: 2019-06-21 15:06
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Console;

use Illuminate\Console\GeneratorCommand;

class CreateControllerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:create-controller 
    {name} 
    {--model=}
    {--store_request=} 
    {--update_request=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a swift-api controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/ApiController.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('api.route.namespace');
    }

    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(
            [
                'DummyModel',
                'DummyStoreRequest',
                'DummyUpdateRequest',
            ],
            [
                $this->option('model'),
                $this->option('store_request'),
                $this->option('update_request'),
            ],
            $stub
        );
    }

    protected function getNameInput()
    {
        return str_replace('/', '\\', trim($this->argument('name')));
    }
}
