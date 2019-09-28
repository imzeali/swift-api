<?php
/**
 * User: babybus zhili
 * Date: 2019-06-25 14:46
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DatabaseToolController extends Controller
{
    public static $db_types = [
        'string', 'integer', 'text', 'float', 'double', 'decimal', 'boolean', 'date', 'time',
        'dateTime', 'timestamp', 'char', 'mediumText', 'longText', 'tinyInteger', 'smallInteger',
        'mediumInteger', 'bigInteger', 'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger',
        'unsignedInteger', 'unsignedBigInteger', 'enum', 'json', 'jsonb', 'dateTimeTz', 'timeTz',
        'timestampTz', 'nullableTimestamps', 'binary', 'ipAddress', 'macAddress',
    ];
    public static $key_types = [
      'index',
      'unique'
    ];

    public $doctrineTypeMapping = [
        'string' => [
            'enum', 'geometry', 'geometrycollection', 'linestring',
            'polygon', 'multilinestring', 'multipoint', 'multipolygon',
            'point',
        ],
    ];

    public function showTable()
    {
        $tables_guarded = ['migrations'];
        $tables = DB::select('SHOW TABLES');
        $key = "Tables_in_" . config('database.connections.mysql.database');
        $tables = collect($tables)->map(function ($item) use ($key) {
            return $item->$key;
        })->reject(function ($table) use ($tables_guarded, $key) {
            return in_array($table, $tables_guarded);
        });
        return array_values($tables->toArray());
    }

    public function getTableColumns($table_name)
    {
        if (!DB::isDoctrineAvailable()) {
            throw new \Exception(
                'You need to require doctrine/dbal: ~2.3 in your own composer.json to get database columns. '
            );
        }
        $table = DB::getTablePrefix().$table_name;

        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schema */
        $schema = DB::getDoctrineSchemaManager($table);

        // custom mapping the types that doctrine/dbal does not support
        $databasePlatform = $schema->getDatabasePlatform();
        foreach ($this->doctrineTypeMapping as $doctrineType => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $databasePlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }
        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }
        $columns = array_values($schema->listTableColumns($table, $database));
        $columns_arr = collect($columns)->map(function ($column){
            $type = $column->getType()->getName();
            $column = $column->toArray();
            $column['type']=$type;
            return $column;
        });

        return $columns_arr;
    }
}
