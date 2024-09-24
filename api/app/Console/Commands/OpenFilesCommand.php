<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OpenFilesCommand extends Command
{
    protected $signature = 'app:open-files {name}';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info('Opening files... 🪄');

        $name = $this->argument('name');

        $this->openMigration($name);
        $this->openModel($name);
        $this->openFactory($name);
        $this->openSeeder($name);
        $this->openActions($name);
        $this->openResource($name);
        $this->openPolicy($name);
        $this->openRequest($name);
        $this->openController($name);
        $this->openActionsTest($name);
        $this->openAPITest($name);
    }

    public function openMigration($name)
    {
        $name = Str::snake($name);
        $name = Str::plural($name);

        $migrationsPath = database_path('migrations');

        $migrations = collect(File::allFiles($migrationsPath))
            ->filter(function ($file) use ($name) {
                return Str::contains($file->getFilename(), $name);
            })
            ->map(function ($file) {
                return $file->getPathname();
            });

        foreach ($migrations as $migration) {
            if (! File::exists($migration)) {
                $this->error("Migration file not found: {$migration}");

                continue;
            } else {
                $this->info("Opening: {$migration}");
                $this->openInVSCode($migration);
            }
        }
    }

    public function openModel($name)
    {
        $modelPath = app_path("Models/{$name}.php");

        if (! File::exists($modelPath)) {
            $this->error("Model file not found: {$modelPath}");

            return;
        } else {
            $this->info("Opening: {$modelPath}");
            $this->openInVSCode($modelPath);
        }
    }

    public function openFactory($name)
    {
        $factoryPath = database_path("factories/{$name}Factory.php");

        if (! File::exists($factoryPath)) {
            $this->error("Factory file not found: {$factoryPath}");

            return;
        } else {
            $this->info("Opening: {$factoryPath}");
            $this->openInVSCode($factoryPath);
        }
    }

    public function openSeeder($name)
    {
        $seederPath = database_path("seeders/{$name}TableSeeder.php");

        if (! File::exists($seederPath)) {
            $this->error("Seeder file not found: {$seederPath}");

            return;
        } else {
            $this->info("Opening: {$seederPath}");
            $this->openInVSCode($seederPath);
        }
    }

    public function openActions($name)
    {
        $ActionsPath = app_path("Actions/{$name}/{$name}Actions.php");

        if (! File::exists($ActionsPath)) {
            $this->error("Actions file not found: {$ActionsPath}");

            return;
        } else {
            $this->info("Opening: {$ActionsPath}");
            $this->openInVSCode($ActionsPath);
        }
    }

    public function openResource($name)
    {
        $resourcePath = app_path("Http/Resources/{$name}Resource.php");

        if (! File::exists($resourcePath)) {
            $this->error("Resource file not found: {$resourcePath}");

            return;
        } else {
            $this->info("Opening: {$resourcePath}");
            $this->openInVSCode($resourcePath);
        }
    }

    public function openPolicy($name)
    {
        $policyPath = app_path("Policies/{$name}Policy.php");

        if (! File::exists($policyPath)) {
            $this->error("Policy file not found: {$policyPath}");

            return;
        } else {
            $this->info("Opening: {$policyPath}");
            $this->openInVSCode($policyPath);
        }
    }

    public function openRequest($name)
    {
        $requestPath = app_path("Http/Requests/{$name}Request.php");

        if (File::exists($requestPath)) {
            $this->info("Opening: {$requestPath}");
            $this->openInVSCode($requestPath);
        } else {
            $this->error("Request file not found: {$requestPath}");
        }
    }

    public function openController($name)
    {
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");

        if (! File::exists($controllerPath)) {
            $this->error("Controller file not found: {$controllerPath}");

            return;
        } else {
            $this->info("Opening: {$controllerPath}");
            $this->openInVSCode($controllerPath);
        }
    }

    public function openActionsTest($name)
    {
        $createTest = base_path("tests/Unit/Actions/{$name}Actions/{$name}ActionsTest.php");

        if (! File::exists($createTest)) {
            $this->error("Test file not found: {$createTest}");
        } else {
            $this->info("Opening: {$createTest}");
            $this->openInVSCode($createTest);
        }

        $readTest = base_path("tests/Unit/Actions/{$name}Actions/{$name}ActionsReadTest.php");

        if (! File::exists($readTest)) {
            $this->error("Test file not found: {$readTest}");
        } else {
            $this->info("Opening: {$readTest}");
            $this->openInVSCode($readTest);
        }

        $editTest = base_path("tests/Unit/Actions/{$name}Actions/{$name}ActionsEditTest.php");

        if (! File::exists($editTest)) {
            $this->error("Test file not found: {$editTest}");
        } else {
            $this->info("Opening: {$editTest}");
            $this->openInVSCode($editTest);
        }

        $deleteTest = base_path("tests/Unit/Actions/{$name}Actions/{$name}ActionsDeleteTest.php");

        if (! File::exists($deleteTest)) {
            $this->error("Test file not found: {$deleteTest}");
        } else {
            $this->info("Opening: {$deleteTest}");
            $this->openInVSCode($deleteTest);
        }
    }

    public function openAPITest($name)
    {
        $createTest = base_path("tests/Feature/API/{$name}API/{$name}APICreateTest.php");

        if (! File::exists($createTest)) {
            $this->error("Test file not found: {$createTest}");
        } else {
            $this->info("Opening: {$createTest}");
            $this->openInVSCode($createTest);
        }

        $readTest = base_path("tests/Feature/API/{$name}API/{$name}APIReadTest.php");

        if (! File::exists($readTest)) {
            $this->error("Test file not found: {$readTest}");

            return;
        } else {
            $this->info("Opening: {$readTest}");
            $this->openInVSCode($readTest);
        }

        $editTest = base_path("tests/Feature/API/{$name}API/{$name}APIEditTest.php");

        if (! File::exists($editTest)) {
            $this->error("Test file not found: {$editTest}");
        } else {
            $this->info("Opening: {$editTest}");
            $this->openInVSCode($editTest);
        }

        $deleteTest = base_path("tests/Feature/API/{$name}API/{$name}APIDeleteTest.php");

        if (! File::exists($deleteTest)) {
            $this->error("Test file not found: {$deleteTest}");
        } else {
            $this->info("Opening: {$deleteTest}");
            $this->openInVSCode($deleteTest);
        }
    }

    protected function openInVSCode($filePath)
    {
        $command = 'code '.escapeshellarg($filePath);
        exec($command);
    }
}
