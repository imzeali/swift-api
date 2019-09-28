<?php


namespace SwiftApi\Console;


use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateViewCommand extends Command
{

    protected $signature = 'swift-api:create-view {name} 
        {--fields=*} 
        {--api} 
        {--route} 
        {--view} 
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a vue view';


    public function handle()
    {

        $directory = config('api.resources.directory');



    }

}
