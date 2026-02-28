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
        Paginator::useTailwind();
        Paginator::useBootstrap();
        Passport::hashClientSecrets();
        View::composer('*', function ($view) {
            $storeId = session('selected_store') ?? session('selected_store_');
            $selected_store = $storeId ? Store::find($storeId) : null;
            $view->with('selected_store', $selected_store);
        });
        
    }
}
