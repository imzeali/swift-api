<?php


namespace SwiftApi\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use SwiftApi\Requests\PostScaffold;

class HelperController extends Controller
{
    public static $script = [
        'view',
        'model',
        'controller',
        'request',
        'migration',
        'route',
        'run_migration'
    ];

    public static $column_type_map = [
        'bigint' => 'bigInteger',
        'smallint'=>'smallInteger'
    ];

    public function postScaffold(PostScaffold $request)
    {
        $data = $request->validated();
        $script = $data['script'];
        $fields = $data['fields'];
        $table_name = $data['table_name'];
        return $this->buildApi($table_name, $script, $fields);
    }

    public function buildApi($table_name, $script, $fields)
    {
        $name = str_replace('_', '/', $table_name);
        $options = [];
        $options['name'] = $name;
        if (in_array('model', $script)) {
            $options['--model'] = true;
        }
        if (in_array('migration', $script)) {
            $options['--migration'] = true;
        }

        if (in_array('request', $script)) {
            $options['--request'] = true;
        }

        if (in_array('route', $script)) {
            $options['--route'] = true;
        }

        foreach ($fields as &$field) {
            $field = json_encode($field);
        }
        $options['--fields'] = $fields;
        return Artisan::call('swift-api:create-api', $options);
    }

    public function getScaffold()
    {
        return [
            'controller_namespace' => config('api.route.namespace'),
            'model_namespace' => config('api.database.namespace'),
            'script' => self::$script
        ];
    }

    public function getColumnType($type)
    {
        return array_get(self::$column_type_map, $type) ? self::$column_type_map[$type] : $type;
    }

    public function handOfGod(DatabaseToolController $controller)
    {
        $tables = $controller->showTable();
        $script = [
            'model',
            'controller',
            'request',
            'migration',
            'route',
        ];
        foreach ($tables as $table) {
            $columns = $controller->getTableColumns($table)->toArray();
            $fields = [];
            $fields_guarded = ['id', 'created_at', 'updated_at'];
            foreach ($columns as $column) {
                if (!in_array($column['name'], $fields_guarded)) {
                    $fields[] = [
                        'name' => $column['name'],
                        'type' => $this->getColumnType($column['type']),
                        'length' => $column['length'],
                        'default' => $column['default'],
                        'nullable' => !$column['notnull'],
                        'comment' => is_null($column['comment']) ?: $column['comment'],
                    ];
                }
            }
//            dump([$table, $script, $fields]);
            $this->buildApi($table, $script, $fields);
        }
    }


}
