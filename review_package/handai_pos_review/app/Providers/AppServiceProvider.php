<?php

namespace App\Providers;
use App\Models\Store;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CekRole;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('cekrole', CekRole::class);
        Route::aliasMiddleware('role', CheckRole::class);
        Route::aliasMiddleware('cors', CorsMiddleware::class);
        Paginator::useBootstrap();
        Passport::hashClientSecrets();

        // Cache Store::find per request — avoids N+1 query on every partial/component render
        View::composer('*', function ($view) {
            static $resolved = false;
            static $cachedStore = null;

            if (!$resolved) {
                $storeId = session('selected_store') ?? session('selected_store_');
                try {
                    $cachedStore = $storeId ? Store::find($storeId) : null;
                } catch (\Throwable $e) {
                    $cachedStore = null;
                }
                $resolved = true;
            }

            $view->with('selected_store', $cachedStore);
        });
        
    }
}
