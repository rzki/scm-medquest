<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
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
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
        {
            $name = $this->argument('name');
            $servicePath = app_path('Services');

            // Make sure Services directory exists
            if (!File::exists($servicePath)) {
                File::makeDirectory($servicePath, 0755, true);
            }

            // Create full path for the service class
            $className = ucfirst($name);
            $filePath = $servicePath . "/{$className}.php";

            // Avoid overwriting existing file
            if (File::exists($filePath)) {
                $this->error("Service {$className} already exists!");
                return;
            }

            // Template
            $template = <<<PHP
                <?php

                namespace App\Services;

                class {$className}
                {
                    //
                }
                PHP;

            File::put($filePath, $template);

            $this->info("Service {$className} created successfully.");
        }
}
