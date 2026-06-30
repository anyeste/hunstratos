<?php

namespace Modules\HunFDM\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\HunFDM\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
             ->namespace($this->moduleNamespace)
             ->group(module_path('HunFDM', '/Http/Routes/api.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
             ->namespace($this->moduleNamespace)
             ->group(module_path('HunFDM', '/Http/Routes/admin.php'));
    }
}
