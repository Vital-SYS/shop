<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        /*
         * Если мы в панели управления — страница будет получена из
         * БД по id, если в публичной части сайта — то по slug
         */
        Route::bind('page', function ($value) {
            $current = Route::currentRouteName();
            if ('page.show' == $current) { // публичная часть сайта
                return Page::whereSlug($value)->firstOrFail();
            }
            // панель управления сайта
            return Page::findOrFail($value);
        });
    }
}
