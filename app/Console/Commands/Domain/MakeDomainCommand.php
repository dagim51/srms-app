<?php

namespace App\Console\Commands\Domain;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDomainCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:domain {name : The name of the domain (e.g., Identity)} 
                            {--force : Overwrite existing files}
                            {--api-only : Only create API controllers}
                            {--web-only : Only create Web controllers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new domain with all required structure';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $force = $this->option('force');
        $apiOnly = $this->option('api-only');
        $webOnly = $this->option('web-only');

        $domainName = Str::studly($name);
        $domainPath = app_path("Domains/{$domainName}");

        if ($this->files->exists($domainPath) && ! $force) {
            $this->error("Domain [{$domainName}] already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("Creating domain: {$domainName}");

        // Create directory structure
        $directories = [
            'Models',
            'Services',
            'Events',
            'Listeners',
            'Policies',
            'Exceptions',
            'Http/Controllers/Api/V1',
            'Http/Controllers/Web',
            'Http/Resources',
            'Http/Requests',
            'routes',
        ];

        foreach ($directories as $dir) {
            $path = "app/Domains/{$domainName}/{$dir}";
            if (! $this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true, true);
                $this->line("  Created: {$path}");
            }
        }

        // Create route files
        if (! $webOnly) {
            $this->createRouteFile('api', $domainName);
        }
        if (! $apiOnly) {
            $this->createRouteFile('web', $domainName);
        }

        // Create base files
        $this->createBaseController($domainName);
        $this->createBaseService($domainName);
        $this->createBasePolicy($domainName);
        $this->createBaseException($domainName);
        $this->createBaseEvent($domainName);

        // Create factory directory
        $factoryPath = "database/factories/Domains/{$domainName}/Models";
        if (! $this->files->isDirectory($factoryPath)) {
            $this->files->makeDirectory($factoryPath, 0755, true, true);
            $this->line("  Created: {$factoryPath}");
        }

        // Create test directory
        $testPath = "tests/Feature/Domains/{$domainName}";
        if (! $this->files->isDirectory($testPath)) {
            $this->files->makeDirectory($testPath, 0755, true, true);
            $this->line("  Created: {$testPath}");
        }

        $this->info("Domain [{$domainName}] created successfully!");
        $this->newLine();
        $this->line('Next steps:');
        $this->line("  1. Add models to app/Domains/{$domainName}/Models/");
        $this->line("  2. Add services to app/Domains/{$domainName}/Services/");
        $this->line("  3. Add controllers to app/Domains/{$domainName}/Http/Controllers/");
        $this->line("  4. Add routes to app/Domains/{$domainName}/routes/");
        $this->line('  5. Run: composer dump-autoload');

        return self::SUCCESS;
    }

    /**
     * Create route file.
     */
    protected function createRouteFile(string $type, string $domainName): void
    {
        $path = "app/Domains/{$domainName}/routes/{$type}.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = $type === 'api'
            ? $this->getApiRouteTemplate($domainName)
            : $this->getWebRouteTemplate($domainName);

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Create base controller.
     */
    protected function createBaseController(string $domainName): void
    {
        $path = "app/Domains/{$domainName}/Http/Controllers/Controller.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = <<<PHP
<?php

namespace App\Domains\\{$domainName}\\Http\Controllers;

use App\Domains\Shared\Http\Controllers\DomainController as BaseController;

class Controller extends BaseController
{
    //
}
PHP;

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Create base service.
     */
    protected function createBaseService(string $domainName): void
    {
        $path = "app/Domains/{$domainName}/Services/{$domainName}Service.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = <<<PHP
<?php

namespace App\Domains\\{$domainName}\\Services;

use App\Domains\Shared\Services\DomainService;

class {$domainName}Service extends DomainService
{
    public static function domain(): string
    {
        return '{$domainName}';
    }
}
PHP;

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Create base policy.
     */
    protected function createBasePolicy(string $domainName): void
    {
        $path = "app/Domains/{$domainName}/Policies/{$domainName}Policy.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = <<<PHP
<?php

namespace App\Domains\\{$domainName}\\Policies;

use App\Domains\Shared\Policies\DomainPolicy;
use App\Models\User;

class {$domainName}Policy extends DomainPolicy
{
    public static function domain(): string
    {
        return '{$domainName}';
    }
}
PHP;

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Create base exception.
     */
    protected function createBaseException(string $domainName): void
    {
        $path = "app/Domains/{$domainName}/Exceptions/{$domainName}Exception.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = <<<PHP
<?php

namespace App\Domains\\{$domainName}\\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class {$domainName}Exception extends DomainException
{
    public static function domain(): string
    {
        return '{$domainName}';
    }
}
PHP;

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Create base event.
     */
    protected function createBaseEvent(string $domainName): void
    {
        $path = "app/Domains/{$domainName}/Events/{$domainName}Event.php";

        if ($this->files->exists($path)) {
            return;
        }

        $content = <<<PHP
<?php

namespace App\Domains\\{$domainName}\\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class {$domainName}Event extends DomainEvent
{
    public static function domain(): string
    {
        return '{$domainName}';
    }
}
PHP;

        $this->files->put($path, $content);
        $this->line("  Created: {$path}");
    }

    /**
     * Get API route template.
     */
    protected function getApiRouteTemplate(string $domainName): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Domains\\{$domainName}\\Http\Controllers\Api\V1;

Route::prefix('{$domainName}')
    ->middleware(['auth:sanctum', 'org.scope'])
    ->group(function () {
        // Add your API routes here
        // Route::apiResource('resource', V1\ResourceController::class);
    });
PHP;
    }

    /**
     * Get Web route template.
     */
    protected function getWebRouteTemplate(string $domainName): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Domains\\{$domainName}\\Http\Controllers\Web;

Route::prefix('dashboard/{$domainName}')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
PHP;
    }
}
