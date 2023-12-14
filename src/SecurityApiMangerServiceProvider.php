<?php
namespace Hso\TestApi;

use Hso\TestApi\Command\DeleteReportData;
use Hso\TestApi\Command\Report;
use Hso\TestApi\Middleware\SecurityApiManagerMiddleware;
use Illuminate\Support\ServiceProvider;

class SecurityApiMangerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register your middleware
        $this->app['router']->aliasMiddleware('security-api-manger', SecurityApiManagerMiddleware::class);

        // Register your command
        $this->commands([
            Report::class,
            DeleteReportData::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/apimanger.php' => config_path('apimanger.php'),
            __DIR__.'/../config/database.php' => config_path('database.php'),
        ]);
    }

    public function boot()
    {
        // Register your configuration file
        $this->mergeConfigFrom(__DIR__.'/../config/apimanger.php', 'apimanger');
        $this->mergeConfigFrom(__DIR__ . '/../config/database.php', 'database');

    }

}