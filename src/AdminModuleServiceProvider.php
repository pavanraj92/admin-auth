<?php

namespace admin\admin_auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AdminModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes, views, migrations from the package
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'admin');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../resources/assets/backend' => public_path('backend'),
        ], 'admin_assets');


        // Optionally: Automatically publish once
        if (!file_exists(public_path('backend'))) {
            Artisan::call('vendor:publish', [
                '--tag' => 'admin_assets',
                '--force' => true,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('Modules/AdminAuth/resources/views'),
            __DIR__ . '/../src/Controllers' => base_path('Modules/AdminAuth/Controllers'),
            __DIR__ . '/../src/Models' => base_path('Modules/AdminAuth/Models'),
            __DIR__ . '/routes/web.php' => base_path('Modules/AdminAuth/routes/web.php'),
        ], 'admin_auth');

        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();

        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\admin_auth\Console\PublishAdminAuthModuleCommand::class,
                \admin\admin_auth\Console\CheckAdminAuthModuleStatusCommand::class,
                \admin\admin_auth\Console\DebugAdminAuthModuleCommand::class,
                \admin\admin_auth\Console\TestAdminAuthViewResolutionCommand::class,
            ]);
        }
    }
}
