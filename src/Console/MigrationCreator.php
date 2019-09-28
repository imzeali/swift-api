<?php

namespace SwiftApi\Console;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
    /**
     * @var string
     */
    protected $bluePrint = '';

    /**
     * Create a new model.
     *
     * @param string $name
     * @param string $path
     * @param null $table
     * @param bool|true $create
     *
     * @return string
     */
    public function create($name, $path, $table = null, $create = true)
    {
        $this->ensureMigrationDoesntAlreadyExist($name);
        $path = $this->getPath($name, $path);
        $stub = $this->files->get(__DIR__ . '/stubs/migration.stub');
        $this->files->put($path, $this->populateStub($name, $stub, $table));
        $this->firePostCreateHooks($table);
        return $path;
    }

    /**
     * Populate stub.
     *
     * @param string $name
     * @param string $stub
     * @param string $table
     *
     * @return mixed
     */
    protected function populateStub($name, $stub, $table)
    {
        return str_replace(
            ['DummyClass', 'DummyTable', 'DummyStructure'],
            [$this->getClassName($name), $table, $this->bluePrint],
            $stub
        );
    }

    /**
     * Build the table blueprint.
     *
     * @param array $fields
     * @param string $keyName
     * @param bool|true $useTimestamps
     * @param bool|false $softDeletes
     *
     * @return $this
     * @throws \Exception
     *
     */
    public $without_length = [
        'text'
    ];

    public function buildBluePrint($fields = [], $keyName = 'id', $useTimestamps = true, $softDeletes = false)
    {

        $fields = collect($fields)->map(function ($field) {
            return json_decode($field, true);
        })->reject(function ($field) {
            return !isset($field['name']) || empty($field['name']);
        })->toArray();

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }
        $rows[] = "\$table->increments('$keyName');\n";


        foreach ($fields as $field) {
            $length = '';
            if (isset($field['length']) && $field['length'] && !in_array($field['type'], $this->without_length)) {
                $length = ",{$field['length']}";
            }
            $column = "\$table->{$field['type']}('{$field['name']}'{$length})";

            if (isset($field['key']) && $field['key']) {
                $column .= "->{$field['key']}()";
            }
            if (isset($field['default']) && $field['default']) {
                $column .= "->default('{$field['default']}')";
            }
            if (isset($field['comment']) && $field['comment'] && is_string($field['comment'])) {
                $column .= "->comment('{$field['comment']}')";
            }

            if (array_get($field, 'nullable') == true) {
                $column .= '->nullable()';
            }
            $rows[] = $column . ";\n";
        }
        if ($useTimestamps) {
            $rows[] = "\$table->timestamps();\n";
        }
        if ($softDeletes) {
            $rows[] = "\$table->softDeletes();\n";
        }


        $this->bluePrint = trim(implode(str_repeat(' ', 12), $rows), "\n");
        return $this;
    }
}
