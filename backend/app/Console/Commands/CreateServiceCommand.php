<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'サービスクラスを作成する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $serviceDir = base_path('app/Services');
        $serviceClass = "$serviceDir/$name.php";

        if (file_exists($serviceClass)) {
            $this->error("$name.php is already exists!");
            return;
        }

        $file = File::get('app/Console/TemplateService.php');
        $file = str_replace('TemplateService', $name, $file);
        $file = str_replace('namespace App\Console', 'namespace App\Services', $file);

        file_put_contents($serviceClass, $file);
    }
}
