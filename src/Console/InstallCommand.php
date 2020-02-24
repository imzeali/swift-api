<?php
/**
 * User: babybus zhili
 * Date: 2019-06-14 09:25
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Console;


use Illuminate\Console\Command;
use SwiftApi\Model\ApiTablesSeeder;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swift-api:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the swift-api package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    public function initDatabase()
    {
        $this->call('migrate');

        $userModel = config('api.database.users_model');

        if ($userModel::count() == 0) {
            $this->call('db:seed', ['--class' => ApiTablesSeeder::class]);
        }
    }

    public function handle()
    {
        $this->initDatabase();

        $this->initApiDirectory();
    }


    protected function initApiDirectory()
    {
        $this->directory = config('api.directory');

        if (is_dir($this->directory)) {
            $this->line("<error>{$this->directory} directory already exists !</error> ");
            return;
        }

        $this->makeDir('/');
        $this->line('<info>Api directory was created:</info> ' . str_replace(base_path(), '', $this->directory));

        $this->makeDir('Controllers');

        $this->createAuthController();

        $this->createRoutesFile();


    }

    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/$path", 0755, true, true);
    }

    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__ . "/stubs/$name.stub");
    }

    protected function createAuthController()
    {
        $authController = $this->directory . '/Controllers/AuthController.php';
        $contents = $this->getStub('AuthController');

        $this->laravel['files']->put(
            $authController,
            str_replace('DummyNamespace', config('api.route.namespace'), $contents)
        );
        $this->line('<info>loginController file was created:</info> ' . str_replace(base_path(), '', $authController));
    }

    protected function createRoutesFile()
    {
        $routes_file = $this->directory . '/routes.php';
        $api_resources_file = $this->directory . '/api_resources.php';

        $routes_contents = $this->getStub('routes');
        $api_resources_contents = $this->getStub('api_resources');
        $this->laravel['files']->put($routes_file, str_replace('DummyNamespace', config('api.route.namespace'), $routes_contents));
        $this->laravel['files']->put($api_resources_file, str_replace('DummyApiResoures', '[]', $api_resources_contents));
        $this->line('<info>Routes file was created:</info> ' . str_replace(base_path(), '', $routes_file));
        $this->line('<info>Api Resources file was created:</info> ' . str_replace(base_path(), '', $api_resources_file));
    }

    protected function createResources()
    {
        $resources_directory = config('api.resources.directory');

        $this->laravel['files']->copyDirectory(__DIR__ ."/../Resources",$resources_directory);

        #前端资源文件编译
        $command = ['cd', $resources_directory];
        $command = array_merge($command, ['&&', 'npm', 'install', '--registry', 'https://registry.npm.taobao.org']);
        $command = array_merge($command, ['&&', 'npm', 'run', 'build']);
//        $command = array_merge($command, ['&&', 'npm', 'run', 'serve']);
        $command = implode(" ", $command);

        $process = new Process($command);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });

        $this->line('<info>Resources file was created:</info> ' . str_replace(base_path(), '', $resources_directory));
    }


}
