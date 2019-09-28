<?php
/**
 * User: babybus zhili
 * Date: 2019-06-21 15:06
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class CreateModelCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'swift-api:create-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a swift-api model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('api.database.namespace');
    }

    protected function getNameInput()
    {
        return str_replace('/', '\\', trim($this->argument('name')));
    }

}
