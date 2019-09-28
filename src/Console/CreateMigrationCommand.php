<?php

namespace SwiftApi\Console;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Support\Str;
use Illuminate\Support\Composer;

class CreateMigrationCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'swift-api:create-migration {name}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}
        {--fields=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new swift-api migration file';

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool $create
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $migrationName = 'create_' . $name . '_table';
        if ($this->option('fields')) {
            $file = (new MigrationCreator(app('files')))->buildBluePrint(
                $this->option('fields'),
                'id',
                true,
                false
            )->create($migrationName, $this->getMigrationPath(), $table, $create);
        } else {
            $file = $this->creator->create(
                $migrationName, $this->getMigrationPath(), $table, $create
            );
        }

        if (!$this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Migration:</info> {$file}");
    }

}
