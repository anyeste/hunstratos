<?php

namespace Modules\HunFDM\Providers;

use Illuminate\Support\ServiceProvider;

class HunFDMServiceProvider extends ServiceProvider
{
    protected string $moduleName      = 'HunFDM';
    protected string $moduleNameLower = 'hunfdm';

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'hunfdm');
        $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/admin.php');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
